<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../sign-in.php');
    exit();
}
try {

    require('../../php/mysqli-connect.php');
    foreach ($_SESSION['reservation-ids'] as $element) {
        // Retrieve the reservation detail using the reservation id
        $query = "SELECT first_name, last_name FROM reservations WHERE reservation_id=?";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, "i", $element);
        mysqli_stmt_execute($q);
        $res = mysqli_stmt_get_result($q);
        $value = mysqli_fetch_array($res, MYSQLI_NUM);
        $first_name = $value[0];
        $last_name = $value[1];
        print '
        <div class="row border border-success rounded text-start w-50 m-auto mb-3">
            <a href="edit-passenger-detail.php?id=' . $element . '" class="text-decoration-none">
                <div class="row text-success"><p>Adult</p></div>
                <div class="row text-success fw-bold border-top border-bottom">
                    <p>
                        ' . $first_name . ' — ' . $last_name . '<img src="../../images/icons/checkmark_yes_20px.png" alt="checkmark" class="float-end" />
                    </p>
                </div>
                <div class="row text-secondary"><p>Click to edit</p></div>
            </a>
        </div>';
    }
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
