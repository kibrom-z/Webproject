<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Dispatcher")) {
    header('location: ../../sign-in.php');
    exit();
}

try {

    require('../../../php/mysqli-connect.php');

    $query = "SELECT trip_id, departure_date, departure_time, route_id, tariff, bus_plate_number, bookings FROM trips ORDER BY departure_date ASC";
    // Prepared statement not needed since hardcoded
    $result = mysqli_query($dbcon, $query);

    if ($result) {

        print '
        <table class="table table-striped table-success">
        <tr>
        <th>Edit</th>
        <th>Delete</th>
        <th>Trip ID</th>
        <th>Departure Date</th>
        <th>Departure Time</th>
        <th>Route ID</th>
        <th>Tariff</th>
        <th>Bus Plate №</th>
        <th>Bookings</th>
        </tr>';

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

            $trip_id = htmlspecialchars($row['trip_id'], ENT_QUOTES);
            $departure_date = date_create(htmlspecialchars($row['departure_date'], ENT_QUOTES));
            $departure_time = date_create(htmlspecialchars($row['departure_time'], ENT_QUOTES));
            $route = htmlspecialchars($row['route_id'], ENT_QUOTES);
            $tariff = htmlspecialchars($row['tariff'], ENT_QUOTES);
            $bus = htmlspecialchars($row['bus_plate_number'], ENT_QUOTES);
            $bookings = htmlspecialchars($row['bookings'], ENT_QUOTES);

            print '<tr>
            <td><a href="update-trip.php?id=' . $trip_id . '" class="text-success">Edit</a></td>
            <td><a href="delete-trip.php?id=' . $trip_id . '" class="text-success">Delete</a></td>';
            print '<td>' . $trip_id . '</td>
            <td>' . $departure_date->format('M-d-Y') . '</td>
            <td>' . $departure_time->format('h:i A') . '</td>
            <td>' . $route . '</td>
            <td>' . $tariff . ' Birr</td>
            <td>' . $bus . '</td>
            <td>' . $bookings . '</td>
            </tr>';
        }
        print '</table>';
        $qry = "SELECT COUNT(trip_id) FROM trips";
        $output = mysqli_query($dbcon, $qry);
        $entry = mysqli_fetch_array($output, MYSQLI_NUM);
        $total = htmlspecialchars($entry[0], ENT_QUOTES);
        mysqli_free_result($output);
        print '<div class="field"><p>Total number of trips: ' . $total . '</p></div>';
    } else {
        print
            '<p>The current trips could not be retrieved. ';
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
