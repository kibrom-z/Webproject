<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Manager")) {
    header('location: ../../sign-in.php');
    exit();
}
// The code looks for a valid user ID, either through GET or POST:
if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
    $trip_identifier = htmlspecialchars($_GET['id'], ENT_QUOTES);
} elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
    $trip_identifier = htmlspecialchars($_POST['id'], ENT_QUOTES);
} else { // No valid ID, kill the script.
    print '<p>This page has been accessed in error.</p>';
    exit();
}

try {

    require('../../../php/mysqli-connect.php');

    $query = "SELECT reservation_id, first_name, last_name, sex, phone, boarding_place, trip_id, seat_number, ";
    $query .= "passenger_has_attended FROM reservations WHERE trip_id=? ORDER BY seat_number ASC";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'i', $trip_identifier);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);

    print '
    <table class="table table-striped table-success">
        <tr>
            <th>Seat №</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Sex</th>
            <th>Phone Number</th>
            <th>Boarding Place</th>
            <th>Trip ID</th>
            <th>Attended</th>
        </tr>';

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

        $reservation_id = htmlspecialchars($row['reservation_id'], ENT_QUOTES);
        $first_name = htmlspecialchars($row['first_name'], ENT_QUOTES);
        $last_name = htmlspecialchars($row['last_name'], ENT_QUOTES);
        $sex = htmlspecialchars($row['sex'], ENT_QUOTES);
        $phone = htmlspecialchars($row['phone'], ENT_QUOTES);
        $boarding_place = htmlspecialchars($row['boarding_place'], ENT_QUOTES);
        $trip_id = htmlspecialchars($row['trip_id'], ENT_QUOTES);
        $seat = htmlspecialchars($row['seat_number'], ENT_QUOTES);
        $passenger_boarded = htmlspecialchars($row['passenger_has_attended'], ENT_QUOTES);

        print '
        <td>' . $seat . '</td>
        <td>' . $first_name . '</td>
        <td>' . $last_name . '</td>
        <td>' . $sex . '</td>
        <td>' . $phone . '</td>
        <td>' . $boarding_place . '</td>
        <td>' . $trip_id . '</td>
        <td>' . $passenger_boarded . '</td>
        </tr>';
    }
    print '</table>';

    $qry = "SELECT COUNT(reservation_id) FROM reservations WHERE trip_id=$trip_identifier";
    $output = mysqli_query($dbcon, $qry);
    $entry = mysqli_fetch_array($output, MYSQLI_NUM);
    $total = htmlspecialchars($entry[0], ENT_QUOTES);
    mysqli_free_result($output);
    print '<div class="field"><p>Total number of reservations: ' . $total . '</p></div>';
} catch (Exception $e) {
    print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
