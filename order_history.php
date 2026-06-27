<?php
session_start();
require_once('3_Connections/DB_Configuration.php');
if (!isset($conn) && isset($GLOBALS['conn'])) $conn = $GLOBALS['conn'];

if (!isset($_SESSION['customerID']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}
$cid = (int)$_SESSION['customerID'];

/* ── API ── */
$ct = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($ct, 'application/json') !== false) {
    header('Content-Type: application/json');
    $body = json_decode(file_get_contents('php://input'), true);
    //    $oid  = (int)($body['orderID'] ?? 0);

    $oid  = (int)(isset($body['orderID']) ? $body['orderID'] : 0);

    $check = mysqli_query($conn, "SELECT orderID,orderDeliveryDate,orderStatu FROM customerOrder WHERE orderID=$oid AND customerID=$cid");
    if (!$check || mysqli_num_rows($check) === 0) {
        echo json_encode(['ok' => false, 'msg' => 'Order not found or access denied.']);
        exit();
    }
    $orderRow   = mysqli_fetch_assoc($check);
    $statusInt  = (int)$orderRow['orderStatu'];
    $deliveryTs = strtotime($orderRow['orderDeliveryDate']);

    if (!in_array($statusInt, [1, 2])) {
        echo json_encode(['ok' => false, 'msg' => 'Only Pending or Processing orders can be deleted.']);
        exit();
    }
    if ($deliveryTs - time() < 2 * 24 * 60 * 60) {
        echo json_encode(['ok' => false, 'msg' => 'Cannot delete: must cancel at least 2 days before delivery.']);
        exit();
    }

    $detailRes = mysqli_query($conn, "SELECT furnitureID,orderQty FROM OrderFurniture WHERE orderID=$oid");
    mysqli_query($conn, "START TRANSACTION");
    $dbError = false;

    if ($detailRes) {
        while ($dr = mysqli_fetch_assoc($detailRes)) {
            $fid = (int)$dr['furnitureID'];
            $qty = (int)$dr['orderQty'];
            $matRes = mysqli_query($conn, "SELECT materialID,materialRequiredQty FROM FurnitureMaterial WHERE furnitureID=$fid");
            if ($matRes) {
                while ($mr = mysqli_fetch_assoc($matRes)) {
                    $restore = (int)$mr['materialRequiredQty'] * $qty;
                    //                    if (!mysqli_query($conn, "UPDATE Material SET materialPhysicalQty=materialPhysicalQty+$restore WHERE materialID={(int)$mr['materialID']}"))
                    //                        $dbError = true;

                    // 💡 做法：在外面先轉成整數，確保安全
                    $materialID = (int)$mr['materialID'];

                    // 這樣 SQL 字串就非常乾淨，不會再引發 PHP 語法解析錯誤
                    if (!mysqli_query($conn, "UPDATE Material SET materialPhysicalQty=materialPhysicalQty+$restore  WHERE materialID=$materialID")) {
                        $dbError = true;
                    }
                }
            }
        }
    }
    if (!mysqli_query($conn, "DELETE FROM OrderFurniture WHERE orderID=$oid")) $dbError = true;
    if (!mysqli_query($conn, "DELETE FROM customerOrder WHERE orderID=$oid AND customerID=$cid")) $dbError = true;

    if (!$dbError) {
        mysqli_query($conn, "COMMIT");
        echo json_encode(['ok' => true, 'msg' => "Order #$oid deleted. Stock restored.", 'orderID' => $oid]);
    } else {
        mysqli_query($conn, "ROLLBACK");
        echo json_encode(['ok' => false, 'msg' => 'System error: ' . mysqli_error($conn)]);
    }
    exit();
}

/* ── 页面模式 ── */
$allowedCols = ['orderID', 'orderDate', 'orderDeliveryDate', 'orderTotalAmount', 'orderStatu'];
$sortCol = (isset($_GET['sort']) && in_array($_GET['sort'], $allowedCols)) ? $_GET['sort'] : 'orderDate';
$sortDir = (isset($_GET['dir']) && $_GET['dir'] === 'asc') ? 'ASC' : 'DESC';
$nextDir = $sortDir === 'DESC' ? 'asc' : 'desc';

function sortLink($col, $label, $cur, $curDir, $nxtDir)
{
    $arrow = '';
    $dir = 'desc';
    if ($cur === $col) {
        $arrow = $curDir === 'DESC' ? ' ▼' : ' ▲';
        $dir = $nxtDir;
    }
    return "<a href='order_history.php?sort=$col&dir=$dir'>$label<span class='sort-arrow'>$arrow</span></a>";
}

$sql = "SELECT o.orderID,o.orderDate,o.orderTotalAmount,o.orderDeliveryDate,o.orderDeliveryAddress,o.orderStatu,
               c.customerID,
               GROUP_CONCAT(CONCAT(f.furnitureID,' - ',f.furnitureName,' x',of.orderQty) SEPARATOR '<br>') AS items_detail
        FROM customerOrder o
        JOIN Customer c ON o.customerID=c.customerID
        JOIN OrderFurniture of ON o.orderID=of.orderID
        JOIN Furniture f ON of.furnitureID=f.furnitureID
        WHERE o.customerID=$cid
        GROUP BY o.orderID
        ORDER BY $sortCol $sortDir";
$orders     = mysqli_query($conn, $sql);
$statusMap  = [1 => 'Pending', 2 => 'Processing', 3 => 'Delivering', 4 => 'Completed', 5 => 'Cancelled'];
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
        <div id="history-msg"></div>
        <p style="font-size:13px;color:#666;margin-bottom:12px;">
            Orders can only be deleted <strong>more than 2 days</strong> before delivery, and only when Pending or Processing.
        </p>
        <table id="history-table">
            <thead>
                <tr>
                    <th><?php echo sortLink('orderID', 'Order ID', $sortCol, $sortDir, $nextDir); ?></th>
                    <th><?php echo sortLink('orderDate', 'Order Date', $sortCol, $sortDir, $nextDir); ?></th>
                    <th>Furniture (ID – Name × Qty)</th>
                    <th>Customer ID</th>
                    <th><?php echo sortLink('orderTotalAmount', 'Total', $sortCol, $sortDir, $nextDir); ?></th>
                    <th><?php echo sortLink('orderDeliveryDate', 'Delivery Date', $sortCol, $sortDir, $nextDir); ?></th>
                    <th>Delivery Address</th>
                    <th><?php echo sortLink('orderStatu', 'Status', $sortCol, $sortDir, $nextDir); ?></th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && mysqli_num_rows($orders) > 0):
                    while ($o = mysqli_fetch_assoc($orders)):
                        $statusInt   = (int)$o['orderStatu'];
                        $deliveryTs  = strtotime($o['orderDeliveryDate']);
                        $canDelete   = in_array($statusInt, [1, 2]) && ($deliveryTs - time() >= 2 * 24 * 60 * 60);
                        //                $statusLabel = $statusMap[$statusInt]??'Unknown';
                        $statusLabel = isset($statusMap[$statusInt]) ? $statusMap[$statusInt] : 'Unknown';

                ?>
                        <tr data-order-id="<?php echo $o['orderID']; ?>">
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
                                    <button class="btn-delete delete-btn"
                                        data-order-id="<?php echo $o['orderID']; ?>">Delete</button>
                                <?php else: ?>
                                    <span style="color:#999;font-size:12px;">
                                        <?php echo in_array($statusInt, [3, 4, 5]) ? $statusLabel : 'Within 2 days'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr id="empty-row">
                        <td colspan="9">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="js/confirm.js"></script>
    <script src="js/order_history.js"></script>
</body>

</html>