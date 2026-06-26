<?php
session_start();
require_once('Connections/conn.php');

$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['cid']) && isset($_POST['cpassword'])) {
        $cid = mysqli_real_escape_string($conn, $_POST['cid']);
        $pwd = mysqli_real_escape_string($conn, $_POST['cpassword']);
        
        $sql = "SELECT * FROM Customer WHERE (customerID='$cid' OR contactNumber='$cid') AND password='$pwd'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
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
    elseif (isset($_POST['sid']) && isset($_POST['spassword'])) {
        $sid = mysqli_real_escape_string($conn, $_POST['sid']);
        $pwd = mysqli_real_escape_string($conn, $_POST['spassword']);
        
        $sql = "SELECT * FROM Staff WHERE staffID='$sid' AND password='$pwd'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $_SESSION['staffID'] = $sid;
            $_SESSION['role'] = 'admin';
            header("Location: admin.php");
            exit();
        } else {
            $errorMsg = "Invalid Staff ID or Password.";
        }
    }
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
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="order.php">Cart</a></li>
      </ul>
    </div>
  </nav>

  <div class="container">
    <section class="section" id="login-tabs">
      <h2 class="section-title">Login Portal</h2>
      
      <?php if($errorMsg != "") { echo "<div style='color:red; text-align:center; margin-bottom:15px;'>$errorMsg</div>"; } ?>

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