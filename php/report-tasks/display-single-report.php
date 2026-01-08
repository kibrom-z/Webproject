<?php
if (!isset($_SESSION['account_type']) or (($_SESSION['account_type'] != "Finance Officer") and ($_SESSION['account_type'] != "Manager"))) {
  header('location: ../../sign-in.php');
  exit();
}

// The code looks for a valid user ID, either through GET or POST:
if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
  $report_id = htmlspecialchars($_GET['id'], ENT_QUOTES);
} elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
  $report_id = htmlspecialchars($_POST['id'], ENT_QUOTES);
} else { // No valid ID, kill the script.
  print '<p>This page has been accessed in error.</p>';
  exit();
}

try {

  require('../../../php/mysqli-connect.php');

  $query = "SELECT route_id, start_date, end_date, generated_on FROM reports WHERE report_id=?";
  $q = mysqli_stmt_init($dbcon);
  mysqli_stmt_prepare($q, $query);
  mysqli_stmt_bind_param($q, 'i', $report_id);
  mysqli_stmt_execute($q);
  $result = mysqli_stmt_get_result($q);
  $row = mysqli_fetch_array($result, MYSQLI_NUM);
  $route_id = $row[0];
  $from = date_create($row[1]);
  $to = date_create($row[2]);
  $generated_on = date_create($row[3]);

  if ($route_id == 0) {
    $full_route_name = 'All';
  } else {

    $query2 = "SELECT departure_place, arrival_place FROM routes WHERE route_id=?";
    $q2 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q2, $query2);
    mysqli_stmt_bind_param($q2, 'i', $route_id);
    mysqli_stmt_execute($q2);
    $result2 = mysqli_stmt_get_result($q2);
    $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
    $departure_place = $row2[0];
    $arrival_place = $row2[1];

    $full_route_name = $departure_place . ' to ' . $arrival_place;
  }

  print '
  <div class="row bg-light mb-3 rounded p-3 border-start border-top border-end border-bottom border-secondary">
    <div class="row">
      <div class="col text-start fs-4">
        <p><img src="../../../images/zemen-bus-logo.png" alt="logo" />ZEMEN BUS ETHIOPIA</p>
      </div>
      <div class="col text-end fs-4">
        <p>Ticket Sale Report</p>
      </div>
    </div>
    <div class="row">
      <div class="col text-start ms-5">
        <p><span class="fw-bold">Route:</span> ' . $full_route_name . '</p>
      </div>
      <div class="col text-end">
        <p><span class="fw-bold">Generated on:</span> ' . $generated_on->format('M d, Y h:m:i A') . '</p>
      </div>
    </div>
    <div class="row">
      <div class="col text-start ms-5">
        <p><span class="fw-bold">Start date:</span> ' . $from->format('M d, Y') . '</p>
      </div>
    </div>
    <div class="row">
      <div class="col text-start ms-5">
        <p><span class="fw-bold">End date:</span> ' . $to->format('M d, Y') . '</p>
      </div>
    </div>
    <table class="table table-striped table-success">
      <tr>
        <th>#</th>
        <th>Date</th>
        <th>Number of Tickets</th>
        <th>Tariff</th>
        <th>Total Sale</th>
        <th>Remark (Additional Note)</th>
      </tr>';

  if ($route_id == 0) {

    $retrieve_query = "SELECT DISTINCT trip_id FROM reservations";
    $result3 = mysqli_query($dbcon, $retrieve_query);
  } else {

    $retrieve_query = "SELECT trip_id FROM trips WHERE route_id=?";
    $q3 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q3, $retrieve_query);
    mysqli_stmt_bind_param($q3, 'i', $route_id);
    mysqli_stmt_execute($q3);
    $result3 = mysqli_stmt_get_result($q3);
  }

  $loop_counter = 0;
  $gross_sale = 0;

  while ($row3 = mysqli_fetch_array($result3, MYSQLI_ASSOC)) {

    $loop_counter++;
    $trip_id = htmlspecialchars($row3['trip_id'], ENT_QUOTES);

    $query4 = "SELECT departure_date, bookings, tariff FROM trips WHERE trip_id=?";
    $q4 = mysqli_stmt_init($dbcon);
    mysqli_stmt_prepare($q4, $query4);
    mysqli_stmt_bind_param($q4, 'i', $trip_id);
    mysqli_stmt_execute($q4);
    $result4 = mysqli_stmt_get_result($q4);
    $row4 = mysqli_fetch_array($result4, MYSQLI_NUM);
    $departure_date = date_create($row4[0]);

    $start_difference = date_diff($from, $departure_date)->format("%R%a");
    $end_difference = date_diff($departure_date, $to)->format("%R%a");

    if ($start_difference >= 0 && $end_difference >= 0) {

      $bookings = $row4[1];
      $tariff = $row4[2];

      $total_sale = $bookings * $tariff;
      $gross_sale += $total_sale;

      print '
          <tr style="text-align: center;">
            <td>' . $loop_counter . '</td>
            <td>' . $departure_date->format('M d, Y') . '</td>
            <td>' . $bookings . '</td>
            <td>' . $tariff . ' Birr</td>
            <td>' . $total_sale . ' Birr</td>
            <td></td>
          </tr>
          ';
    }
  }

  print '
      </table>
      <p style="text-align: center; margin: 20px auto;">Gross total sale: ' . $gross_sale . ' Birr</p>
    </div>
    <a href="view-reports.php">
      <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-5 me-lg-5" id="back-button" /></a>
    <a href="../../../php/report-tasks/generate-pdf.php?id=' . $report_id . '">
    <input type="button" name="pdf" value="EXPORT TO PDF" class="fs-5 btn btn-success rounded-pill mt-4 py-1 px-4 ms-lg-5" id="pdf-button" /></a>
    ';

  mysqli_close($dbcon);
} catch (Exception $e) {
  // print "An Exception occurred. Message: " . $e->getMessage();
  print "The system is busy please try later";
} catch (Error $e) {
  print "An Error occurred. Message: " . $e->getMessage();
  print "The system is busy please try again later.";
}
