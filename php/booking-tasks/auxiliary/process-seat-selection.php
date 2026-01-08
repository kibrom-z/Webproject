<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

try {

    require('../../php/mysqli-connect.php');
    require('../../php/error-style2.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $seat = filter_var($_POST['seat-num'], FILTER_SANITIZE_STRING);

        $query = "UPDATE reservations SET seat_number=? WHERE reservation_id=? LIMIT 1";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, 'ii', $seat, $_SESSION['reservation-id']);
        mysqli_stmt_execute($q);

        if (mysqli_stmt_affected_rows($q) == 1) {
            // Increase the bookings value for the new trip by 1
            // Retrieve the current booking number
            $retrieve_query4 = "SELECT bookings FROM trips WHERE trip_id=?";
            $prepared4 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($prepared4, $retrieve_query4);
            mysqli_stmt_bind_param($prepared4, "i", $_SESSION['to-be-postponed-to-trip-id']);
            mysqli_stmt_execute($prepared4);
            $result4 = mysqli_stmt_get_result($prepared4);
            $row4 = mysqli_fetch_array($result4, MYSQLI_NUM);
            $bookings = $row4[0];
            $new_bookings = $bookings + 1;

            // Increase the number of bookings by the number of reservations
            $query5 = "UPDATE trips SET bookings=? WHERE trip_id=?";
            $q5 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q5, $query5);
            mysqli_stmt_bind_param($q5, 'ii', $new_bookings, $_SESSION['to-be-postponed-to-trip-id']);
            mysqli_stmt_execute($q5);
        } else {
            // Public message:
            $errorstring = "<p>System Error<br />You could not be registered due ";
            $errorstring .= "to a system error. We apologize for any inconvenience.</p>";
            print "<p style='$error_style'>$errorstring</p>";
            mysqli_close($dbcon);
            exit();
        }

        // If there is tariff change, send to payment otherwise, finish process

        // Retrieve current route tariff
        $query = "SELECT tariff FROM routes WHERE route_id=?";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, "i", $_SESSION['route-id']);
        mysqli_stmt_execute($q);
        $output = mysqli_stmt_get_result($q);
        $single_output = mysqli_fetch_array($output, MYSQLI_NUM);
        $current_tariff = $single_output[0];

        // Retrieve paid tariff for the trip to be postponed
        $query = "SELECT trip_id FROM reservations WHERE reservation_id=?";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, 'i', $_SESSION['postpone-reservation-id']);
        mysqli_stmt_execute($q);
        $result = mysqli_stmt_get_result($q);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);
        $trip_id = $row[0];

        $query2 = "SELECT tariff FROM trips WHERE trip_id=?";
        $q2 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q2, $query2);
        mysqli_stmt_bind_param($q2, 'i', $trip_id);
        mysqli_stmt_execute($q2);
        $result2 = mysqli_stmt_get_result($q2);
        $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
        $paid_tariff = $row2[0];

        //print "Paid tariff: " . $paid_tariff . "<br>";
        //print "Current tariff: " . $current_tariff . "<br>";

        if ($current_tariff == $paid_tariff) {

            $payment_status = "completed";

            // Change payment status from pending to completed
            $query4 = "UPDATE reservations SET payment_status=? WHERE reservation_id=?";
            $q4 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q4, $query4);
            mysqli_stmt_bind_param($q4, 'si', $payment_status, $_SESSION['reservation-id']);
            mysqli_stmt_execute($q4);

            header('location: ../../php/booking-tasks/auxiliary/successful-trip-postpone.php');
        } else if ($current_tariff > $paid_tariff) {
            header('location: postpone-payment.php');
        } else { // If the current tariff is less than the paid tariff
            // Create refund request

            $reason = "postpone discount";
            $postpone_time = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
            $postpone_time = $postpone_time->format('y-m-d H:i:s');
            $price = $paid_tariff - $current_tariff;
            $refund_status = "pending";

            $query9 = "INSERT INTO refund_requests (refund_request_id, reason, issued_date, passenger_id, reservation_id, refund_amount, refund_status) "; // Step 3
            $query9 .= "VALUES(' ', ?, ?, ?, ?, ?, ?)";
            $q9 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q9, $query9);
            mysqli_stmt_bind_param($q9, 'ssiids', $reason, $postpone_time, $_SESSION['user_id'], $_SESSION['postpone-reservation-id'], $price, $refund_status);
            mysqli_stmt_execute($q9);

            // Change payment status from pending to completed

            $payment_status = "completed";

            $query4 = "UPDATE reservations SET payment_status=? WHERE reservation_id=?";
            $q4 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q4, $query4);
            mysqli_stmt_bind_param($q4, 'si', $payment_status, $_SESSION['reservation-id']);
            mysqli_stmt_execute($q4);

            header('location: ../../php/booking-tasks/auxiliary/successful-trip-postpone.php');
        }
    }

    //Retrieve the bus plate number to get the carrying capacity
    $query2 = "SELECT bus_plate_number FROM trips WHERE trip_id=?";
    $q2 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q2, $query2);
    mysqli_stmt_bind_param($q2, "i", $_SESSION['to-be-postponed-to-trip-id']);
    mysqli_stmt_execute($q2);
    $result2 = mysqli_stmt_get_result($q2);
    $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
    $plate_number = $row2[0];

    $query3 = "SELECT carrying_capacity FROM buses WHERE plate_number=?";
    $q3 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q3, $query3);
    mysqli_stmt_bind_param($q3, "i", $plate_number);
    mysqli_stmt_execute($q3);
    $result3 = mysqli_stmt_get_result($q3);
    $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
    $carrying_capacity = $row3[0];

    if ($carrying_capacity == 49) {
        print '<img src="../../images/bus-seat-49-map.jpg" alt="seat map" class="mb-5" />';
    } else if ($carrying_capacity == 50) {
        print '<img src="../../images/bus-seat-50-map.jpg" alt="seat map" class="mb-5" />';
    } else {
        print '<img src="../../images/bus-seat-51-map.jpg" alt="seat map" class="mb-5" />';
    }

    $accessibility = "accessible";

    $query5 = "SELECT first_name FROM reservations WHERE reservation_id=?";
    $q5 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q5, $query5);
    mysqli_stmt_bind_param($q5, "i", $_SESSION['reservation-id']);
    mysqli_stmt_execute($q5);
    $result5 = mysqli_stmt_get_result($q5);
    $row5 = mysqli_fetch_array($result5, MYSQLI_NUM);
    $first_name = $row5[0];

    // Print available seats
    print '
    <div class="input-group mb-3 w-75">
        <label for="seat-num" class="form-label w-50 pt-1 pe-3 text-end">' . $first_name . '\'s seat</label>
        <select name="seat-num" id="seat-num" class="form-select text-center">';

    for ($i = 1; $i <= $carrying_capacity; $i++) {

        // Check if $i is reserved or not from the reservation table using the trip id
        $query = "SELECT seat_number FROM reservations WHERE trip_id=? AND seat_number=? AND accessibility=?";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, "iis", $_SESSION['to-be-postponed-to-trip-id'], $i, $accessibility);
        mysqli_stmt_execute($q);
        $result = mysqli_stmt_get_result($q);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);

        if (mysqli_num_rows($result) == 1) { // The seat is booked
            // Don't print this seat number
        } else { // The seat is free
            // Print this seat number
            print '<option value=' . $i . '>' . $i . '</option>';
        }
    }

    print '
            </select>
        </div>';
} catch (Exception $e) {
    print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
