<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "System Administrator")) {
    header('location: ../../sign-in.php');
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
$username = trim(filter_var($_POST['username'], FILTER_SANITIZE_STRING));
if (empty($username)) {
    $errors[] = 'You forgot to enter your username.';
}
$password1 = filter_var($_POST['new-password'], FILTER_SANITIZE_STRING);
$password2 = filter_var($_POST['confirm-password'], FILTER_SANITIZE_STRING);
if (!empty($password1)) {
    if ($password1 !== $password2) {
        $errors[] = 'Your two passwords did not match.';
    }
} else {
    $errors[] = 'You forgot to enter your password.';
}
$email = trim(filter_var($_POST['e-mail'], FILTER_SANITIZE_EMAIL));
if (empty($email)) {
    $errors[] = 'You forgot to enter your email address.';
}
$account_type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);

require('../../../php/error-style.php'); // For formatting the error message

if (empty($errors)) { // If everything's OK
    try {
        require('../../../php/mysqli-connect.php');

        $retrieve_query = "SELECT username FROM users WHERE username=?";
        $prepared = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($prepared, $retrieve_query);
        mysqli_stmt_bind_param($prepared, "s", $username);
        mysqli_stmt_execute($prepared);
        $result = mysqli_stmt_get_result($prepared);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);

        if (mysqli_num_rows($result) == 1) {
            $errors[] = 'The username is taken. Try a new username.';
        } else { // The username is unique

            $hashed_passcode = password_hash($password1, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (user_id, first_name, last_name, sex, phone, email, username, password, account_type) ";
            $query .= "VALUES(' ', ?, ?, ?, ?, ?, ?, ?, ?)";
            $q = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q, $query);
            mysqli_stmt_bind_param($q, 'ssssssss', $first_name, $last_name, $sex, $phone, $email, $username, $hashed_passcode, $account_type);
            mysqli_stmt_execute($q);

            if (mysqli_stmt_affected_rows($q) == 1) {
                header('location: ../../../php/user-tasks/successful-account-creation.php');
                exit();
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
