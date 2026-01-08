<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}


try {
    require_once('../../php/mysqli-connect.php');

    $accessibility = "accessible";

    // Find reservations where the current user's id is linked with a trip
    $query = "SELECT * FROM reservations WHERE passenger_id=? AND accessibility=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, "is", $_SESSION['user_id'], $accessibility);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);

    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

            $reservation_id = htmlspecialchars($row['reservation_id'], ENT_QUOTES);
            $first_name = htmlspecialchars($row['first_name'], ENT_QUOTES);
            $last_name = htmlspecialchars($row['last_name'], ENT_QUOTES);
            $full_name = strtoupper($first_name) . ' ' . strtoupper($last_name);
            $trip_id = htmlspecialchars($row['trip_id'], ENT_QUOTES);
            $seat_number = htmlspecialchars($row['seat_number'], ENT_QUOTES);

            // Select trip detail from trip table using the trip id
            $query2 = "SELECT DATE_FORMAT(departure_date, '%a, %b %d, %Y'), TIME_FORMAT(departure_time, '%h:%i %p'), ";
            $query2 .= "route_id, bus_plate_number, tariff FROM trips WHERE trip_id=?";
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
            $tariff = $row2[4];

            // Select route detail from route table using the route id
            $query3 = "SELECT departure_place, arrival_place, distance_in_km FROM routes WHERE route_id=?";
            $q3 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q3, $query3);
            mysqli_stmt_bind_param($q3, "i", $route_id);
            mysqli_stmt_execute($q3);
            $result3 = mysqli_stmt_get_result($q3);
            $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
            $departure_place = $row3[0];
            $arrival_place = $row3[1];
            $distance = $row3[2];

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
            <div class="row bg-light mb-3 rounded-4 p-3 border-start border-top border-end border-bottom" id="trip-div">
                <a href="selected-trip.php?id=' . $reservation_id . '" class="text-decoration-none">
                    <div class="row text-dark">
                        <div class="col text-start">
                            <p class="fw-bold">' . $full_name . '</p>
                        </div>
                        <div class="col">
                            <p>Seat №: ' . $seat_number . '</p>
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
                </a>
            </div>';
        }
    } else {
        print '<p class="fs-5">Your booked trips will appear here.</p>';
    }
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
