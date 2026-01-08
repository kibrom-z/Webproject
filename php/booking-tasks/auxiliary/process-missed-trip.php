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

        $query5 = "SELECT trip_id, passenger_has_attended FROM reservations WHERE reservation_id=?";
        $q5 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q5, $query5);
        mysqli_stmt_bind_param($q5, "i", $reservation_id);
        mysqli_stmt_execute($q5);
        $result5 = mysqli_stmt_get_result($q5);
        $row5 = mysqli_fetch_array($result5, MYSQLI_NUM);
        $trip_identifier = $row5[0];
        $travelled = $row5[1];

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

        $report_time = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
        $day_difference = date_diff($trip_time, $report_time)->format("%R%a");

        $report_time = $report_time->format('y-m-d H:i:s');
        $accessibility = "inaccessible";
        $reason = "missed trip";

        if (($day_difference >= 0) && ($day_difference <= 7)) {

            if ($travelled == "No") {

                // Step 1: Make the reservation inaccessible for the passenger
                // Step 2: Create a refund request with half the payment

                $query7 = "UPDATE reservations SET accessibility=? WHERE reservation_id=? LIMIT 1"; // Step 1
                $q7 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q7, $query7);
                mysqli_stmt_bind_param($q7, 'si', $accessibility, $reservation_id);
                mysqli_stmt_execute($q7);

                if (mysqli_stmt_affected_rows($q7) == 1) { // Update OK

                    $price /= 2;

                    $query9 = "INSERT INTO refund_requests (refund_request_id, reason, issued_date, passenger_id, reservation_id, refund_amount) "; // Step 2
                    $query9 .= "VALUES(' ', ?, ?, ?, ?, ?)";
                    $q9 = mysqli_stmt_init($dbcon);
                    mysqli_stmt_prepare($q9, $query9);
                    mysqli_stmt_bind_param($q9, 'ssiid', $reason, $report_time, $_SESSION['user_id'], $reservation_id, $price);
                    mysqli_stmt_execute($q9);
                }

                header('location: ../../php/booking-tasks/auxiliary/successful-missed-trip-report.php');
            } else {
                $errors[] = 'You can not report a trip you travelled as missed.';
            }
        } else {
            $errors[] = 'You can not report a trip as missed before or a week after the journey.';
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
    <form action="report-missed.php?id=' . $reservation_id . '" method="post" class="small-container">
        <div class="field">
            <div class="triple-box">
                <div id="top">
                    <p id="passenger-name">' . $full_name . '</p>
                    <p id="seat-num"> [Seat №: ' . $seat_number . ']</p>
                    <p id="plate-num">Plate №: ' . $plate_number . '</p>
                    <p></p>
                    <p id="dep-date">' . $departure_date . '</p>
                    <p id="side-num">Side №: ' . $side_number . '</p>
                </div>
                <div id="middle">
                    <p id="dep-place">' . $departure_place . '</p>
                    <p id="to">To</p>
                    <p id="arv-place">' . $arrival_place . '</p>
                    <p id="dep-time">' . $departure_time . '</p>
                </div>
                <div id="bottom"> 
                    <p id="distance">' . $distance . ' km</p>
                    <p id="price">' . $tariff . ' Birr</p>
                </div>
            </div>
        </div>
        <div class="field">
            <p style="color: red;">Are you sure you want to report this trip as missed?<br />The action can not be undone.</p>
        </div>
        <div class="field">
            <a href="selected-trip.php?id=' . $reservation_id . '">
            <input type="button" name="back" value="Go Back" class="button i-need-space-button" id="back-button" /></a>
            <input type="submit" name="delete-route" value="report as missed" class="button" />
        </div>
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
