<?php
session_start();
require_once('3_Connections/DB_Configuration.php');

if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}

// 门禁：仅 admin
if (!isset($_SESSION['staffID']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/*
 * 报表查询：按家具汇总
 * - 各品项累计订购数量  (SUM orderQty)
 * - 各品项累计销售额    (SUM orderQty × furniturePrice)
 */
$sql = "SELECT f.furnitureID,
               f.furnitureName,
               f.furnitureImage,
               f.furniturePrice,
               IFNULL(SUM(of.orderQty), 0)                        AS totalQty,
               IFNULL(SUM(of.orderQty * f.furniturePrice), 0.00)  AS totalSales
        FROM Furniture f
        LEFT JOIN OrderFurniture of ON f.furnitureID = of.furnitureID
        GROUP BY f.furnitureID
        ORDER BY totalSales DESC";

$report = mysqli_query($conn, $sql);

// 全局汇总
$grandQty   = 0;
$grandSales = 0.0;
$rows       = array();
if ($report) {
    while ($r = mysqli_fetch_assoc($report)) {
        $grandQty   += (int)$r['totalQty'];
        $grandSales += (float)$r['totalSales'];
        $rows[]      = $r;
    }
}

$staffName = isset($_SESSION['staffName']) ? $_SESSION['staffName'] : 'Staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - Furniture System</title>
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
    <h2 class="section-title">Sales Report</h2>

    <div class="admin-tabs">
        <a href="admin.php">Orders</a>
        <a href="admin_furniture.php">Furniture</a>
        <a href="admin_material.php">Materials</a>
        <a href="admin_report.php" class="active">Report</a>
    </div>

    <!-- Summary Bar -->
    <div style="display:flex;gap:24px;margin-bottom:28px;flex-wrap:wrap;">
        <div class="form-box" style="flex:1;min-width:200px;text-align:center;padding:20px;">
            <div style="font-size:13px;color:#666;margin-bottom:6px;">Total Items Ordered</div>
            <div style="font-size:28px;font-weight:700;color:var(--primary);"><?php echo number_format($grandQty); ?></div>
        </div>
        <div class="form-box" style="flex:1;min-width:200px;text-align:center;padding:20px;">
            <div style="font-size:13px;color:#666;margin-bottom:6px;">Total Sales Revenue</div>
            <div style="font-size:28px;font-weight:700;color:var(--secondary);">$<?php echo number_format($grandSales, 2); ?></div>
        </div>
        <div class="form-box" style="flex:1;min-width:200px;text-align:center;padding:20px;">
            <div style="font-size:13px;color:#666;margin-bottom:6px;">Total Furniture Types</div>
            <div style="font-size:28px;font-weight:700;color:var(--primary);"><?php echo count($rows); ?></div>
        </div>
    </div>

    <!-- Card Grid View -->
    <h3 style="color:var(--primary);margin-bottom:16px;">Sales by Furniture Item</h3>
    <div class="report-grid">
        <?php foreach ($rows as $r): ?>
        <div class="report-card">
            <?php $imgPath = '1_Resources/furntiure_images/' . ($r['furnitureImage'] ? $r['furnitureImage'] : 'default.png'); ?>
            <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="<?php echo htmlspecialchars($r['furnitureName']); ?>"
                 class="report-card-img" onerror="this.src='https://placehold.co/400x140?text=No+Image'">
            <div class="report-card-body">
                <div class="report-card-title"><?php echo htmlspecialchars($r['furnitureName']); ?></div>
                <div class="report-stat">Furniture ID: <span><?php echo $r['furnitureID']; ?></span></div>
                <div class="report-stat">Unit Price: <span>$<?php echo number_format($r['furniturePrice'], 2); ?></span></div>
                <div class="report-stat">Total Qty Ordered: <span><?php echo number_format($r['totalQty']); ?></span></div>
                <div class="report-total">$<?php echo number_format($r['totalSales'], 2); ?></div>
                <div style="font-size:11px;color:#999;margin-top:2px;">Total Sales Amount</div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
        <p style="color:#999;">No data available.</p>
        <?php endif; ?>
    </div>

    <!-- Detail Table View -->
    <h3 style="color:var(--primary);margin:32px 0 8px;">Detail Table</h3>
    <table>
        <thead>
        <tr>
            <th>Furniture ID</th>
            <th>Image</th>
            <th>Furniture Name</th>
            <th>Unit Price</th>
            <th>Total Qty Ordered</th>
            <th>Total Sales Amount ($)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td><?php echo $r['furnitureID']; ?></td>
            <td>
                <img src="<?php echo htmlspecialchars('1_Resources/furntiure_images/' . ($r['furnitureImage'] ? $r['furnitureImage'] : 'default.png')); ?>"
                     class="table-img" onerror="this.style.display='none'" alt="">
            </td>
            <td><?php echo htmlspecialchars($r['furnitureName']); ?></td>
            <td>$<?php echo number_format($r['furniturePrice'], 2); ?></td>
            <td><?php echo number_format($r['totalQty']); ?></td>
            <td><strong>$<?php echo number_format($r['totalSales'], 2); ?></strong></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
        <tr><td colspan="6">No report data available.</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot>
        <tr style="background:#f0f0f0;font-weight:700;">
            <td colspan="4" style="text-align:right;">Grand Total</td>
            <td><?php echo number_format($grandQty); ?></td>
            <td>$<?php echo number_format($grandSales, 2); ?></td>
        </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
