<?php
session_start();
if (file_exists("connect.php")) {
    require_once "connect.php";
} elseif (file_exists("config.php")) {
    require_once "config.php";
}

if (isset($_SESSION['customer_id']) || isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

$initialMode = isset($_GET['mode']) && $_GET['mode'] === 'login' ? 'login' : 'choice';
?>
<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>ເຂົ້າສູ່ລະບົບ / ລົງທະບຽນ - iJobs LMS</title>
<!-- Google Fonts: Noto Sans Lao -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
    * {
        box-sizing: border-box;
        font-family: 'Noto Sans Lao', sans-serif !important;
    }
    body {
        background-color: #f4f6fc;
        margin: 0;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
    }
    .login-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 12px 35px rgba(50, 70, 120, 0.08);
        width: 100%;
        max-width: 400px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .login-header {
        background: linear-gradient(135deg, #7496ea 0%, #4f79dc 100%);
        padding: 36px 20px 30px 20px;
        text-align: center;
        position: relative;
        color: #ffffff;
        overflow: hidden;
    }
    /* Dynamic decorative background circles */
    .login-header::before {
        content: '';
        position: absolute;
        top: -40px;
        left: -30px;
        width: 130px;
        height: 130px;
        background: rgba(255,255,255,0.12);
        border-radius: 50%;
    }
    .login-header::after {
        content: '';
        position: absolute;
        bottom: -50px;
        right: -30px;
        width: 160px;
        height: 160px;
        background: rgba(255,255,255,0.12);
        border-radius: 50%;
    }
    .logo-circle {
        width: 84px;
        height: 84px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0 auto 16px auto;
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }
    .logo-circle img {
        max-width: 60px;
        max-height: 60px;
        object-fit: contain;
    }
    .login-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        position: relative;
        z-index: 1;
        letter-spacing: 0.3px;
    }
    .login-header p {
        margin: 8px 0 0 0;
        font-size: 13px;
        color: #e2ebff;
        position: relative;
        z-index: 1;
        font-weight: 400;
    }
    .login-body {
        padding: 28px 24px 32px 24px;
    }
    .error-msg {
        background: #fee2e2;
        color: #dc2626;
        padding: 11px 14px;
        border-radius: 12px;
        text-align: center;
        font-size: 14px;
        margin-bottom: 20px;
        border: 1px solid #fca5a5;
    }

    /* Choice View Styling */
    .choice-view {
        text-align: center;
    }
    .notice-text {
        color: #3b50a1;
        font-size: 15px;
        font-weight: 600;
        margin: 22px 0 16px 0;
        line-height: 1.5;
    }
    .btn-action {
        width: 100%;
        background: linear-gradient(135deg, #4f7ee8 0%, #3b6bd6 100%);
        color: #ffffff;
        border: none;
        padding: 14px 20px;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        gap: 10px;
        transition: all 0.2s ease;
        box-shadow: 0 6px 16px rgba(59, 107, 214, 0.25);
    }
    .btn-action:hover {
        background: linear-gradient(135deg, #4272e0 0%, #2f5fc9 100%);
        transform: translateY(-1px);
        color: #ffffff;
    }
    .btn-action:active {
        transform: translateY(1px);
        box-shadow: 0 2px 8px rgba(59, 107, 214, 0.2);
    }

    /* Input Form Styling */
    .input-group {
        margin-bottom: 18px;
    }
    .input-group label {
        display: block;
        font-size: 15px;
        color: #374151;
        margin-bottom: 8px;
        font-weight: 600;
    }
    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .input-wrapper i {
        position: absolute;
        left: 16px;
        color: #4f7ee8;
        font-size: 17px;
    }
    .input-wrapper input {
        width: 100%;
        padding: 13px 14px 13px 44px;
        background-color: #edf2fc;
        border: 1px solid #e0e7f7;
        border-radius: 12px;
        font-size: 15px;
        color: #1f2937;
        outline: none;
        transition: all 0.2s;
    }
    .input-wrapper input:focus {
        background-color: #ffffff;
        border-color: #4f7ee8;
        box-shadow: 0 0 0 3px rgba(79, 126, 232, 0.15);
    }
    .input-wrapper input.readonly {
        background-color: #f3f4f6;
        color: #6b7280;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6b7280;
        font-size: 13px;
        margin-top: 14px;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.2s;
    }
    .back-link:hover {
        color: #3b6bd6;
    }
</style>
</head>
<body>

<div class="login-card">
    <!-- Header Component -->
    <div class="login-header">
        <div class="logo-circle">
            <img src="IjobsLogo.png" alt="Logo" onerror="this.src='korea.png'">
        </div>
        <h2>ລະບົບບໍລິການ-ການຈັດຫາງານ</h2>
        <p>LMS- Labor Management System</p>
    </div>

    <div class="login-body">
        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- STEP 1: CHOICE SCREEN -->
        <div id="choice-screen" class="choice-view" style="<?php echo ($initialMode === 'choice') ? 'display: block;' : 'display: none;'; ?>">
            <button type="button" class="btn-action" onclick="showFormScreen()">
                ເຂົ້າສູ່ລະບົບ
            </button>

            <div class="notice-text">
                ຖ້າບໍ່ທັນມີຂໍ້ມູນສ່ວນຕົວ ກະລຸນາລົງທະບຽນ
            </div>

            <a href="file/vacancy_add.php" class="btn-action">
                ລົງທະບຽນໃໝ່
            </a>
        </div>

        <!-- STEP 2: LOGIN FORM SCREEN -->
        <div id="form-screen" style="<?php echo ($initialMode === 'login') ? 'display: block;' : 'display: none;'; ?>">
            <form action="login_action.php" method="POST" autocomplete="off">
                
                <div class="input-group">
                    <label for="customer_id">ຊື່ຜູ້ໃຊ້ (Username) :</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-fill"></i>
                        <input type="text" id="customer_id" name="customer_id" placeholder="ປ້ອນຊື່ຜູ້ໃຊ້" required autofocus>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password">ລະຫັດຜ່ານ (Password) :</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock-fill"></i>
                        <input type="password" id="password" name="password" placeholder="ປ້ອນລະຫັດຜ່ານ">
                    </div>
                </div>

                <div class="input-group" id="customer_name_group" style="display:none;">
                    <label for="customer_name">ຊື່ລູກຄ້າ/ຊື່ຮ້ານ :</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-lines-fill"></i>
                        <input type="text" id="customer_name" name="customer_name" class="readonly" readonly tabindex="-1">
                    </div>
                </div>

                <input type="hidden" id="phone" name="phone">
                <input type="hidden" id="address" name="address">

                <button type="submit" class="btn-action">
                    ເຂົ້າສູ່ລະບົບ
                </button>

                <div style="text-align: center;">
                    <span class="back-link" onclick="showChoiceScreen()">
                        <i class="bi bi-arrow-left"></i> ຍ້ອນກັບ (Back)
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showFormScreen() {
    document.getElementById('choice-screen').style.display = 'none';
    document.getElementById('form-screen').style.display = 'block';
    document.getElementById('customer_id').focus();
}

function showChoiceScreen() {
    document.getElementById('form-screen').style.display = 'none';
    document.getElementById('choice-screen').style.display = 'block';
}

let typingTimer;
document.getElementById('customer_id').addEventListener('input', function () {
    clearTimeout(typingTimer);
    const id = this.value.trim();
    if (!id) {
        document.getElementById('customer_name_group').style.display = 'none';
        return;
    }
    typingTimer = setTimeout(() => {
        fetch('get_customer_info.php?customer_id=' + encodeURIComponent(id))
            .then(res => res.json())
            .then(data => {
                if (data && data.found) {
                    document.getElementById('customer_name_group').style.display = 'block';
                    document.getElementById('customer_name').value = data.customer_name || '';
                    document.getElementById('phone').value = data.phone || '';
                    document.getElementById('address').value = data.address || '';
                } else {
                    document.getElementById('customer_name_group').style.display = 'none';
                    document.getElementById('customer_name').value = '';
                }
            })
            .catch(() => {});
    }, 500);
});
</script>

</body>
</html>
