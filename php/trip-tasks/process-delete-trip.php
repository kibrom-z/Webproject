<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Dispatcher")) {
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

    // Has the form been submitted?
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $query = "DELETE FROM trips WHERE trip_id=? LIMIT 1";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, 'i', $id);
        mysqli_stmt_execute($q);

        if (mysqli_stmt_affected_rows($q) == 1) { // Update OK
            header('location: ../../../php/trip-tasks/successful-trip-delete.php');
        } else { // print a message if the query failed.
            $errors[] = 'You clicked the button with out changing anything';
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
    $query = "SELECT departure_date, departure_time, route_id, bus_plate_number FROM trips WHERE trip_id=?";
    $q = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'i', $id);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    if (mysqli_num_rows($result) == 1) { // Valid user ID, display the form.
        // Get the user's information:
        // Create the form:
        $date = htmlspecialchars($row[0], ENT_QUOTES);
        $time = htmlspecialchars($row[1], ENT_QUOTES);
        $route_id = htmlspecialchars($row[2], ENT_QUOTES);
        $bus_plate_number = htmlspecialchars($row[3], ENT_QUOTES);

        // Before printing, get the name of the route from it's table
        $query = "SELECT departure_place, arrival_place FROM routes WHERE route_id=?";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, 'i', $route_id);
        mysqli_stmt_execute($q);
        $result = mysqli_stmt_get_result($q);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);
        if (mysqli_num_rows($result) == 1) {
            $departure_place = htmlspecialchars($row[0], ENT_QUOTES);
            $arrival_place = htmlspecialchars($row[1], ENT_QUOTES);
            $route = $departure_place . ' — ' . $arrival_place;
        } else {
            print '<p>This page has been accessed in error.</p>';
        }

        // Print the form
        print '
        <form action="delete-trip.php?id=' . $id . '" method="post">
        <fieldset class="border border-secondary rounded p-3 mb-3">
            <legend class="fs-5 mb-3">Trip Information</legend>
            <div class="input-group mb-3">
                <label for="route" class="form-label w-35 pt-1 pe-3 text-end">Trip route</label>
                <input type="text" class="form-control text-center" name="route" id="route" min="2" max="30" value="' . $route . '" disabled>
            </div>
            <div class="input-group mb-3">
                <label for="bus" class="form-label w-35 pt-1 pe-3 text-end">Bus</label>
                <input type="text" class="form-control text-center" name="bus" id="bus" min="2" max="15" value="PN — ' . $bus_plate_number . '" disabled>
            </div>
            <div class="input-group mb-3">
                <label for="dep-date" class="form-label w-35 pt-1 pe-3 text-end">Departure date</label>
                <input type="date" class="form-control text-center" name="dep-date" id="dep-date" value="' . $date . '" disabled />
            </div>
            <div class="input-group mb-3">
                <label for="dep-time" class="form-label w-35 pt-1 pe-3 text-end">Departure time</label>
                <input type="time" class="form-control text-center" name="dep-time" id="dep-time" value="' . $time . '" disabled />
            </div>
        </fieldset>
        <p class="fs-5 text-danger">Are you sure you want to delete this trip?<br />The action can not be undone.</p>
        <a href="schedule-trip.php">
            <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-5 me-lg-5" id="back-button" /></a>
        <input type="submit" name="delete-trip" id="delete-trip" value="DELETE TRIP" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-4 ms-lg-5" />
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
    print "The system is currently busy. Please try later";
    //print "An Error occurred. Message: " . $e->getMessage();
}
