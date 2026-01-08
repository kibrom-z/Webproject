<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Manager")) {
    header('location: ../../sign-in.php');
    exit();
}

try {
    require('../../../php/mysqli-connect.php');
    $query = "SELECT route_id, departure_place, arrival_place, distance_in_km, tariff FROM routes";
    // Prepared statement not needed since hardcoded
    $result = mysqli_query($dbcon, $query);
    if ($result) {
        print '<table class="table table-striped table-success">
        <tr>
        <th>Assign Tariff</th>
        <th>Route ID</th>
        <th>Departure Place</th>
        <th>Arrival Place</th>
        <th>Distance</th>
        <th>Tariff</th>
        </tr>';
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $route_id = htmlspecialchars($row['route_id'], ENT_QUOTES);
            $departure = htmlspecialchars($row['departure_place'], ENT_QUOTES);
            $arrival = htmlspecialchars($row['arrival_place'], ENT_QUOTES);
            $distance = htmlspecialchars($row['distance_in_km'], ENT_QUOTES);
            $tariff = htmlspecialchars($row['tariff'], ENT_QUOTES);
            print '<tr>
            <td><a href="assign-tariff.php?id=' . $route_id . '" class="text-success">Assign Tariff</a></td>';
            print '<td>' . $route_id . '</td>
            <td>' . $departure . '</td>
            <td>' . $arrival . '</td>
            <td>' . $distance . ' km</td>
            <td>' . $tariff . ' Birr</td>
            </tr>';
        }
        print '</table>';
        $qry = "SELECT COUNT(route_id) FROM routes";
        $output = mysqli_query($dbcon, $qry);
        $entry = mysqli_fetch_array($output, MYSQLI_NUM);
        $total = htmlspecialchars($entry[0], ENT_QUOTES);
        mysqli_free_result($output);
        print '<div class="field"><p>Total number of routes: ' . $total . '</p></div>';
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
    // print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
