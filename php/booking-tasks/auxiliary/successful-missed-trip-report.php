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
    <title>Missed reprot submitted</title>
    <link rel="stylesheet" href="../../../bootstrap-5.2.2-dist/css/bootstrap.css" />
    <link rel="stylesheet" href="../../../css/background-image.css">
    <link rel="icon" href="../../../images/icon-image.png" />
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-success navbar-dark fixed-top">
            <div class="container">
                <img src="../../../images/zemen-bus-logo.png" class="navbar-brand" alt="Logo" />
                <a href="#" class="navbar-brand fs-4 me-auto">ZEMEN BUS ETHIOPIA</a>
            </div>
        </nav>
    </header>

    <section class="bg-light py-sm-5 mt-5 min-vh-100" id="bg-image">
        <div class="container">
            <div class="row w-50 m-auto mt-3">
                <div class="col bg-light rounded-3 p-5 text-center">
                    <p class="fs-4 text-success mb-5">Trip report successful</p>
                    <p class="fs-5 text-start">Your missed trip report has been submitted successfully. You will have your money returned shortly. Thank you for using our service.</p>
                    <a href="../../../users/passenger/my-trips.php">
                        <input type="button" name="ok" value="OK" class="fs-5 btn btn-success rounded-pill mt-5 py-1 w-25" id="ok" /></a>
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
                    Copyright &copy; 20202422 Zemen Express Cooperate. All rights reserved.
                </p>
            </div>
        </section>
    </footer>
    <script src="../../../bootstrap-5.2.2-dist/js/bootstrap.js"></script>
</body>

</html>