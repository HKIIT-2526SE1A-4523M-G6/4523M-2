<?php
session_start();
require_once('3_Connections/DB_Configuration.php');

if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}

$isCustomer = isset($_SESSION['customerID']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
$isAdmin    = isset($_SESSION['staffID'])    && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

/* ============================================================
   API 模式：POST application/json → 整车下单 → 返回 JSON
   ============================================================ */
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($contentType, 'application/json') !== false) {
    header('Content-Type: application/json');

    if (!$isCustomer) {
        echo json_encode(['ok' => false, 'msg' => 'Please login as a customer first.']);
        exit();
    }

    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body || empty($body['items']) || empty($body['address']) || empty($body['deliveryDate'])) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid request payload.']);
        exit();
    }

    $items        = $body['items'];          // [{furnitureID, qty}, ...]
    $address      = mysqli_real_escape_string($conn, trim($body['address']));
    $deliveryDate = mysqli_real_escape_string($conn, trim($body['deliveryDate']));
    $cid          = (int)$_SESSION['customerID'];

    // ── 1. 校验每件家具存在并取单价 ──
    $furnitureMap = [];   // furnitureID => ['price'=>..., 'name'=>...]
    foreach ($items as $item) {
        $fid = (int)$item['furnitureID'];
        $qty = (int)$item['qty'];
        if ($fid <= 0 || $qty <= 0) {
            echo json_encode(['ok' => false, 'msg' => "Invalid item data (furnitureID=$fid, qty=$qty)."]);
            exit();
        }
        $res = mysqli_query($conn, "SELECT furnitureName, furniturePrice FROM Furniture WHERE furnitureID = $fid");
        if (!$res || mysqli_num_rows($res) === 0) {
            echo json_encode(['ok' => false, 'msg' => "Furniture #$fid not found."]);
            exit();
        }
        $row = mysqli_fetch_assoc($res);
        $furnitureMap[$fid] = ['price' => (float)$row['furniturePrice'], 'name' => $row['furnitureName'], 'qty' => $qty];
    }

    // ── 2. 汇总物料需求（关键：先跨商品合并，再对比库存）──
    // materialUsage[materialID] => total units needed
    $materialUsage = [];
    foreach ($furnitureMap as $fid => $info) {
        $qty    = $info['qty'];
        $matRes = mysqli_query($conn, "SELECT materialID, materialRequiredQty FROM FurnitureMaterial WHERE furnitureID = $fid");
        if ($matRes) {
            while ($mr = mysqli_fetch_assoc($matRes)) {
                $mid  = (int)$mr['materialID'];
                $need = (int)$mr['materialRequiredQty'] * $qty;
                $materialUsage[$mid] = isset($materialUsage[$mid]) ? $materialUsage[$mid] + $need : $need;
            }
        }
    }

    // ── 3. 逐一检查汇总后的物料库存 ──
    foreach ($materialUsage as $mid => $totalNeed) {
        $stRes = mysqli_query($conn, "SELECT materialName, materialPhysicalQty FROM Material WHERE materialID = $mid");
        if (!$stRes || mysqli_num_rows($stRes) === 0) {
            echo json_encode(['ok' => false, 'msg' => "Material #$mid not found."]);
            exit();
        }
        $stRow = mysqli_fetch_assoc($stRes);
        if ((int)$stRow['materialPhysicalQty'] < $totalNeed) {
            echo json_encode([
                'ok'  => false,
                'msg' => "Not enough stock for material [{$stRow['materialName']}]. Need: $totalNeed, Available: {$stRow['materialPhysicalQty']}."
            ]);
            exit();
        }
    }

    // ── 4. 开事务，落库 ──
    mysqli_query($conn, "START TRANSACTION");
    $dbError = false;

    // 4-a. 扣减物料库存
    foreach ($materialUsage as $mid => $totalNeed) {
        if (!mysqli_query($conn, "UPDATE Material SET materialPhysicalQty = materialPhysicalQty - $totalNeed WHERE materialID = $mid")) {
            $dbError = true;
        }
    }

    // 4-b. 计算订单总额
    $totalAmount = 0;
    foreach ($furnitureMap as $info) {
        $totalAmount += $info['price'] * $info['qty'];
    }

    // 4-c. 插入主订单
    $newOrderID = 0;
    if (!$dbError) {
        $insOrder = "INSERT INTO customerOrder (orderDate, orderTotalAmount, customerID, orderDeliveryDate, orderDeliveryAddress, orderStatu)
                     VALUES (CURRENT_TIMESTAMP, $totalAmount, $cid, '$deliveryDate', '$address', 1)";
        if (mysqli_query($conn, $insOrder)) {
            $newOrderID = mysqli_insert_id($conn);
        } else {
            $dbError = true;
        }
    }

    // 4-d. 插入 OrderFurniture 明细（每件家具一行）
    if (!$dbError && $newOrderID > 0) {
        foreach ($furnitureMap as $fid => $info) {
            $qty = $info['qty'];
            if (!mysqli_query($conn, "INSERT INTO OrderFurniture (orderID, furnitureID, orderQty) VALUES ($newOrderID, $fid, $qty)")) {
                $dbError = true;
            }
        }
    }

    if (!$dbError) {
        mysqli_query($conn, "COMMIT");
        echo json_encode(['ok' => true, 'msg' => 'Order placed successfully!', 'orderID' => $newOrderID]);
    } else {
        mysqli_query($conn, "ROLLBACK");
        echo json_encode(['ok' => false, 'msg' => 'Database error: ' . mysqli_error($conn)]);
    }
    exit();
}

/* ============================================================
   页面模式：GET → 输出 HTML 骨架
   购物车列表、下单表单全部由 order.js 渲染
   ============================================================ */

// 把登录态注入给 JS（避免 JS 自己读 localStorage 判断）
$sessionData = [
    'isCustomer' => $isCustomer,
    'isAdmin'    => $isAdmin,
    'fullName'   => isset($_SESSION['fullName'])  ? $_SESSION['fullName']  : '',
    'staffName'  => isset($_SESSION['staffName']) ? $_SESSION['staffName'] : '',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - Furniture System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <nav>
        <div class="container nav-inner">
            <div class="logo">Furniture System</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="admin.php">Admin Panel</a></li>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['staffName']); ?>)</a></li>
                <?php elseif ($isCustomer): ?>
                    <li><a href="order.php">Cart</a></li>
                    <li><a href="order_history.php">My Orders</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['fullName']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="order.php">Cart</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <section class="section" id="order">
            <h2 class="section-title">Place New Order</h2>
            <!-- 🎯 JS 挂载点：order.js 负责填充以下容器的全部内容 -->
            <div id="order-app"></div>
        </section>
    </div>

    <!-- 把 PHP session 状态注入给 JS，order.js 读取后决定渲染逻辑 -->
    <script>
        const ORDER_SESSION = <?php echo json_encode($sessionData); ?>;
    </script>
    <script src="js/order.js"></script>
</body>

</html>