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

$message = "";

// ── 处理新增物料 POST ──
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_material'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['materialName']));
    $qty  = (int)$_POST['materialPhysicalQty'];
    $unit = mysqli_real_escape_string($conn, trim($_POST['materialUnit']));

    if ($name === "" || $unit === "") {
        $message = "<div class='alert alert-warning'>Material Name and Unit are required.</div>";
    } elseif ($qty < 0) {
        $message = "<div class='alert alert-warning'>Physical Quantity cannot be negative.</div>";
    } else {
        $sql = "INSERT INTO Material (materialName, materialPhysicalQty, materialUnit)
                VALUES ('$name', $qty, '$unit')";
        if (mysqli_query($conn, $sql)) {
            $newID   = mysqli_insert_id($conn);
            $message = "<div class='alert alert-success'>Material added successfully. (Material ID: $newID)</div>";
        } else {
            $message = "<div class='alert alert-error'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// ── 读取现有物料列表 ──
$materials = mysqli_query($conn, "SELECT * FROM Material ORDER BY materialID DESC");
$staffName = isset($_SESSION['staffName']) ? $_SESSION['staffName'] : 'Staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Materials - Furniture System</title>
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
    <h2 class="section-title">Material Management</h2>

    <div class="admin-tabs">
        <a href="admin.php">Orders</a>
        <a href="admin_furniture.php">Furniture</a>
        <a href="admin_material.php" class="active">Materials</a>
        <a href="admin_report.php">Report</a>
    </div>

    <!-- Add Material Form -->
    <div class="form-box" style="margin-bottom:32px;">
        <h3 class="form-title">Add New Material</h3>
        <?php echo $message; ?>
        <form action="admin_material.php" method="post">
            <div class="form-group">
                <label>Material Name <span style="color:red">*</span></label>
                <input type="text" class="form-control" name="materialName" required placeholder="e.g. Oak Wood Plank">
            </div>
            <div class="form-group">
                <label>Physical Quantity <span style="color:red">*</span></label>
                <input type="number" class="form-control" name="materialPhysicalQty" min="0" value="0" required>
            </div>
            <div class="form-group">
                <label>Unit <span style="color:red">*</span></label>
                <input type="text" class="form-control" name="materialUnit" required placeholder="e.g. pcs / meter / block">
            </div>
            <button type="submit" name="add_material" class="btn-submit">Add Material</button>
        </form>
    </div>

    <!-- Existing Materials Table -->
    <h3 style="margin-bottom:8px;color:var(--primary);">Current Material Inventory</h3>
    <table>
        <thead>
        <tr>
            <th>Material ID</th>
            <th>Material Name</th>
            <th>Physical Quantity</th>
            <th>Unit</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($materials && mysqli_num_rows($materials) > 0): ?>
            <?php while ($m = mysqli_fetch_assoc($materials)): ?>
            <tr>
                <td><?php echo $m['materialID']; ?></td>
                <td><?php echo htmlspecialchars($m['materialName']); ?></td>
                <td><?php echo $m['materialPhysicalQty']; ?></td>
                <td><?php echo htmlspecialchars($m['materialUnit']); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No materials found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
