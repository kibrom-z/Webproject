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
  <title>Book</title>
  <link rel="stylesheet" href="../../../bootstrap-5.2.2-dist/css/bootstrap.css" />
  <link rel="stylesheet" href="../../../css/background-image.css">
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
  <!-- Validate Input -->
  <?php
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require('../../../php/booking-tasks/round-trip/process-find-first-trip.php');
  }
  ?>
  <section class="bg-light py-sm-5 mt-5 min-vh-100" id="bg-image">
    <div class="container">
      <div class="row w-50 m-auto mt-3">
        <div class="col bg-light rounded-3 p-5 text-center">
          <ul class="nav nav-tabs justify-content-center mb-5 fs-5">
            <li class="nav-item">
              <a href="../one-way-trip.php" class="nav-link text-secondary">One Way Trip</a>
            </li>
            <li class="nav-item">
              <a href="round-trip/round-trip.php" class="nav-link text-success active">Round Trip</a>
            </li>
          </ul>
          <form action="round-trip.php" method="post">
            <?php
            require('../../../php/booking-tasks/round-trip/load-cities.php');
            ?>
            <div class="input-group mb-3">
              <label for="dep-date" class="form-label w-35 pt-1 pe-3 text-end">Departure date</label>
              <input type="date" class="form-control text-center" name="dep-date" id="dep-date" value="<?php if (isset($_POST['dep-date'])) print $_POST['dep-date']; ?>" required />
            </div>
            <!-- For JavaScript validation -->
            <p id="dep-date-feedback" class="text-danger"></p>
            <div class="input-group mb-3">
              <label for="ret-date" class="form-label w-35 pt-1 pe-3 text-end">Return date</label>
              <input type="date" class="form-control text-center" name="ret-date" id="ret-date" value="<?php if (isset($_POST['ret-date'])) print $_POST['ret-date']; ?>" required />
            </div>
            <!-- For JavaScript validation -->
            <p id="ret-date-feedback" class="text-danger"></p>
            <fieldset>
              <legend class="fs-5">Passengers</legend>
              <div class="input-group mb-3">
                <label for="adult-num" class="form-label w-35 pt-1 pe-3 text-end">Adult (+7 years)</label>
                <input type="number" class="form-control text-center" name="adult-num" class="passenger-number" id="adult-num" min="1" max="9" value="<?php print isset($_POST['adult-num']) ? $_POST['adult-num'] : 1; ?>" required />
              </div>
              <div class="input-group mb-3">
                <label for="child-num" class="form-label w-35 pt-1 pe-3 text-end">Children (0-7 years)</label>
                <input type="number" class="form-control text-center" name="child-num" class="passenger-number" id="child-num" min="0" max="2" value="<?php print isset($_POST['child-num']) ? $_POST['child-num'] : 0; ?>" required />
              </div>
            </fieldset>
            <input type="submit" name="find-trips" value="FIND TRIPS" class="fs-5 btn btn-success rounded-pill mt-5 py-1 w-50" />
          </form>
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
  <script src="../../../js/jquery-3.6.1.js"></script>
  <script src="../../../js/validate-date-script.js"></script>
</body>

</html>