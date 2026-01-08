<?php
session_start();
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Finance Officer")) {
  header('location: ../../../sign-in.php');
  exit();
}

$_SESSION['generate-report-error'] = array();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Generate report</title>
  <link rel="stylesheet" href="../../../bootstrap-5.2.2-dist/css/bootstrap.css" />
  <link rel="stylesheet" href="../../../css/background-image.css" />
  <link rel="icon" href="../../../images/icon-image.png" />
</head>

<body>
  <header>
    <nav class="navbar navbar-expand-xxl bg-success navbar-dark fixed-top">
      <div class="container">
        <img src="../../../images/zemen-bus-logo.png" class="navbar-brand" alt="Logo" />
        <a href="#" class="navbar-brand fs-4">ZEMEN BUS ETHIOPIA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navmenu">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item fs-5 me-lg-5">
              <a href="refund-requests.php" class="nav-link">Refund</a>
            </li>
            <li class="nav-item fs-5 me-lg-5">
              <a href="view-reports.php" class="nav-link active">Report</a>
            </li>
            <li class="nav-item fs-5 me-lg-5">
              <a href="my-profile.php" class="nav-link">My Profile</a>
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
    require('../../../php/report-tasks/process-generate-report.php');
  }
  ?>
  <section class="bg-light py-sm-5 mt-5 min-vh-100" id="bg-image">
    <div class="container">
      <div class="row w-50 m-auto mt-3">
        <div class="col bg-light rounded-3 p-5 text-center">
          <p class="fs-4 text-success mb-5">Select a route and the dates the report covers</p>
          <form action="generate-report.php" method="post">
            <?php
            require('../../../php/report-tasks/load-routes.php');
            ?>
            <div class="input-group mb-3">
              <label for="start-date" class="form-label w-35 pt-1 pe-3 text-end">Start date</label>
              <input type="date" class="form-control text-center" name="start-date" id="start-date" value="<?php if (isset($_POST['start-date'])) print $_POST['start-date']; ?>" required />
            </div>
            <div class="input-group mb-3">
              <label for="end-date" class="form-label w-35 pt-1 pe-3 text-end">End date</label>
              <input type="date" class="form-control text-center" name="end-date" id="end-date" value="<?php if (isset($_POST['end-date'])) print $_POST['end-date']; ?>" required />
            </div>
            <?php
            if (!empty($_SESSION['generate-report-error'])) {
              $errorstring = "";
              foreach ($_SESSION['generate-report-error'] as $msg) { // Print each error.
                $errorstring .= "$msg<br>";
              }
              print "<p class='text-danger'>$errorstring</p>";
            }
            ?>
            <a href="view-reports.php">
              <input type="button" name="back" value="GO BACK" class="fs-5 btn btn-success rounded-pill mt-5 py-1 px-5 me-lg-5" id="back-button" /></a>
            <input type="submit" name="submit" value="GENERATE REPORT" class="fs-5 btn btn-success rounded-pill mt-5 py-1 px-4 ms-lg-5" />
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
            <p class="mb-3 fs-5 fw-bold text-light">Developed By </p>
            <ol>

                <li>Kibrom Zebrehe</li>
                
            </ol>
          </div>
          <div class="col-lg m-3">
            <p class="mb-3 fs-5 fw-bold text-light">Support</p>
            <p>
              <a href="../tutorial.html" class="text-decoration-none text-light">Tutorial</a>
            </p>
          </div>
          <div class="col-lg m-3">
            <p class="mb-3 fs-5 fw-bold text-light">Company</p>
            <p><a href="../about.html" class="text-decoration-none text-light">About</a></p>
            <p>
              <a href="../contact.html" class="text-decoration-none text-light">Contact</a>
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
