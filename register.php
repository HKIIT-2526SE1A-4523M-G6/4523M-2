<?php
session_start();
require_once('3_Connections/DB_Configuration.php');

if (!isset($conn) && isset($GLOBALS['conn'])) {
    $conn = $GLOBALS['conn'];
}

/* ============================================================
   API 模式：POST application/json → 注册 → 返回 JSON
   ============================================================ */
$ct = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($ct, 'application/json') !== false) {
    header('Content-Type: application/json');

    $body      = json_decode(file_get_contents('php://input'), true);
    //    $cname     = mysqli_real_escape_string($conn, trim($body['cname']     ?? ''));
    //    $cpassword = mysqli_real_escape_string($conn, trim($body['cpassword'] ?? ''));
    //    $ctel      = mysqli_real_escape_string($conn, trim($body['ctel']      ?? ''));
    //    $caddr     = mysqli_real_escape_string($conn, trim($body['caddr']     ?? ''));

    $cname     = mysqli_real_escape_string($conn, trim(isset($body['cname']) ? $body['cname'] : ''));
    $cpassword = mysqli_real_escape_string($conn, trim(isset($body['cpassword']) ? $body['cpassword'] : ''));
    $ctel      = mysqli_real_escape_string($conn, trim(isset($body['ctel']) ? $body['ctel'] : ''));
    $caddr     = mysqli_real_escape_string($conn, trim(isset($body['caddr']) ? $body['caddr'] : ''));


    if ($cname === '' || $cpassword === '' || $ctel === '' || $caddr === '') {
        echo json_encode(['ok' => false, 'msg' => 'All required fields must be filled in.']);
        exit();
    }

    // 检查电话是否已注册
    $chk = mysqli_query($conn, "SELECT customerID FROM Customer WHERE customerNumber = '$ctel'");
    if ($chk && mysqli_num_rows($chk) > 0) {
        echo json_encode(['ok' => false, 'msg' => 'Registration failed: telephone number already registered.']);
        exit();
    }

    $sql = "INSERT INTO Customer (fullName, customerPassword, customerNumber, customerAddress)
            VALUES ('$cname', '$cpassword', '$ctel', '$caddr')";

    if (mysqli_query($conn, $sql)) {
        $newID = mysqli_insert_id($conn);
        echo json_encode(['ok' => true, 'customerID' => $newID, 'fullName' => $cname]);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Database error: ' . mysqli_error($conn)]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Furniture System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* 确认弹窗 */
        #reg-confirm-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }

        #reg-confirm-overlay.show {
            display: flex;
        }

        #reg-confirm-box {
            background: #fff;
            border-radius: 12px;
            padding: 40px 36px 32px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .18);
        }

        #reg-confirm-box .icon {
            font-size: 52px;
            margin-bottom: 12px;
        }

        #reg-confirm-box h3 {
            margin: 0 0 10px;
            font-size: 20px;
            color: #27ae60;
        }

        #reg-confirm-box p {
            color: #555;
            font-size: 14px;
            margin: 6px 0;
            line-height: 1.6;
        }

        #reg-confirm-box .customer-id-badge {
            display: inline-block;
            margin: 14px 0 20px;
            padding: 10px 28px;
            background: #f0fdf4;
            border: 2px solid #27ae60;
            border-radius: 8px;
            font-size: 22px;
            font-weight: 700;
            color: #27ae60;
            letter-spacing: 2px;
        }

        #reg-confirm-box .iknow-btn {
            display: inline-block;
            padding: 12px 40px;
            background: #27ae60;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }

        #reg-confirm-box .iknow-btn:hover {
            background: #219150;
        }
    </style>
</head>

