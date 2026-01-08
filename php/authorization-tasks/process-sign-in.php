<?php

$errors = array(); // Stores all error messages concatenated
$_SESSION['authorization_error'] = '';

// Checking if the form has been submitted:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {

        require('php/mysqli-connect.php');

        // Validate the username
        $username = trim(filter_var($_POST['username'], FILTER_SANITIZE_STRING));
        if (empty($username)) {
            $errors[] = 'You forgot to enter your username.';
        }
        // Validate the password
        $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
        if (empty($password)) {
            $errors[] = 'You forgot to enter your password.';
        }
        $hide_retry = FALSE; // Whether to show or hide the please try again sentence

        if (empty($errors)) { // If everything's OK

            $query = "SELECT user_id, username, password, account_type, account_status FROM users WHERE username=?";
            $q = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q, $query);
            mysqli_stmt_bind_param($q, "s", $username);
            mysqli_stmt_execute($q);
            $result = mysqli_stmt_get_result($q);
            $row = mysqli_fetch_array($result, MYSQLI_NUM);

            if (mysqli_num_rows($result) == 1) {

                $user_id = $row[0];
                $user_name = $row[1];
                $user_password = $row[2];
                $user_account_type = $row[3];
                $user_account_status = $row[4];

                $appropriate_page = "";

                switch ($user_account_type) {
                    case 'Bus Attendant':
                        $appropriate_page = "users/employee/bus-attendant/select-trip-id.php";
                        break;
                    case 'Dispatcher':
                        $appropriate_page = "users/employee/dispatcher/schedule-trip.php";
                        break;
                    case 'Finance Officer':
                        $appropriate_page = "users/employee/finance-officer/refund-requests.php";
                        break;
                    case 'Manager':
                        $appropriate_page = "users/employee/manager/select-route.php";
                        break;
                    case 'System Administrator':
                        $appropriate_page = "users/employee/system-administrator/user-accounts.php";
                        break;
                    default:
                        $appropriate_page = "users/passenger/one-way-trip.php";
                        break;
                }

                if ($user_account_status == "Deactive") {

                    $_SESSION['authorization_error'] = 'Your account is deactivated.<br>Contact the system admin.';
                    $hide_retry = TRUE;
                } else if (password_verify($password, $user_password)) {

                    session_start();
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $user_name;
                    $_SESSION['account_type'] = $user_account_type;

                    header("location: " . $appropriate_page);
                } else { // No password match was made.
                    $_SESSION['authorization_error'] = 'Incorrect username or password.';
                }
            } else { // No username match was made.
                $_SESSION['authorization_error'] = 'Incorrect username or password.';
            }
            if (!empty($errors)) {
                $errorstring = "ERROR! The following error(s) occurred:<br><br>";
                foreach ($errors as $msg) { // Print each error.
                    $errorstring .= "— $msg<br>\n";
                }
                if (!$hide_retry) {
                    $errorstring .= "<br>Please try again.<br>";
                }
                require('php/error-style.php');
                print "<p style='$error_style'>$errorstring</p>";
            }
            mysqli_stmt_free_result($q);
            mysqli_stmt_close($q);
            mysqli_close($dbcon);
        }
    } catch (Exception $e) {
        // print "An Exception occurred. Message: " . $e->getMessage();
        print "The system is busy please try later.";
    } catch (Error $e) {
        //print "An Error occurred. Message: " . $e->getMessage();
        print "The system is busy please try again later.";
    }
} // no else to allow user to enter values
