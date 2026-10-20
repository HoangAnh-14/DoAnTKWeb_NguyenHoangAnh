<?php

$conn = new mysqli("localhost", "root", "", "WebXemPhim");

if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


?>