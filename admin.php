<?php
session_start();
require_once('Connections/conn.php');

if (!isset($_SESSION['staffID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $oid = (int)$_POST['orderID'];
    $status = mysqli_real_escape_string($conn, $_POST['new_status']);
    mysqli_query($conn, "UPDATE Orders SET orderStatus = '$status' WHERE orderID = $oid");
}

$sql = "SELECT o.*, c.fullName, f.furnitureName 
        FROM Orders o 
        JOIN Customer c ON o.customerID = c.customerID 
        JOIN Furniture f ON o.furnitureID = f.furnitureID 
        ORDER BY o.orderDate DESC";
$orders = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Furniture System</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
    th { background-color: var(--primary); color: white; }
    .status-select { padding: 5px; border-radius: 4px; }
  </style>
</head>
<body>
  <nav>
    <div class="container nav-inner">
      <div class="logo">Staff Portal</div>
      <ul class="nav-links">
        <li><a href="logout.php">Logout (<?php echo $_SESSION['staffID']; ?>)</a></li>
      </ul>
    </div>
  </nav>

  <div class="container">
    <section class="section">
      <h2 class="section-title">Order Management</h2>
      <table>
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Item</th>
          <th>Qty</th>
          <th>Total</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php while($order = mysqli_fetch_assoc($orders)): ?>
        <tr>
          <td><?php echo $order['orderID']; ?></td>
          <td><?php echo $order['fullName']; ?></td>
          <td><?php echo $order['furnitureName']; ?></td>
          <td><?php echo $order['orderQty']; ?></td>
          <td>$<?php echo $order['totalAmount']; ?></td>
          <td><?php echo $order['orderStatus']; ?></td>
          <td>
            <form action="admin.php" method="post" style="display:inline;">
              <input type="hidden" name="orderID" value="<?php echo $order['orderID']; ?>">
              <select name="new_status" class="status-select">
                <option value="Open" <?php if($order['orderStatus']=='Open') echo 'selected'; ?>>Open</option>
                <option value="Approved" <?php if($order['orderStatus']=='Approved') echo 'selected'; ?>>Approved</option>
                <option value="Rejected" <?php if($order['orderStatus']=='Rejected') echo 'selected'; ?>>Rejected</option>
              </select>
              <button type="submit" name="update_status" style="padding: 5px 10px; cursor: pointer;">Update</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </table>
    </section>
  </div>
</body>
</html>