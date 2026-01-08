<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

// The code looks for a valid user ID, either through GET or POST:
if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
    $reservation_id = htmlspecialchars($_GET['id'], ENT_QUOTES);
} elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
    $reservation_id = htmlspecialchars($_POST['id'], ENT_QUOTES);
} else { // No valid ID, kill the script.
    print '<p>This page has been accessed in error.</p>';
    exit();
}

$errors = array();

try {

    require('../../php/mysqli-connect.php');
    require('../../php/error-style2.php');

    // Has the form been submitted?
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $query5 = "SELECT trip_id FROM reservations WHERE reservation_id=?";
        $q5 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q5, $query5);
        mysqli_stmt_bind_param($q5, "i", $reservation_id);
        mysqli_stmt_execute($q5);
        $result5 = mysqli_stmt_get_result($q5);
        $row5 = mysqli_fetch_array($result5, MYSQLI_NUM);
        $trip_identifier = $row5[0];

        $query6 = "SELECT departure_date, departure_time, route_id, bookings FROM trips WHERE trip_id=?";
        $q6 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q6, $query6);
        mysqli_stmt_bind_param($q6, "i", $trip_identifier);
        mysqli_stmt_execute($q6);
        $result6 = mysqli_stmt_get_result($q6);
        $row6 = mysqli_fetch_array($result6, MYSQLI_NUM);
        $depart_date = $row6[0];
        $depart_time = $row6[1];
        $trip_time = date_create($depart_date . $depart_time);
        $route_identifier = $row6[2];
        $bookings = $row6[3];

        $query10 = "SELECT tariff FROM routes WHERE route_id=?";
        $q10 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q10, $query10);
        mysqli_stmt_bind_param($q10, "i", $route_identifier);
        mysqli_stmt_execute($q10);
        $result10 = mysqli_stmt_get_result($q10);
        $row10 = mysqli_fetch_array($result10, MYSQLI_NUM);
        $price = $row10[0];

        $cancel_time = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));

        $day_difference = date_diff($cancel_time, $trip_time)->format("%R%a");
        //$hour_difference = date_diff($cancel_time, $trip_time)->format("%R%h");
        //print "Day difference: " . $day_difference . "<br>";
        //print "Hour difference: " . $hour_difference . "<br>";

        $cancel_time = $cancel_time->format('y-m-d H:i:s');
        $accessibility = "inaccessible";
        $reason = "trip cancel";

        if ($day_difference >= 1) {

            // Step 1: Make the reservation inaccessible for the passenger
            // Step 2: Decrease the number of bookings for that trip by 1
            // Step 3: Create a refund request with full payment

            $query7 = "UPDATE reservations SET accessibility=? WHERE reservation_id=? LIMIT 1"; // Step 1
            $q7 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q7, $query7);
            mysqli_stmt_bind_param($q7, 'si', $accessibility, $reservation_id);
            mysqli_stmt_execute($q7);

            if (mysqli_stmt_affected_rows($q7) == 1) { // Update OK

                $bookings--;

                $query8 = "UPDATE trips SET bookings=? WHERE trip_id=? LIMIT 1"; // Step 2
                $q8 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q8, $query8);
                mysqli_stmt_bind_param($q8, 'ii', $bookings, $trip_identifier);
                mysqli_stmt_execute($q8);

                if (mysqli_stmt_affected_rows($q8) == 1) { // Update OK

                    $query9 = "INSERT INTO refund_requests (refund_request_id, reason, issued_date, passenger_id, reservation_id, refund_amount) "; // Step 3
                    $query9 .= "VALUES(' ', ?, ?, ?, ?, ?)";
                    $q9 = mysqli_stmt_init($dbcon);
                    mysqli_stmt_prepare($q9, $query9);
                    mysqli_stmt_bind_param($q9, 'ssiid', $reason, $cancel_time, $_SESSION['user_id'], $reservation_id, $price);
                    mysqli_stmt_execute($q9);
                }
            }

            header('location: ../../php/booking-tasks/auxiliary/successful-trip-cancel.php');
        } else if ($day_difference == 0) {

            // Step 1: Make the reservation inaccessible for the passenger
            // Step 2: Decrease the number of bookings for that trip by 1
            // Step 3: Create a refund request with half the payment

            $query7 = "UPDATE reservations SET accessibility=? WHERE reservation_id=? LIMIT 1"; // Step 1
            $q7 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q7, $query7);
            mysqli_stmt_bind_param($q7, 'si', $accessibility, $reservation_id);
            mysqli_stmt_execute($q7);

            if (mysqli_stmt_affected_rows($q7) == 1) { // Update OK

                $bookings--;

                $query8 = "UPDATE trips SET bookings=? WHERE trip_id=? LIMIT 1"; // Step 2
                $q8 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q8, $query8);
                mysqli_stmt_bind_param($q8, 'ii', $bookings, $trip_identifier);
                mysqli_stmt_execute($q8);

                if (mysqli_stmt_affected_rows($q8) == 1) { // Update OK

                    $price /= 2;

                    $query9 = "INSERT INTO refund_requests (refund_request_id, reason, issued_date, passenger_id, reservation_id, refund_amount) "; // Step 3
                    $query9 .= "VALUES(' ', ?, ?, ?, ?, ?)";
                    $q9 = mysqli_stmt_init($dbcon);
                    mysqli_stmt_prepare($q9, $query9);
                    mysqli_stmt_bind_param($q9, 'ssiid', $reason, $cancel_time, $_SESSION['user_id'], $reservation_id, $price);
                    mysqli_stmt_execute($q9);
                }
            }

            header('location: ../../php/booking-tasks/auxiliary/successful-trip-cancel.php');
        } else {
            // Reject trip cancel
            $errors[] = 'It is late to cancel a trip now. Try reporting it as missed instead.';
        }

        if (!empty($errors)) {
            $errorstring = "ERROR! The following error(s) occurred:<br><br>";
            foreach ($errors as $msg) { // Print each error.
                $errorstring .= "— $msg<br>\n";
            }
            $errorstring .= "<br>Please try again.<br>";
            print "<p style='$error_style'>$errorstring</p>";
        }
    }

    // Populating the form from the database
    // Find reservations where the current user's id is linked with a trip
    $query = "SELECT first_name, last_name, trip_id, seat_number FROM reservations WHERE reservation_id=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, "i", $reservation_id);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $first_name = $row[0];
    $last_name = $row[1];
    $full_name = strtoupper($first_name) . ' ' . strtoupper($last_name);
    $trip_id = $row[2];
    $seat_number = $row[3];

    // Select trip detail from trip table using the trip id
    $query2 = "SELECT DATE_FORMAT(departure_date, '%a, %b %d, %Y'), TIME_FORMAT(departure_time, '%h:%i %p'), ";
    $query2 .= "route_id, bus_plate_number FROM trips WHERE trip_id=?";
    $q2 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q2, $query2);
    mysqli_stmt_bind_param($q2, "i", $trip_id);
    mysqli_stmt_execute($q2);
    $result2 = mysqli_stmt_get_result($q2);
    $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
    $departure_date = $row2[0];
    $departure_time = $row2[1];
    $route_id = $row2[2];
    $plate_number = $row2[3];

    // Select route detail from route table using the route id
    $query3 = "SELECT departure_place, arrival_place, distance_in_km, tariff FROM routes WHERE route_id=?";
    $q3 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q3, $query3);
    mysqli_stmt_bind_param($q3, "i", $route_id);
    mysqli_stmt_execute($q3);
    $result3 = mysqli_stmt_get_result($q3);
    $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
    $departure_place = $row3[0];
    $arrival_place = $row3[1];
    $distance = $row3[2];
    $tariff = $row3[3];

    // Get the bus side number using the plate number
    $query4 = "SELECT side_number FROM buses WHERE plate_number=?";
    $q4 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q4, $query4);
    mysqli_stmt_bind_param($q4, "i", $plate_number);
    mysqli_stmt_execute($q4);
    $result4 = mysqli_stmt_get_result($q4);
    $row4 = mysqli_fetch_array($result4, MYSQLI_NUM);
    $side_number = $row4[0];

    // Print the retrieved information
    print '
    <form action="cancel.php?id=' . $reservation_id . '" method="post">
        <div class="row bg-light mb-3 rounded-4 p-3 border-start border-top border-end border-bottom border-dark">
            <div class="row text-dark">
                <div class="col text-start">
                    <p class="fw-bold">' . $full_name . '</p>
                </div>
                <div class="col">
                    <p> [Seat №: ' . $seat_number . ']</p>
                </div>
                <div class="col text-end">
                    <p>Plate №: ' . $plate_number . '</p>
                </div>
            </div>
            <div class="row text-dark">
                <div class="col text-start">
                    <p>' . $departure_date . '</p>
                </div>
                <div class="col">
                    <p>' . $departure_time . '</p>
                </div>
                <div class="col text-end">
                    <p>Side №: ' . $side_number . '</p>
                </div>
            </div>
            <div class="row text-dark border-top">
                <div class="col text-start">
                    <p>' . $departure_place . '</p>
                </div>
                <div class="col">
                    <p>To</p>
                </div>
                <div class="col text-end">
                    <p>' . $arrival_place . '</p>
                </div>
            </div>
            <div class="row text-dark border-top">
                <div class="col text-start">
                    <p>' . $distance . ' km</p>
                </div>
                <div class="col text-end">
                    <p>' . $tariff . ' Birr</p>
                </div>
            </div>
        </div>
        <p class="text-danger fs-5">Are you sure you want to cancel this trip?<br />The action can not be undone.</p>
        <a href="selected-trip.php?id=' . $reservation_id . '">
        <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-5 me-lg-5" id="back-button" /></a>
        <input type="submit" name="delete-trip" value="CANCEL TRIP" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-5 ms-lg-5" />
    </form>';

    mysqli_stmt_free_result($q);
    mysqli_close($dbcon);
} catch (Exception $e) {
    print "The system is busy. Please try later";
    //print "An Exception occurred. Message: " . $e->getMessage();
} catch (Error $e) {
    print "The system is currently busy. Please try later";
    print "An Error occurred. Message: " . $e->getMessage();
}
