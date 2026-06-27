<?php
session_start();
require_once('3_Connections/DB_Configuration.php');

$message = "";

// 双重保险：确保 $conn 变量在老版本 PHP 作用域存在
if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}

// 当用户点击提交表单时，由当前页面直接处理逻辑
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cname = mysqli_real_escape_string($conn, $_POST['cname']);
    $cpassword = mysqli_real_escape_string($conn, $_POST['cpassword']);
    $ctel = mysqli_real_escape_string($conn, $_POST['ctel']);
    $caddr = mysqli_real_escape_string($conn, $_POST['caddr']);
    $company = isset($_POST['company']) ? mysqli_real_escape_string($conn, $_POST['company']) : '';

    // 【已对齐新版字段】：检查 customerNumber 是否已被注册
    $check_sql = "SELECT * FROM Customer WHERE customerNumber = '$ctel'";
    $check_res = mysqli_query($conn, $check_sql);

    if ($check_res && mysqli_num_rows($check_res) > 0) {
        $message = "<div style='color:red; text-align:center; margin-bottom:15px; font-weight:bold;'>Registration Failed: Telephone number already registered.</div>";
    } else {
        // 【已对齐新版字段】：写入新版字段名 fullName, customerPassword, customerNumber, customerAddress
        $insert_sql = "INSERT INTO Customer (fullName, customerPassword, customerNumber, customerAddress) 
               VALUES ('$cname', '$cpassword', '$ctel', '$caddr')";

        if (mysqli_query($conn, $insert_sql)) {
            $message = "<div style='color:green; text-align:center; margin-bottom:15px; font-weight:bold;'>Registration successful! <a href='login.php'>Click here to login</a></div>";
        } else {
            $message = "<div style='color:red; text-align:center; margin-bottom:15px; font-weight:bold;'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Furniture System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <nav>
        <div class="container nav-inner">
            <div class="logo">Furniture System</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <!-- 已登录状态的动态导航栏 -->
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li><a href="admin.php">Admin Panel</a></li>
                    <?php else: ?>
                        <li><a href="order.php">Cart</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars(isset($_SESSION['fullName']) ? $_SESSION['fullName'] : (isset($_SESSION['staffName']) ? $_SESSION['staffName'] : 'User')); ?>)</a></li>
                <?php else: ?>
                    <!-- 未登录状态 -->
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="order.php">Cart</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <section class="section" id="register">
            <h2 class="section-title">Customer Registration</h2>
            <div class="form-box">
                <h3 class="form-title">Create New Account</h3>

                <?php echo $message; ?>

                <form action="register.php" method="post">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="form-control" name="cname" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="cpassword" required>
                    </div>
                    <div class="form-group">
                        <label>Telephone</label>
                        <input type="text" class="form-control" name="ctel" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="caddr" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Company (Optional)</label>
                        <input type="text" class="form-control" name="company">
                    </div>
                    <button type="submit" class="btn-submit">Register</button>
                </form>
            </div>
        </section>
    </div>

</body>

</html>