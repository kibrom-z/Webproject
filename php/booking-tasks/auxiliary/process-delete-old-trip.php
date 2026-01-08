<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

try {

    require('../../../php/mysqli-connect.php');
    require('../../../php/error-style2.php');

    // Before deleting the reservation, get the trip id number
    $query = "SELECT trip_id FROM reservations WHERE reservation_id=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'i', $_SESSION['postpone-reservation-id']);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $trip_id = $row[0];

    // Delete the old booking
    $query = "DELETE FROM reservations WHERE reservation_id=? LIMIT 1";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'i', $_SESSION['postpone-reservation-id']);
    mysqli_stmt_execute($q);

    if (mysqli_stmt_affected_rows($q) == 1) { // Delete OK

        // Decrease the bookings of trip of the old booking by 1

        // Retrieve the current booking number
        $retrieve_query4 = "SELECT bookings FROM trips WHERE trip_id=?";
        $prepared4 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($prepared4, $retrieve_query4);
        mysqli_stmt_bind_param($prepared4, "i", $trip_id);
        mysqli_stmt_execute($prepared4);
        $result4 = mysqli_stmt_get_result($prepared4);
        $row4 = mysqli_fetch_array($result4, MYSQLI_NUM);
        $bookings = $row4[0];
        $new_bookings = $bookings - $_SESSION['adult-number'];

        // Decrease the number of bookings by the number of reservations
        $query5 = "UPDATE trips SET bookings=? WHERE trip_id=?";
        $q5 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q5, $query5);
        mysqli_stmt_bind_param($q5, 'ii', $new_bookings, $trip_id);
        mysqli_stmt_execute($q5);

        if ((mysqli_stmt_affected_rows($q5) == 1)) { // Do nothing
        } else {
            $errors[] = 'Something went wrong somewhere.';
            //print '<p>' . mysqli_error($dbcon) . '<br />Query: ' . $q . '</p>';
        }
    } else { // print a message if the query failed.
        $errors[] = 'Something went wrong somewhere.';
        //print '<p>' . mysqli_error($dbcon) . '<br />Query: ' . $q . '</p>';
    }
} catch (Exception $e) {
    print "The system is busy. Please try later";
    //print "An Exception occurred. Message: " . $e->getMessage();
} catch (Error $e) {
    print "The system is currently busy. Please try later";
    //print "An Error occurred. Message: " . $e->getMessage();
}
if (!empty($errors)) {
    $errorstring = "ERROR! The following error(s) occurred:<br><br>";
    foreach ($errors as $msg) { // Print each error.
        $errorstring .= "— $msg<br>\n";
    }
    $errorstring .= "<br>Please try again.<br>";
    print "<p style='$error_style'>$errorstring</p>";
}
