<?php
if (!isset($_SESSION['account_type']) or (($_SESSION['account_type'] != "Finance Officer") and ($_SESSION['account_type'] != "Manager"))) {
    header('location: ../../sign-in.php');
    exit();
}

try {

    require('../../../php/mysqli-connect.php');

    $main_query = "SELECT report_id, route_id, start_date, end_date, generated_on FROM reports";
    // Prepared statement not needed since hardcoded
    $main_result = mysqli_query($dbcon, $main_query);

    print '
    <table class="table table-striped table-success">
    <tr>
    <th>View Report</th>
    <th>Report ID</th>
    <th>Departure Place</th>
    <th>Arrival Place</th>
    <th>From</th>
    <th>To</th>
    <th>Generated On</th>
    </tr>';

    if (mysqli_num_rows($main_result) > 0) {

        while ($row = mysqli_fetch_array($main_result, MYSQLI_ASSOC)) {

            $report_id = htmlspecialchars($row['report_id'], ENT_QUOTES);
            $route_id = htmlspecialchars($row['route_id'], ENT_QUOTES);
            $from = date_create(htmlspecialchars($row['start_date'], ENT_QUOTES));
            $to = date_create(htmlspecialchars($row['end_date'], ENT_QUOTES));
            $generated_on = date_create(htmlspecialchars($row['generated_on'], ENT_QUOTES));

            if ($route_id == 0) {
                $departure_place = 'All';
                $arrival_place = 'All';
            } else {

                $query = "SELECT departure_place, arrival_place FROM routes WHERE route_id=?";
                $q = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q, $query);
                mysqli_stmt_bind_param($q, 'i', $route_id);
                mysqli_stmt_execute($q);
                $result = mysqli_stmt_get_result($q);
                $row = mysqli_fetch_array($result, MYSQLI_NUM);
                $departure_place = $row[0];
                $arrival_place = $row[1];
            }

            print '
            <tr>
                <td><a href="view-single-report.php?id=' . $report_id . '" class="text-success">View Report</a></td>';
            print '
                <td>' . $report_id . '</td>
                <td>' . $departure_place . '</td>
                <td>' . $arrival_place . '</td>
                <td>' . $from->format('M d, Y') . '</td>
                <td>' . $to->format('M d, Y') . '</td>
                <td>' . $generated_on->format('M d, Y h:m:i A') . '</td>
            </tr>';
        }
    }

    print '</table>';

    $qry = "SELECT COUNT(report_id) FROM reports";
    $output = mysqli_query($dbcon, $qry);
    $entry = mysqli_fetch_array($output, MYSQLI_NUM);
    $total = htmlspecialchars($entry[0], ENT_QUOTES);
    mysqli_free_result($output);
    print '<div class="field"><p>Total number of reports: ' . $total . '</p></div>';

    mysqli_close($dbcon);
} catch (Exception $e) {
    // print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    //print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
