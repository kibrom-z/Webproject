<?php
session_start();
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Passenger")) {
  header('location: ../../../sign-in.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Return trip</title>
  <link rel="stylesheet" href="../../../bootstrap-5.2.2-dist/css/bootstrap.css" />
  <link rel="stylesheet" href="../../../css/background-image.css">
  <link rel="stylesheet" href="../../../css/trip-hover.css">
  <link rel="icon" href="../../../images/icon-image.png" />
</head>

<body>
  <header>
    <nav class="navbar navbar-expand-xl bg-success navbar-dark fixed-top">
      <div class="container">
        <img src="../../../images/zemen-bus-logo.png" class="navbar-brand" alt="Logo" />
        <a href="#" class="navbar-brand fs-4">ZEMEN BUS ETHIOPIA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navmenu">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item fs-5 me-lg-5">
              <a href="round-trip.php" class="nav-link active">Book</a>
            </li>
            <li class="nav-item fs-5 me-lg-5">
              <a href="../my-trips.php" class="nav-link">My Trips</a>
            </li>
            <li class="nav-item fs-5 me-lg-5">
              <a href="../my-profile.php" class="nav-link">My Profile</a>
            </li>
            <li class="nav-item fs-5 me-lg-5">
              <a href="../../../php/authorization-tasks/process-sign-out.php" class="nav-link">Sign Out</a>
            </li>
            <li class="nav-item me-0">
              <a href="#" class="nav-link"><img src="../../../images/icons/user_25px.png" /></a>
            </li>

            <!-- Prints the username on the top right corner of the page -->
            <?php
            if (isset($_SESSION['username'])) {
              print '
              <li  class="nav-item fs-5">
                <a href="#" class="nav-link text-warning">' . $_SESSION['username'] . '</a>
              </li>';
            }
            ?>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <section class="bg-light py-sm-5 mt-5 min-vh-100" id="bg-image">
    <div class="container">
      <div class="row w-50 m-auto mt-3">
        <div class="col bg-light rounded-3 p-5 text-center">
          <p class="fs-4 text-success mb-5">Select return trip</p>
          <?php
          // The code looks for a valid user ID, either through GET or POST:
          if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
            $id = htmlspecialchars($_GET['id'], ENT_QUOTES);
          } elseif ((isset($_POST['id'])) && (is_numeric($_POST['id']))) {
            $id = htmlspecialchars($_POST['id'], ENT_QUOTES);
          } else { // No valid ID, kill the script.
            print '<p class="fs-5">This page has been accessed in error.</p>';
            exit();
          }

          // Display the appropriate result
          if ($id == 1) {
            require('../../../php/booking-tasks/round-trip/display-available-return-trips.php');
          } else {
            print '<p class="fs-5">No return trips are found. Change the date and try again.</p>';
          }
          ?>
          <a href="round-trip.php">
            <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-5 py-1 w-35" /></a>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <section class="bg-dark text-light py-sm-5">
      <div class="container">
        <div class="row">
          <div class="col-lg m-3">
            <img src="../../../images/zemen-bus-logo.png" alt="Logo" class="float-sm-start me-3" />
            <p class="fs-5 fw-bold">ZEMEN EXPRESS COOPERATE</p>
          </div>
          <div class="col-lg m-3">
            <p class="mb-3 fs-5 fw-bold text-light">Developers</p>
            <ol>
                <li>Behafta Berihu</li>
                <li>Futsum Buruh</li>
                <li>Kibrom Zebrehe</li>
                <li>Zaid Mengstu</li>
                <li>Filmon Hayelom</li>
            </ol>
          </div>
          <div class="col-lg m-3">
            <p class="mb-3 fs-5 fw-bold text-light">Support</p>
            <p>
              <a href="../../../tutorial.html" class="text-decoration-none text-light">Tutorial</a>
            </p>
          </div>
          <div class="col-lg m-3">
            <p class="mb-3 fs-5 fw-bold text-light">Company</p>
            <p><a href="../../../about.html" class="text-decoration-none text-light">About</a></p>
            <p>
              <a href="../../../contact.html" class="text-decoration-none text-light">Contact</a>
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-dark text-light text-end pb-3">
      <div class="container">
        <p>
          Copyright &copy; 2024 Zemen Express Cooperate. All rights reserved.
        </p>
      </div>
    </section>
  </footer>
  <script src="../../../bootstrap-5.2.2-dist/js/bootstrap.js"></script>
</body>

</html>