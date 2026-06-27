<?php
session_start();
require_once('3_Connections/DB_Configuration.php');

// 双重保险：确保 $conn 变量在老版本 PHP 作用域存在
if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}

// 门禁安全检查：只有客户可以下单
//if (!isset($_SESSION['customerID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
//    header("Location: login.php");
//    exit();
//}
$isCustomer = isset($_SESSION['customerID']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
$isAdmin    = isset($_SESSION['staffID'])    && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $isCustomer) {
    $fid = (int)$_POST['fid'];
    $qty = (int)$_POST['oqty'];
    $address = mysqli_real_escape_string($conn, $_POST['odeliveraddress']);
    $date = mysqli_real_escape_string($conn, $_POST['odeliverydate']);
    $cid = (int)$_SESSION['customerID'];

    // 1. 获取商品单价
    $f_sql = "SELECT furniturePrice FROM Furniture WHERE furnitureID = $fid";
    $f_res = mysqli_query($conn, $f_sql);

    if ($f_res && mysqli_num_rows($f_res) > 0) {
        $f_row = mysqli_fetch_assoc($f_res);
        $price = (float)$f_row['furniturePrice'];
        $totalAmount = $price * $qty;

        // 2. 【多对多物料核检】：查询该家具需要的所有原材料及当前库存
        $mat_sql = "SELECT fm.materialID, fm.materialRequiredQty, m.materialName, m.materialPhysicalQty 
                    FROM FurnitureMaterial fm
                    JOIN Material m ON fm.materialID = m.materialID
                    WHERE fm.furnitureID = $fid";
        $mat_res = mysqli_query($conn, $mat_sql);

        $stock_ok = true;
        $materials_to_update = array();

        if ($mat_res) {
            while ($mat_row = mysqli_fetch_assoc($mat_res)) {
                $required_total = (int)$mat_row['materialRequiredQty'] * $qty;
                $current_stock = (int)$mat_row['materialPhysicalQty'];

                if ($current_stock < $required_total) {
                    $stock_ok = false;
                    $message = "<div style='color:red; text-align:center; margin-bottom:15px; font-weight:bold;'>Failed: Not enough [" . htmlspecialchars($mat_row['materialName']) . "] stock available. (Need: $required_total, Left: $current_stock)</div>";
                    break;
                }
                // 暂存需要更新的物料数据
                $materials_to_update[] = array(
                    'id' => $mat_row['materialID'],
                    'deduct' => $required_total
                );
            }
        }

        // 3. 库存储备通过，执行数据扣减与入库（启动事务确保安全）
        if ($stock_ok) {
            mysqli_query($conn, "START TRANSACTION");
            $db_error = false;

            // 扣减每一种所需的原材料库存
            foreach ($materials_to_update as $mat) {
                $m_id = $mat['id'];
                $deduct_qty = $mat['deduct'];
                $update_sql = "UPDATE Material SET materialPhysicalQty = materialPhysicalQty - $deduct_qty WHERE materialID = $m_id";
                if (!mysqli_query($conn, $update_sql)) {
                    $db_error = true;
                }
            }

            // 插入主订单表 `Order` (默认状态: 1 Pending)
            $insertOrder = "INSERT INTO `Order` (orderDate, orderTotalAmount, customerID, orderDeliveryDate, orderDeliveryAddress, orderStatu) 
                            VALUES (CURRENT_TIMESTAMP, $totalAmount, $cid, '$date', '$address', 1)";

            if (mysqli_query($conn, $insertOrder)) {
                $new_order_id = mysqli_insert_id($conn);

                // 插入订单明细表 `OrderFurniture`
                $insertDetail = "INSERT INTO OrderFurniture (orderID, furnitureID, orderQty) 
                                 VALUES ($new_order_id, $fid, $qty)";
                if (!mysqli_query($conn, $insertDetail)) {
                    $db_error = true;
                }
            } else {
                $db_error = true;
            }

            // 事务提交或回滚
            if (!$db_error) {
                mysqli_query($conn, "COMMIT");
                header("Location: order.php?success=1");
                exit();
            } else {
                mysqli_query($conn, "ROLLBACK");
                $message = "<div style='color:red; text-align:center; margin-bottom:15px; font-weight:bold;'>System Error: Failed to process order transaction.</div>";
            }
        }
    } else {
        $message = "<div style='color:red; text-align:center; margin-bottom:15px; font-weight:bold;'>Error: Furniture item not found.</div>";
    }
}

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
    <!-- Navigation -->
    <nav>
        <div class="container nav-inner">
            <div class="logo">Furniture System</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <!--            --><?php //if(isset($_SESSION['role'])): 
                                    ?>
                <!--                --><?php //if($_SESSION['role'] == 'admin'): 
                                        ?>
                <!--                    <li><a href="admin.php">Admin Panel</a></li>-->
                <!--                --><?php //else: 
                                        ?>
                <!--                    <li><a href="order.php">Cart</a></li>-->
                <!--                --><?php //endif; 
                                        ?>
                <!--                <li><a href="logout.php">Logout (--><?php //echo htmlspecialchars(isset($_SESSION['fullName']) ? $_SESSION['fullName'] : $_SESSION['staffName']); 
                                                                        ?><!--)</a></li>-->
                <!--            --><?php //else: 
                                    ?>
                <!--                <li><a href="login.php">Login</a></li>-->
                <!--                <li><a href="register.php">Register</a></li>-->
                <!--                <li><a href="order.php">Cart</a></li>-->
                <!--            --><?php //endif; 
                                    ?>
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
            <?php if (!$isCustomer): ?>
                <div class="form-box" style="text-align:center;padding:40px;">
                    <p style="color:#666;margin-bottom:16px;">Please log in as a customer to place an order.</p>
                    <a href="login.php" class="card-btn" style="display:inline-block;width:auto;padding:10px 28px;">Login</a>
                </div>
            <?php else: ?>
                <div class="form-box">
                    <h3 class="form-title">Order Details</h3>

                    <?php
                    if (isset($_GET['success'])) {
                        echo "<div style='color:green; text-align:center; margin-bottom:15px; font-weight:bold;'>Order placed successfully! Material stock updated.</div>";
                    }
                    ?>
                    <?php echo $message; ?>

                    <form action="order.php" method="post">
                        <div class="form-group">
                            <label>Select Furniture</label>
                            <!-- 🎯 补齐原 order.php 拥有的 id="furniture-select" -->
                            <select class="form-control" name="fid" id="furniture-select" required>
                                <?php
                                // 复用 index.php 相同的可用库存查询
                                $selSql = "SELECT f.furnitureID, f.furnitureName, f.furniturePrice,
                                    IFNULL(MIN(FLOOR(m.materialPhysicalQty / fm.materialRequiredQty)), 0) AS availableStock
                                FROM Furniture f
                                LEFT JOIN FurnitureMaterial fm ON f.furnitureID = fm.furnitureID
                                LEFT JOIN Material m ON fm.materialID = m.materialID
                                GROUP BY f.furnitureID
                                HAVING availableStock > 0";
                                $selResult = mysqli_query($conn, $selSql);
                                while ($opt = mysqli_fetch_assoc($selResult)):
                                ?>
                                    <option value="<?php echo $opt['furnitureID']; ?>">
                                        <?php echo htmlspecialchars($opt['furnitureName']); ?>
                                        — $<?php echo number_format($opt['furniturePrice'], 2); ?>
                                        (Stock: <?php echo $opt['availableStock']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <!-- 🎯 补齐原 order.php 拥有的 id="order-qty" -->
                            <input type="number" class="form-control" name="oqty" id="order-qty" min="1" value="1" required>
                        </div>
                        <div class="form-group">
                            <label>Delivery Address</label>
                            <textarea class="form-control" name="odeliveraddress" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Delivery Date</label>
                            <input type="datetime-local" class="form-control" name="odeliverydate" required>
                        </div>
                        <button type="submit" class="btn-submit">Submit Order</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- 🎯 补全原生 JS 控制下拉联动逻辑 -->
    <script src="js/order.js"></script>
    <script src="js/main.js"></script>
</body>

</html>