<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Finance Officer")) {
    header('location: ../../sign-in.php');
    exit();
}

try {

    require_once('../../../php/mysqli-connect.php');

    $query = "SELECT route_id, departure_place, arrival_place FROM routes";
    $result = mysqli_query($dbcon, $query);

    if ($result) {

        print '
        <div class="input-group mb-3">
            <label for="route" class="form-label w-35 pt-1 pe-3 text-end">Route</label>
            <select name="route" id="route" class="form-select text-center">
            <option value="all" selected>All Routes</option>';

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

    mysqli_close($dbcon);
} catch (Exception $e) {
    //print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
