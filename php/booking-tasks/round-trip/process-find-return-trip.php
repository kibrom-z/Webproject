<?php
session_start();
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

// The code looks for a valid user ID, either through GET or POST:
if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
    $id = htmlspecialchars($_GET['id'], ENT_QUOTES);
} elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
    $id = htmlspecialchars($_POST['id'], ENT_QUOTES);
} else { // No valid ID, kill the script.
    print '<p>This page has been accessed in error id.</p>';
    exit();
}

$_SESSION['selected-first-trip-id'] = $id;

try {
    require('../../../php/mysqli-connect.php');

    // Before processing, get the route (reversed) id using the departure and arrival places
    $query = "SELECT route_id FROM routes WHERE departure_place=? AND arrival_place=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'ss', $_SESSION['arrival-place'], $_SESSION['departure-place']);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    if (mysqli_num_rows($result) == 1) {
        $return_route_id = htmlspecialchars($row[0], ENT_QUOTES);
    } else {
        print '<p>This page has been accessed in error.</p>';
    }

    $retrieve_query = "SELECT trip_id, departure_date, departure_time, route_id, bus_plate_number ";
    $retrieve_query .= "FROM trips WHERE departure_date=? AND route_id=?";
    $prepared = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($prepared, $retrieve_query);
    mysqli_stmt_bind_param($prepared, "ss", $_SESSION['return-date'], $return_route_id);
    mysqli_stmt_execute($prepared);
    $result = mysqli_stmt_get_result($prepared);

    if (mysqli_num_rows($result) == 0) {
        header('location: ../../../users/passenger/round-trip/return-trip.php?id=0');
    } else {
        print 'Trip found';
        $_SESSION['return-route-id'] = $return_route_id;
        $_SESSION['return-trip-ids'] = array(); // If multiple trips are found

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $_SESSION['return-trip-ids'][] = $row['trip_id'];
        }
        header('location: ../../../users/passenger/round-trip/return-trip.php?id=1');
    }
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
