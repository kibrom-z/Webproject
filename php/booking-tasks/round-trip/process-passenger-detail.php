<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

$errors = array(); // Stores all error messages concatenated

// Trimming extra whitespaces and validating inputs
$first_name = ucfirst(trim(filter_var($_POST['f-name'], FILTER_SANITIZE_STRING)));
if (empty($first_name)) {
    $errors[] = 'You forgot to enter your first name.';
}
$last_name = ucfirst(trim(filter_var($_POST['l-name'], FILTER_SANITIZE_STRING)));
if (empty($last_name)) {
    $errors[] = 'You forgot to enter your last name.';
}
$sex = filter_var($_POST['sex'], FILTER_SANITIZE_STRING);
$phone = trim(filter_var($_POST['phone'], FILTER_SANITIZE_STRING));
if (empty($phone)) {
    $errors[] = 'You forgot to enter your phone number.';
}
$email = trim(filter_var($_POST['e-mail'], FILTER_SANITIZE_EMAIL));
if (empty($email)) {
    $errors[] = 'You forgot to enter your email address.';
}
$boarding_place1 = ucfirst(trim(filter_var($_POST['board-place1'], FILTER_SANITIZE_STRING)));
$boarding_place2 = ucfirst(trim(filter_var($_POST['board-place2'], FILTER_SANITIZE_STRING)));
$payment_status = "pending";

require('../../../php/error-style.php'); // For formatting the error message

if (empty($errors)) { // If everything's OK
    try {
        require('../../../php/mysqli-connect.php');

        $retrieve_query = "SELECT * FROM reservations WHERE first_name=? AND email=? AND phone=? AND trip_id=?";
        $prepared = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($prepared, $retrieve_query);
        mysqli_stmt_bind_param($prepared, "sssi", $first_name, $email, $phone, $_SESSION['selected-first-trip-id']);
        mysqli_stmt_execute($prepared);
        $result = mysqli_stmt_get_result($prepared);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);

        $retrieve_query2 = "SELECT * FROM reservations WHERE first_name=? AND email=? AND phone=? AND trip_id=?";
        $prepared2 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($prepared2, $retrieve_query2);
        mysqli_stmt_bind_param($prepared2, "sssi", $first_name, $email, $phone, $_SESSION['selected-return-trip-id']);
        mysqli_stmt_execute($prepared2);
        $result2 = mysqli_stmt_get_result($prepared2);
        $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);

        if ((mysqli_num_rows($result) == 1) || (mysqli_num_rows($result2) == 1)) {
            $errors[] = 'The record already exists.';
        } else { // A new record

            // Insert the first trip
            $query = "INSERT INTO reservations (reservation_id, passenger_id, first_name, last_name, sex, email, phone, boarding_place, trip_id, payment_status, issued_date) ";
            $query .= "VALUES(' ', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $q = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q, $query);
            mysqli_stmt_bind_param($q, 'issssssss', $_SESSION['user_id'], $first_name, $last_name, $sex, $email, $phone, $boarding_place1, $_SESSION['selected-first-trip-id'], $payment_status);
            mysqli_stmt_execute($q);

            // Insert the return trip
            $query3 = "INSERT INTO reservations (reservation_id, passenger_id, first_name, last_name, sex, email, phone, boarding_place, trip_id, payment_status, issued_date) ";
            $query3 .= "VALUES(' ', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $q3 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q3, $query3);
            mysqli_stmt_bind_param($q3, 'issssssss', $_SESSION['user_id'], $first_name, $last_name, $sex, $email, $phone, $boarding_place2, $_SESSION['selected-return-trip-id'], $payment_status);
            mysqli_stmt_execute($q3);

            if ((mysqli_stmt_affected_rows($q) == 1) &&
                (mysqli_stmt_affected_rows($q3) == 1)
            ) {

                // Store the reservation ids in a session variable
                $query2 = "SELECT reservation_id FROM reservations WHERE first_name=? AND email=? AND phone=? AND trip_id=?";
                $q2 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q2, $query2);
                mysqli_stmt_bind_param($q2, "sssi", $first_name, $email, $phone, $_SESSION['selected-first-trip-id']);
                mysqli_stmt_execute($q2);
                $result2 = mysqli_stmt_get_result($q2);
                while ($row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
                    $_SESSION['reservation-ids'][] = $row2['reservation_id'];
                }

                $query4 = "SELECT reservation_id FROM reservations WHERE first_name=? AND email=? AND phone=? AND trip_id=?";
                $q4 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q4, $query4);
                mysqli_stmt_bind_param($q4, "sssi", $first_name, $email, $phone, $_SESSION['selected-return-trip-id']);
                mysqli_stmt_execute($q4);
                $result4 = mysqli_stmt_get_result($q4);
                while ($row4 = mysqli_fetch_array($result4, MYSQLI_ASSOC)) {
                    $_SESSION['reservation-ids'][] = $row4['reservation_id'];
                }

                if ($_SESSION['manipulable-adult-number'] > 0) {
                    $_SESSION['manipulable-adult-number']--;
                    if ($_SESSION['manipulable-adult-number'] == 0) {
                        header('location: passenger-confirmation.php');
                    } else {
                        header('location: passenger-detail.php?id=' . $_SESSION['selected-return-trip-id'] . '');
                    }
                }
            } else {
                // Public message:
                $errorstring = "<p>System Error<br />You could not be registered due ";
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
