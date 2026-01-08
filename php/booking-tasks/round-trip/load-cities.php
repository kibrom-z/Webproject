<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
    header('location: ../../../sign-in.php');
    exit();
}
try {
    require_once('../../../php/mysqli-connect.php');

    // Print departure place
    $query = "SELECT name FROM cities";
    // Prepared statement not needed since hardcoded
    $result = mysqli_query($dbcon, $query);
    if ($result) {
        print '
        <div class="input-group mb-3">
            <label for="dep-place" class="form-label w-35 pt-1 pe-3 text-end">Departure place</label>
            <select name="dep-place" id="dep-place" class="form-select text-center">';
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $name = htmlspecialchars($row['name'], ENT_QUOTES);
            print '<option value="' . $name . '">' . $name . '</option>';
        }
        print '</select>';
        print '</div>';
    } else {
        print
            '<p>The current cities could not be retrieved. ';
        print 'We apologize for any inconvenience.</p>';
        // Debug message:
        // print '<p>' . mysqli_error($dbcon) . '<br><br>Query: ' . $q . '</p>';
        exit;
    }

    // Print arrival place
    $query = "SELECT name FROM cities";
    $result = mysqli_query($dbcon, $query);
    if ($result) {
        print '
        <div class="input-group mb-3">
            <label for="arv-place" class="form-label w-35 pt-1 pe-3 text-end">Arrival place</label>
            <select name="arv-place" id="arv-place" class="form-select text-center">';
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $name = htmlspecialchars($row['name'], ENT_QUOTES);
            print '<option value="' . $name . '">' . $name . '</option>';
        }
        print '</select>';
        print '</div>';
    } else {
        print
            '<p>The current cities could not be retrieved. ';
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
