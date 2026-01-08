<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign up</title>
  <link rel="stylesheet" href="bootstrap-5.2.2-dist/css/bootstrap.css" />
  <link rel="stylesheet" href="css/input-icons.css">
  <link rel="stylesheet" href="css/background-image.css">
  <link rel="icon" href="images/icon-image.png" />
</head>
<body>
  <header>
    <nav class="navbar navbar-expand-lg bg-success navbar-dark fixed-top">
      <div class="container">
        <img src="images/zemen-bus-logo.png" class="navbar-brand" alt="Logo" />
        <a href="#" class="navbar-brand fs-4">ZEMEN BUS ETHIOPIA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navmenu">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item fs-5 me-lg-5">
              <a href="index.html" class="nav-link">Home</a>
            </li>
            <li class="nav-item fs-5 me-lg-5">
              <a href="sign-in.php" class="nav-link">Sign In</a>
            </li>
            <li class="nav-item fs-5 me-lg-5">
              <a href="sign-up.php" class="nav-link active">Sign Up</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </header>


  <?php
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require('php/user-tasks/process-passenger-sign-up.php');
  }
  ?>
  <section class="bg-light py-sm-5 mt-5 min-vh-100" id="bg-image">
    <div class="container">
      <div class="row w-50 m-auto mt-3">
        <div class="col bg-light rounded-3 p-5 text-center">
          <p class="fs-4 text-success mb-5">Fill the form</p>
          <form action="sign-up.php" method="post">
            <div class="input-group mb-3">
              <label for="f-name" class="form-label w-35 pt-1 pe-3 text-end">First name</label>
              <input type="text" class="form-control ps-4" pattern="[a-zA-Z]+" minlength="2" maxlength="20" title="Must be 2 to 20 Alphabetic Characters" name="f-name" id="f-name" value="<?php if (isset($_POST['f-name'])) print $_POST['f-name']; ?>" autofocus required />
            </div>
            <div class="input-group mb-3">
              <label for="l-name" class="form-label w-35 pt-1 pe-3 text-end">Last name</label>
              <input type="text" class="form-control ps-4" pattern="[a-zA-Z]+" minlength="2" maxlength="20" title="Must be 2 to 20 Alphabetic Characters" name="l-name" id="l-name" value="<?php if (isset($_POST['l-name'])) print $_POST['l-name']; ?>" required />
            </div>
            <div class="input-group mb-3">
              <label for="sex" class="form-label w-35 pt-1 pe-3 text-end">Sex</label>
              <select name="sex" class="form-select" id="sex">
                <option value="Male" selected>Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="input-group mb-3">
              <label for="e-mail" class="form-label w-35 pt-1 pe-3 text-end">Email address</label>
              <input type="email" class="form-control ps-4" name="e-mail" id="e-mail" maxlength="40" value="<?php if (isset($_POST['e-mail'])) print $_POST['e-mail']; ?>" required />
            </div>
            <div class="input-group mb-3">
              <label for="phone" class="form-label w-35 pt-1 pe-3 text-end">Phone number</label>
              <input type="tel" class="form-control ps-4" minlength="10" maxlength="10" name="phone" id="phone" value="<?php if (isset($_POST['phone'])) print $_POST['phone']; ?>" required />
            </div>
            <div class="input-group mb-3">
              <label for="bank-acc" class="form-label w-35 pt-1 pe-3 text-end">Bank account</label>
              <input type="text" class="form-control ps-4" pattern="[0-9]{10,15}" title="Must be 10 to 15 Numbers Long" name="bank-acc" id="bank-acc" value="<?php if (isset($_POST['bank-acc'])) print $_POST['bank-acc']; ?>" required />
            </div>
            <div class="input-group mb-3">
              <label for="username" class="form-label w-35 pt-1 pe-3 text-end">Username</label>
              <input type="text" class="form-control ps-4" minlength="5" maxlength="15" title="Must be 5 to 15 Characters Long" name="username" id="username" value="<?php if (isset($_POST['username'])) print $_POST['username']; ?>" required />
            </div>
            <div class="input-group mb-3">
              <label for="new-password" class="form-label w-35 pt-1 pe-3 text-end">Password</label>
              <input type="password" class="form-control ps-4" minlength="8" maxlength="15" name="new-password" id="new-password" value="<?php if (isset($_POST['new-password'])) print $_POST['new-password']; ?>" required />
            </div>
            <div class="input-group mb-3">
              <label for="confirm-password" class="form-label w-35 pt-1 pe-3 text-end">Confirm password</label>
              <input type="password" class="form-control ps-4" minlength="8" maxlength="15" name="confirm-password" id="confirm-password" value="<?php if (isset($_POST['confirm-password'])) print $_POST['confirm-password']; ?>" required />
            </div>
            <!-- For JavaScript validation -->
            <p id="password-feedback" class="text-danger"></p>
            <input type="submit" name="sign-up" value="SIGN UP" class="fs-5 btn btn-success rounded-pill mt-4 py-1 w-50" />
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
            <img src="images/zemen-bus-logo.png" alt="Logo" class="float-sm-start me-3" />
            <p class="fs-5 fw-bold">ZEMEN EXPRESS COOPERATE</p>
          </div>
          <div class="col-lg m-3">
            <p class="mb-3 fs-5 fw-bold text-light">Developed By</p>
            <ol>

                  <li>Kibrom Zebrehe</li>
              
            </ol>
          </div>
          <div class="col-lg m-3">
            <p class="mb-3 fs-5 fw-bold text-light">Support</p>
            <p>
              <a href="tutorial.html" class="text-decoration-none text-light">Tutorial</a>
            </p>
          </div>
          <div class="col-lg m-3">
            <p class="mb-3 fs-5 fw-bold text-light">Company</p>
            <p><a href="about.html" class="text-decoration-none text-light">About</a></p>
            <p>
              <a href="contact.html" class="text-decoration-none text-light">Contact</a>
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
  <script src="bootstrap-5.2.2-dist/js/bootstrap.js"></script>
  <script src="js/jquery-3.6.1.js"></script>
  <script src="js/password-match-script.js"></script>

</body>
</html>
