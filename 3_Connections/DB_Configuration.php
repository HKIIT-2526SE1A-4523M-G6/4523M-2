<?php
// 统一的 XAMPP 数据库连接配置文件
$host = "localhost";  // same as $host = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "projectDB";

$conn = new mysqli($host, $username, $password, $dbname)
    or die(mysqli_connect_error());

// 检查连接
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 统一设置字符集为 utf8mb4，防止中文/特殊字符乱码
$conn->set_charset("utf8mb4");
