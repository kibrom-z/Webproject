<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "System Administrator")) {
    header('location: ../../sign-in.php');
    exit();
}

try {
    require('../../../php/mysqli-connect.php');
    $query = "SELECT user_id, first_name, last_name, sex, phone, email, account_type, account_status FROM users";
    //$query = "SELECT * FROM users WHERE account_type='Passenger'";
    // Prepared statement not needed since hardcoded
    $result = mysqli_query($dbcon, $query);
    if ($result) {
        print '<table class="table table-striped table-success">
        <tr>
        <th>Edit</th>
        <th>Delete</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Sex</th>
        <th>Phone Number</th>
        <th>Email Address</th>
        <th>Account Type</th>
        <th>Status</th>
        </tr>';
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $user_id = htmlspecialchars($row['user_id'], ENT_QUOTES);
            $first_name = htmlspecialchars($row['first_name'], ENT_QUOTES);
            $last_name = htmlspecialchars($row['last_name'], ENT_QUOTES);
            $sex = htmlspecialchars($row['sex'], ENT_QUOTES);
            $phone = htmlspecialchars($row['phone'], ENT_QUOTES);
            $email = htmlspecialchars($row['email'], ENT_QUOTES);
            $account_type = htmlspecialchars($row['account_type'], ENT_QUOTES);
            $account_status = htmlspecialchars($row['account_status'], ENT_QUOTES);
            print '<tr>
            <td><a href="update-account.php?id=' . $user_id . '" class="text-success">Edit</a></td>
            <td><a href="delete-account.php?id=' . $user_id . '" class="text-success">Delete</a></td>';
            print '<td>' . $first_name . '</td>
            <td>' . $last_name . '</td>
            <td>' . $sex . '</td>
            <td>' . $phone . '</td>
            <td>' . $email . '</td>
            <td>' . $account_type . '</td>
            <td>' . $account_status . '</td>
            </tr>';
        }
        print '</table>';
        $qry = "SELECT COUNT(user_id) FROM users";
        $output = mysqli_query($dbcon, $qry);
        $entry = mysqli_fetch_array($output, MYSQLI_NUM);
        $total = htmlspecialchars($entry[0], ENT_QUOTES);
        mysqli_free_result($output);
        print '<p>Total number of users: ' . $total . '</p>';
    } else {
        print
            '<p>The current users could not be retrieved. ';
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
