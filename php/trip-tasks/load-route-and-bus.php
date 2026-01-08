<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Dispatcher")) {
    header('location: ../../sign-in.php');
    exit();
}
try {
    require_once('../../../php/mysqli-connect.php');

    // Print route
    $query = "SELECT route_id, departure_place, arrival_place FROM routes";
    // Prepared statement not needed since hardcoded
    $result = mysqli_query($dbcon, $query);
    if ($result) {
        print '
        <div class="input-group mb-3">
            <label for="route" class="form-label w-35 pt-1 pe-3 text-end">Route</label>
            <select name="route" id="route" class="form-select text-center">
            <option value="" selected hidden>Selecte Here</option>';
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $route_id = htmlspecialchars($row['route_id'], ENT_QUOTES);
            $departure_place = htmlspecialchars($row['departure_place'], ENT_QUOTES);
            $arrival_place = htmlspecialchars($row['arrival_place'], ENT_QUOTES);
            print '<option value="' . $route_id . '">' . $departure_place . ' — ' . $arrival_place . '</option>';
        }
        print '</select>';
        print '</div>';
    } else {
        print
            '<p>The current routes could not be retrieved. ';
        print 'We apologize for any inconvenience.</p>';
        // Debug message:
        // print '<p>' . mysqli_error($dbcon) . '<br><br>Query: ' . $q . '</p>';
        exit;
    }

    // Print buses
    $query = "SELECT plate_number FROM buses";
    $result = mysqli_query($dbcon, $query);
    if ($result) {
        print '
        <div class="input-group mb-3">
            <label for="bus" class="form-label w-35 pt-1 pe-3 text-end">Bus</label>
            <select name="bus" id="bus" class="form-select text-center">
                <option value="" selected hidden>Selecte Here</option>';
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $plate_number = htmlspecialchars($row['plate_number'], ENT_QUOTES);
            print '<option value="' . $plate_number . '">PN — ' . $plate_number . '</option>';
        }
        print '</select>';
        print '</div>';
    } else {
        print
            '<p>The current buses could not be retrieved. ';
        print 'We apologize for any inconvenience.</p>';
        // Debug message:
        // print '<p>' . mysqli_error($dbcon) . '<br><br>Query: ' . $q . '</p>';
        exit;
    }

    mysqli_close($dbcon);
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
