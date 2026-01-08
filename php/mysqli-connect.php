<?php

Define('DB_USER', 'webmaster');
Define('DB_PASSWORD', '#web@master');
Define('DB_HOST', 'localhost');
Define('DB_NAME', 'zemen_bus_trs');

try {
    $dbcon = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset($dbcon, 'utf8');
} catch (Exception $e) {
    print "An Exception occurred. Message: " . $e->getMessage();
    print "The system is busy please try later";
} catch (Error $e) {
    print "An Error occurred. Message: " . $e->getMessage();
    print "The system is busy please try again later.";
}
