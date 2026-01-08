<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Finance Officer")) {
    header('location: ../../sign-in.php');
    exit();
}

// Validating inputs
$route = filter_var($_POST['route'], FILTER_SANITIZE_STRING);
if (empty($route)) {
    $_SESSION['generate-report-error'][] = 'You forgot to select a route.';
}
$from = filter_var($_POST['start-date'], FILTER_SANITIZE_STRING);
if (empty($from)) {
    $_SESSION['generate-report-error'][] = 'You forgot to enter a start date.';
}
$to = filter_var($_POST['end-date'], FILTER_SANITIZE_STRING);
if (empty($to)) {
    $_SESSION['generate-report-error'][] = 'You forgot to enter an end date.';
}

$start_date = date_create($from);
$end_date = date_create($to);
$day_difference = date_diff($start_date, $end_date)->format("%R%a");

if ($day_difference < 0) {
    $_SESSION['generate-report-error'][] = 'End date can not be earlier than start date.';
}

if (empty($_SESSION['generate-report-error'])) { // If everything's OK

    try {

        require('../../../php/mysqli-connect.php');

        $query5 = "SELECT bookings FROM trips WHERE route_id=?";
        $q5 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q5, $query5);
        mysqli_stmt_bind_param($q5, 'i', $route);
        mysqli_stmt_execute($q5);
        $result5 = mysqli_stmt_get_result($q5);
        $row5 = mysqli_fetch_array($result5, MYSQLI_NUM);

        if (mysqli_num_rows($result5) > 0 || $route == "all") {

            if ($route == "all") {
                $bookings = 1;
            } else {
                $bookings = $row5[0];
            }

            if ($bookings == 0) {
                $_SESSION['generate-report-error'][] = 'No reservations are made with the route you selected.';
            } else {

                $query3 = "SELECT * FROM reports WHERE route_id=? AND start_date=? AND end_date=?";
                $q3 = mysqli_stmt_init($dbcon);
                mysqli_stmt_prepare($q3, $query3);
                mysqli_stmt_bind_param($q3, 'iss', $route, $from, $to);
                mysqli_stmt_execute($q3);
                $result3 = mysqli_stmt_get_result($q3);

                if (mysqli_num_rows($result3) == 0) {

                    $retrieve_query = "SELECT trip_id FROM reservations";
                    $result = mysqli_query($dbcon, $retrieve_query);

                    if (mysqli_num_rows($result) > 0) {

                        $reservations_found = FALSE;

                        while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {

                            $trip_id = $row[0];

                            $query2 = "SELECT departure_date FROM trips WHERE trip_id=?";
                            $q2 = mysqli_stmt_init($dbcon);
                            mysqli_stmt_prepare($q2, $query2);
                            mysqli_stmt_bind_param($q2, 'i', $trip_id);
                            mysqli_stmt_execute($q2);
                            $result2 = mysqli_stmt_get_result($q2);
                            $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);
                            $departure_date = date_create($row2[0]);

                            $start_difference = date_diff($start_date, $departure_date)->format("%R%a");
                            $end_difference = date_diff($departure_date, $end_date)->format("%R%a");

                            // Outputs for debugging
                            //print '<br><br><br>Trip departure: ' . $departure_date->format('d-M-Y') . '<br>';
                            //print 'Start difference: ' . $start_difference . '<br>';
                            //print 'End difference: ' . $end_difference . '<br>';

                            if ($start_difference >= 0 && $end_difference >= 0) {
                                $reservations_found = TRUE;
                                break 1;
                            }
                        }

                        if ($reservations_found == TRUE) {

                            // Create report
                            $query = "INSERT INTO reports (report_id, route_id, start_date, end_date) ";
                            $query .= "VALUES(' ', ?, ?, ?)";
                            $q = mysqli_stmt_init($dbcon);
                            mysqli_stmt_prepare($q, $query);
                            mysqli_stmt_bind_param($q, 'iss', $route, $from, $to);
                            mysqli_stmt_execute($q);

                            if (mysqli_stmt_affected_rows($q) == 1) {
                                header('location: ../../../php/report-tasks/successful-report-generation.php');
                            } else {
                                $_SESSION['generate-report-error'][] = 'Report can not be generated due to a system error';
                                mysqli_close($dbcon);
                                exit();
                            }
                        } else {
                            $_SESSION['generate-report-error'][] = 'No reservations found between the dates you entered.';
                        }
                    } else {
                        $_SESSION['generate-report-error'][] = 'No reservations are made.';
                    }
                } else {
                    $_SESSION['generate-report-error'][] = 'The report already exists.';
                }
            }
        } else {
            $_SESSION['generate-report-error'][] = 'No trips are found with the route you selected.';
        }
    } catch (Exception $e) {
        //print "An Exception occurred. Message: " . $e->getMessage();
        print "The system is busy please try later";
    } catch (Error $e) {
        //print "An Error occurred. Message: " . $e->getMessage();
        print "The system is busy please try again later.";
    }
}
