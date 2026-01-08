<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

try {

    require('../../php/mysqli-connect.php');

    $retrieve_query = "SELECT first_name, last_name, sex, phone, email, boarding_place FROM reservations WHERE reservation_id=?";
    $prepared = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($prepared, $retrieve_query);
    mysqli_stmt_bind_param($prepared, "i", $_SESSION['postpone-reservation-id']);
    mysqli_stmt_execute($prepared);
    $result = mysqli_stmt_get_result($prepared);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $first_name = $row[0];
    $last_name = $row[1];
    $sex = $row[2];
    $phone = $row[3];
    $email = $row[4];
    $boarding_place = $row[5];
    $payment_status = "pending";

    $query = "INSERT INTO reservations (reservation_id, passenger_id, first_name, last_name, sex, email, phone, boarding_place, trip_id, payment_status, issued_date) ";
    $query .= "VALUES(' ', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'issssssss', $_SESSION['user_id'], $first_name, $last_name, $sex, $email, $phone, $boarding_place, $_SESSION['to-be-postponed-to-trip-id'], $payment_status);
    mysqli_stmt_execute($q);

    if (mysqli_stmt_affected_rows($q) == 1) {

        // Store the reservation id in a session variable
        $query2 = "SELECT reservation_id FROM reservations WHERE first_name=? AND email=? AND phone=? AND trip_id=?";
        $q2 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q2, $query2);
        mysqli_stmt_bind_param($q2, "sssi", $first_name, $email, $phone, $_SESSION['to-be-postponed-to-trip-id']);
        mysqli_stmt_execute($q2);
        $result2 = mysqli_stmt_get_result($q2);
        $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
        $_SESSION['reservation-id'] = $row2[0];

        header('location: postpone-seat.php');
    } else {
        // Public message:
        $errorstring = "<p>System Error<br />You could not be registered due ";
        $errorstring .= "to a system error. We apologize for any inconvenience.</p>";
        print "<p style='$error_style'>$errorstring</p>";
        mysqli_close($dbcon);
        exit();
    }
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