<body>

    <nav>
        <div class="container nav-inner">
            <div class="logo">Furniture System</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin.php">Admin Panel</a></li>
                    <?php else: ?>
                        <li><a href="order.php">Cart</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout (<?php
                                                        echo htmlspecialchars(
                                                            isset($_SESSION['fullName'])  ? $_SESSION['fullName']  : (isset($_SESSION['staffName']) ? $_SESSION['staffName'] : 'User')
                                                        ); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="order.php">Cart</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <section class="section" id="register">
            <h2 class="section-title">Customer Registration</h2>

            <!-- 注册表单（成功后会被替换） -->
            <div class="form-box" id="register-box">
                <h3 class="form-title">Create New Account</h3>
                <div id="reg-msg"></div>

                <div class="form-group">
                    <label>Full Name <span style="color:red">*</span></label>
                    <input type="text" class="form-control" id="r-name" required>
                </div>
                <div class="form-group">
                    <label>Password <span style="color:red">*</span></label>
                    <input type="password" class="form-control" id="r-password" required>
                </div>
                <div class="form-group">
                    <label>Telephone <span style="color:red">*</span></label>
                    <input type="text" class="form-control" id="r-tel" required>
                </div>
                <div class="form-group">
                    <label>Address <span style="color:red">*</span></label>
                    <textarea class="form-control" id="r-addr" required></textarea>
                </div>
                <div class="form-group">
                    <label>Company <span style="color:#999;font-size:12px;">(Optional)</span></label>
                    <input type="text" class="form-control" id="r-company">
                </div>
                <button class="btn-submit" id="register-btn">Register</button>
            </div>
        </section>
    </div>

    <!-- 注册成功确认弹窗 -->
    <div id="reg-confirm-overlay">
        <div id="reg-confirm-box">
            <div class="icon">🎉</div>
            <h3>Registration Successful!</h3>
            <p>Welcome, <strong id="confirm-name"></strong>!</p>
            <p>Your Customer ID is:</p>
            <div class="customer-id-badge" id="confirm-cid"></div>
            <p style="color:#e67e22;font-size:13px;">
                Please save this ID — you will need it to log in.
            </p>
            <button class="iknow-btn" id="iknow-btn">I Know</button>
        </div>
    </div>

    <script>
        (function() {
            function showMsg(text, ok) {
                const el = document.getElementById('reg-msg')
                el.innerHTML = `<div style="color:${ok?'#27ae60':'#e74c3c'};font-weight:bold;
            padding:10px;margin-bottom:12px;border-radius:6px;
            background:${ok?'#f0fdf4':'#fdf0f0'}">${text}</div>`
            }

            document.getElementById('register-btn').addEventListener('click', async () => {
                const name = document.getElementById('r-name').value.trim()
                const password = document.getElementById('r-password').value.trim()
                const tel = document.getElementById('r-tel').value.trim()
                const addr = document.getElementById('r-addr').value.trim()

                if (!name || !password || !tel || !addr) {
                    showMsg('Please fill in all required fields.', false)
                    return
                }

                const btn = document.getElementById('register-btn')
                btn.disabled = true
                btn.textContent = 'Registering…'

                try {
                    const res = await fetch('register.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            cname: name,
                            cpassword: password,
                            ctel: tel,
                            caddr: addr
                        })
                    })
                    const data = await res.json()

                    if (data.ok) {
                        // 填入弹窗信息
                        document.getElementById('confirm-name').textContent = data.fullName
                        document.getElementById('confirm-cid').textContent = data.customerID
                        // 显示弹窗
                        document.getElementById('reg-confirm-overlay').classList.add('show')
                    } else {
                        showMsg(data.msg, false)
                        btn.disabled = false
                        btn.textContent = 'Register'
                    }
                } catch (e) {
                    showMsg('Network error. Please try again.', false)
                    btn.disabled = false
                    btn.textContent = 'Register'
                }
            })

            // I Know 按钮：关闭弹窗，表单区替换为成功提示
            document.getElementById('iknow-btn').addEventListener('click', () => {
                document.getElementById('reg-confirm-overlay').classList.remove('show')
                document.getElementById('register-box').innerHTML = `
            <div style="text-align:center;padding:48px 24px;">
                <div style="font-size:48px;margin-bottom:16px;">✅</div>
                <h3 style="color:#27ae60;margin-bottom:8px;">You're all set!</h3>
                <p style="color:#666;margin-bottom:20px;">
                    Your account has been created. Use your Customer ID or telephone number to log in.
                </p>
                <a href="login.php" class="card-btn"
                   style="display:inline-block;width:auto;padding:12px 32px;">Go to Login</a>
            </div>`
            })
        })()
    </script>

</body>

</html>