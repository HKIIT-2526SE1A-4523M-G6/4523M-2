<?php
session_start();
require_once('3_Connections/DB_Configuration.php');

$errorMsg = "";

// 双重保险：确保 $conn 变量在当前作用域存在
if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. 客户登录逻辑
    if (isset($_POST['cid']) && isset($_POST['cpassword'])) {
        $cid = mysqli_real_escape_string($conn, $_POST['cid']);
        $pwd = mysqli_real_escape_string($conn, $_POST['cpassword']);

        // 对齐最新 SQL 附件字段：Customer 表使用 customerID, customerNumber, customerPassword
        $sql = "SELECT * FROM Customer WHERE (customerID='$cid' OR customerNumber='$cid') AND customerPassword='$pwd'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['customerID'] = $row['customerID'];
            $_SESSION['role'] = 'customer';
            $_SESSION['fullName'] = $row['fullName'];
            header("Location: index.php");
            exit();
        } else {
            $errorMsg = "Invalid Customer ID/Phone or Password.";
        }
    }
    // 2. 管理员登录逻辑
    elseif (isset($_POST['sid']) && isset($_POST['spassword'])) {
        $sid = mysqli_real_escape_string($conn, $_POST['sid']);
        $pwd = mysqli_real_escape_string($conn, $_POST['spassword']);

        // 对齐最新 SQL 附件字段：Staff 表使用 staffID, staffPassword
        $sql = "SELECT * FROM Staff WHERE staffID='$sid' AND staffPassword='$pwd'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['staffID'] = $row['staffID'];
            $_SESSION['role'] = 'admin';
            $_SESSION['staffName'] = $row['staffName'];
            header("Location: admin.php");
            exit();
        } else {
            $errorMsg = "Invalid Staff ID or Password.";
        }
    }
}

// 兼容老版本 PHP：替代 ?? 运算符获取用户姓名
$displayName = "";
if (isset($_SESSION['fullName'])) {
    $displayName = $_SESSION['fullName'];
} elseif (isset($_SESSION['staffName'])) {
    $displayName = $_SESSION['staffName'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Furniture System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<nav>
    <div class="container nav-inner">
        <div class="logo">Furniture System</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>

            <?php if(isset($_SESSION['role'])): ?>
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <li><a href="admin.php">Admin Panel</a></li>
                <?php else: ?>
                    <li><a href="order.php">Cart</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout (<?php echo htmlspecialchars($displayName); ?>)</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="order.php">Cart</a></li>
            <?php endif; ?>

        </ul>
    </div>
</nav>

<div class="container">
    <section class="section" id="login-tabs">
        <h2 class="section-title">Login Portal</h2>

        <?php if($errorMsg != ""): ?>
            <div style="color: red; text-align: center; margin-bottom: 15px; font-weight: bold;">
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>

        <div class="tab-buttons">
            <button id="customer-tab" class="active">Customer Login</button>
            <button id="admin-tab">Admin Login</button>
        </div>

        <div id="customer-content" class="tab-content active">
            <div class="form-box">
                <h3 class="form-title">Customer Sign In</h3>
                <form action="login.php" method="post">
                    <div class="form-group">
                        <label>Customer ID / Phone</label>
                        <input type="text" class="form-control" name="cid" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="cpassword" required>
                    </div>
                    <button type="submit" class="btn-submit">Login</button>
                </form>
            </div>
        </div>

        <div id="admin-content" class="tab-content">
            <div class="form-box admin-box">
                <h3 class="form-title">Admin Sign In</h3>
                <form action="login.php" method="post">
                    <div class="form-group">
                        <label>Staff ID</label>
                        <input type="text" class="form-control" name="sid" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="spassword" required>
                    </div>
                    <button type="submit" class="btn-submit">Login as Admin</button>
                </form>
            </div>
        </div>
    </section>
</div>

<script src="js/auth.js"></script>
</body>
</html>