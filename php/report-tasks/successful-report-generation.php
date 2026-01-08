<?php
session_start();
if (!isset($_SESSION['account_type']) or ($_SESSION['account_type'] != "Finance Officer")) {
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
    <title>Report generated</title>
    <link rel="stylesheet" href="../../css/general-style.css" />
    <link rel="stylesheet" href="../../css/form-style.css" />
    <link rel="stylesheet" href="../../css/sucessful-sign-up-style.css" />
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
            </ul>
        </nav>
    </header>
    <section class="content">
        <div class="large-container">
            <p class="container-title">Report generation successful</p>
            <p class="text">The new report has been successfully added to the database.</p>
            <div class="field">
                <a href="../../users/employee/finance-officer/view-reports.php">
                    <input type="button" name="ok" value="ok" class="button" id="ok" /></a>
            </div>
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
            <p><a href="../../users/employee/tutorial.html">Tutorial</a></p>
        </section>
        <section>
            <p class="footer-title">Company</p>
            <p><a href="../../users/employee/about.html">About</a></p>
            <p><a href="../../users/employee/contact.html">Contact</a></p>
        </section>
        <p class="copy-right">
            Copyright &copy; 2024 Zemen Express Cooperate. All rights reserved.
        </p>
    </footer>
</body>

</html>