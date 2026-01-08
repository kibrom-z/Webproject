<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Finance Officer")) {
    header('location: ../../sign-in.php');
    exit();
}

try {
    require('../../../php/mysqli-connect.php');

    $query = "SELECT * FROM refund_requests";
    $result = mysqli_query($dbcon, $query);

    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

            $refund_request_id = htmlspecialchars($row['refund_request_id'], ENT_QUOTES);
            $reservation_id = htmlspecialchars($row['reservation_id'], ENT_QUOTES);
            $reason = htmlspecialchars($row['reason'], ENT_QUOTES);
            $issued_date = date_create(htmlspecialchars($row['issued_date'], ENT_QUOTES));
            $refund_status = htmlspecialchars($row['refund_status'], ENT_QUOTES);

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

            // Print the retrieved information
            print '
            <div class="row bg-light mb-3 rounded-4 p-3 border-start border-top border-end border-bottom" id="request-div">
                <a href="selected-request.php?id=' . $refund_request_id . '" class="text-decoration-none">
                    <div class="row text-dark">
                        <div class="col text-start"><p>Passenger: <span class="fst-italic">' . $full_name . '</span></p></div>
                    </div>
                    <div class="row text-dark border-top">
                        <div class="col text-start"><p>Trip date: <span class="fst-italic">' . $departure_date->format('M d, Y') . '</span></p></div>
                        <div class="col text-end"><p>Issued on: <span class="fst-italic">' . $issued_date->format('M d, Y') . '</span></p></div>
                    </div>
                    <div class="row text-dark border-bottom">
                        <div class="col text-start"><p>Refund reason: <span class="fst-italic">' . $reason . '</span></p></div>
                    </div>
                    <div class="row text-dark">
                        <div class="col text-start"><p >Refund status: <span class="fst-italic">' . $refund_status . '</span></p></div>
                    </div>
                </a>
            </div>';
        }
    } else {
        print '<p>Refund requests will appear here.</p>';
    }
} catch (Exception $e) {
    print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
