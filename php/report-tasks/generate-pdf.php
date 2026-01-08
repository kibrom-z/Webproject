<?php

use Dompdf\Dompdf;

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

  require_once('../mysqli-connect.php');

  // Include autoloader 
  require_once('../../dompdf/autoload.inc.php');

  // Instantiate and use the dompdf class 
  $dompdf = new Dompdf();

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

  $pdf_content = '
  <style>
  table {
    border: 1px solid black;
    margin: auto;
  }
  tr {
    background-color: whitesmoke;
  }
  th {
    background-color: wheat;
  }
  th,
  td {
    padding: 5px;
  }
  .small-container {
    margin: 20px;
    border: 1px solid black;
    padding: 20px;
    text-align: left;
  }
  .small-container p {
    line-height: 30px;
    width: 300px;
  }
  #company,
  #title,
  #route,
  #report-date {
    display: inline;
  }
  #title,
  #report-date {
    float: right;
    text-align: right;
  }
  #company,
  #title {
    font-size: 24px;
  }
  #company {
    margin-right: 100px;
    text-align: center;
  }
  table {
    margin-top: 20px;
  }
  #back-button {
    margin-right: 100px;
  }
  </style>
  <div class="small-container">
    <p id="company">
      ZEMEN BUS ETHIOPIA
    </p>
    <p id="title">Ticket Sale Report</p>
    <p></p>
    <p id="route"><span style="font-weight: bold;">Route:</span> ' . $full_route_name . '</p>
    <p id="report-date"><span style="font-weight: bold;">Generated on:</span> ' . $generated_on->format('M d, Y h:m:i A') . '</p>
    <p id="start-date"><span style="font-weight: bold;">Start date:</span> ' . $from->format('M d, Y') . '</p>
    <p id="end-date"><span style="font-weight: bold;">End date:</span> ' . $to->format('M d, Y') . '</p>
    <table>
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

      $pdf_content .= '
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

  $pdf_content .= '
    </table>
    <p style="text-align: center; margin: 20px auto;">Gross total sale: ' . $gross_sale . ' Birr</p>
    ';

  // Load the content of the PDF
  $dompdf->loadHtml($pdf_content);

  // (Optional) Setup the paper size and orientation 
  $dompdf->setPaper('A4', 'portrait');

  // Render the HTML as PDF 
  $dompdf->render();

  // Output the generated PDF to Browser 
  $dompdf->stream();

  mysqli_close($dbcon);
} catch (Exception $e) {
  print "An Exception occurred. Message: " . $e->getMessage();
  print "The system is busy please try later";
} catch (Error $e) {
  //print "An Error occurred. Message: " . $e->getMessage();
  print "The system is busy please try again later.";
}
