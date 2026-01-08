<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}

// The code looks for a valid user ID, either through GET or POST:
if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
    $id = htmlspecialchars($_GET['id'], ENT_QUOTES);
} elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
    $id = htmlspecialchars($_POST['id'], ENT_QUOTES);
} else { // No valid ID, kill the script.
    print '<p>This page has been accessed in error.</p>';
    exit();
}
$_SESSION['postpone-reservation-id'] = $id;

try {

    require('../../php/mysqli-connect.php');
    require('../../php/error-style2.php');

    // Populating the form from the database
    $query2 = "SELECT trip_id FROM reservations WHERE reservation_id=?";
    $q2 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q2, $query2);
    mysqli_stmt_bind_param($q2, 'i', $id);
    mysqli_stmt_execute($q2);
    $result2 = mysqli_stmt_get_result($q2);
    $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
    $trip_id = $row2[0];

    $query = "SELECT departure_date, route_id FROM trips WHERE trip_id=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'i', $trip_id);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);

    if (mysqli_num_rows($result) == 1) {

        $date = date_create(htmlspecialchars($row[0], ENT_QUOTES));
        $route_identifier = htmlspecialchars($row[1], ENT_QUOTES);

        $query3 = "SELECT departure_place, arrival_place FROM routes WHERE route_id=?";
        $q3 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q3, $query3);
        mysqli_stmt_bind_param($q3, 'i', $route_identifier);
        mysqli_stmt_execute($q3);
        $result3 = mysqli_stmt_get_result($q3);
        $row3 = mysqli_fetch_array($result3, MYSQLI_NUM);
        $dep_place = $row3[0];
        $arv_place = $row3[1];

        // Print the form
        print '<form action="postpone.php?id=' . $id . '" method="post">
        <div class="input-group mb-3">
          <label for="old-dep-date" class="form-label w-35 pt-1 pe-3 text-end">Old departure date</label>
          <input type="date" class="form-control text-center" name="old-dep-date" id="old-dep-date" value="' . $date->format('Y-m-d') . '" />
        </div>
        <div class="input-group mb-3">
            <label for="dep-date" class="form-label w-35 pt-1 pe-3 text-end">New departure date</label>
            <input type="date" class="form-control text-center" name="dep-date" id="dep-date" required />
        </div>
        <!-- Hidden form fields -->
        <div class="input-group mb-3" hidden>
            <input type="text" name="dep-place" id="dep-place" value="' . $dep_place . '" /><br>
            <input type="text" name="arv-place" id="arv-place" value="' . $arv_place . '" /><br>
            <input type="text" name="adult-num" id="adult-num" value="1" /><br>
            <input type="text" name="child-num" id="child-num" value="0" /><br>
        </div>
        <a href="selected-trip.php?id=' . $id . '">
        <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-5 me-lg-5" /></a>
        <input type="submit" name="update-date" value="POSTPONE TRIP" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-4 ms-lg-5" />
      </form>';
    } else {
        print '<p>This page has been accessed in error.</p>';
    } // Populating form completed

    // Has the form been submitted?
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $new_departure_date = date_create(filter_var($_POST['dep-date'], FILTER_SANITIZE_STRING));
        $day_difference = date_diff($date, $new_departure_date)->format('%R%a');

        if ($day_difference > 0) {
            require('../../php/booking-tasks/auxiliary/process-find-trips.php');
        } else {
            $errors[] = 'Postpone date must be later than the first booking date.';
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

    mysqli_stmt_free_result($q);
    mysqli_close($dbcon);
} catch (Exception $e) {
    print "The system is busy. Please try later";
    //print "An Exception occurred. Message: " . $e->getMessage();
} catch (Error $e) {
    print "The system is currently busy. Please try later";
    //print "An Error occurred. Message: " . $e->getMessage();
}
