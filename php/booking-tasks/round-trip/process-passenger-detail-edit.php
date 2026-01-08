<?php
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
  header('location: ../../../sign-in.php');
  exit();
}

// The code looks for a valid user ID, either through GET or POST:
if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
  $id = htmlspecialchars($_GET['id'], ENT_QUOTES);
} elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
  $id = htmlspecialchars($_POST['id'], ENT_QUOTES);
} else { // No valid ID, kill the script.
  print '<p>This page has been accessed in error.</p>';
  exit();
}

try {

  require('../../../php/mysqli-connect.php');
  require('../../../php/error-style2.php'); // For formatting the error message

  // If the form is submitted
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = array(); // Stores all error messages concatenated

    // Trimming extra whitespaces and validating inputs
    $first_name = ucfirst(trim(filter_var($_POST['f-name'], FILTER_SANITIZE_STRING)));
    if (empty($first_name)) {
      $errors[] = 'You forgot to enter your first name.';
    }
    $last_name = ucfirst(trim(filter_var($_POST['l-name'], FILTER_SANITIZE_STRING)));
    if (empty($last_name)) {
      $errors[] = 'You forgot to enter your last name.';
    }
    $sex = filter_var($_POST['sex'], FILTER_SANITIZE_STRING);
    $phone = trim(filter_var($_POST['phone'], FILTER_SANITIZE_STRING));
    if (empty($phone)) {
      $errors[] = 'You forgot to enter your phone number.';
    }
    $email = trim(filter_var($_POST['e-mail'], FILTER_SANITIZE_EMAIL));
    if (empty($email)) {
      $errors[] = 'You forgot to enter your email address.';
    }
    $payment_status = "pending";

    if (empty($errors)) { // If everything's OK

      $retrieve_query = "SELECT reservation_id FROM reservations WHERE first_name=? AND email=? AND phone=? AND trip_id=?";
      $prepared = mysqli_stmt_init($dbcon);
      mysqli_stmt_prepare($prepared, $retrieve_query);
      mysqli_stmt_bind_param($prepared, "sssi", $first_name, $email, $phone, $_SESSION['selected-first-trip-id']);
      mysqli_stmt_execute($prepared);
      $result = mysqli_stmt_get_result($prepared);
      $row = mysqli_fetch_array($result, MYSQLI_NUM);

      if (mysqli_num_rows($result) == 1) {
        if ($row[0] == $id) { // the same user data checked with itself
        } else {
          $errors[] = 'The record already exists.';
        }
      }

      $retrieve_query2 = "SELECT reservation_id FROM reservations WHERE first_name=? AND email=? AND phone=? AND trip_id=?";
      $prepared2 = mysqli_stmt_init($dbcon);
      mysqli_stmt_prepare($prepared2, $retrieve_query2);
      mysqli_stmt_bind_param($prepared2, "sssi", $first_name, $email, $phone, $_SESSION['selected-return-trip-id']);
      mysqli_stmt_execute($prepared2);
      $result2 = mysqli_stmt_get_result($prepared2);
      $row2 = mysqli_fetch_array($result2, MYSQLI_NUM);

      if (mysqli_num_rows($result) == 1) {
        if ($row2[0] == $id) { // the same user data checked with itself
        } else {
          $errors[] = 'The record already exists.';
        }
      }
      if (empty($errors)) { // A new record

        $query = "UPDATE reservations SET first_name=?, last_name=?, sex=?, email=?, phone=? ";
        $query .= "WHERE reservation_id=? LIMIT 1";
        $q = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q, $query);
        mysqli_stmt_bind_param($q, 'sssssi', $first_name, $last_name, $sex, $email, $phone, $id);
        mysqli_stmt_execute($q);

        $next_reservation_id = $id + 1;
        $query2 = "UPDATE reservations SET first_name=?, last_name=?, sex=?, email=?, phone=? ";
        $query2 .= "WHERE reservation_id=? LIMIT 1";
        $q2 = mysqli_stmt_init($dbcon);
        mysqli_stmt_prepare($q2, $query2);
        mysqli_stmt_bind_param($q2, 'sssssi', $first_name, $last_name, $sex, $email, $phone, $next_reservation_id);
        mysqli_stmt_execute($q2);

        if ((mysqli_stmt_affected_rows($q) == 1) && (mysqli_stmt_affected_rows($q2) == 1)) {
          header('location: passenger-confirmation.php');
        } else {
          $errors[] = 'You clicked the button without changing anything';
          //print '<p>' . mysqli_error($dbcon) . '<br />Query: ' . $q . '</p>';
        }
      }
    }
  }
  // Populate form data from database
  $q = mysqli_stmt_init($dbcon);
  $query = "SELECT first_name, last_name, sex, email, phone FROM reservations WHERE reservation_id=?";
  mysqli_stmt_prepare($q, $query);
  mysqli_stmt_bind_param($q, 'i', $id);
  mysqli_stmt_execute($q);
  $result = mysqli_stmt_get_result($q);
  $row = mysqli_fetch_array($result, MYSQLI_NUM);
  if (mysqli_num_rows($result) == 1) { // Valid user ID, display the form.
    // Get the user's information:
    // Create the form:
    $firstName = htmlspecialchars($row[0], ENT_QUOTES);
    $lastName = htmlspecialchars($row[1], ENT_QUOTES);
    $gender = htmlspecialchars($row[2], ENT_QUOTES);
    $emailAddress = htmlspecialchars($row[3], ENT_QUOTES);
    $phoneNumber = htmlspecialchars($row[4], ENT_QUOTES);

    print '
    <form action="edit-passenger-detail.php?id=' . $id . '" method="post">
        <div class="input-group mb-3">
          <label for="f-name" class="form-label w-35 pt-1 pe-3 text-end">First name</label>
          <input type="text" class="form-control ps-4" pattern="[a-zA-Z]+" minlength="2" maxlength="20" title="Must be 2 to 20 Alphabetic Characters" name="f-name" id="f-name" value="' . $firstName . '" autofocus required />
        </div>
        <div class="input-group mb-3">
          <label for="l-name" class="form-label w-35 pt-1 pe-3 text-end">Last name</label>
          <input type="text" class="form-control ps-4" pattern="[a-zA-Z]+" minlength="2" maxlength="20" title="Must be 2 to 20 Alphabetic Characters" name="l-name" id="l-name" value="' . $lastName . '" required />
        </div>';
    if ($gender == 'Male') {
      print '<div class="input-group mb-3">
            <label for="sex" class="form-label w-35 pt-1 pe-3 text-end">Sex</label>
            <select name="sex" id="sex" class="form-select">
              <option value="Male" selected>Male</option>
              <option value="Female">Female</option>
            </select>
          </div>';
    } else {
      print '<div class="input-group mb-3">
            <label for="sex" class="form-label w-35 pt-1 pe-3 text-end">Sex</label>
            <select name="sex" id="sex" class="form-select">
              <option value="Male">Male</option>
              <option value="Female" selected>Female</option>
            </select>
          </div>';
    }
    print '
          <div class="input-group mb-3">
            <label for="e-mail" class="form-label w-35 pt-1 pe-3 text-end">Email address</label>
            <input type="email" class="form-control ps-4" name="e-mail" id="e-mail" maxlength="40" value="' . $emailAddress . '" required />
          </div>
          <div class="input-group mb-3">
            <label for="phone" class="form-label w-35 pt-1 pe-3 text-end">Phone number</label>
            <input type="tel" class="form-control ps-4" minlength="10" maxlength="10" name="phone" id="phone" value="' . $phoneNumber . '" required />
          </div>';

    require('../../../php/booking-tasks/round-trip/display-trip-info.php');

    print '
      <input type="submit" name="confirm" value="UPDATE DETAIL" class="fs-5 btn btn-success rounded-pill mt-5 py-1 w-50" id="confirm-button" />
    </form>
        ';
  } else {
    print '<p>This page has been accessed in error.</p>';
  } // Populating the form ended

  mysqli_close($dbcon);
} catch (Exception $e) {
  //print "An Exception occurred. Message: " . $e->getMessage();
  print "The system is busy please try later";
} catch (Error $e) {
  //print "An Error occurred. Message: " . $e->getMessage();
  print "The system is busy please try again later.";
}
if (!empty($errors)) {
  $errorstring = "ERROR! The following error(s) occurred:<br><br>";
  foreach ($errors as $msg) { // Print each error.
    $errorstring .= "— $msg<br>\n";
  }
  $errorstring .= "<br>Please try again.<br>";
  print "<p style='$error_style'>$errorstring</p>";
}
