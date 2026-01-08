<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Manager")) {
    header('location: ../../sign-in.php');
    exit();
}


$errors = array(); // Stores all error messages concatenated

// Validating inputs
$departure_place = filter_var($_POST['dep-place'], FILTER_SANITIZE_STRING);
if (empty($departure_place)) {
    $errors[] = 'You forgot to enter departure place.';
}
$arrival_place = filter_var($_POST['arv-place'], FILTER_SANITIZE_STRING);
if (empty($arrival_place)) {
    $errors[] = 'You forgot to enter arrival place.';
}
if (strcasecmp($departure_place, $arrival_place) == 0) { // Compares case insensitively
    $errors[] = 'Departure and arrival can not be the same.';
}
$departure_date = filter_var($_POST['dep-date'], FILTER_SANITIZE_STRING);
if (empty($departure_date)) {
    $errors[] = 'You forgot to enter departure date.';
}

$_SESSION['departure-city'] = $departure_place;
$_SESSION['arrival-city'] = $arrival_place;
$_SESSION['departure-date'] = $departure_date;

require('../../../php/error-style.php'); // For formatting the error message

if (empty($errors)) { // If everything's OK

    try {

        require('../../../php/mysqli-connect.php');

        $query2 = "SELECT route_id FROM routes WHERE departure_place=? AND arrival_place=?";
        $q2 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q2, $query2);
        mysqli_stmt_bind_param($q2, 'ss', $departure_place, $arrival_place);
        mysqli_stmt_execute($q2);
        $result2 = mysqli_stmt_get_result($q2);

        if (mysqli_num_rows($result2) == 1) {

            $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
            $route_id = $row2[0];

            $query3 = "SELECT trip_id FROM trips WHERE route_id=? AND departure_date=?";
            $q3 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q3, $query3);
            mysqli_stmt_bind_param($q3, 'is', $route_id, $departure_date);
            mysqli_stmt_execute($q3);
            $result3 = mysqli_stmt_get_result($q3);
            if (mysqli_num_rows($result3) > 0) {
                $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
                $trip_identifier = $row3[0];
                header('location: reservations.php?id=' . $trip_identifier . '');
            } else {
                $errors[] = 'No reservations found';
            }
        } else {
            $errors[] = 'No reservations found';
        }
    } catch (Exception $e) {
        print "An Exception occurred. Message: " . $e->getMessage();
        print "The system is busy please try later";
    } catch (Error $e) {
        print "An Error occurred. Message: " . $e->getMessage();
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
