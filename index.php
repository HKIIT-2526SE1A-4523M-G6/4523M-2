<?php
session_start();
require_once('Connections/conn.php');

$sql = "SELECT f.*, m.physicalQty, FLOOR(m.physicalQty / f.materialQty) AS availableStock 
        FROM Furniture f 
        LEFT JOIN Material m ON f.materialID = m.materialID";
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
  <nav>
    <div class="container nav-inner">
      <div class="logo">Furniture System</div>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <?php if(isset($_SESSION['customerID'])): ?>
            <li><a href="order.php">Place Order</a></li>
            <li><a href="logout.php">Logout (<?php echo $_SESSION['fullName']; ?>)</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <div class="container">
    <section class="hero" id="home">
      <h1>Furniture Sales & Order Management System</h1>
      <p>Browse furniture, customer register & login, place orders, admin manage orders & inventory</p>
    </section>

    <section class="section" id="furniture">
      <h2 class="section-title">Furniture List</h2>
      <div id="product-grid" class="furniture-grid">
        
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="card">
              <div class="card-img zoom-container">
                <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['furnitureName']; ?>" class="zoom-img">
              </div>
              <div class="card-body">
                <h3 class="card-title"><?php echo $row['furnitureName']; ?></h3>
                <p class="card-desc"><?php echo $row['description']; ?></p>
                <div class="card-price">$<?php echo $row['price']; ?></div>
                
                <?php if($row['availableStock'] > 0): ?>
                    <p style="color: green; font-weight: bold;">Stock: <?php echo $row['availableStock']; ?></p>
                    <a href="order.php?fid=<?php echo $row['furnitureID']; ?>" class="card-btn" style="text-decoration:none;">Order Now</a>
                <?php else: ?>
                    <p style="color: red; font-weight: bold;">Sold Out</p>
                    <button class="card-btn" style="background-color: #ccc; cursor: not-allowed;" disabled>Out of Stock</button>
                <?php endif; ?>
              </div>
            </div>
        <?php endwhile; ?>

      </div>
    </section>
  </div>
</body>
</html>