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
    <title>Report missed trip</title>
    <link rel="stylesheet" href="../../css/general-style.css" />
    <link rel="stylesheet" href="../../css/form-style.css" />
    <link rel="stylesheet" href="../../css/book-style.css" />
    <link rel="stylesheet" href="../../css/my-trips-style.css" />
    <link rel="stylesheet" href="../../css/navigation-bar-style3.css" />
    <link rel="icon" href="../../images/icon-image.png" />
</head>

<body>
    <header>
        <nav>
            <ul>
                <li>
                    <img src="../../images/zemen-bus-logo.png" alt="Logo" />
                </li>
                <li><span>zemen bus ethiopia</span></li>
                <li><a href="one-way-trip.php" class="other">Book</a></li>
                <li><a href="my-trips.php" class="current">My Trips</a></li>
                <li><a href="my-profile.php" class="other">My Profile</a></li>
                <li><a href="../../php/authorization-tasks/process-sign-out.php" class="other">Sign Out</a></li>

                <!-- Prints the username on the top right corner of the page -->
                <?php
                print '<li><img src="../../images/icons/male_user_20px.png" alt="user"
                style="position: relative; top: 5px; left: 30px;" /></li>';
                if (isset($_SESSION['username'])) {
                    print "<li style='color: grey;'>{$_SESSION['username']}</li>";
                }
                print '</ul>';
                ?>
        </nav>
    </header>
    <section class="content">
        <div class="large-container">
            <p class="container-title">Please confirm report</p>
            <?php
            require('../../php/booking-tasks/auxiliary/process-missed-trip.php');
            ?>
        </div>
    </section>
    <footer>
        <section>
            <img src="../../images/zemen-bus-logo.png" alt="Logo" />
            <p class="company-name">zemen express cooperate</p>
        </section>
        <section>
            <p class="footer-title">Developers</p>
            <ol>
                <li>Behafta Berihu</li>
                <li>Futsum Buruh</li>
                <li>Kibrom Zebrehe</li>
                <li>Zaid Mengstu</li>
                <li>Filmon Hayelom</li>
            </ol>
        </section>
        <section>
            <p class="footer-title">Support</p>
            <p><a href="../../tutorial.html">Tutorial</a></p>
        </section>
        <section>
            <p class="footer-title">Company</p>
            <p><a href="../../about.html">About</a></p>
            <p><a href="../../contact.html">Contact</a></p>
        </section>
        <p class="copy-right">
            Copyright &copy; 2024 Zemen Express Cooperate. All rights reserved.
        </p>
    </footer>
    <script src="../../js/jquery-3.6.1.js"></script>
    <script src="../../js/departure-date-validator.js"></script>
</body>

</html>