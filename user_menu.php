<?php
session_start();

if (!isset($_SESSION['customer_id']) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (file_exists("connect.php")) {
    require_once "connect.php";
} elseif (file_exists("config.php")) {
    require_once "config.php";
}

$customer_id = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? '';
$user_info = null;

if (isset($conn) && $conn && !empty($customer_id)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM data_entry_korea WHERE data_id = :id OR vacancy_check = :id OR phone1 = :id OR passport = :id LIMIT 1");
        $stmt->execute([':id' => $customer_id]);
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Handle query exception
    }
}

// ตรวจสอบเงื่อนไข 3 สถานะ: 1. sts_save / 2. sts_data / 3. sts_approve
$sts_save = $user_info['sts_save'] ?? null;
$sts_data = $user_info['sts_data'] ?? null;
$sts_approve = $user_info['sts_approve'] ?? null;

$current_status = "";

if (!empty($sts_approve)) {
    $current_status = $sts_approve;
} elseif (!empty($sts_data)) {
    $current_status = $sts_data;
} elseif (!empty($sts_save)) {
    $current_status = $sts_save;
} else {
    // ถ้าทั้ง 3 ตัวเป็น NULL หรือว่าง ให้แสดงตัวที่ 1 (sts_save หรือ Pending)
    $current_status = !empty($sts_save) ? $sts_save : 'Pending';
}

$displayName = $_SESSION['customer_name'] ?? $customer_id;
if ($user_info && !empty($user_info['vacancy_check'])) {
    $displayName = $user_info['vacancy_check'];
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>สถานะปัจจุบัน - iJobs LMS</title>
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
    .status-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 12px 35px rgba(50, 70, 120, 0.08);
        width: 100%;
        max-width: 400px;
        overflow: hidden;
    }
    .status-header {
        background: linear-gradient(135deg, #7496ea 0%, #4f79dc 100%);
        padding: 36px 20px 30px 20px;
        text-align: center;
        position: relative;
        color: #ffffff;
        overflow: hidden;
    }
    .status-header::before {
        content: '';
        position: absolute;
        top: -40px;
        left: -30px;
        width: 130px;
        height: 130px;
        background: rgba(255,255,255,0.12);
        border-radius: 50%;
    }
    .status-header::after {
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
    .status-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        position: relative;
        z-index: 1;
    }
    .status-header p {
        margin: 8px 0 0 0;
        font-size: 13px;
        color: #e2ebff;
        position: relative;
        z-index: 1;
    }
    .status-body {
        padding: 28px 24px 32px 24px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
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
    }
    .status-title-box {
        background-color: #f8fafc;
        border-left: 4px solid #4f7ee8;
        padding: 6px 12px;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
    }
    .status-display-box {
        width: 100%;
        padding: 14px 16px;
        background-color: #ffffff;
        border: 2px solid #4f7ee8;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 600;
        color: #2563eb;
        text-align: center;
        margin-bottom: 22px;
        box-shadow: 0 2px 8px rgba(79, 126, 232, 0.1);
    }

    .btn-menu {
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
        margin-bottom: 14px;
        transition: all 0.2s ease;
        box-shadow: 0 6px 16px rgba(59, 107, 214, 0.22);
    }
    .btn-menu:hover {
        background: linear-gradient(135deg, #4272e0 0%, #2f5fc9 100%);
        transform: translateY(-1px);
        color: #ffffff;
    }
    .btn-menu:active {
        transform: translateY(1px);
    }

    .btn-logout {
        width: 100%;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
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
        margin-top: 18px;
        transition: all 0.2s ease;
        box-shadow: 0 6px 16px rgba(220, 38, 38, 0.22);
    }
    .btn-logout:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-1px);
        color: #ffffff;
    }
    .btn-logout:active {
        transform: translateY(1px);
    }
</style>
</head>
<body>

<div class="status-card">
    <!-- Header -->
    <div class="status-header">
        <div class="logo-circle">
            <img src="IjobsLogo.png" alt="Logo" onerror="this.src='korea.png'">
        </div>
        <h2>ລະບົບບໍລິການ-ການຈັດຫາງານ</h2>
        <p>LMS- Labor Management System</p>
    </div>

    <!-- Body -->
    <div class="status-body">
        <!-- Username Field -->
        <div class="form-group">
            <label for="username_disp">ຊື່ຜູ້ໃຊ້ (Username) :</label>
            <div class="input-wrapper">
                <i class="bi bi-person-fill"></i>
                <input type="text" id="username_disp" value="<?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>
        </div>

        <!-- Current Status Label & Display Box -->
        <div class="status-title-box">
            ສະຖານະປັດຈຸບັນ
        </div>
        <div class="status-display-box">
            <?php echo htmlspecialchars($current_status, ENT_QUOTES, 'UTF-8'); ?>
        </div>


        <a href="file/check.php" class="btn-menu">
            ຂໍຕໍ່ວີຊາ
        </a>

        <a href="file/check.php" class="btn-menu">
            ຂໍສະເໜີອື່ນໆ
        </a>

        <a href="file/check.php" class="btn-menu">
            ການປະເມິນນາຍຈ້າງ
        </a>
           <a href="file/vacancy_edit.php?vacancy_check=<?php echo urlencode($user_info['vacancy_check'] ?? ''); ?>" class="btn-menu">
            ຂໍ້ມູນສ່ວນຕົວ
        </a>

        <!-- Logout Button -->
        <a href="logout.php" class="btn-logout">
              ອອກຈາກລະບົບ
        </a>
    </div>
</div>

</body>
</html>
