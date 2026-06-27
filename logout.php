<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head></head>
<body>
<script>
    localStorage.removeItem('cart');
    window.location.href = 'index.php?_=' + Date.now();
</script>
</body>
</html>