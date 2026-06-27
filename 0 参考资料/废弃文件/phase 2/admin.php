<?php
session_start();
require_once('3_Connections/DB_Configuration.php');

// 双重保险：确保 $conn 变量在老版本 PHP 作用域存在
if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}

// 安全门禁：检查是否是管理员登录，不是则退回登录页
if (!isset($_SESSION['staffID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 处理修改订单状态的 POST 请求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $oid = (int)$_POST['orderID'];
    $new_status = (int)$_POST['new_status']; // 新版状态为数字：1 Pending、2 Processing、3 Delivering、4 Completed、5 Cancelled

    // 对齐新版表名 `Order` 和状态字段 `orderStatu`
    $update_sql = "UPDATE `Order` SET `orderStatu` = $new_status WHERE `orderID` = $oid";
    mysqli_query($conn, $update_sql);
}

/* * 【完美对齐新版多对多数据库逻辑】：
 * 因为订单(Order)和家具(Furniture)通过 OrderFurniture 关联，
 * 这里使用 GROUP_CONCAT 将一个订单下的多种家具合并显示，计算总价
 */
$sql = "SELECT o.orderID, o.orderDate, o.orderTotalAmount, o.orderDeliveryAddress, o.orderStatu, c.fullName,
               GROUP_CONCAT(CONCAT(f.furnitureName, ' x', of.orderQty) SEPARATOR '<br>') as items_detail
        FROM `Order` o
        JOIN `Customer` c ON o.customerID = c.customerID
        JOIN `OrderFurniture` of ON o.orderID = of.orderID
        JOIN `Furniture` f ON of.furnitureID = f.furnitureID
        GROUP BY o.orderID
        ORDER BY o.orderDate DESC";

$orders = mysqli_query($conn, $sql);

// 兼容老版本 PHP 的状态文字映射数组
$statusMap = array(
        1 => 'Pending',
        2 => 'Processing',
        3 => 'Delivering',
        4 => 'Completed',
        5 => 'Cancelled'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Furniture System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container { padding: 30px; max-width: 1200px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
        th { background-color: #333; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-select { padding: 5px; border-radius: 4px; }
        .btn-update { padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-update:hover { background: #218838; }
    </style>
</head>
<body>
<nav>
    <div class="container nav-inner">
        <div class="logo">Furniture System (Admin Panel)</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="admin.php">Admin Panel</a></li>
            <li><a href="logout.php">Logout (<?php echo htmlspecialchars(isset($_SESSION['staffName']) ? $_SESSION['staffName'] : 'Staff'); ?>)</a></li>
        </ul>
    </div>
</nav>

<div class="admin-container">
    <h2>Customer Orders Management</h2>

    <table>
        <thead>
        <tr>
            <th>Order ID</th>
            <th>Order Date</th>
            <th>Customer Name</th>
            <th>Furniture Items</th>
            <th>Total Amount</th>
            <th>Delivery Address</th>
            <th>Current Status</th>
            <th>Change Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
            <?php while($order = mysqli_fetch_assoc($orders)): ?>
                <tr>
                    <td><?php echo $order['orderID']; ?></td>
                    <td><?php echo $order['orderDate']; ?></td>
                    <td><?php echo htmlspecialchars($order['fullName']); ?></td>
                    <td style="text-align: left;"><?php echo $order['items_detail']; ?></td>
                    <td>$<?php echo number_format($order['orderTotalAmount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($order['orderDeliveryAddress']); ?></td>
                    <td>
                        <strong>
                            <?php
                            $current_st = (int)$order['orderStatu'];
                            echo isset($statusMap[$current_st]) ? $statusMap[$current_st] : 'Unknown';
                            ?>
                        </strong>
                    </td>
                    <td>
                        <form action="admin.php" method="post" style="display:inline;">
                            <input type="hidden" name="orderID" value="<?php echo $order['orderID']; ?>">
                            <select name="new_status" class="status-select">
                                <?php foreach($statusMap as $val => $text): ?>
                                    <option value="<?php echo $val; ?>" <?php if($order['orderStatu'] == $val) echo 'selected'; ?>>
                                        <?php echo $text; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn-update">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No orders found in the system.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>