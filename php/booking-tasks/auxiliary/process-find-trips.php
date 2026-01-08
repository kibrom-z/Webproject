<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

$errors = array(); // Stores all error messages concatenated

// Validating inputs
$departure_place = filter_var($_POST['dep-place'], FILTER_SANITIZE_STRING);
if (empty($departure_place)) {
    $errors[] = 'You forgot to select a departure place.';
}
$arrival_place = filter_var($_POST['arv-place'], FILTER_SANITIZE_STRING);
if (empty($arrival_place)) {
    $errors[] = 'You forgot to select an arrival place.';
}
if (strcasecmp($departure_place, $arrival_place) == 0) { // Compares case insensitively
    $errors[] = 'Departure and arrival can not be the same.';
}
$departure_date = filter_var($_POST['dep-date'], FILTER_SANITIZE_STRING);
if (empty($departure_date)) {
    $errors[] = 'You forgot to select departure date.';
}
$adult_number = filter_var($_POST['adult-num'], FILTER_SANITIZE_NUMBER_INT);
$child_number = filter_var($_POST['child-num'], FILTER_SANITIZE_NUMBER_INT);

require('../../php/error-style.php'); // For formatting the error message

if (empty($errors)) { // If everything's OK
    try {
        require_once('../../php/mysqli-connect.php');

        // Before processing, get the route id using the departure and arrival places
        $query = "SELECT route_id FROM routes WHERE departure_place=? AND arrival_place=?";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, 'ss', $departure_place, $arrival_place);
        mysqli_stmt_execute($q);
        $result = mysqli_stmt_get_result($q);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);
        if (mysqli_num_rows($result) == 1) {
            $route_id = htmlspecialchars($row[0], ENT_QUOTES);
        } else {
            print '<p>This page has been accessed in error.</p>';
        }

        $retrieve_query = "SELECT trip_id, departure_date, departure_time, route_id, bus_plate_number ";
        $retrieve_query .= "FROM trips WHERE departure_date=? AND route_id=?";
        $prepared = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($prepared, $retrieve_query);
        mysqli_stmt_bind_param($prepared, "ss", $departure_date, $route_id);
        mysqli_stmt_execute($prepared);
        $result = mysqli_stmt_get_result($prepared);

        if (mysqli_num_rows($result) == 0) {
            header('location: postpone-available-trips.php?id=0');
        } else {
            $_SESSION['adult-number'] = $adult_number;
            $_SESSION['manipulable-adult-number'] = $adult_number;
            $_SESSION['child-number'] = $child_number;
            $_SESSION['route-id'] = $route_id;
            $_SESSION['trip-ids'] = array(); // If multiple trips are found

            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $_SESSION['trip-ids'][] = $row['trip_id'];
            }
            header('location: postpone-available-trips.php?id=1');
        }
    } catch (Exception $e) {
        //print "An Exception occurred. Message: " . $e->getMessage();
        print "The system is busy please try later";
    } catch (Error $e) {
        //print "An Error occurred. Message: " . $e->getMessage();
        print "The system is busy please try again later.";
    }
}
if (!empty($errors)) {
    $errorstring = "ERROR! The following error(s) occurred:<br><br>";
    foreach ($errors as $msg) { // Print each error.
        $errorstring .= "— $msg<br>\n";
    }
    $errorstring .= "<br>Please try again.<br>";
    print "<p style='$error_style'>$errorstring</p>";
}
