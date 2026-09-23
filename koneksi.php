<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "basket_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {

    error_log(
        "Database connection failed: " .
        $conn->connect_error
    );

    die("Terjadi gangguan pada sistem. Silakan coba lagi nanti.");
}

$conn->set_charset("utf8mb4");

?>