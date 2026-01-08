<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Finance Officer")) {
    header('location: ../../sign-in.php');
    exit();
}

// The code looks for a valid user ID, either through GET or POST:
if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
    $request_id = htmlspecialchars($_GET['id'], ENT_QUOTES);
} elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
    $request_id = htmlspecialchars($_POST['id'], ENT_QUOTES);
} else { // No valid ID, kill the script.
    print '<p>This page has been accessed in error.</p>';
    exit();
}

try {

    require('../../../php/mysqli-connect.php');

    $query = "SELECT reason, issued_date, passenger_id, reservation_id, refund_amount, refund_status FROM refund_requests WHERE refund_request_id=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, "i", $request_id);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    $reason = $row[0];
    $issued_date = date_create($row[1]);
    $passenger_id = $row[2];
    $reservation_id = $row[3];
    $refund_amount = $row[4];
    $refund_status = $row[5];

    $query2 = "SELECT first_name, last_name, trip_id FROM reservations WHERE reservation_id=?";
    $q2 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q2, $query2);
    mysqli_stmt_bind_param($q2, "i", $reservation_id);
    mysqli_stmt_execute($q2);
    $result2 = mysqli_stmt_get_result($q2);
    $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
    $first_name = $row2[0];
    $last_name = $row2[1];
    $full_name = $first_name . " " . $last_name;
    $trip_id = $row2[2];

    $query3 = "SELECT departure_date FROM trips WHERE trip_id=?";
    $q3 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q3, $query3);
    mysqli_stmt_bind_param($q3, "i", $trip_id);
    mysqli_stmt_execute($q3);
    $result3 = mysqli_stmt_get_result($q3);
    $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
    $departure_date = date_create($row3[0]);

    $query4 = "SELECT phone, bank_acc_number FROM users WHERE user_id=?";
    $q4 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q4, $query4);
    mysqli_stmt_bind_param($q4, "i", $passenger_id);
    mysqli_stmt_execute($q4);
    $result4 = mysqli_stmt_get_result($q4);
    $row4 = mysqli_fetch_array($result4, MYSQLI_NUM);
    $phone = $row4[0];
    $bank = $row4[1];

    // Print the retrieved information
    print '
    <div class="row bg-light mb-3 rounded-4 p-3 border-start border-top border-end border-bottom border-secondary">
        <div class="row text-dark">
            <div class="col text-start"><p>Passenger: <span class="fst-italic">' . $full_name . '</span></p></div>
            <div class="col text-end"><p>Bank account: <span class="fst-italic">' . $bank . '</span></p></div>
        </div>
        <div class="row text-dark">
            <div class="col text-start"><p>Phone number: <span class="fst-italic">' . $phone . '</span></p></div>
        </div>
        <div class="row text-dark border-top">
            <div class="col text-start"><p>Trip date: <span class="fst-italic">' . $departure_date->format('M d, Y') . '</span></p></div>
            <div class="col text-end"> <p>Issued on: <span class="fst-italic">' . $issued_date->format('M d, Y') . '</span></p></div>
        </div>
        <div class="row text-dark border-bottom">
            <div class="col text-start"><p>Refund reason: <span class="fst-italic">' . $reason . '</span></p></div>
        </div>
        <div class="row text-dark">
            <div class="col text-start"><p>Refund status: <span class="fst-italic">' . $refund_status . '</span></p></div>
            <div class="col text-end"><p>Refund amount: <span class="fst-italic">' . $refund_amount . ' Birr </span></p></div>
        </div>
    </div>';

    if ($refund_status == "pending") {
        print '
        <a href="refund-requests.php">
            <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-5 py-1 px-5 me-lg-5" id="go-back" />
        </a>
        <a href="../../bank-system/Finance-officer-simulator.php?id=' . $request_id . '">
            <input type="button" name="change" value="REFUND MONEY" class="fs-5 btn btn-success rounded-pill mt-5 py-1 px-4 ms-lg-5" id="change-status" />
        </a>';
    } else if ($refund_status == "processed") {
        print '
        <a href="refund-requests.php">
            <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-5 py-1 w-35" id="go-back" />
        </a>';
    }
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
