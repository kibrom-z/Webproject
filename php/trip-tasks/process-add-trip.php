<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Dispatcher")) {
    header('location: ../../sign-in.php');
    exit();
}

$errors = array(); // Stores all error messages concatenated

// Validating inputs
$departure_date = filter_var($_POST['dep-date'], FILTER_SANITIZE_STRING);
if (empty($departure_date)) {
    $errors[] = 'You forgot to enter departure date.';
}
$departure_time = filter_var($_POST['dep-time'], FILTER_SANITIZE_STRING);
if (empty($departure_time)) {
    $errors[] = 'You forgot to enter departure time.';
}
$route = filter_var($_POST['route'], FILTER_SANITIZE_STRING);
if (empty($route)) {
    $errors[] = 'You forgot to select a route.';
}
$bus = filter_var($_POST['bus'], FILTER_SANITIZE_STRING);
if (empty($bus)) {
    $errors[] = 'You forgot to select a bus.';
}

require('../../../php/error-style.php'); // For formatting the error message

if (empty($errors)) { // If everything's OK
    try {
        require('../../../php/mysqli-connect.php');

        $retrieve_query = "SELECT departure_date, departure_time, bus_plate_number FROM trips ";
        $retrieve_query .= "WHERE departure_date=? AND departure_time=? AND bus_plate_number=?";
        $prepared = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($prepared, $retrieve_query);
        mysqli_stmt_bind_param($prepared, "sss", $departure_date, $departure_time, $bus);
        mysqli_stmt_execute($prepared);
        $result = mysqli_stmt_get_result($prepared);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);

        if (mysqli_num_rows($result) == 1) {
            $errors[] = 'The trip already exists.';
        } else { // The trip is new
            $query2 = "SELECT tariff FROM routes WHERE route_id=?";
            $q2 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q2, $query2);
            mysqli_stmt_bind_param($q2, 'i', $route);
            mysqli_stmt_execute($q2);
            $result2 = mysqli_stmt_get_result($q2);
            $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
            $tariff = $row2[0];

            $query = "INSERT INTO trips (trip_id, departure_date, departure_time, route_id, tariff, bus_plate_number) ";
            $query .= "VALUES(' ', ?, ?, ?, ?, ?)";
            $q = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q, $query);
            mysqli_stmt_bind_param($q, 'sssss', $departure_date, $departure_time, $route, $tariff, $bus);
            mysqli_stmt_execute($q);

            if (mysqli_stmt_affected_rows($q) == 1) {
                header('location: ../../../php/trip-tasks/successful-trip-addition.php');
                exit();
            } else {
                // Public message:
                $errorstring = "<p>System Error<br />The trip could not be added due ";
                $errorstring .= "to a system error. We apologize for any inconvenience.</p>";
                print "<p style='$error_style'>$errorstring</p>";
                mysqli_close($dbcon);
                exit();
            }
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
