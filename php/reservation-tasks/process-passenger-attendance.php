<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Bus Attendant")) {
    header('location: ../../sign-in.php');
    exit();
}

try {

    require('../../../php/mysqli-connect.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $query2 = "SELECT reservation_id, passenger_has_attended FROM reservations WHERE trip_id=?";
        $q2 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q2, $query2);
        mysqli_stmt_bind_param($q2, 'i', $_SESSION['resv-trip-id']);
        mysqli_stmt_execute($q2);
        $result2 = mysqli_stmt_get_result($q2);

        while ($row2 = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {

            $reservation_id = htmlspecialchars($row2['reservation_id'], ENT_QUOTES);
            $passenger_has_attended = htmlspecialchars($row2['passenger_has_attended'], ENT_QUOTES);
            $is_check_box_checked = isset($_POST['has-attended' . $reservation_id . '']);

            if ($passenger_has_attended == "No" && $is_check_box_checked) {
                $has_attended = "Yes";
                // Update the column
                $query3 = "UPDATE reservations SET passenger_has_attended=? WHERE reservation_id=? LIMIT 1";
                $q3 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q3, $query3);
                mysqli_stmt_bind_param($q3, 'si', $has_attended, $reservation_id);
                mysqli_stmt_execute($q3);
            }
            if ($passenger_has_attended == "Yes" && (!$is_check_box_checked)) {
                $has_not_attended = "No";
                // Update the column
                $query4 = "UPDATE reservations SET passenger_has_attended=? WHERE reservation_id=? LIMIT 1";
                $q4 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q4, $query4);
                mysqli_stmt_bind_param($q4, 'si', $has_not_attended, $reservation_id);
                mysqli_stmt_execute($q4);
            }
        }
        header('location: ../../../php/reservation-tasks/successful-attendance-submit.php');
    }

    $query = "SELECT reservation_id, first_name, last_name, sex, phone, boarding_place, trip_id, seat_number, ";
    $query .= "passenger_has_attended FROM reservations WHERE trip_id=? ORDER BY seat_number ASC";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'i', $_SESSION['resv-trip-id']);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);

    print '
    <table class="table table-success table-striped">
        <tr>
            <th>Seat №</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Sex</th>
            <th>Phone Number</th>
            <th>Boarding Place</th>
            <th>Trip ID</th>
            <th>Attendance</th>
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
        <td>' . $trip_id . '</td>';
        if ($passenger_boarded == "Yes") {
            print '<td><input type="checkbox" name="has-attended' . $reservation_id . '" id="has-attended' . $reservation_id . '" checked /></td>';
        } else {
            print '<td><input type="checkbox" name="has-attended' . $reservation_id . '" id="has-attended' . $reservation_id . '" /></td>';
        }
        print '</tr>';
    }
    print '</table>';

    $qry = "SELECT COUNT(reservation_id) FROM reservations WHERE trip_id={$_SESSION['resv-trip-id']}";
    $output = mysqli_query($dbcon, $qry);
    $entry = mysqli_fetch_array($output, MYSQLI_NUM);
    $total = htmlspecialchars($entry[0], ENT_QUOTES);
    mysqli_free_result($output);
    print '<p>Total number of reservations: ' . $total . '</p>';

    mysqli_close($dbcon);
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
