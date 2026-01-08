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

    // Has the form been submitted?
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $query = 'DELETE FROM users WHERE user_id=? LIMIT 1';
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, 'i', $id);
        mysqli_stmt_execute($q);
        if (mysqli_stmt_affected_rows($q) == 1) { // Delete OK
            header('location: ../../../php/user-tasks/successful-account-deletion.php');
        } else { // print a message if the query failed.
            print '<p>The user could not be deleted due to a system error.';
            print ' We apologize for any inconvenience.</p>'; // Public message.
            //print '<p>' . mysqli_error($dbcon) . '<br />Query: ' . $q . '</p>';
        }
    } else {
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
                '<form action="delete-account.php?id=' . $id . '" method="post">
                <fieldset class="border border-secondary rounded p-3 mb-3">
                    <legend class="fs-5 mb-3">User Information</legend>
                    <div class="input-group mb-3">
                        <label for="f-name" class="form-label w-35 pt-1 pe-3 text-end">First name</label>
                        <input type="text" class="form-control text-center" name="f-name" id="f-name" value="' . $first_name . '" disabled />
                    </div>
                    <div class="input-group mb-3">
                        <label for="l-name" class="form-label w-35 pt-1 pe-3 text-end">Last name</label>
                        <input type="text" class="form-control text-center" name="l-name" id="l-name" value="' . $last_name . '" disabled />
                    </div>
                    <div class="input-group mb-3">
                        <label for="account-type" class="form-label w-35 pt-1 pe-3 text-end">Account type</label>
                        <input type="text" class="form-control text-center" name="account-type" id="account-type" value="' . $type . '" disabled />
                    </div>
                    <div class="input-group mb-3">
                        <label for="account-status" class="form-label w-35 pt-1 pe-3 text-end">Account status</label>
                        <input type="text" class="form-control text-center" name="account-status" id="account-status" value="' . $status . '" disabled />
                    </div>
                </fieldset>
                <p class="text-danger fs-5">Are you sure you want to delete this account?<br />The action can not be undone.</p>
                <a href="user-accounts.php">
                    <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-5 me-lg-5" id="back-button" /></a>
                <input type="submit" name="delete-acc" value="DELETE ACCOUNT" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-4 ms-lg-5" />
            </form>';
        } else {
            print '<p>This page has been accessed in error.</p>';
        }
    }
    mysqli_stmt_free_result($q);
    mysqli_close($dbcon);
} catch (Exception $e) {
    print "The system is busy. Please try later";
    //print "An Exception occurred. Message: " . $e->getMessage();
} catch (Error $e) {
    print "The system is currently buys. Please try later";
    //print "An Error occurred. Message: " . $e->getMessage();
}
