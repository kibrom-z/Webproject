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
        // Two routes are updated together (as they were created at once)

        // Select the departure and arrival of the route selected to be edited
        // These two information are used to find the other roted
        $second_query = "SELECT departure_place, arrival_place FROM routes WHERE route_id=?";
        $second_q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($second_q, $second_query);
        mysqli_stmt_bind_param($second_q, 'i', $id);
        mysqli_stmt_execute($second_q);
        $second_result = mysqli_stmt_get_result($second_q);
        $second_row = mysqli_fetch_array($second_result, MYSQLI_NUM);
        if (mysqli_num_rows($second_result) == 1) {
            $second_departure = htmlspecialchars($second_row[0], ENT_QUOTES);
            $second_arrival = htmlspecialchars($second_row[1], ENT_QUOTES);
        } else {
            print '<p>This page has been accessed in error.</p>';
        }

        // Start updating
        $distance_in_km = filter_var($_POST['distance'], FILTER_SANITIZE_NUMBER_FLOAT);

        // Updates the selected route based on its id
        $query = "UPDATE routes SET distance_in_km=? WHERE route_id=? LIMIT 1";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, 'di', $distance_in_km, $id);
        mysqli_stmt_execute($q);

        // Updates the other route using the two infomation
        $third_query = "UPDATE routes SET distance_in_km=? WHERE departure_place=? AND arrival_place=? LIMIT 1";
        $third_q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($third_q, $third_query);
        mysqli_stmt_bind_param($third_q, 'dss', $distance_in_km, $second_arrival, $second_departure); // Crafty place switch
        mysqli_stmt_execute($third_q);

        if ((mysqli_stmt_affected_rows($q) == 1) && (mysqli_stmt_affected_rows($third_q) == 1)) { // Update OK
            header('location: ../../../php/route-tasks/successful-route-update.php');
        } else { // print a message if the query failed.
            $errors[] = 'You clicked the button with out changing anything.';
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
    $q = mysqli_stmt_init($dbcon);
    $query = "SELECT departure_place, arrival_place, distance_in_km FROM routes WHERE route_id=?";
    mysqli_stmt_prepare($q, $query);
    mysqli_stmt_bind_param($q, 'i', $id);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);
    if (mysqli_num_rows($result) == 1) { // Valid user ID, display the form.
        // Get the user's information:
        // Create the form:
        $departure = htmlspecialchars($row[0], ENT_QUOTES);
        $arrival = htmlspecialchars($row[1], ENT_QUOTES);
        $distance = htmlspecialchars($row[2], ENT_QUOTES);
        print '
        <form action="update-route.php?id=' . $id . '" method="post">
            <fieldset class="border border-secondary rounded p-3 mb-3">
                <legend class="fs-5 mb-3">Route Information</legend>
                <div class="input-group mb-3">
                    <label for="dep-place" class="form-label w-35 pt-1 pe-3 text-end">Departure place</label>
                    <input type="text" class="form-control text-center" name="dep-place" id="dep-place" min="2" max="30" value="' . $departure . '" disabled>
                </div>
                <div class="input-group mb-3">
                    <label for="arv-place" class="form-label w-35 pt-1 pe-3 text-end">Arrival place</label>
                    <input type="text" class="form-control text-center" name="arv-place" id="arv-place" min="2" max="30" value="' . $arrival . '" disabled>
                </div>
            </fieldset>
            <div class="input-group mb-3">
                <label for="distance" class="form-label w-35 pt-1 pe-3 text-end">Distance in km</label>
                <input type="number" class="form-control text-center" name="distance" id="distance" min="0" max="2000" value="' . $distance . '" />
            </div>
            <a href="manage-route.php">
            <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-5 me-lg-5" id="back-button" /></a>
            <input type="submit" name="update-route" id="update-route" value="UPDATE ROUTE" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-4 ms-lg-5" />
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
