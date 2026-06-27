<?php
session_start();
require_once('3_Connections/DB_Configuration.php');

// 双重保险：确保 $conn 变量在老版本 PHP 作用域存在
if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}

/* * 【核心算法重构：计算多对多物料下的最大可用库存】
 * 对于每件家具，计算其所有原材料的 (materialPhysicalQty / materialRequiredQty)，并取最小值作为该家具的当前可用库存。
 * 同时对齐最新字段：furniturePrice, furnitureDescription, furnitureImage
 */

$sql = "SELECT f.furnitureID, f.furnitureSKU, f.furnitureName, f.furnitureModel,
               f.furnitureDescription, f.furniturePrice, f.furnitureImage,
               IFNULL(MIN(FLOOR(m.materialPhysicalQty / fm.materialRequiredQty)), 0) AS availableStock
        FROM Furniture f
        LEFT JOIN FurnitureMaterial fm ON f.furnitureID = fm.furnitureID
        LEFT JOIN Material m ON fm.materialID = m.materialID
        GROUP BY f.furnitureID";

$result = mysqli_query($conn, $sql);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furniture Sales Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- 🔝 Navigation -->
    <nav>
        <div class="container nav-inner">
            <div class="logo">Furniture System</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li><a href="admin.php">Admin Panel</a></li>
                    <?php else: ?>
                        <li><a href="order.php">Cart</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars(isset($_SESSION['fullName']) ? $_SESSION['fullName'] : $_SESSION['staffName']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="order.php">Cart</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- 🌟 Hero Section -->
        <section class="hero" id="home">
            <h1>Furniture Sales & Order Management System</h1>
            <p>Browse furniture, customer register & login, place orders, admin manage orders & inventory</p>
        </section>

        <!-- 🪑 Furniture List -->
        <section class="section" id="furniture">
            <h2 class="section-title">Furniture List</h2>
            <!-- 对齐原 HTML 挂载点 -->
            <div id="product-grid" class="furniture-grid">

            </div>
        </section>

        <!-- 🛒 Shopping Cart Section (对齐原 HTML 挂载结构与前端 JS 逻辑) -->
        <section class="section" id="cart">
            <h2 class="section-title">Shopping Cart</h2>
            <!-- 原 js/cart.js 动态填充的容器挂载点 -->
            <div class="furniture-grid cart-grid" id="cart-container">
                <!-- 这里的内部结构会自动由你的旧版 js/cart.js 渲染补全 -->
            </div>
        </section>

        <?php
        // 将数据库数据映射为前端 JS 兼容格式
        $products_for_js = [];
        mysqli_data_seek($result, 0); // 重置结果集指针
        while ($row = mysqli_fetch_assoc($result)) {
            // 查该家具的所有 options
            $fid = (int)$row['furnitureID'];
            $opt_res = mysqli_query($conn, "SELECT optionColor, optionMaterial FROM FurnitureOption WHERE furnitureID = $fid");
            $options = [];
            while ($opt = mysqli_fetch_assoc($opt_res)) {
                $options[] = ['color' => $opt['optionColor'], 'material' => $opt['optionMaterial']];
            }


            $products_for_js[] = [
                'sku'   => isset($row['furnitureSKU']) ? $row['furnitureSKU'] : 'FP-' . str_pad($fid, 3, '0', STR_PAD_LEFT),
                'furnitureID'    => $fid,
                'name'           => $row['furnitureName'],
                'model' => isset($row['furnitureModel']) ? $row['furnitureModel'] : $row['furnitureDescription'],
                'price'          => (float)$row['furniturePrice'],
                'image'          => '1_Resources/furntiure_images/' . (!empty($row['furnitureImage']) ? $row['furnitureImage'] : 'default.png'),
                'availableStock' => (int)$row['availableStock'],
                'options'        => $options
            ];
        }
        ?>

        <script>
            const DB_PRODUCTS = <?php echo json_encode($products_for_js, JSON_UNESCAPED_UNICODE); ?>;
        </script>

        <!-- 📜 重新引入并对齐原有的前端交互脚本 -->
        <script src="js/cart.js"></script>
        <script src="js/product.js"></script>
        <script src="js/main.js"></script>
    </div>

</body>

</html>