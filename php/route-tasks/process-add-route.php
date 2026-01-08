<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Dispatcher")) {
    header('location: ../../sign-in.php');
    exit();
}

$errors = array(); // Stores all error messages concatenated

// Trimming extra whitespaces and validating inputs
$departure_place = ucfirst(trim(filter_var($_POST['dep-place'], FILTER_SANITIZE_STRING)));
if (empty($departure_place)) {
    $errors[] = 'You forgot to enter departure place.';
}
$arrival_place = ucfirst(trim(filter_var($_POST['arv-place'], FILTER_SANITIZE_STRING)));
if (empty($arrival_place)) {
    $errors[] = 'You forgot to enter arrival place.';
}
if (strcasecmp($departure_place, $arrival_place) == 0) { // Compares case insensitively
    $errors[] = 'Departure and arrival can not be the same.';
}
$distance_in_km = filter_var($_POST['distance'], FILTER_SANITIZE_NUMBER_FLOAT);
if (empty($distance_in_km)) {
    $errors[] = 'You forgot to enter the distance';
}


require('../../../php/error-style.php'); // For formatting the error message

if (empty($errors)) { // If everything's OK
    try {
        require('../../../php/mysqli-connect.php');

        $retrieve_query = "SELECT departure_place, arrival_place FROM routes WHERE departure_place=? AND arrival_place=?";
        $prepared = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($prepared, $retrieve_query);
        mysqli_stmt_bind_param($prepared, "ss", $departure_place, $arrival_place);
        mysqli_stmt_execute($prepared);
        $result = mysqli_stmt_get_result($prepared);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);

        if (mysqli_num_rows($result) == 1) {
            $errors[] = 'The route already exists.';
        } else { // The route is new
            $query = "INSERT INTO routes (route_id, departure_place, arrival_place, distance_in_km) ";
            $query .= "VALUES(' ', ?, ?, ?)";
            $q = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q, $query);
            mysqli_stmt_bind_param($q, 'sss', $departure_place, $arrival_place, $distance_in_km);
            mysqli_stmt_execute($q);

            $query2 = "INSERT INTO routes (route_id, departure_place, arrival_place, distance_in_km) ";
            $query2 .= "VALUES(' ', ?, ?, ?)";
            $q2 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q2, $query2);
            mysqli_stmt_bind_param($q2, 'sss', $arrival_place, $departure_place, $distance_in_km);
            mysqli_stmt_execute($q2);

            if ((mysqli_stmt_affected_rows($q) == 1) && (mysqli_stmt_affected_rows($q2) == 1)) {
                header('location: ../../../php/route-tasks/successful-route-addition.php');
                exit();
            } else {
                // Public message:
                $errorstring = "<p>System Error<br />The route could not be added due ";
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
