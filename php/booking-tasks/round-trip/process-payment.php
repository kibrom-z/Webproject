<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

$errors = array();

try {
    require('../../php/mysqli-connect.php');
    require('../../php/error-style2.php');

    $retrieve_query = "SELECT account_number, balance FROM bank_accounts WHERE passenger_id=?";
    $prepared = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($prepared, $retrieve_query);
    mysqli_stmt_bind_param($prepared, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($prepared);
    $result = mysqli_stmt_get_result($prepared);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $account_number = $row[0];
    $balance = $row[1];

    $retrieve_query2 = "SELECT first_name, last_name FROM users WHERE user_id=?";
    $prepared2 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($prepared2, $retrieve_query2);
    mysqli_stmt_bind_param($prepared2, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($prepared2);
    $result2 = mysqli_stmt_get_result($prepared2);
    $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
    $account_holder = $row2[0] . ' ' . $row2[1];

    // Calculate the total amount of money
    $query = "SELECT tariff FROM trips WHERE trip_id=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, "i", $_SESSION['selected-first-trip-id']);
    mysqli_stmt_execute($q);
    $output = mysqli_stmt_get_result($q);
    $single_output = mysqli_fetch_array($output, MYSQLI_NUM);
    $tariff = $single_output[0];
    $total_price = $tariff * $_SESSION['adult-number'] * 2;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($total_price <= $balance) {
            // Steps:
            // 1. Deduct the trip price from passenger and update balance
            // 2. Get the company's balance; add the price; update the balance column
            // 3. Change payment status to completed and increment bookings column in trips table
            $zemen_account = "6162";
            $new_passenger_balance = $balance - $total_price;
            $payment_status = "completed";

            // Update passenger balance
            $query2 = "UPDATE bank_accounts SET balance=? WHERE passenger_id=? LIMIT 1";
            $q2 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q2, $query2);
            mysqli_stmt_bind_param($q2, 'di', $new_passenger_balance, $_SESSION['user_id']);
            mysqli_stmt_execute($q2);

            $retrieve_query3 = "SELECT balance FROM bank_accounts WHERE account_number=?";
            $prepared3 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($prepared3, $retrieve_query3);
            mysqli_stmt_bind_param($prepared3, "s", $zemen_account);
            mysqli_stmt_execute($prepared3);
            $result3 = mysqli_stmt_get_result($prepared3);
            $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
            $zemen_balance = $row3[0];

            $new_zemen_balance = $zemen_balance + $total_price;

            // Update the company's balance
            $query3 = "UPDATE bank_accounts SET balance=? WHERE account_number=? LIMIT 1";
            $q3 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q3, $query3);
            mysqli_stmt_bind_param($q3, 'di', $new_zemen_balance, $zemen_account);
            mysqli_stmt_execute($q3);

            // Change payment status from pending to completed
            foreach ($_SESSION['reservation-ids'] as $reservation) {

                $query4 = "UPDATE reservations SET payment_status=? WHERE reservation_id=?";
                $q4 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q4, $query4);
                mysqli_stmt_bind_param($q4, 'si', $payment_status, $reservation);
                mysqli_stmt_execute($q4);
            }

            // Retrieve the current booking number for first trip and increase
            $retrieve_query4 = "SELECT bookings FROM trips WHERE trip_id=?";
            $prepared4 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($prepared4, $retrieve_query4);
            mysqli_stmt_bind_param($prepared4, "i", $_SESSION['selected-first-trip-id']);
            mysqli_stmt_execute($prepared4);
            $result4 = mysqli_stmt_get_result($prepared4);
            $row4 = mysqli_fetch_array($result4, MYSQLI_NUM);
            $bookings = $row4[0];
            $new_bookings = $bookings + $_SESSION['adult-number'];

            $query5 = "UPDATE trips SET bookings=? WHERE trip_id=?";
            $q5 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q5, $query5);
            mysqli_stmt_bind_param($q5, 'ii', $new_bookings, $_SESSION['selected-first-trip-id']);
            mysqli_stmt_execute($q5);

            // Retrieve the current booking number for return trip and increase
            $retrieve_query6 = "SELECT bookings FROM trips WHERE trip_id=?";
            $prepared6 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($prepared6, $retrieve_query6);
            mysqli_stmt_bind_param($prepared6, "i", $_SESSION['selected-return-trip-id']);
            mysqli_stmt_execute($prepared6);
            $result6 = mysqli_stmt_get_result($prepared6);
            $row6 = mysqli_fetch_array($result6, MYSQLI_NUM);
            $bookings2 = $row6[0];
            $new_bookings2 = $bookings2 + $_SESSION['adult-number'];

            // Increase the number of bookings by the number of reservations
            $query7 = "UPDATE trips SET bookings=? WHERE trip_id=?";
            $q7 = mysqli_stmt_init($dbcon);
            mysqli_stmt_prepare($q7, $query7);
            mysqli_stmt_bind_param($q7, 'ii', $new_bookings2, $_SESSION['selected-return-trip-id']);
            mysqli_stmt_execute($q7);

            if ((mysqli_stmt_affected_rows($q2) == 1) &&
                (mysqli_stmt_affected_rows($q3) == 1) &&
                (mysqli_stmt_affected_rows($q4) > 0) &&
                (mysqli_stmt_affected_rows($q5) == 1) &&
                (mysqli_stmt_affected_rows($q7) == 1)
            ) {
                header('location: ../../php/booking-tasks/round-trip/successful-booking.php');
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
            <input type="text" class="form-control ps-4" name="account-holder" id="acc-hold" value="' . $account_holder . '" disabled />
        </div>
        <div class="input-group mb-3">
            <label for="acc-num" class="form-label w-35 pt-1 pe-3 text-end">Account number</label>
            <input type="number" class="form-control ps-4" name="account-number" id="acc-num" value="' . $account_number . '" disabled />
        </div>
        <div class="input-group mb-3">
            <label for="bal" class="form-label w-35 pt-1 pe-3 text-end">Balance</label>
            <input type="text" class="form-control ps-4" name="balance" id="bal" value="' . $balance . ' Birr" disabled />
        </div>
    </fieldset>
    <fieldset class="border border-secondary rounded mb-3 p-3">
        <legend class="fs-5 mb-3 text-success">Transfer Money</legend>
        <form action="Round-trip-simulator.php" method="post">
            <div class="input-group mb-3">
                <label for="trans-to" class="form-label w-35 pt-1 pe-3 text-end">Transfer to</label>
                <input type="text" class="form-control ps-4" name="transfer-to" id="trans-to" value="Zemen Bus Ethiopia" disabled />
            </div>
            <div class="input-group mb-3">
                <label for="acc-num2" class="form-label w-35 pt-1 pe-3 text-end">Account number</label>
                <input type="number" class="form-control ps-4" name="account-number2" id="acc-num2" value="6162" disabled />
            </div>
            <div class="input-group mb-3">
                <label for="amount" class="form-label w-35 pt-1 pe-3 text-end">Amount</label>
                <input type="text" class="form-control ps-4" name="amount" id="amount" value="' . $total_price . ' Birr" disabled />
            </div>
            <div class="input-group mb-3">
                <label for="reason" class="form-label w-35 pt-1 pe-3 text-end">Reason</label>
                <input type="text" class="form-control ps-4" name="reason" id="reason" value="AC' . $_SESSION['user_id'] . '" disabled />
            </div>
    </fieldset>
            <input type="submit" name="transfer" value="TRANSFER MONEY" class="fs-5 btn btn-success rounded-pill mt-5 py-1 w-50" id="transfer-button" />
        </form>';
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
