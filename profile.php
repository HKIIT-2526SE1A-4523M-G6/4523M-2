<?php
session_start();
require_once('3_Connections/DB_Configuration.php');

if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}

// 只允许 customer 访问
if (!isset($_SESSION['customerID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$cid     = (int)$_SESSION['customerID'];
$message = "";

// ── 处理 POST 更新 ──
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = trim($_POST['new_password']);
    $newContact  = mysqli_real_escape_string($conn, trim($_POST['new_contact']));
    $newAddress  = mysqli_real_escape_string($conn, trim($_POST['new_address']));

    // 至少填一项
    if ($newPassword === "" && $newContact === "" && $newAddress === "") {
        $message = "<div class='alert alert-warning'>Please fill in at least one field to update.</div>";
    } else {
        // 构建动态 SET 子句
        $setParts = array();
        if ($newContact !== "") {
            $setParts[] = "customerNumber = '$newContact'";
        }
        if ($newAddress !== "") {
            $setParts[] = "customerAddress = '$newAddress'";
        }
        if ($newPassword !== "") {
            $hashedPwd  = mysqli_real_escape_string($conn, $newPassword);
            $setParts[] = "customerPassword = '$hashedPwd'";
        }

        $setClause = implode(", ", $setParts);
        $sql = "UPDATE Customer SET $setClause WHERE customerID = $cid";

        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'>Profile updated successfully.</div>";
        } else {
            $message = "<div class='alert alert-error'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// ── 读取当前资料（只读展示）──
$row = null;
$res = mysqli_query($conn, "SELECT fullName, customerNumber, customerAddress, customerEmail FROM Customer WHERE customerID = $cid");
if ($res) {
    $row = mysqli_fetch_assoc($res);
}

$displayName = isset($_SESSION['fullName']) ? $_SESSION['fullName'] : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Furniture System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<nav>
    <div class="container nav-inner">
        <div class="logo">Furniture System</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="order.php">Cart</a></li>
            <li><a href="order_history.php">My Orders</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout (<?php echo htmlspecialchars($displayName); ?>)</a></li>
        </ul>
    </div>
</nav>

<div class="container">
    <section class="section">
        <h2 class="section-title">My Profile</h2>

        <!-- 只读信息 -->
        <?php if ($row): ?>
        <div class="form-box" style="margin-bottom:24px;">
            <h3 class="form-title">Current Information</h3>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr><td style="padding:8px;color:#666;width:40%;">Full Name</td>
                    <td style="padding:8px;font-weight:600;"><?php echo htmlspecialchars($row['fullName']); ?></td></tr>
                <tr style="background:#f9f9f9;"><td style="padding:8px;color:#666;">Contact Number</td>
                    <td style="padding:8px;"><?php echo htmlspecialchars($row['customerNumber']); ?></td></tr>
                <tr><td style="padding:8px;color:#666;">Address</td>
                    <td style="padding:8px;"><?php echo htmlspecialchars($row['customerAddress']); ?></td></tr>
                <tr style="background:#f9f9f9;"><td style="padding:8px;color:#666;">Email</td>
                    <td style="padding:8px;"><?php echo htmlspecialchars($row['customerEmail'] ? $row['customerEmail'] : '—'); ?></td></tr>
            </table>
        </div>
        <?php endif; ?>

        <!-- 更新表单 -->
        <div class="form-box">
            <h3 class="form-title">Update Profile</h3>
            <?php echo $message; ?>
            <form action="profile.php" method="post">
                <div class="form-group">
                    <label>New Password <span style="color:#999;font-size:12px;">(leave blank to keep current)</span></label>
                    <input type="password" class="form-control" name="new_password" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label>New Contact Number <span style="color:#999;font-size:12px;">(leave blank to keep current)</span></label>
                    <input type="text" class="form-control" name="new_contact" placeholder="Enter new contact number">
                </div>
                <div class="form-group">
                    <label>New Address <span style="color:#999;font-size:12px;">(leave blank to keep current)</span></label>
                    <textarea class="form-control" name="new_address" placeholder="Enter new address"></textarea>
                </div>
                <button type="submit" class="btn-submit">Save Changes</button>
            </form>
        </div>

    </section>
</div>
</body>
</html>
