<?php
//session_start();
//require_once('3_Connections/DB_Configuration.php');
//
//if (!isset($conn) && isset($GLOBALS['conn'])) {
//    $conn = $GLOBALS['conn'];
//}
//
//// 门禁
//if (!isset($_SESSION['staffID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//    header("Location: login.php");
//    exit();
//}
//
//$message = "";
//
//// ── 处理修改订单状态 + 数量 POST ──
//if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {
//    $oid        = (int)$_POST['orderID'];
//    $new_status = (int)$_POST['new_status'];
//    $new_qty    = (int)$_POST['new_qty'];
//    $fid        = (int)$_POST['furnitureID'];
//
//    // 读取旧数量
//    $oldRes = mysqli_query($conn, "SELECT orderQty FROM OrderFurniture WHERE orderID = $oid AND furnitureID = $fid");
//    $oldRow = $oldRes ? mysqli_fetch_assoc($oldRes) : null;
//    $old_qty = $oldRow ? (int)$oldRow['orderQty'] : 0;
//
//    mysqli_query($conn, "START TRANSACTION");
//    $dbError = false;
//
//    // 如果数量有变动 → 调整物料库存
//    if ($new_qty !== $old_qty && $new_qty > 0) {
//        $delta = $new_qty - $old_qty; // 正数=增购(要扣库存)，负数=减购(要还库存)
//
//        $matRes = mysqli_query($conn, "SELECT materialID, materialRequiredQty FROM FurnitureMaterial WHERE furnitureID = $fid");
//        if ($matRes) {
//            while ($mr = mysqli_fetch_assoc($matRes)) {
//                $mid        = (int)$mr['materialID'];
//                $perUnit    = (int)$mr['materialRequiredQty'];
//                $adjustment = $perUnit * $delta; // 正=扣减，负=归还
//                $upd = "UPDATE Material SET materialPhysicalQty = materialPhysicalQty - $adjustment WHERE materialID = $mid";
//                if (!mysqli_query($conn, $upd)) { $dbError = true; }
//            }
//        }
//
//        // 更新 OrderFurniture 数量
//        $price_res = mysqli_query($conn, "SELECT furniturePrice FROM Furniture WHERE furnitureID = $fid");
//        $price_row = mysqli_fetch_assoc($price_res);
//        $price     = (float)$price_row['furniturePrice'];
//        $newTotal  = $price * $new_qty;
//
//        if (!mysqli_query($conn, "UPDATE OrderFurniture SET orderQty = $new_qty WHERE orderID = $oid AND furnitureID = $fid")) {
//            $dbError = true;
//        }
//        // 更新主订单总额（简化：单品订单，与 order.php 下单逻辑一致）
//        if (!mysqli_query($conn, "UPDATE `Order` SET orderTotalAmount = $newTotal WHERE orderID = $oid")) {
//            $dbError = true;
//        }
//    }
//
//    // 更新订单状态
//    if (!mysqli_query($conn, "UPDATE `Order` SET orderStatu = $new_status WHERE orderID = $oid")) {
//        $dbError = true;
//    }
//
//    if (!$dbError) {
//        mysqli_query($conn, "COMMIT");
//        $message = "<div class='alert alert-success'>Order #$oid updated successfully.</div>";
//    } else {
//        mysqli_query($conn, "ROLLBACK");
//        $message = "<div class='alert alert-error'>Error: " . mysqli_error($conn) . "</div>";
//    }
//}
//
//// ── 查询所有订单（含物料详情）──
//$sql = "SELECT o.orderID, o.orderDate, o.orderTotalAmount, o.orderDeliveryAddress, o.orderDeliveryDate,
//               o.orderStatu,
//               c.fullName, c.customerNumber,
//               f.furnitureID, f.furnitureName, f.furnitureImage, f.furniturePrice,
//               of.orderQty
//        FROM `Order` o
//        JOIN Customer c ON o.customerID = c.customerID
//        JOIN OrderFurniture of ON o.orderID = of.orderID
//        JOIN Furniture f ON of.furnitureID = f.furnitureID
//        ORDER BY o.orderDate DESC";
//
//$orders = mysqli_query($conn, $sql);
//
//$statusMap = array(1 => 'Pending', 2 => 'Processing', 3 => 'Delivering', 4 => 'Completed', 5 => 'Cancelled');
//$staffName = isset($_SESSION['staffName']) ? $_SESSION['staffName'] : 'Staff';
//?>
<!--<!DOCTYPE html>-->
<!--<html lang="en">-->
<!--<head>-->
<!--    <meta charset="UTF-8">-->
<!--    <meta name="viewport" content="width=device-width, initial-scale=1.0">-->
<!--    <title>Order Management - Furniture System</title>-->
<!--    <link rel="stylesheet" href="css/style.css">-->
<!--    <link rel="stylesheet" href="css/admin.css">-->
<!--</head>-->
<!--<body>-->
<!--<nav>-->
<!--    <div class="container nav-inner">-->
<!--        <div class="logo">Furniture System (Staff)</div>-->
<!--        <ul class="nav-links">-->
<!--            <li><a href="index.php">Home</a></li>-->
<!--            <li><a href="admin.php">Orders</a></li>-->
<!--            <li><a href="admin_furniture.php">Furniture</a></li>-->
<!--            <li><a href="admin_material.php">Materials</a></li>-->
<!--            <li><a href="admin_report.php">Report</a></li>-->
<!--            <li><a href="logout.php">Logout (--><?php //echo htmlspecialchars($staffName); ?><!--)</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</nav>-->
<!---->
<!--<div class="admin-container">-->
<!--    <h2 class="section-title">Order Management</h2>-->
<!---->
<!--    <div class="admin-tabs">-->
<!--        <a href="admin.php" class="active">Orders</a>-->
<!--        <a href="admin_furniture.php">Furniture</a>-->
<!--        <a href="admin_material.php">Materials</a>-->
<!--        <a href="admin_report.php">Report</a>-->
<!--    </div>-->
<!---->
<!--    --><?php //echo $message; ?>
<!---->
<!--    <table>-->
<!--        <thead>-->
<!--        <tr>-->
<!--            <th>Order ID</th>-->
<!--            <th>Order Date</th>-->
<!--            <th>Customer Name</th>-->
<!--            <th>Contact No.</th>-->
<!--            <th>Furniture</th>-->
<!--            <th>Image</th>-->
<!--            <th>Qty</th>-->
<!--            <th>Total ($)</th>-->
<!--            <th>Delivery Address</th>-->
<!--            <th>Delivery Date</th>-->
<!--            <th>Status</th>-->
<!--            <th>Materials Used</th>-->
<!--            <th>Update</th>-->
<!--        </tr>-->
<!--        </thead>-->
<!--        <tbody>-->
<!--        --><?php //if ($orders && mysqli_num_rows($orders) > 0): ?>
<!--            --><?php //while ($o = mysqli_fetch_assoc($orders)):
//                $fid       = (int)$o['furnitureID'];
//                $statusInt = (int)$o['orderStatu'];
//                $qty       = (int)$o['orderQty'];
//
//                // 查该家具 BOM 物料及库存
//                $matSQL = "SELECT m.materialID, m.materialName, m.materialUnit,
//                                  m.materialPhysicalQty,
//                                  fm.materialRequiredQty,
//                                  FLOOR(m.materialPhysicalQty / fm.materialRequiredQty) AS availableStock
//                           FROM FurnitureMaterial fm
//                           JOIN Material m ON fm.materialID = m.materialID
//                           WHERE fm.furnitureID = $fid";
//                $matRes = mysqli_query($conn, $matSQL);
//            ?>
<!--            <tr>-->
<!--                <td>--><?php //echo $o['orderID']; ?><!--</td>-->
<!--                <td>--><?php //echo $o['orderDate']; ?><!--</td>-->
<!--                <td>--><?php //echo htmlspecialchars($o['fullName']); ?><!--</td>-->
<!--                <td>--><?php //echo htmlspecialchars($o['customerNumber']); ?><!--</td>-->
<!--                <td>--><?php //echo htmlspecialchars($o['furnitureName']); ?><!--<br>-->
<!--                    <small style="color:#999;">ID: --><?php //echo $fid; ?><!--</small></td>-->
<!--                <td>-->
<!--                    --><?php //$imgPath = '1_Resources/furntiure_images/' . ($o['furnitureImage'] ? $o['furnitureImage'] : 'default.png'); ?>
<!--                    <img src="--><?php //echo htmlspecialchars($imgPath); ?><!--" class="table-img"-->
<!--                         onerror="this.style.display='none'" alt="">-->
<!--                </td>-->
<!--                <td>--><?php //echo $qty; ?><!--</td>-->
<!--                <td>$--><?php //echo number_format($o['orderTotalAmount'], 2); ?><!--</td>-->
<!--                <td>--><?php //echo htmlspecialchars($o['orderDeliveryAddress']); ?><!--</td>-->
<!--                <td>--><?php //echo $o['orderDeliveryDate']; ?><!--</td>-->
<!--                <td><strong>--><?php //echo isset($statusMap[$statusInt]) ? $statusMap[$statusInt] : 'Unknown'; ?><!--</strong></td>-->
<!--                <td style="min-width:260px;">-->
<!--                    --><?php //if ($matRes && mysqli_num_rows($matRes) > 0): ?>
<!--                    <table class="material-table" style="width:100%">-->
<!--                        <thead><tr>-->
<!--                            <th>Material</th>-->
<!--                            <th>Used (order)</th>-->
<!--                            <th>Phys. Stock</th>-->
<!--                            <th>Avail.</th>-->
<!--                            <th>Unit</th>-->
<!--                        </tr></thead>-->
<!--                        <tbody>-->
<!--                        --><?php //while ($mr = mysqli_fetch_assoc($matRes)):
//                            $usedInOrder = (int)$mr['materialRequiredQty'] * $qty;
//                        ?>
<!--                        <tr>-->
<!--                            <td>--><?php //echo htmlspecialchars($mr['materialName']); ?><!--</td>-->
<!--                            <td>--><?php //echo $usedInOrder; ?><!--</td>-->
<!--                            <td>--><?php //echo $mr['materialPhysicalQty']; ?><!--</td>-->
<!--                            <td>--><?php //echo $mr['availableStock']; ?><!--</td>-->
<!--                            <td>--><?php //echo htmlspecialchars($mr['materialUnit']); ?><!--</td>-->
<!--                        </tr>-->
<!--                        --><?php //endwhile; ?>
<!--                        </tbody>-->
<!--                    </table>-->
<!--                    --><?php //else: ?>
<!--                        <span style="color:#999;font-size:12px;">No BOM data</span>-->
<!--                    --><?php //endif; ?>
<!--                </td>-->
<!--                <td style="min-width:180px;">-->
<!--                    <form action="admin.php" method="post">-->
<!--                        <input type="hidden" name="orderID"    value="--><?php //echo $o['orderID']; ?><!--">-->
<!--                        <input type="hidden" name="furnitureID" value="--><?php //echo $fid; ?><!--">-->
<!--                        <div style="margin-bottom:6px;">-->
<!--                            <label style="font-size:12px;display:block;margin-bottom:3px;">Qty</label>-->
<!--                            <input type="number" name="new_qty" value="--><?php //echo $qty; ?><!--" min="1"-->
<!--                                   style="width:70px;padding:4px 6px;border:1px solid #ddd;border-radius:4px;">-->
<!--                        </div>-->
<!--                        <div style="margin-bottom:8px;">-->
<!--                            <label style="font-size:12px;display:block;margin-bottom:3px;">Status</label>-->
<!--                            <select name="new_status" class="status-select">-->
<!--                                --><?php //foreach ($statusMap as $val => $text): ?>
<!--                                <option value="--><?php //echo $val; ?><!--" --><?php //if ($statusInt == $val) echo 'selected'; ?><!-->-->
<!--                                    --><?php //echo $text; ?>
<!--                                </option>-->
<!--                                --><?php //endforeach; ?>
<!--                            </select>-->
<!--                        </div>-->
<!--                        <button type="submit" name="update_order" class="btn-update">Update</button>-->
<!--                    </form>-->
<!--                </td>-->
<!--            </tr>-->
<!--            --><?php //endwhile; ?>
<!--        --><?php //else: ?>
<!--            <tr><td colspan="13">No orders found.</td></tr>-->
<!--        --><?php //endif; ?>
<!--        </tbody>-->
<!--    </table>-->
<!--</div>-->
<!--</body>-->
<!--</html>-->

<?php
session_start();
require_once('3_Connections/DB_Configuration.php');
if (!isset($conn) && isset($GLOBALS['conn'])) $conn = $GLOBALS['conn'];

if (!isset($_SESSION['staffID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit();
}

/* ============================================================
   API 模式：POST application/json → 更新订单 → 返回 JSON
   ============================================================ */
$ct = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($ct, 'application/json') !== false) {
    header('Content-Type: application/json');

    $body      = json_decode(file_get_contents('php://input'), true);
    $oid       = isset($body['orderID'])    ? (int)$body['orderID']    : 0;
    $newStatus = isset($body['new_status']) ? (int)$body['new_status'] : 0;
    $newQty    = isset($body['new_qty'])    ? (int)$body['new_qty']    : 0;
    $fid       = isset($body['furnitureID'])? (int)$body['furnitureID']: 0;

    if ($oid <= 0 || $newStatus <= 0 || $newQty <= 0 || $fid <= 0) {
        echo json_encode(['ok'=>false,'msg'=>'Invalid parameters.']); exit();
    }

    // 读旧数量
    $oldRes = mysqli_query($conn, "SELECT orderQty FROM OrderFurniture WHERE orderID=$oid AND furnitureID=$fid");
    $oldRow = $oldRes ? mysqli_fetch_assoc($oldRes) : null;
    $oldQty = $oldRow ? (int)$oldRow['orderQty'] : 0;

    mysqli_query($conn, "START TRANSACTION");
    $dbError = false;

    // 数量有变动 → 调整物料库存 & OrderFurniture
    if ($newQty !== $oldQty) {
        $delta  = $newQty - $oldQty;
        $matRes = mysqli_query($conn, "SELECT materialID, materialRequiredQty FROM FurnitureMaterial WHERE furnitureID=$fid");
//        if ($matRes) {
//            while ($mr = mysqli_fetch_assoc($matRes)) {
//                $adj = (int)$mr['materialRequiredQty'] * $delta;
//                if (!mysqli_query($conn, "UPDATE Material SET materialPhysicalQty=materialPhysicalQty-$adj WHERE materialID={(int)$mr['materialID']}"))
//                    $dbError = true;
//            }
//        }

        if ($matRes) {
            while ($mr = mysqli_fetch_assoc($matRes)) {
                $adj = (int)$mr['materialRequiredQty'] * $delta;

                // 💡 做法：在外面先轉成整數，確保安全
                $materialID = (int)$mr['materialID'];

                // 這樣 SQL 字串就非常乾淨，不會再引發 PHP 語法解析錯誤
                if (!mysqli_query($conn, "UPDATE Material SET materialPhysicalQty=materialPhysicalQty-$adj WHERE materialID=$materialID")) {
                    $dbError = true;
                }
            }
        }


        // 重算总额
        $pRes  = mysqli_query($conn, "SELECT furniturePrice FROM Furniture WHERE furnitureID=$fid");
        $pRow  = mysqli_fetch_assoc($pRes);
        $total = (float)$pRow['furniturePrice'] * $newQty;

        if (!mysqli_query($conn, "UPDATE OrderFurniture SET orderQty=$newQty WHERE orderID=$oid AND furnitureID=$fid")) $dbError=true;
        if (!mysqli_query($conn, "UPDATE 'Order' SET orderTotalAmount=$total WHERE orderID=$oid")) $dbError=true;
    }

    // 更新状态
    if (!mysqli_query($conn, "UPDATE 'Order' SET orderStatu=$newStatus WHERE orderID=$oid")) $dbError=true;

    if (!$dbError) {
        mysqli_query($conn, "COMMIT");
        // 重新读最新总额返回给前端
        $newTotalRes = mysqli_query($conn, "SELECT orderTotalAmount FROM 'Order' WHERE orderID=$oid");
        $newTotalRow = mysqli_fetch_assoc($newTotalRes);
        echo json_encode([
                'ok'           => true,
                'msg'          => "Order #$oid updated.",
                'orderID'      => $oid,
                'newQty'       => $newQty,
                'newStatus'    => $newStatus,
                'newTotal'     => number_format((float)$newTotalRow['orderTotalAmount'], 2)
        ]);
    } else {
        mysqli_query($conn, "ROLLBACK");
        echo json_encode(['ok'=>false,'msg'=>'DB error: '.mysqli_error($conn)]);
    }
    exit();
}

/* ============================================================
   页面模式：查询所有订单，渲染表格
   ============================================================ */
$sql = "SELECT o.orderID, o.orderDate, o.orderTotalAmount, o.orderDeliveryAddress, o.orderDeliveryDate,
               o.orderStatu,
               c.fullName, c.customerNumber,
               f.furnitureID, f.furnitureName, f.furnitureImage, f.furniturePrice,
               of.orderQty
        FROM \`Order\` o
        JOIN Customer c  ON o.customerID   = c.customerID
        JOIN OrderFurniture of ON o.orderID = of.orderID
        JOIN Furniture f ON of.furnitureID  = f.furnitureID
        ORDER BY o.orderDate DESC";
$orders    = mysqli_query($conn, $sql);
$statusMap = [1=>'Pending',2=>'Processing',3=>'Delivering',4=>'Completed',5=>'Cancelled'];
$staffName = isset($_SESSION['staffName']) ? $_SESSION['staffName'] : 'Staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Furniture System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<nav>
    <div class="container nav-inner">
        <div class="logo">Furniture System (Staff)</div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="admin.php">Orders</a></li>
            <li><a href="admin_furniture.php">Furniture</a></li>
            <li><a href="admin_material.php">Materials</a></li>
            <li><a href="admin_report.php">Report</a></li>
            <li><a href="logout.php">Logout (<?php echo htmlspecialchars($staffName); ?>)</a></li>
        </ul>
    </div>
</nav>

<div class="admin-container">
    <h2 class="section-title">Order Management</h2>
    <div class="admin-tabs">
        <a href="admin.php" class="active">Orders</a>
        <a href="admin_furniture.php">Furniture</a>
        <a href="admin_material.php">Materials</a>
        <a href="admin_report.php">Report</a>
    </div>

    <div id="admin-msg"></div>

    <table>
        <thead><tr>
            <th>Order ID</th><th>Order Date</th><th>Customer</th><th>Contact</th>
            <th>Furniture</th><th>Image</th><th>Qty</th><th>Total ($)</th>
            <th>Delivery Address</th><th>Delivery Date</th><th>Status</th>
            <th>Materials Used</th><th>Update</th>
        </tr></thead>
        <tbody>
        <?php if ($orders && mysqli_num_rows($orders) > 0):
            while ($o = mysqli_fetch_assoc($orders)):
                $fid       = (int)$o['furnitureID'];
                $statusInt = (int)$o['orderStatu'];
                $qty       = (int)$o['orderQty'];
                $matRes    = mysqli_query($conn,
                        "SELECT m.materialName, m.materialUnit, m.materialPhysicalQty,
                            fm.materialRequiredQty,
                            FLOOR(m.materialPhysicalQty/fm.materialRequiredQty) AS availableStock
                     FROM FurnitureMaterial fm
                     JOIN Material m ON fm.materialID=m.materialID
                     WHERE fm.furnitureID=$fid");
                ?>
                <tr data-order-id="<?php echo $o['orderID']; ?>">
                    <td><?php echo $o['orderID']; ?></td>
                    <td><?php echo $o['orderDate']; ?></td>
                    <td><?php echo htmlspecialchars($o['fullName']); ?></td>
                    <td><?php echo htmlspecialchars($o['customerNumber']); ?></td>
                    <td><?php echo htmlspecialchars($o['furnitureName']); ?><br>
                        <small style="color:#999;">ID: <?php echo $fid; ?></small></td>
                    <td>
                        <?php $img = '1_Resources/furntiure_images/'.($o['furnitureImage']?$o['furnitureImage']:'default.png'); ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" class="table-img"
                             onerror="this.style.display='none'" alt="">
                    </td>
                    <td class="cell-qty"><?php echo $qty; ?></td>
                    <td class="cell-total">$<?php echo number_format($o['orderTotalAmount'],2); ?></td>
                    <td><?php echo htmlspecialchars($o['orderDeliveryAddress']); ?></td>
                    <td><?php echo $o['orderDeliveryDate']; ?></td>
<!--                    7.0-->
<!--                    <td class="cell-status"><strong>--><?php //echo $statusMap[$statusInt]??'Unknown'; ?><!--</strong></td>-->
                    <td class="cell-status"><strong><?php echo isset($statusMap[$statusInt]) ? $statusMap[$statusInt] : 'Unknown'; ?></strong></td>

                    <td style="min-width:260px;">
                        <?php if ($matRes && mysqli_num_rows($matRes)>0): ?>
                            <table class="material-table" style="width:100%">
                                <thead><tr><th>Material</th><th>Used</th><th>Stock</th><th>Avail.</th><th>Unit</th></tr></thead>
                                <tbody>
                                <?php while ($mr=mysqli_fetch_assoc($matRes)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mr['materialName']); ?></td>
                                        <td><?php echo (int)$mr['materialRequiredQty']*$qty; ?></td>
                                        <td><?php echo $mr['materialPhysicalQty']; ?></td>
                                        <td><?php echo $mr['availableStock']; ?></td>
                                        <td><?php echo htmlspecialchars($mr['materialUnit']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        <?php else: ?><span style="color:#999;font-size:12px;">No BOM</span><?php endif; ?>
                    </td>
                    <td style="min-width:180px;">
                        <div style="margin-bottom:6px;">
                            <label style="font-size:12px;display:block;margin-bottom:3px;">Qty</label>
                            <input type="number" class="input-qty" value="<?php echo $qty; ?>" min="1"
                                   data-order-id="<?php echo $o['orderID']; ?>"
                                   data-furniture-id="<?php echo $fid; ?>"
                                   style="width:70px;padding:4px 6px;border:1px solid #ddd;border-radius:4px;">
                        </div>
                        <div style="margin-bottom:8px;">
                            <label style="font-size:12px;display:block;margin-bottom:3px;">Status</label>
                            <select class="select-status status-select"
                                    data-order-id="<?php echo $o['orderID']; ?>"
                                    data-furniture-id="<?php echo $fid; ?>">
                                <?php foreach ($statusMap as $val=>$text): ?>
                                    <option value="<?php echo $val; ?>" <?php if($statusInt==$val)echo 'selected'; ?>>
                                        <?php echo $text; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn-update update-btn"
                                data-order-id="<?php echo $o['orderID']; ?>"
                                data-furniture-id="<?php echo $fid; ?>">Update</button>
                    </td>
                </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="13">No orders found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="js/admin_orders.js"></script>
</body>
</html>
