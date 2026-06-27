
<?php
session_start();
require_once('3_Connections/DB_Configuration.php');
if (!isset($conn) && isset($GLOBALS['conn'])) $conn = $GLOBALS['conn'];

if (!isset($_SESSION['staffID']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); exit();
}

/* ============================================================
   API：POST application/json
   action = "add" | "delete"
   ============================================================ */
$ct = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($ct, 'application/json') !== false) {
    header('Content-Type: application/json');
    $body   = json_decode(file_get_contents('php://input'), true);
    $action = isset($body['action']) ? $body['action'] : '';

    /* ── 新增家具 ── */
    if ($action === 'add') {
//        $fname  = mysqli_real_escape_string($conn, trim($body['furnitureName']   ?? ''));
//        $fmodel = mysqli_real_escape_string($conn, trim($body['furnitureModel']  ?? ''));
//        $fdesc  = mysqli_real_escape_string($conn, trim($body['furnitureDescription'] ?? ''));
//        $fprice = (float)($body['furniturePrice'] ?? 0);
//        $fimage = mysqli_real_escape_string($conn, trim($body['furnitureImage']   ?? ''));
//        $fcat   = mysqli_real_escape_string($conn, trim($body['furnitureCategory']?? ''));
//        $bom    = $body['bom'] ?? [];   // [{materialID, qty}, ...]
        $fname  = mysqli_real_escape_string($conn, trim(isset($body['furnitureName']) ? $body['furnitureName'] : ''));
        $fmodel = mysqli_real_escape_string($conn, trim(isset($body['furnitureModel']) ? $body['furnitureModel'] : ''));
        $fdesc  = mysqli_real_escape_string($conn, trim(isset($body['furnitureDescription']) ? $body['furnitureDescription'] : ''));
        $fprice = (float)(isset($body['furniturePrice']) ? $body['furniturePrice'] : 0);
        $fimage = mysqli_real_escape_string($conn, trim(isset($body['furnitureImage']) ? $body['furnitureImage'] : ''));
        $fcat   = mysqli_real_escape_string($conn, trim(isset($body['furnitureCategory']) ? $body['furnitureCategory'] : ''));
        $bom    = isset($body['bom']) ? $body['bom'] : array();   // [{materialID, qty}, ...]

        if ($fname === '' || $fprice <= 0) {
            echo json_encode(['ok'=>false,'msg'=>'Name and valid Price are required.']); exit();
        }
        if (empty($bom)) {
            echo json_encode(['ok'=>false,'msg'=>'At least one BOM material is required.']); exit();
        }

        mysqli_query($conn, "START TRANSACTION");
        $dbError = false;

        $skuRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM Furniture"));
        $fsku   = 'FP-'.str_pad((int)$skuRow['cnt']+1, 3, '0', STR_PAD_LEFT);

        $insF = "INSERT INTO Furniture (furnitureSKU,furnitureName,furnitureModel,furnitureDescription,
                                        furniturePrice,furnitureImage,furnitureCategory,furnitureStockStatus)
                 VALUES ('$fsku','$fname','$fmodel','$fdesc',$fprice,'$fimage','$fcat',1)";
        if (!mysqli_query($conn, $insF)) { $dbError = true; }
        $newFID = mysqli_insert_id($conn);

        foreach ($bom as $b) {
//            $mid  = (int)($b['materialID'] ?? 0);
//            $mQty = max(1, (int)($b['qty'] ?? 1));
            // 迴圈內部的修改
            $mid  = (int)(isset($b['materialID']) ? $b['materialID'] : 0);
            $mQty = max(1, (int)(isset($b['qty']) ? $b['qty'] : 1));
            if ($mid <= 0) continue;
            if (!mysqli_query($conn, "INSERT INTO FurnitureMaterial (furnitureID,materialID,materialRequiredQty)
                                      VALUES ($newFID,$mid,$mQty)")) $dbError = true;
        }

        if (!$dbError) {
            mysqli_query($conn, "COMMIT");
            // 拼 BOM 文字返回给前端
            $bomRes  = mysqli_query($conn,
                    "SELECT m.materialName, m.materialUnit, fm.materialRequiredQty
                 FROM FurnitureMaterial fm JOIN Material m ON fm.materialID=m.materialID
                 WHERE fm.furnitureID=$newFID");
            $bomParts = [];
            while ($br = mysqli_fetch_assoc($bomRes))
                $bomParts[] = $br['materialName'].' x'.$br['materialRequiredQty'].' '.$br['materialUnit'];
            echo json_encode([
                    'ok'    => true,
                    'msg'   => "Furniture added. (ID: $newFID, SKU: $fsku)",
                    'row'   => [
                            'furnitureID'   => $newFID,
                            'furnitureSKU'  => $fsku,
                            'furnitureName' => $fname,
                            'furniturePrice'=> $fprice,
                            'furnitureImage'=> $fimage,
                            'bom'           => implode(', ', $bomParts)
                    ]
            ]);
        } else {
            mysqli_query($conn, "ROLLBACK");
            echo json_encode(['ok'=>false,'msg'=>'DB error: '.mysqli_error($conn)]);
        }
        exit();
    }

    /* ── 删除家具 ── */
    if ($action === 'delete') {
//        $fid = (int)($body['furnitureID'] ?? 0);
        $fid  = (int)(isset($body['furnitureID']) ? $body['furnitureID'] : 0);
        $chk = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) AS cnt FROM OrderFurniture WHERE furnitureID=$fid"));
        if ((int)$chk['cnt'] > 0) {
            echo json_encode(['ok'=>false,'msg'=>'Cannot delete: existing orders reference this furniture.']); exit();
        }
        mysqli_query($conn, "START TRANSACTION");
        $dbError = false;
        if (!mysqli_query($conn, "DELETE FROM FurnitureOption WHERE furnitureID=$fid"))   $dbError=true;
        if (!mysqli_query($conn, "DELETE FROM FurnitureMaterial WHERE furnitureID=$fid")) $dbError=true;
        if (!mysqli_query($conn, "DELETE FROM Furniture WHERE furnitureID=$fid"))         $dbError=true;
        if (!$dbError) {
            mysqli_query($conn, "COMMIT");
            echo json_encode(['ok'=>true,'msg'=>"Furniture #$fid deleted.",'furnitureID'=>$fid]);
        } else {
            mysqli_query($conn, "ROLLBACK");
            echo json_encode(['ok'=>false,'msg'=>'DB error: '.mysqli_error($conn)]);
        }
        exit();
    }

    echo json_encode(['ok'=>false,'msg'=>'Unknown action.']); exit();
}

/* ============================================================
   页面模式
   ============================================================ */
$matList = mysqli_query($conn, "SELECT materialID,materialName,materialUnit FROM Material ORDER BY materialID");
$furnitures = mysqli_query($conn,
        "SELECT f.furnitureID,f.furnitureSKU,f.furnitureName,f.furniturePrice,f.furnitureImage,
            GROUP_CONCAT(CONCAT(m.materialName,' x',fm.materialRequiredQty,' ',m.materialUnit) SEPARATOR ', ') AS bom
     FROM Furniture f
     LEFT JOIN FurnitureMaterial fm ON f.furnitureID=fm.furnitureID
     LEFT JOIN Material m ON fm.materialID=m.materialID
     GROUP BY f.furnitureID ORDER BY f.furnitureID DESC");

// 物料下拉选项（JS 动态行复用）
$matOptions = '';
$matDataForJs = [];
if ($matList) {
    while ($m = mysqli_fetch_assoc($matList)) {
        $matOptions .= "<option value='{$m['materialID']}'>" . htmlspecialchars($m['materialName']) . " ({$m['materialUnit']})</option>";
        $matDataForJs[] = $m;
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

    <!-- Add Form -->
    <div class="form-box" style="max-width:760px;margin-bottom:36px;">
        <h3 class="form-title">Add New Furniture</h3>
        <div id="furniture-msg"></div>
        <div class="form-group">
            <label>Furniture Name <span style="color:red">*</span></label>
            <input type="text" class="form-control" id="f-name">
        </div>
        <div class="form-group">
            <label>Model</label>
            <input type="text" class="form-control" id="f-model" placeholder="e.g. DC-101">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea class="form-control" id="f-desc"></textarea>
        </div>
        <div class="form-group">
            <label>Price <span style="color:red">*</span></label>
            <input type="number" class="form-control" id="f-price" step="0.01" min="0.01">
        </div>
        <div class="form-group">
            <label>Image Filename <span style="color:#999;font-size:12px;">(e.g. 7.png)</span></label>
            <input type="text" class="form-control" id="f-image" placeholder="7.png">
        </div>
        <div class="form-group">
            <label>Category</label>
            <input type="text" class="form-control" id="f-category" placeholder="e.g. Bedroom">
        </div>
        <div class="form-group">
            <label>Bill of Materials <span style="color:red">*</span></label>
            <div id="bom-container">
                <div class="bom-row">
                    <select name="mat_id[]" class="form-control">
                        <option value="">-- Select Material --</option>
                        <?php echo $matOptions; ?>
                    </select>
                    <input type="number" name="mat_qty[]" class="form-control" min="1" value="1">
                    <button type="button" class="btn-remove-bom" onclick="removeBomRow(this)">✕</button>
                </div>
            </div>
            <button type="button" id="add-bom-row">+ Add Material Row</button>
        </div>
        <button class="btn-submit" id="add-furniture-btn">Add Furniture</button>
    </div>

    <!-- Furniture List -->
    <h3 style="margin-bottom:8px;color:var(--primary);">Current Furniture List</h3>
    <table id="furniture-table">
        <thead><tr>
            <th>ID</th><th>SKU</th><th>Image</th><th>Name</th><th>Price</th><th>BOM</th><th>Action</th>
        </tr></thead>
        <tbody>
        <?php if ($furnitures && mysqli_num_rows($furnitures)>0):
            while ($f = mysqli_fetch_assoc($furnitures)): ?>
                <tr data-fid="<?php echo $f['furnitureID']; ?>">
                    <td><?php echo $f['furnitureID']; ?></td>
                    <td><?php echo htmlspecialchars($f['furnitureSKU']); ?></td>
                    <td>
                        <?php $img='1_Resources/furntiure_images/'.($f['furnitureImage']?$f['furnitureImage']:'default.png'); ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" class="table-img" onerror="this.style.display='none'" alt="">
                    </td>
                    <td><?php echo htmlspecialchars($f['furnitureName']); ?></td>
                    <td>$<?php echo number_format($f['furniturePrice'],2); ?></td>
                    <td style="font-size:12px;text-align:left;"><?php echo htmlspecialchars($f['bom']?$f['bom']:'—'); ?></td>
                    <td>
                        <button class="btn-delete del-f-btn"
                                data-fid="<?php echo $f['furnitureID']; ?>"
                                data-fname="<?php echo htmlspecialchars($f['furnitureName']); ?>">Delete</button>
                    </td>
                </tr>
            <?php endwhile; else: ?>
            <tr id="empty-row"><td colspan="7">No furniture found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    const MAT_OPTIONS_HTML = '<option value="">-- Select Material --</option><?php echo addslashes($matOptions); ?>'
</script>
<script src="js/confirm.js"></script>
<script src="js/admin_furniture.js"></script>
</body>
</html>