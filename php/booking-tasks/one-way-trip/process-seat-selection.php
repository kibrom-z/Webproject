<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

$errors = array();

try {

    require('../../php/mysqli-connect.php');
    require('../../php/error-style2.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Check if there are repeated seat numbers
        foreach ($_SESSION['reservation-ids'] as $reservation_id) {

            $seat_num = filter_var($_POST['seat-num' . $reservation_id . ''], FILTER_SANITIZE_STRING);

            foreach ($_SESSION['reservation-ids'] as $check_id) {
                if ($reservation_id !== $check_id) {
                    $check_seat = filter_var($_POST['seat-num' . $check_id . ''], FILTER_SANITIZE_STRING);
                    if ($seat_num == $check_seat) {
                        $errors[] = 'Identical seat numbers found.';
                        break 2; // breaks out of the two loops
                    }
                }
            }
        }
        if (empty($errors)) {

            foreach ($_SESSION['reservation-ids'] as $resv_id) {

                $seat = filter_var($_POST['seat-num' . $resv_id . ''], FILTER_SANITIZE_STRING);

                $query = "UPDATE reservations SET seat_number=? WHERE reservation_id=? LIMIT 1";
                $q = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q, $query);
                mysqli_stmt_bind_param($q, 'ii', $seat, $resv_id);
                mysqli_stmt_execute($q);

                if (mysqli_stmt_affected_rows($q) == 1) {
                    // Do nothing
                } else {
                    // Public message:
                    $errorstring = "<p>System Error<br />You could not be registered due ";
                    $errorstring .= "to a system error. We apologize for any inconvenience.</p>";
                    print "<p style='$error_style'>$errorstring</p>";
                    mysqli_close($dbcon);
                    exit();
                }
            }
            header('location: payment.php');
        }
    }

    //Retrieve the bus plate number to get the carrying capacity
    $query2 = "SELECT bus_plate_number FROM trips WHERE trip_id=?";
    $q2 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q2, $query2);
    mysqli_stmt_bind_param($q2, "i", $_SESSION['selected-trip-id']);
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

    foreach ($_SESSION['reservation-ids'] as $reservation) {

        $query5 = "SELECT first_name FROM reservations WHERE reservation_id=?";
        $q5 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q5, $query5);
        mysqli_stmt_bind_param($q5, "i", $reservation);
        mysqli_stmt_execute($q5);
        $result5 = mysqli_stmt_get_result($q5);
        $row5 = mysqli_fetch_array($result5, MYSQLI_NUM);
        $first_name = $row5[0];

        // Print available seats
        print '
        <div class="input-group mb-3 w-75">
            <label for="seat-num' . $reservation . '" class="form-label w-50 pt-1 pe-3 text-end">' . $first_name . '\'s seat</label>
            <select name="seat-num' . $reservation . '" id="seat-num' . $reservation . '" class="form-select text-center">';

        for ($i = 1; $i <= $carrying_capacity; $i++) {

            // Check if $i is reserved or not from the reservation table using the trip id
            $query = "SELECT seat_number FROM reservations WHERE trip_id=? AND seat_number=? AND accessibility=?";
            $q = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q, $query);
            mysqli_stmt_bind_param($q, "iis", $_SESSION['selected-trip-id'], $i, $accessibility);
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
    }
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
if (!empty($errors)) {
    $errorstring = "ERROR! The following error(s) occurred:<br><br>";
    foreach ($errors as $msg) { // Print each error.
        $errorstring .= "— $msg<br>\n";
    }
    $errorstring .= "<br>Please try again.<br>";
    print "<p style='$error_style'>$errorstring</p>";
}
