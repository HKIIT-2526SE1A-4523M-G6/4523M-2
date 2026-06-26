<?php
session_start();
require_once('Connections/conn.php');

if (!isset($_SESSION['customerID']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fid = (int)$_POST['fid'];
    $qty = (int)$_POST['oqty'];
    $address = mysqli_real_escape_string($conn, $_POST['odeliveraddress']);
    $date = mysqli_real_escape_string($conn, $_POST['odeliverydate']);
    $cid = $_SESSION['customerID'];

    $f_sql = "SELECT price, materialID, materialQty FROM Furniture WHERE furnitureID = $fid";
    $f_res = mysqli_query($conn, $f_sql);
    $f_row = mysqli_fetch_assoc($f_res);
    
    $totalAmount = $f_row['price'] * $qty;
    $matID = $f_row['materialID'];
    $matRequired = $f_row['materialQty'] * $qty;

    $m_sql = "SELECT physicalQty FROM Material WHERE materialID = $matID";
    $m_res = mysqli_query($conn, $m_sql);
    $m_row = mysqli_fetch_assoc($m_res);

    if ($m_row['physicalQty'] >= $matRequired) {
        $updateMat = "UPDATE Material SET physicalQty = physicalQty - $matRequired WHERE materialID = $matID";
        mysqli_query($conn, $updateMat);
        
        $insertOrder = "INSERT INTO Orders (customerID, furnitureID, orderQty, totalAmount, deliveryAddress, deliveryDate, orderStatus) 
                        VALUES ($cid, $fid, $qty, $totalAmount, '$address', '$date', 'Open')";
        mysqli_query($conn, $insertOrder);
        
        $message = "<div style='color:green; text-align:center; margin-bottom:15px;'>Order placed successfully! Stock deducted.</div>";
    } else {
        $message = "<div style='color:red; text-align:center; margin-bottom:15px;'>Failed: Not enough material stock available.</div>";
    }
}

$furnitures = mysqli_query($conn, "SELECT furnitureID, furnitureName, price FROM Furniture");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Place Order - Furniture System</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <nav>
    <div class="container nav-inner">
      <div class="logo">Furniture System</div>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="order.php">Place Order</a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container">
    <section class="section" id="order">
      <h2 class="section-title">Place New Order</h2>
      <div class="form-box">
        <h3 class="form-title">Order Details</h3>
        <?php echo $message; ?>
        <form action="order.php" method="post">
          <div class="form-group">
            <label>Select Furniture</label>
            <select class="form-control" name="fid" required>
              <?php while($item = mysqli_fetch_assoc($furnitures)): ?>
                  <option value="<?php echo $item['furnitureID']; ?>">
                      <?php echo $item['furnitureName'] . " - $" . $item['price']; ?>
                  </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Quantity</label>
            <input type="number" class="form-control" name="oqty" min="1" value="1" required>
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
    </section>
  </div>
</body>
</html>