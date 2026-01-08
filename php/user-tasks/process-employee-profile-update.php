<?php
if (!isset($_SESSION['account_type'])) {
    header('location: ../../sign-in.php');
    exit();
}
$id = $_SESSION['user_id'];

try {

    require('../../../php/mysqli-connect.php');
    require('../../../php/error-style2.php'); // For formatting the error message

    // If the form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

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
        $email = trim(filter_var($_POST['e-mail'], FILTER_SANITIZE_EMAIL));
        if (empty($email)) {
            $errors[] = 'You forgot to enter your email address.';
        }
        // Check for passwords
        $password_change_needed = FALSE;
        $old_password = filter_var($_POST['old-password'], FILTER_SANITIZE_STRING);
        if (!empty($old_password)) { // If the old password field is empty, the user didn't want to change their password
            $new_password = filter_var($_POST['new-password'], FILTER_SANITIZE_STRING);
            $confirm_password = filter_var($_POST['confirm-password'], FILTER_SANITIZE_STRING);
            if (!empty($new_password)) {
                if ($new_password != $confirm_password) {
                    $errors[] = 'Your new password did not match the confirmed password.';
                }
                if ($old_password == $new_password) {
                    $errors[] = 'Your old password is the same as your new password.';
                }
            } else {
                $errors[] = 'You did not enter a new password.';
            }
            $password_change_needed = TRUE;
        }

        if (empty($errors)) { // If everything's OK
            try {
                // Check the uniqueness of username and correctness of password
                // Retrieve username
                $new_query = "SELECT username FROM users WHERE user_id=?";
                $new_q = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($new_q, $new_query);
                mysqli_stmt_bind_param($new_q, "i", $id);
                mysqli_stmt_execute($new_q);
                $new_result = mysqli_stmt_get_result($new_q);
                $new_row = mysqli_fetch_array($new_result, MYSQLI_NUM);
                // Retrieve password
                $retrieve_query = "SELECT username, password FROM users WHERE username=?";
                $prepared = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($prepared, $retrieve_query);
                mysqli_stmt_bind_param($prepared, "s", $username);
                mysqli_stmt_execute($prepared);
                $result = mysqli_stmt_get_result($prepared);
                $row = mysqli_fetch_array($result, MYSQLI_NUM);

                if (mysqli_num_rows($result) == 1) {
                    if ($new_row[0] == $row[0]) {
                    } else {
                        $errors[] = 'The username is taken. Try a new username.';
                    }
                }
                if ($password_change_needed) {
                    if (!password_verify($old_password, $row[1])) {
                        $errors[] = 'The old password is incorrect.';
                    } else {
                        $hashed_passcode = password_hash($new_password, PASSWORD_DEFAULT);

                        $pswd_query = "UPDATE users SET password=? WHERE user_id=? LIMIT 1";
                        $pswd_q = mysqli_stmt_init($dbcon);
                        mysqli_stmt_prepare($pswd_q, $pswd_query);
                        mysqli_stmt_bind_param($pswd_q, 'si', $hashed_passcode, $id);
                        mysqli_stmt_execute($pswd_q);

                        if (mysqli_stmt_affected_rows($pswd_q) == 1) { // Update OK

                        } else { // print a message if the query failed.
                            print '<p>The user could not be edited due to a system error.';
                            print ' We apologize for any inconvenience.</p>'; // Public message.
                            //print '<p>' . mysqli_error($dbcon) . '<br />Query: ' . $q . '</p>';
                        }
                    }
                }
                if (empty($errors)) { // The username is unique and the password is correct

                    $query = "UPDATE users SET first_name=?, last_name=?, sex=?, phone=?, email=?, username=? WHERE user_id=? LIMIT 1";
                    $q = mysqli_stmt_init($dbcon);
                    mysqli_stmt_prepare($q, $query);
                    mysqli_stmt_bind_param($q, 'ssssssi', $first_name, $last_name, $sex, $phone, $email, $username, $id);
                    mysqli_stmt_execute($q);

                    if (mysqli_stmt_affected_rows($q) == 1) { // Update OK
                        header('location: ../../../php/user-tasks/successful-profile-update.php');
                    } else { // print a message if the query failed.
                        if ($password_change_needed) {
                            header('location: ../../../php/user-tasks/successful-profile-update.php');
                        } else {
                            $errors[] = 'You clicked the button without changing anything.';
                            //print '<p>' . mysqli_error($dbcon) . '<br />Query: ' . $q . '</p>';
                        }
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
    }

    // Populate form data from database
    $q = mysqli_stmt_init($dbcon);
    $query = "SELECT first_name, last_name, sex, phone, email, username FROM users WHERE user_id=?";
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'i', $id);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    if (mysqli_num_rows($result) == 1) { // Valid user ID, display the form.
        // Get the user's information:
        // Create the form:
        $firstName = htmlspecialchars($row[0], ENT_QUOTES);
        $lastName = htmlspecialchars($row[1], ENT_QUOTES);
        $gender = htmlspecialchars($row[2], ENT_QUOTES);
        $phoneNumber = htmlspecialchars($row[3], ENT_QUOTES);
        $emailAddress = htmlspecialchars($row[4], ENT_QUOTES);
        $userName = htmlspecialchars($row[5], ENT_QUOTES);

        print '
            <form action="my-profile.php" method="post">
                <div class="input-group mb-3">
                    <label for="f-name" class="form-label w-35 pt-1 pe-3 text-end">First name</label>
                    <input type="text" class="form-control ps-4" pattern="[a-zA-Z]+" minlength="2" maxlength="20" title="Must be 2 to 20 Alphabetic Characters" name="f-name" id="f-name" value="' . $firstName . '" />
                </div>
                <div class="input-group mb-3">
                    <label for="l-name" class="form-label w-35 pt-1 pe-3 text-end">Last name</label>
                    <input type="text" class="form-control ps-4" pattern="[a-zA-Z]+" minlength="2" maxlength="20" title="Must be 2 to 20 Alphabetic Characters" name="l-name" id="l-name" value="' . $lastName . '" />
                </div>';
        if ($gender == "Male") {
            print '
                <div class="input-group mb-3">
                    <label for="sex" class="form-label w-35 pt-1 pe-3 text-end">Sex</label>
                    <select name="sex" id="sex" class="form-select">
                        <option value="Male" selected>Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>';
        } else {
            print '
                <div class="input-group mb-3">
                    <label for="sex" class="form-label w-35 pt-1 pe-3 text-end">Sex</label>
                    <select name="sex" id="sex" class="form-select">
                        <option value="Male">Male</option>
                        <option value="Female" selected>Female</option>
                    </select>
                </div>';
        }
        print '
                <div class="input-group mb-3">
                    <label for="e-mail" class="form-label w-35 pt-1 pe-3 text-end">Email address</label>
                    <input type="email" class="form-control ps-4" name="e-mail" id="e-mail" maxlength="40" value="' . $emailAddress . '" />
                </div>
                <div class="input-group mb-3">
                    <label for="phone" class="form-label w-35 pt-1 pe-3 text-end">Phone number</label>
                    <input type="tel" class="form-control ps-4" minlength="10" maxlength="10" name="phone" id="phone" value="' . $phoneNumber . '" />
                </div>
                <div class="input-group mb-3">
                    <label for="username" class="form-label w-35 pt-1 pe-3 text-end">User name</label>
                    <input type="text" class="form-control ps-4" minlength="5" maxlength="15" title="Must be 5 to 15 Characters Long" name="username" id="username" value="' . $userName . '" />
                </div>
                <fieldset>
                    <legend class="fs-5">Change password</legend>
                    <div class="input-group mb-3">
                        <label for="old-password" class="form-label w-35 pt-1 pe-3 text-end">Old password</label>
                        <input type="password" class="form-control ps-4" minlength="8" maxlength="15" name="old-password" id="old-password" />
                    </div>
                    <div class="input-group mb-3">
                        <label for="new-password" class="form-label w-35 pt-1 pe-3 text-end">New password</label>
                        <input type="password" class="form-control ps-4" minlength="8" maxlength="15" name="new-password" id="new-password" />
                    </div>
                    <div class="input-group mb-3">
                        <label for="confirm-password" class="form-label w-35 pt-1 pe-3 text-end">Confirm password</label>
                        <input type="password" class="form-control ps-4" minlength="8" maxlength="15" name="confirm-password" id="confirm-password" />
                    </div>
                    <!-- For JavaScript validation -->
                    <p id="password-feedback"></p>
                </fieldset>
                <input type="submit" name="update" value="UPDATE PROFILE" class="fs-5 btn btn-success rounded-pill mt-4 py-1 w-50" />
            </form>';
    } else {
        print '<p>This page has been accessed in error.</p>';
    } // Populating the form ended

    mysqli_close($dbcon);
} catch (Exception $e) {
    print "The system is busy. Please try later";
    //print "An Exception occurred. Message: " . $e->getMessage();
} catch (Error $e) {
    print "The system is currently buys. Please try later";
    //print "An Error occurred. Message: " . $e->getMessage();
}
