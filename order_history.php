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

// ── 处理删单 POST ──
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_order'])) {
    $oid = (int)$_POST['orderID'];

    // 1. 验证该订单属于当前客户
    $check = mysqli_query($conn, "SELECT orderID, orderDeliveryDate FROM `Order` WHERE orderID = $oid AND customerID = $cid");
    if (!$check || mysqli_num_rows($check) === 0) {
        $message = "<div class='alert alert-error'>Order not found or access denied.</div>";
    } else {
        $orderRow = mysqli_fetch_assoc($check);
        $deliveryDate = strtotime($orderRow['orderDeliveryDate']);
        $now          = time();
        $twoDays      = 2 * 24 * 60 * 60; // 2 days in seconds

        // 2. 校验"交货日期前两天"规则
        if ($deliveryDate - $now < $twoDays) {
            $message = "<div class='alert alert-error'>Cannot delete: Order must be cancelled at least 2 days before the delivery date.</div>";
        } else {
            // 3. 读取该订单的 OrderFurniture 明细，用于还原物料库存
            $detailRes = mysqli_query($conn,
                "SELECT of.furnitureID, of.orderQty FROM OrderFurniture of WHERE of.orderID = $oid");

            mysqli_query($conn, "START TRANSACTION");
            $dbError = false;

            if ($detailRes) {
                while ($dr = mysqli_fetch_assoc($detailRes)) {
                    $fid = (int)$dr['furnitureID'];
                    $qty = (int)$dr['orderQty'];

                    // 还原每种原材料库存
                    $matRes = mysqli_query($conn,
                        "SELECT materialID, materialRequiredQty FROM FurnitureMaterial WHERE furnitureID = $fid");
                    if ($matRes) {
                        while ($mr = mysqli_fetch_assoc($matRes)) {
                            $mid      = (int)$mr['materialID'];
                            $restore  = (int)$mr['materialRequiredQty'] * $qty;
                            $upd = "UPDATE Material SET materialPhysicalQty = materialPhysicalQty + $restore WHERE materialID = $mid";
                            if (!mysqli_query($conn, $upd)) { $dbError = true; }
                        }
                    }
                }
            }

            // 4. 删除 OrderFurniture 明细 → 删除主订单
            if (!mysqli_query($conn, "DELETE FROM OrderFurniture WHERE orderID = $oid")) { $dbError = true; }
            if (!mysqli_query($conn, "DELETE FROM `Order` WHERE orderID = $oid AND customerID = $cid")) { $dbError = true; }

            if (!$dbError) {
                mysqli_query($conn, "COMMIT");
                $message = "<div class='alert alert-success'>Order #$oid deleted successfully. Material stock has been restored.</div>";
            } else {
                mysqli_query($conn, "ROLLBACK");
                $message = "<div class='alert alert-error'>System error: Failed to delete order. Please try again.</div>";
            }
        }
    }
}

// ── 排序参数 ──
$allowedCols = array('orderID', 'orderDate', 'orderDeliveryDate', 'orderTotalAmount', 'orderStatu');
$sortCol = (isset($_GET['sort']) && in_array($_GET['sort'], $allowedCols)) ? $_GET['sort'] : 'orderDate';
$sortDir = (isset($_GET['dir']) && $_GET['dir'] === 'asc') ? 'ASC' : 'DESC';
$nextDir = ($sortDir === 'DESC') ? 'asc' : 'desc';

function sortLink($col, $label, $currentCol, $currentDir, $nextDir) {
    $arrow = "";
    if ($currentCol === $col) {
        $arrow = $currentDir === 'DESC' ? ' ▼' : ' ▲';
        $dir   = $nextDir;
    } else {
        $dir = 'desc';
    }
    return "<a href='order_history.php?sort=$col&dir=$dir'>$label<span class='sort-arrow'>$arrow</span></a>";
}

