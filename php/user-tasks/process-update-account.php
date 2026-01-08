<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "System Administrator")) {
    header('location: ../../sign-in.php');
    exit();
}

try {
    // The code looks for a valid user ID, either through GET or POST:
    if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
        $id = htmlspecialchars($_GET['id'], ENT_QUOTES);
    } elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
        $id = htmlspecialchars($_POST['id'], ENT_QUOTES);
    } else { // No valid ID, kill the script.
        print '<p>This page has been accessed in error.</p>';
        exit();
    }

    require('../../../php/mysqli-connect.php');
    require('../../../php/error-style2.php');

    // Get the account type to differentiate between passengers and employee
    $other_q = mysqli_stmt_init($dbcon);
    $other_query = "SELECT account_type FROM users WHERE user_id=?";
    mysqli_stmt_prepare($other_q, $other_query);
    mysqli_stmt_bind_param($other_q, 'i', $id);
    mysqli_stmt_execute($other_q);
    $other_result = mysqli_stmt_get_result($other_q);
    $other_row = mysqli_fetch_array($other_result, MYSQLI_NUM);
    if (mysqli_num_rows($other_result) == 1) {
        $user_type = htmlspecialchars($other_row[0], ENT_QUOTES);
    } else {
        print '<p>This page has been accessed in error.</p>';
    }

    // Has the form been submitted?
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($user_type == "Passenger") {
            $account_status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

            $query = 'UPDATE users SET account_status=? WHERE user_id=? LIMIT 1';
            $q = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q, $query);
            mysqli_stmt_bind_param($q, 'si', $account_status, $id);
            mysqli_stmt_execute($q);
            if (mysqli_stmt_affected_rows($q) == 1) { // Update OK
                header('location: ../../../php/user-tasks/successful-account-update.php');
            } else {
                $errors[] = 'You clicked the button with out changing anything';
            }
        } else {
            $account_type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
            $account_status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);

            $query = 'UPDATE users SET account_type=?, account_status=? WHERE user_id=? LIMIT 1';
            $q = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q, $query);
            mysqli_stmt_bind_param($q, 'ssi', $account_type, $account_status, $id);
            mysqli_stmt_execute($q);
            if (mysqli_stmt_affected_rows($q) == 1) { // Update OK
                header('location: ../../../php/user-tasks/successful-account-update.php');
            } else { // print a message if the query failed.
                $errors[] = 'You clicked the button with out changing anything';
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

    // Populating the form from the database
    $q = mysqli_stmt_init($dbcon);
    $query = "SELECT first_name, last_name, account_type, account_status FROM users WHERE user_id=?";
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'i', $id);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    if (mysqli_num_rows($result) == 1) { // Valid user ID, display the form.
        // Get the user's information:
        // Create the form:
        $first_name = htmlspecialchars($row[0], ENT_QUOTES);
        $last_name = htmlspecialchars($row[1], ENT_QUOTES);
        $type = htmlspecialchars($row[2], ENT_QUOTES);
        $status = htmlspecialchars($row[3], ENT_QUOTES);

        print
            '<form action="update-account.php?id=' . $id . '" method="post">
            <fieldset class="border border-secondary rounded p-3 mb-3">
                <legend  class="fs-5 mb-3">User Information</legend>
                <div class="input-group mb-3">
                    <label for="f-name"  class="form-label w-35 pt-1 pe-3 text-end">First name</label>
                    <input type="text" class="form-control text-center" name="f-name" id="f-name" value="' . $first_name . '" disabled />
                </div>
                <div class="input-group mb-3">
                    <label for="l-name"  class="form-label w-35 pt-1 pe-3 text-end">Last name</label>
                    <input type="text" class="form-control text-center" name="l-name" id="l-name" value="' . $last_name . '" disabled />
                </div>
                <div class="input-group mb-3">
                    <label for="account-type"  class="form-label w-35 pt-1 pe-3 text-end">Account type</label>
                    <input type="text" class="form-control text-center" name="account-type" id="account-type" value="' . $type . '" disabled />
                </div>
                <div class="input-group mb-3">
                    <label for="account-status"  class="form-label w-35 pt-1 pe-3 text-end">Account status</label>
                    <input type="text" class="form-control text-center" name="account-status" id="account-status" value="' . $status . '" disabled />
                </div>
            </fieldset>';
        // Passengers account type must never change
        if ($type !== "Passenger") {
            switch ($type) {
                case "Bus Attendant":
                    print '
                        <div class="input-group mb-3">
                            <label for="acc-type" class="form-label w-35 pt-1 pe-3 text-end">Change account type</label>
                            <select name="type" id="acc-type" class="form-select text-center" autofocus>
                                <option selected value="Bus Attendant">Bus Attendant</option>
                                <option value="Dispatcher">Dispatcher</option>
                                <option value="Finance Officer">Finance Officer</option>
                                <option value="Manager">Manager</option>
                                <option value="System Administrator">System Administrator</option>
                            </select>
                        </div>';
                    break;
                case "Dispatcher":
                    print '
                        <div class="input-group mb-3">
                            <label for="acc-type" class="form-label w-35 pt-1 pe-3 text-end">Change account type</label>
                            <select name="type" id="acc-type" class="form-select text-center" autofocus>
                                <option value="Bus Attendant">Bus Attendant</option>
                                <option selected value="Dispatcher">Dispatcher</option>
                                <option value="Finance Officer">Finance Officer</option>
                                <option value="Manager">Manager</option>
                                <option value="System Administrator">System Administrator</option>
                            </select>
                        </div>';
                    break;
                case "Finance Officer":
                    print '
                        <div class="input-group mb-3">
                            <label for="acc-type" class="form-label w-35 pt-1 pe-3 text-end">Change account type</label>
                            <select name="type" id="acc-type" class="form-select text-center" autofocus>
                                <option value="Bus Attendant">Bus Attendant</option>
                                <option value="Dispatcher">Dispatcher</option>
                                <option selected value="Finance Officer">Finance Officer</option>
                                <option value="Manager">Manager</option>
                                <option value="System Administrator">System Administrator</option>
                            </select>
                        </div>';
                    break;
                case "Manager":
                    print '
                        <div class="input-group mb-3">
                            <label for="acc-type" class="form-label w-35 pt-1 pe-3 text-end">Change account type</label>
                            <select name="type" id="acc-type" class="form-select text-center" autofocus>
                                <option value="Bus Attendant">Bus Attendant</option>
                                <option value="Dispatcher">Dispatcher</option>
                                <option value="Finance Officer">Finance Officer</option>
                                <option selected value="Manager">Manager</option>
                                <option value="System Administrator">System Administrator</option>
                            </select>
                        </div>';
                    break;
                default:
                    print '
                        <div class="input-group mb-3">
                            <label for="acc-type" class="form-label w-35 pt-1 pe-3 text-end">Change account type</label>
                            <select name="type" id="acc-type" class="form-select text-center" autofocus>
                                <option value="Bus Attendant">Bus Attendant</option>
                                <option value="Dispatcher">Dispatcher</option>
                                <option value="Finance Officer">Finance Officer</option>
                                <option value="Manager">Manager</option>
                                <option selected value="System Administrator">System Administrator</option>
                            </select>
                        </div>';
                    break;
            }
        }
        if ($status == "Active") {
            print '
                <div class="input-group mb-3">
                    <label for="acc-status" class="form-label w-35 pt-1 pe-3 text-end">Change status</label>
                    <select name="status" id="acc-status" class="form-select text-center">
                        <option selected value="Active">Active</option>
                        <option value="Deactive">Deactive</option>
                    </select>
                </div>';
        } else {
            print '
                <div class="input-group mb-3">
                    <label for="acc-status" class="form-label w-35 pt-1 pe-3 text-end">Change status</label>
                    <select name="status" id="acc-status" class="form-select text-center">
                        <option value="Active">Active</option>
                        <option selected value="Deactive">Deactive</option>
                    </select>
                </div>';
        }
        print '
            <a href="user-accounts.php">
                <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-5 me-lg-5" id="back-button" /></a>
            <input type="submit" name="update-acc" value="UPDATE ACCOUNT" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-4 ms-lg-5" />
        </form>';
    } else {
        print '<p>This page has been accessed in error.</p>';
    } // Populating form completed

    mysqli_stmt_free_result($q);
    mysqli_close($dbcon);
} catch (Exception $e) {
    print "The system is busy. Please try later";
    //print "An Exception occurred. Message: " . $e->getMessage();
} catch (Error $e) {
    print "The system is currently buys. Please try later";
    //print "An Error occurred. Message: " . $e->getMessage();
}
