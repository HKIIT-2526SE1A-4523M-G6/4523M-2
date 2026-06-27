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

// ── 处理新增家具 POST ──
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_furniture'])) {
    $fname = mysqli_real_escape_string($conn, trim($_POST['furnitureName']));
    $fmodel= mysqli_real_escape_string($conn, trim($_POST['furnitureModel']));
    $fdesc = mysqli_real_escape_string($conn, trim($_POST['furnitureDescription']));
    $fprice= (float)$_POST['furniturePrice'];
    $fimage= mysqli_real_escape_string($conn, trim($_POST['furnitureImage']));
    $fcat  = mysqli_real_escape_string($conn, trim($_POST['furnitureCategory']));

    $matIDs  = isset($_POST['mat_id'])  ? $_POST['mat_id']  : array();
    $matQtys = isset($_POST['mat_qty']) ? $_POST['mat_qty'] : array();

    if ($fname === "" || $fprice <= 0) {
        $message = "<div class='alert alert-warning'>Furniture Name and a valid Price are required.</div>";
    } elseif (empty($matIDs)) {
        $message = "<div class='alert alert-warning'>At least one material must be specified in the BOM.</div>";
    } else {
        mysqli_query($conn, "START TRANSACTION");
        $dbError = false;

        // 1. 插入家具主记录（SKU 自动生成）
        $skuSql = "SELECT COUNT(*) AS cnt FROM Furniture";
        $skuRes = mysqli_query($conn, $skuSql);
        $skuRow = mysqli_fetch_assoc($skuRes);
        $skuNum = str_pad((int)$skuRow['cnt'] + 1, 3, '0', STR_PAD_LEFT);
        $fsku   = "FP-$skuNum";

        $insF = "INSERT INTO Furniture (furnitureSKU, furnitureName, furnitureModel, furnitureDescription,
                                        furniturePrice, furnitureImage, furnitureCategory, furnitureStockStatus)
                 VALUES ('$fsku', '$fname', '$fmodel', '$fdesc', $fprice, '$fimage', '$fcat', 1)";
        if (!mysqli_query($conn, $insF)) { $dbError = true; }
        $newFID = mysqli_insert_id($conn);

        // 2. 插入 FurnitureMaterial BOM 行
        for ($i = 0; $i < count($matIDs); $i++) {
            $mid  = (int)$matIDs[$i];
            $mQty = max(1, (int)$matQtys[$i]);
            if ($mid <= 0) continue;
            $insBOM = "INSERT INTO FurnitureMaterial (furnitureID, materialID, materialRequiredQty)
                       VALUES ($newFID, $mid, $mQty)";
            if (!mysqli_query($conn, $insBOM)) { $dbError = true; }
        }

        if (!$dbError) {
            mysqli_query($conn, "COMMIT");
            $message = "<div class='alert alert-success'>Furniture added successfully. (Furniture ID: $newFID, SKU: $fsku)</div>";
        } else {
            mysqli_query($conn, "ROLLBACK");
            $message = "<div class='alert alert-error'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// ── 处理删除家具 POST ──
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_furniture'])) {
    $fid = (int)$_POST['furnitureID'];

    // 检查是否有关联订单
    $chk = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM OrderFurniture WHERE furnitureID = $fid");
    $chkRow = mysqli_fetch_assoc($chk);
    if ((int)$chkRow['cnt'] > 0) {
        $message = "<div class='alert alert-error'>Cannot delete: This furniture has existing orders and cannot be removed.</div>";
    } else {
        mysqli_query($conn, "START TRANSACTION");
        $dbError = false;
        if (!mysqli_query($conn, "DELETE FROM FurnitureOption WHERE furnitureID = $fid"))   { $dbError = true; }
        if (!mysqli_query($conn, "DELETE FROM FurnitureMaterial WHERE furnitureID = $fid")) { $dbError = true; }
        if (!mysqli_query($conn, "DELETE FROM Furniture WHERE furnitureID = $fid"))         { $dbError = true; }
        if (!$dbError) {
            mysqli_query($conn, "COMMIT");
            $message = "<div class='alert alert-success'>Furniture #$fid deleted successfully.</div>";
        } else {
            mysqli_query($conn, "ROLLBACK");
            $message = "<div class='alert alert-error'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// ── 读取物料列表（用于 BOM 下拉）──
$matList = mysqli_query($conn, "SELECT materialID, materialName, materialUnit FROM Material ORDER BY materialID");

// ── 读取现有家具列表 ──
$furnitures = mysqli_query($conn,
    "SELECT f.furnitureID, f.furnitureSKU, f.furnitureName, f.furniturePrice, f.furnitureImage,
            GROUP_CONCAT(CONCAT(m.materialName, ' x', fm.materialRequiredQty, ' ', m.materialUnit) SEPARATOR ', ') AS bom
     FROM Furniture f
     LEFT JOIN FurnitureMaterial fm ON f.furnitureID = fm.furnitureID
     LEFT JOIN Material m ON fm.materialID = m.materialID
     GROUP BY f.furnitureID
     ORDER BY f.furnitureID DESC");

// 构建物料下拉 options HTML（JS 需要用）
$matOptions = "";
if ($matList) {
    while ($m = mysqli_fetch_assoc($matList)) {
        $matOptions .= "<option value='{$m['materialID']}'>" . htmlspecialchars($m['materialName']) . " ({$m['materialUnit']})</option>";
    }
}

$staffName = isset($_SESSION['staffName']) ? $_SESSION['staffName'] : 'Staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Furniture - Furniture System</title>
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
    <h2 class="section-title">Furniture Management</h2>

    <div class="admin-tabs">
        <a href="admin.php">Orders</a>
        <a href="admin_furniture.php" class="active">Furniture</a>
        <a href="admin_material.php">Materials</a>
        <a href="admin_report.php">Report</a>
    </div>

    <!-- Add Furniture Form -->
    <div class="form-box" style="max-width:760px;margin-bottom:36px;">
        <h3 class="form-title">Add New Furniture</h3>
        <?php echo $message; ?>
        <form action="admin_furniture.php" method="post">
            <div class="form-group">
                <label>Furniture Name <span style="color:red">*</span></label>
                <input type="text" class="form-control" name="furnitureName" required>
            </div>
            <div class="form-group">
                <label>Model</label>
                <input type="text" class="form-control" name="furnitureModel" placeholder="e.g. DC-101">
            </div>
            <div class="form-group">
                <label>Description <span style="color:red">*</span></label>
                <textarea class="form-control" name="furnitureDescription" required></textarea>
            </div>
            <div class="form-group">
                <label>Price (per item) <span style="color:red">*</span></label>
                <input type="number" class="form-control" name="furniturePrice" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label>Image Filename <span style="color:#999;font-size:12px;">(e.g. 7.png — place file in 1_Resources/furntiure_images/)</span></label>
                <input type="text" class="form-control" name="furnitureImage" placeholder="7.png">
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" class="form-control" name="furnitureCategory" placeholder="e.g. Bedroom / Dining / Living Room">
            </div>

            <!-- BOM Section -->
            <div class="form-group">
                <label>Bill of Materials (BOM) <span style="color:red">*</span></label>
                <div id="bom-container">
                    <div class="bom-row">
                        <select name="mat_id[]" class="form-control" required>
                            <option value="">-- Select Material --</option>
                            <?php echo $matOptions; ?>
                        </select>
                        <input type="number" name="mat_qty[]" class="form-control" min="1" value="1" placeholder="Qty Required" required>
                        <button type="button" class="btn-remove-bom" onclick="removeBomRow(this)">✕</button>
                    </div>
                </div>
                <button type="button" id="add-bom-row">+ Add Material Row</button>
            </div>

            <button type="submit" name="add_furniture" class="btn-submit">Add Furniture</button>
        </form>
    </div>

    <!-- Furniture List -->
    <h3 style="margin-bottom:8px;color:var(--primary);">Current Furniture List</h3>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>SKU</th>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>BOM</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($furnitures && mysqli_num_rows($furnitures) > 0): ?>
            <?php while ($f = mysqli_fetch_assoc($furnitures)): ?>
            <tr>
                <td><?php echo $f['furnitureID']; ?></td>
                <td><?php echo htmlspecialchars($f['furnitureSKU']); ?></td>
                <td>
                    <?php $imgPath = '1_Resources/furntiure_images/' . ($f['furnitureImage'] ? $f['furnitureImage'] : 'default.png'); ?>
                    <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="" class="table-img"
                         onerror="this.style.display='none'">
                </td>
                <td><?php echo htmlspecialchars($f['furnitureName']); ?></td>
                <td>$<?php echo number_format($f['furniturePrice'], 2); ?></td>
                <td style="text-align:left;font-size:12px;"><?php echo htmlspecialchars($f['bom'] ? $f['bom'] : '—'); ?></td>
                <td>
                    <form class="delete-furniture-form" action="admin_furniture.php" method="post"
                          data-fid="<?php echo $f['furnitureID']; ?>"
                          data-fname="<?php echo htmlspecialchars($f['furnitureName']); ?>">
                        <input type="hidden" name="furnitureID" value="<?php echo $f['furnitureID']; ?>">
                        <button type="button" class="btn-delete del-f-btn">Delete</button>
                        <input type="hidden" name="delete_furniture" value="1">
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No furniture found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="js/confirm.js"></script>
<script>
// BOM 动态行
var matOptionsHtml = '<option value="">-- Select Material --</option><?php echo addslashes($matOptions); ?>';

document.getElementById('add-bom-row').addEventListener('click', function() {
    var container = document.getElementById('bom-container');
    var row = document.createElement('div');
    row.className = 'bom-row';
    row.innerHTML = '<select name="mat_id[]" class="form-control" required>' + matOptionsHtml + '</select>' +
                    '<input type="number" name="mat_qty[]" class="form-control" min="1" value="1" placeholder="Qty Required" required>' +
                    '<button type="button" class="btn-remove-bom" onclick="removeBomRow(this)">✕</button>';
    container.appendChild(row);
});

function removeBomRow(btn) {
    var rows = document.querySelectorAll('#bom-container .bom-row');
    if (rows.length <= 1) { alert('At least one material row is required.'); return; }
    btn.closest('.bom-row').remove();
}

// Delete confirmation
document.querySelectorAll('.del-f-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var form  = btn.closest('.delete-furniture-form');
        var fid   = form.dataset.fid;
        var fname = form.dataset.fname;
        showConfirm(
            'Delete Furniture #' + fid,
            'Delete "' + fname + '"? This action is permanent. (Only allowed if there are no existing orders.)'
        ).then(function(ok) {
            if (ok) form.submit();
        });
    });
});
</script>
</body>
</html>
