<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Finance Officer")) {
    header('location: ../../sign-in.php');
    exit();
}

// The code looks for a valid user ID, either through GET or POST:
if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
    $refund_request_id = htmlspecialchars($_GET['id'], ENT_QUOTES);
} elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
    $refund_request_id = htmlspecialchars($_POST['id'], ENT_QUOTES);
} else { // No valid ID, kill the script.
    print '<p>This page has been accessed in error.</p>';
    exit();
}

$errors = array();

try {
    require('../../php/mysqli-connect.php');
    require('../../php/error-style2.php');

    $query = "SELECT passenger_id, refund_amount FROM refund_requests WHERE refund_request_id=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, "i", $refund_request_id);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $passenger_id = $row[0];
    $refund_amount = $row[1];

    $retrieve_query2 = "SELECT first_name, last_name FROM users WHERE user_id=?";
    $prepared2 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($prepared2, $retrieve_query2);
    mysqli_stmt_bind_param($prepared2, "i", $passenger_id);
    mysqli_stmt_execute($prepared2);
    $result2 = mysqli_stmt_get_result($prepared2);
    $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
    $account_holder = $row2[0] . ' ' . $row2[1];

    $retrieve_query = "SELECT account_number, balance FROM bank_accounts WHERE passenger_id=?";
    $prepared = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($prepared, $retrieve_query);
    mysqli_stmt_bind_param($prepared, "i", $passenger_id);
    mysqli_stmt_execute($prepared);
    $result = mysqli_stmt_get_result($prepared);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $account_number = $row[0];
    $balance = $row[1];

    $zemen_account = "6162";

    $retrieve_query3 = "SELECT balance FROM bank_accounts WHERE account_number=?";
    $prepared3 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($prepared3, $retrieve_query3);
    mysqli_stmt_bind_param($prepared3, "s", $zemen_account);
    mysqli_stmt_execute($prepared3);
    $result3 = mysqli_stmt_get_result($prepared3);
    $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
    $zemen_balance = $row3[0];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if ($refund_amount <= $zemen_balance) {

            $new_passenger_balance = $balance + $refund_amount;
            $new_zemen_balance = $zemen_balance - $refund_amount;
            $refund_status = "processed";

            // Update passenger balance
            $query2 = "UPDATE bank_accounts SET balance=? WHERE passenger_id=? LIMIT 1";
            $q2 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q2, $query2);
            mysqli_stmt_bind_param($q2, 'di', $new_passenger_balance, $passenger_id);
            mysqli_stmt_execute($q2);

            // Update the company's balance
            $query3 = "UPDATE bank_accounts SET balance=? WHERE account_number=? LIMIT 1";
            $q3 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q3, $query3);
            mysqli_stmt_bind_param($q3, 'di', $new_zemen_balance, $zemen_account);
            mysqli_stmt_execute($q3);

            // Change refund status from pending to processed
            $query4 = "UPDATE refund_requests SET refund_status=?, finance_officer_id=? WHERE refund_request_id=?";
            $q4 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q4, $query4);
            mysqli_stmt_bind_param($q4, 'sii', $refund_status, $_SESSION['user_id'], $refund_request_id);
            mysqli_stmt_execute($q4);

            if ((mysqli_stmt_affected_rows($q2) == 1) &&
                (mysqli_stmt_affected_rows($q3) == 1) &&
                (mysqli_stmt_affected_rows($q4) > 0)
            ) {
                header('location: ../../php/refund-tasks/successful-payment-refund.php');
            } else {
                $errors[] = 'You clicked the button without changing anything';
                //print '<p>' . mysqli_error($dbcon) . '<br />Query: ' . $q . '</p>';
            }
        } else {
            $errors[] = 'Your balance is insufficient.';
        }
    }

    print '
    <fieldset class="border border-secondary rounded mb-3 p-3">
        <legend class="fs-5 mb-3 text-success">Account Detail</legend>
        <div class="input-group mb-3">
            <label for="acc-hold" class="form-label w-35 pt-1 pe-3 text-end">Account holder</label>
            <input type="text" class="form-control ps-4" name="account-holder" id="acc-hold" value="Zemen Bus Ethiopia" disabled />
        </div>
        <div class="input-group mb-3">
            <label for="acc-num" class="form-label w-35 pt-1 pe-3 text-end">Account number</label>
            <input type="number" class="form-control ps-4" name="account-number" id="acc-num" value="6162" disabled />
        </div>
        <div class="input-group mb-3">
            <label for="bal" class="form-label w-35 pt-1 pe-3 text-end">Balance</label>
            <input type="text" class="form-control ps-4" name="balance" id="bal" value="' . $zemen_balance . ' Birr" disabled />
        </div>
    </fieldset>
    <fieldset class="border border-secondary rounded mb-3 p-3">
        <legend class="fs-5 mb-3 text-success">Transfer Money</legend>
        <form action="Finance-officer-simulator.php?id=' . $refund_request_id . '" method="post">
            <div class="input-group mb-3">
                <label for="trans-to" class="form-label w-35 pt-1 pe-3 text-end">Transfer to</label>
                <input type="text" class="form-control ps-4" name="transfer-to" id="trans-to" value="' . $account_holder . '" disabled />
            </div>
            <div class="input-group mb-3">
                <label for="acc-num2" class="form-label w-35 pt-1 pe-3 text-end">Account number</label>
                <input type="number" class="form-control ps-4" name="account-number2" id="acc-num2" value="' . $account_number . '" disabled />
            </div>
            <div class="input-group mb-3">
                <label for="amount" class="form-label w-35 pt-1 pe-3 text-end">Amount</label>
                <input type="text" class="form-control ps-4" name="amount" id="amount" value="' . $refund_amount . ' Birr" disabled />
            </div>
            <div class="input-group mb-3">
                <label for="reason" class="form-label w-35 pt-1 pe-3 text-end">Reason</label>
                <input type="text" class="form-control ps-4" name="reason" id="reason" value="Refund" disabled />
            </div>
            <input type="submit" name="transfer" value="TRANSFER MONEY" class="fs-5 btn btn-success rounded-pill mt-5 mb-3 py-1 w-50" id="transfer-button" />
        </form>
    </fieldset>
    <a href="../employee/finance-officer/selected-request.php?id=' . $refund_request_id . '">
        <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-5 py-1 w-35" id="back-button" /></a>
    ';
} catch (Exception $e) {
    print "An Exception occurred. Message:<br> " . $e->getMessage();
    //print "The system is busy please try later";
} catch (Error $e) {
    print "An Error occurred. Message: " . $e->getMessage();
    //print "The system is busy please try again later.";
}

if (!empty($errors)) {
    $errorstring = "ERROR! The following error(s) occurred:<br><br>";
    foreach ($errors as $msg) { // Print each error.
        $errorstring .= "— $msg<br>\n";
    }
    $errorstring .= "<br>Please try again.<br>";
    print "<p style='$error_style'>$errorstring</p>";
}
