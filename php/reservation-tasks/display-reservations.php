<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Bus Attendant")) {
    header('location: ../../sign-in.php');
    exit();
}

$errors = array(); // Stores all error messages concatenated

$trip_number = filter_var($_POST['trip-id'], FILTER_SANITIZE_NUMBER_FLOAT);
if (empty($trip_number)) {
    $errors[] = 'You forgot to enter trip id.';
}

try {

    require('../../../php/mysqli-connect.php');
    require('../../../php/error-style2.php'); // For formatting the error message

    if (empty($errors)) {
        $query = "SELECT first_name, last_name FROM reservations WHERE trip_id=?";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, 'i', $trip_number);
        mysqli_stmt_execute($q);
        $result = mysqli_stmt_get_result($q);

        if (mysqli_num_rows($result) > 0) {

            $query2 = "SELECT departure_date, route_id FROM trips WHERE trip_id=?";
            $q2 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q2, $query2);
            mysqli_stmt_bind_param($q2, 'i', $trip_number);
            mysqli_stmt_execute($q2);
            $result2 = mysqli_stmt_get_result($q2);
            $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
            $departure_date = $row2[0];
            $route_id = $row2[1];

            $query3 = "SELECT departure_place, arrival_place FROM routes WHERE route_id=?";
            $q3 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q3, $query3);
            mysqli_stmt_bind_param($q3, 'i', $route_id);
            mysqli_stmt_execute($q3);
            $result3 = mysqli_stmt_get_result($q3);
            $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
            $departure_place = $row3[0];
            $arrival_place = $row3[1];

            $_SESSION['departure-city'] = $departure_place;
            $_SESSION['arrival-city'] = $arrival_place;
            $_SESSION['departure-date'] = $departure_date;

            header('location: reservations.php?id=' . $trip_number . '');
        } else {
            $errors[] = 'No reservations have been made with that trip.';
        }
    }

    mysqli_close($dbcon);
} catch (Exception $e) {
    // print "An Exception occurred. Message: " . $e->getMessage();
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