// ── 查询订单列表 ──
$sql = "SELECT o.orderID, o.orderDate, o.orderTotalAmount, o.orderDeliveryDate, o.orderDeliveryAddress, o.orderStatu,
               c.customerID,
               GROUP_CONCAT(CONCAT(f.furnitureID, ' - ', f.furnitureName, ' x', of.orderQty) SEPARATOR '<br>') AS items_detail
        FROM `Order` o
        JOIN Customer c ON o.customerID = c.customerID
        JOIN OrderFurniture of ON o.orderID = of.orderID
        JOIN Furniture f ON of.furnitureID = f.furnitureID
        WHERE o.customerID = $cid
        GROUP BY o.orderID
        ORDER BY $sortCol $sortDir";

$orders = mysqli_query($conn, $sql);

$statusMap = array(1 => 'Pending', 2 => 'Processing', 3 => 'Delivering', 4 => 'Completed', 5 => 'Cancelled');
$displayName = isset($_SESSION['fullName']) ? $_SESSION['fullName'] : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Furniture System</title>
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

<div class="admin-container">
    <h2 class="section-title">My Order History</h2>

    <?php echo $message; ?>

    <p style="font-size:13px;color:#666;margin-bottom:12px;">
        Click column headers to sort. Orders can only be deleted <strong>more than 2 days</strong> before the delivery date.
    </p>

    <table>
        <thead>
        <tr>
            <th><?php echo sortLink('orderID',          'Order ID',       $sortCol, $sortDir, $nextDir); ?></th>
            <th><?php echo sortLink('orderDate',         'Order Date',     $sortCol, $sortDir, $nextDir); ?></th>
            <th>Furniture (ID - Name × Qty)</th>
            <th>Customer ID</th>
            <th><?php echo sortLink('orderTotalAmount',  'Total Amount',   $sortCol, $sortDir, $nextDir); ?></th>
            <th><?php echo sortLink('orderDeliveryDate', 'Delivery Date',  $sortCol, $sortDir, $nextDir); ?></th>
            <th>Delivery Address</th>
            <th><?php echo sortLink('orderStatu',        'Status',         $sortCol, $sortDir, $nextDir); ?></th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
            <?php while ($o = mysqli_fetch_assoc($orders)):
                $deliveryTs  = strtotime($o['orderDeliveryDate']);
                $canDelete   = ($deliveryTs - time()) >= (2 * 24 * 60 * 60);
                $statusInt   = (int)$o['orderStatu'];
                $statusLabel = isset($statusMap[$statusInt]) ? $statusMap[$statusInt] : 'Unknown';
                // 仅 Pending / Processing 状态可删
                $canDelete   = $canDelete && in_array($statusInt, array(1, 2));
            ?>
            <tr>
                <td><?php echo $o['orderID']; ?></td>
                <td><?php echo $o['orderDate']; ?></td>
                <td style="text-align:left;"><?php echo $o['items_detail']; ?></td>
                <td><?php echo $o['customerID']; ?></td>
                <td>$<?php echo number_format($o['orderTotalAmount'], 2); ?></td>
                <td><?php echo $o['orderDeliveryDate']; ?></td>
                <td><?php echo htmlspecialchars($o['orderDeliveryAddress']); ?></td>
                <td><strong><?php echo $statusLabel; ?></strong></td>
                <td>
                    <?php if ($canDelete): ?>
                    <form class="delete-form" action="order_history.php?sort=<?php echo $sortCol; ?>&dir=<?php echo strtolower($sortDir); ?>" method="post"
                          data-orderid="<?php echo $o['orderID']; ?>">
                        <input type="hidden" name="orderID" value="<?php echo $o['orderID']; ?>">
                        <button type="button" class="btn-delete delete-btn">Delete</button>
                        <input type="hidden" name="delete_order" value="1">
                    </form>
                    <?php else: ?>
                        <span style="color:#999;font-size:12px;">
                            <?php echo in_array($statusInt, array(3,4,5)) ? $statusLabel : 'Within 2 days'; ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="9">No orders found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<link rel="stylesheet" href="css/admin.css">
<script src="js/confirm.js"></script>
<script>
document.querySelectorAll('.delete-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var form    = btn.closest('.delete-form');
        var orderId = form.dataset.orderid;
        showConfirm(
            'Delete Order #' + orderId,
            'Are you sure you want to delete this order? The material stock will be restored. This action cannot be undone.'
        ).then(function(ok) {
            if (ok) form.submit();
        });
    });
});
</script>
</body>
</html>
