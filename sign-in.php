<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign in</title>
  <link rel="stylesheet" href="bootstrap-5.2.2-dist/css/bootstrap.css" />
  <link rel="stylesheet" href="css/input-icons.css">
  <link rel="icon" href="images/icon-image.png" />
  <style>
    #bg-image {
      background-image: url("images/sign-in-background.jpg");
      background-repeat: no-repeat;
      background-size: cover;
    }

    #bg-image2 {
      background-image: url("images/bus-background.jpg");
      background-repeat: no-repeat;
      background-size: cover;
      background-position: bottom;
    }
  </style>
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
                <a href="sign-in.php" class="nav-link active">Sign In</a>
              </li>
              <li class="nav-item fs-5 me-lg-5">
                <a href="sign-up.php" class="nav-link">Sign Up</a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </header>

    <?php
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require('php/authorization-tasks/process-sign-in.php');
  }
  ?>
  <section class="bg-light py-sm-5 d-flex align-items-center min-vh-100" id="bg-image2">
    <div class="container">
      <div class="row w-75 m-auto mt-3">
        <div class="col-lg rounded-start pt-5 px-5" id="bg-image">
          <div class="text-light">
            <p class="h2 my-5 pt-5 px-3 pb-3">Welcome back!</p>
            <p class="px-3 pb-5 mb-5 fs-5">You can sign in to access our services with your existing account.</p>
          </div>
        </div>
        <div class="col-lg ps-5 bg-light rounded-end pt-5 pb-5">
          <p class="fs-4 pt-5 pb-3">Sign In</p>
          <form action="sign-in.php" method="post">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <input type="text" minlength="5" maxlength="15" class="rounded-pill py-1 ps-4 form-control w-50" title="Must be 5 to 15 Characters Long" name="username" id="username" value="<?php if (isset($_POST['username'])) print $_POST['username']; ?>" autofocus required />
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" minlength="8" maxlength="15" name="password" class="rounded-pill py-1 ps-4 form-control w-50" id="password" value="<?php if (isset($_POST['password'])) print $_POST['password']; ?>" required />
            </div>
            <?php
            if (isset($_SESSION['authorization_error'])) {
              print '<p class="text-danger">' . $_SESSION['authorization_error'] . '</p>';
            }
            ?>
            <input type="submit" name="sign-in" value="SIGN IN" class="fs-5 btn btn-success rounded-pill mt-4 py-1 w-50" />
            <p class="my-5 ">New here?
              <a href="sign-up.php" class="link-success text-decoration-none">Create an Account.</a>
            </p>
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
</body>
</html>