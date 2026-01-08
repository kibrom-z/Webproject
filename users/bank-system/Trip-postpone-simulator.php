<?php
session_start();
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
  header('location: ../../sign-in.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bank simulator — Passenger</title>
  <link rel="stylesheet" href="../../bootstrap-5.2.2-dist/css/bootstrap.css" />
  <link rel="stylesheet" href="../../css/input-icons.css" />
  <link rel="icon" href="../../images/icons/b_20px.png" />
  <style>
    * {
      margin: 0;
    }

    #bg-image {
      background-image: url('../../images/background-gradient.jpg');
      background-repeat: no-repeat;
      background-size: cover;
    }
  </style>
</head>

<body>
  <section class="bg-light py-3 min-vh-100" id="bg-image">
    <div class="container">
      <div class="row w-50 m-auto mt-3">
        <div class="col bg-light rounded-3 p-5 text-center">
          <img src="../../images/bank-logos/cbe-logo.png" alt="bank-logo" />
          <p class="fs-5 mb-5">COMMERCIAL BANK OF ETHIOPIA</p>
          <?php
          require('../../php/booking-tasks/auxiliary/process-payment.php');
          ?>
        </div>
      </div>
    </div>
  </section>
</body>

</html>