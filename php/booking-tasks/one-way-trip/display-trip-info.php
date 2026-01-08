<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

// Load trip detail on the bottom of the page
try {
    require_once('../../php/mysqli-connect.php');

    // Retrieve the route detail using the route id
    $query = "SELECT departure_place, arrival_place, tariff FROM routes WHERE route_id=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, "i", $_SESSION['route-id']);
    mysqli_stmt_execute($q);
    $res = mysqli_stmt_get_result($q);
    $value = mysqli_fetch_array($res, MYSQLI_NUM);

    $departure_place = $value[0];
    $arrival_place = $value[1];

    $retrieve_query = "SELECT DATE_FORMAT(departure_date, '%b %d, %Y'), TIME_FORMAT(departure_time, '%h:%i %p'), ";
    $retrieve_query .= "tariff FROM trips WHERE trip_id=?";
    $prepared = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($prepared, $retrieve_query);
    mysqli_stmt_bind_param($prepared, "s", $_SESSION['selected-trip-id']);
    mysqli_stmt_execute($prepared);
    $result = mysqli_stmt_get_result($prepared);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);

    $departure_date = $row[0];
    $departure_time = $row[1];
    $tariff = $row[2];

    print '
    <div class="row bg-light mt-4 mb-3 rounded p-3 border-start border-top border-end border-bottom border-secondary">
        <div class="row text-dark">
            <div class="col text-start text-secondary"><p>' . $departure_place . ' to ' . $arrival_place . '</p></div>
            <div class="col text-end text-secondary"><p>Price</p></div>
        </div>
        <div class="row text-dark border-top">
            <div class="col text-start text-secondary"><p>' . $departure_date . ' @' . $departure_time . '</p></div>
            <div class="col text-end text-secondary"><p>' . $tariff . ' Birr</p></div>
        </div>
    </div>';
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
