<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}
try {

    require('../../php/mysqli-connect.php');

    // Retrieve the route detail using the route id
    $query = "SELECT departure_place, arrival_place, distance_in_km FROM routes WHERE route_id=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, "i", $_SESSION['route-id']);
    mysqli_stmt_execute($q);
    $res = mysqli_stmt_get_result($q);
    $value = mysqli_fetch_array($res, MYSQLI_NUM);
    $departure_place = $value[0];
    $arrival_place = $value[1];
    $distance = $value[2];

    // To print appropriate messages
    $trips_found = FALSE;
    $too_many = FALSE;

    // Loop through each trip
    foreach ($_SESSION['trip-ids'] as $item) {

        // But before displaying the trips, we have to check whether or not
        // they are booked in full--using bus carrying capacity, current
        // booking number (from trip table) and the adult number.

        //Retrieve the bus plate number to get the carrying capacity
        $query2 = "SELECT DATE_FORMAT(departure_date, '%a, %b %d, %Y'), TIME_FORMAT(departure_time, '%h:%i %p'), ";
        $query2 .= "bus_plate_number, bookings, tariff FROM trips WHERE trip_id=?";
        $q2 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q2, $query2);
        mysqli_stmt_bind_param($q2, "i", $item);
        mysqli_stmt_execute($q2);
        $result2 = mysqli_stmt_get_result($q2);
        $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
        $departure_date = $row2[0];
        $departure_time = $row2[1];
        $plate_number = $row2[2];
        $bookings = $row2[3];
        $tariff = $row2[4];

        $query3 = "SELECT side_number, carrying_capacity FROM buses WHERE plate_number=?";
        $q3 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q3, $query3);
        mysqli_stmt_bind_param($q3, "i", $plate_number);
        mysqli_stmt_execute($q3);
        $result3 = mysqli_stmt_get_result($q3);
        $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
        $side_number = $row3[0];
        $carrying_capacity = $row3[1];

        if ($bookings < $carrying_capacity) {
            if (($bookings + $_SESSION['adult-number']) <= $carrying_capacity) {
                // things ok
                $trips_found = TRUE;
                print '
                <div class="row bg-light mb-3 rounded-4 p-3 border-start border-top border-end border-bottom" id="trip-div">
                    <a href="passenger-detail.php?id=' . $item . '" class="text-decoration-none">
                        <div class="row text-dark">
                            <div class="col text-start">
                                <p>' . $departure_date . '</p>
                            </div>
                            <div class="col text-end">
                                <p>Plate №: ' . $plate_number . '</p>
                            </div>
                        </div>
                        <div class="row text-dark">
                            <div class="col text-start">
                                <p>' . $departure_time . '</p>
                            </div>
                            <div class="col text-end">
                                <p>Side №: ' . $side_number . '</p>
                            </div>
                        </div>
                        <div class="row text-dark border-top border-bottom">
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
                        <div class="row text-dark">
                            <div class="col text-start">
                                <p>' . $distance . ' km</p>
                            </div>
                            <div class="col text-end">
                                <p>' . $tariff . ' Birr</p>
                            </div>
                        </div>
                    </a>
                </div>';
            } else {
                // too many adults
                $too_many = TRUE;
            }
        }
    }

    if ($trips_found == FALSE) {
        if ($too_many) {
            print '<p class="fs-5">No trips are found that have enough seat for ' . $_SESSION['adult-number'] . ' passengers</p>';
        } else {
            print '<p class="fs-5">No trips are found. Change the date and try again.</p>';
        }
    }
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
