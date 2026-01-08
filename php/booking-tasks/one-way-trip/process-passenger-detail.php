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
$boarding_place = ucfirst(trim(filter_var($_POST['board-place'], FILTER_SANITIZE_STRING)));
$payment_status = "pending";

require('../../php/error-style.php'); // For formatting the error message

if (empty($errors)) { // If everything's OK
    try {
        require('../../php/mysqli-connect.php');

        $retrieve_query = "SELECT * FROM reservations WHERE first_name=? AND email=? AND phone=? AND trip_id=?";
        $prepared = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($prepared, $retrieve_query);
        mysqli_stmt_bind_param($prepared, "sssi", $first_name, $email, $phone, $_SESSION['selected-trip-id']);
        mysqli_stmt_execute($prepared);
        $result = mysqli_stmt_get_result($prepared);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);

        if (mysqli_num_rows($result) == 1) {
            $errors[] = 'The record already exists.';
        } else { // A new record

            $query = "INSERT INTO reservations (reservation_id, passenger_id, first_name, last_name, sex, email, phone, boarding_place, trip_id, payment_status, issued_date) ";
            $query .= "VALUES(' ', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $q = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q, $query);
            mysqli_stmt_bind_param($q, 'issssssss', $_SESSION['user_id'], $first_name, $last_name, $sex, $email, $phone, $boarding_place, $_SESSION['selected-trip-id'], $payment_status);
            mysqli_stmt_execute($q);

            if (mysqli_stmt_affected_rows($q) == 1) {

                // Store the reservation ids in a session variable
                $query2 = "SELECT reservation_id FROM reservations WHERE first_name=? AND email=? AND phone=? AND trip_id=?";
                $q2 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q2, $query2);
                mysqli_stmt_bind_param($q2, "sssi", $first_name, $email, $phone, $_SESSION['selected-trip-id']);
                mysqli_stmt_execute($q2);
                $result2 = mysqli_stmt_get_result($q2);
                while ($row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
                    $_SESSION['reservation-ids'][] = $row2['reservation_id'];
                }

                if ($_SESSION['manipulable-adult-number'] > 0) {
                    $_SESSION['manipulable-adult-number']--;
                    if ($_SESSION['manipulable-adult-number'] == 0) {
                        header('location: passenger-confirmation.php');
                    } else {
                        header('location: passenger-detail.php?id=' . $_SESSION['selected-trip-id'] . '');
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
