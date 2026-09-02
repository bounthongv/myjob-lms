<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../connect.php');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['customer_id']) && !isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$user_session_id = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? '';

// ຄວາມປອດໄພ: ຫ້າມໃຊ້ $_GET['vacancy_check'] ເປັນຕົວກຳນົດວ່າຈະໂຫຼດຂໍ້ມູນຂອງໃຜ
// ລະບົບນີ້ບໍ່ມີ Role/Admin ແຍກຕ່າງຫາກ (login_action.php ອອກ session ດຽວກັນໃຫ້ທຸກຄົນ)
// ດັ່ງນັ້ນຕ້ອງຜູກກັບ session ຂອງຄົນທີ່ login ຢູ່ສະເໝີ ບໍ່ດັ່ງນັ້ນຄົນທີ່ login ຢູ່ແລ້ວ
// ຈະສາມາດໃສ່ ?vacancy_check=<ຄົນອື່ນ> ແລ້ວເບິ່ງ/ແກ້ໄຂຂໍ້ມູນສ່ວນຕົວຄົນອື່ນໄດ້ (IDOR)
if (empty($user_session_id)) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>ກະລຸນາເຂົ້າສູ່ລະບົບ</h2><a href='../login.php'>ເຂົ້າສູ່ລະບົບ</a></div>");
}
$vacancy_check = $user_session_id;

$sql = $conn->prepare("SELECT *,
-- ປັດຈຸບັນ
pro.pro_name_lao as pro_name_lao,
dis.dis_name_lao as dis_name_lao,
vill.vill_name_lao as vill_name_lao,
pro.pro_id as pro_id,
dis.dis_id as dis_id,
vill.vill_id as vill_id,
-- ບ່ອນເກີດ
pro_b.pro_name_lao as pro_name_b,
dis_b.dis_name_lao as dis_name_b,
vill_b.vill_name_lao as vill_name_b,
cand.pro_id_b,
cand.dis_id_b,
cand.vill_id_b

FROM candidate_korea as cand
LEFT JOIN province as pro ON cand.pro_id=pro.pro_id
LEFT JOIN district as dis ON cand.dis_id=dis.dis_id
LEFT JOIN village as vill ON cand.vill_id=vill.vill_id

LEFT JOIN province as pro_b ON cand.pro_id_b=pro_b.pro_id
LEFT JOIN district as dis_b ON cand.dis_id_b=dis_b.dis_id
LEFT JOIN village as vill_b ON cand.vill_id_b=vill_b.vill_id

WHERE cand.vacancy_check = :id OR cand.cid = :id OR cand.phone1 = :id OR cand.passport = :id
LIMIT 1");

$sql->execute([':id' => $vacancy_check]);
$row = $sql->fetch(PDO::FETCH_ASSOC);

// หากไม่พบข้อมูล ให้แสดงแจ้งเตือนปลอดภัย
if (!$row) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>ບໍ່ພົບຂໍ້ມູນ ຫຼື ທ່ານບໍ່ມີສິດເຂົ້າເຖິງຂໍ້ມູນນີ້</h2><a href='../user_menu.php'>ກັບຄືນໜ້າຫຼັກ</a></div>");
}

$sql_pro = $conn->prepare("SELECT * FROM province ORDER BY pro_id ASC");
$sql_pro->execute();
$pro = $sql_pro->fetchAll(PDO::FETCH_ASSOC);

// ຟັງຊັນ escape ສຳລັບສະແດງຜົນ (ໃຊ້ທຸກບ່ອນທີ່ເອົາຄ່າຈາກຖານຂໍ້ມູນມາໃສ່ HTML)
// html_entity_decode() ກ່ອນ ເພື່ອຈັດການຂໍ້ມູນເກົ່າທີ່ຖືກ encode ໄວ້ແລ້ວຕອນບັນທຶກ
// (ລະບົບເກົ່າ getPost() ໃສ່ htmlspecialchars ກ່ອນເກັບລົງ DB — ດຽວນີ້ເອົາອອກແລ້ວ)
// ຖ້າບໍ່ decode ກ່ອນ ຂໍ້ມູນເກົ່າຈະສະແດງເປັນ &#039; ໃຫ້ຜູ້ໃຊ້ເຫັນ
function h($value) {
    return htmlspecialchars(html_entity_decode((string)($value ?? ''), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
}

function getImgUrl($filename) {
    if (empty($filename)) return '';
    $possiblePaths = [
        '/var/www/html/job/file/korea/uploads/' . $filename,
        __DIR__ . '/uploads/' . $filename,
        __DIR__ . '/korea/uploads/' . $filename,
    ];
    foreach ($possiblePaths as $p) {
        if (file_exists($p) && is_file($p)) {
            return 'get_image.php?f=' . urlencode($filename);
        }
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APLABOR</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>">
    <style>
        :root {
            --green-dark: #1a4d2e;
            --green-mid: #000;
            --green-btn: #2d9e5f;
            --green-light: #e8f5ee;
            --green-border: #d1e8d8;
            --green-text: #000;
        }

        body {
            background: #f0f4f0;
            padding: 10px;
        }

        .card {
            border: 1px solid var(--green-border);
            border-radius: 10px;
        }

        /* ===== ຫົວ section ຟອມ ===== */
        .section-head {
            background: #f5fbf7;
            border-bottom: 1px solid var(--green-border);
            padding: 10px 16px;
            font-size: 16px;
            font-weight: 700;
            color: var(--green-mid);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .form-label {
            font-size: 16px;
            font-weight: 600;
            color: var(--green-mid);
            margin-bottom: 4px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--green-btn);
            box-shadow: 0 0 0 2px rgba(45, 158, 95, .12);
        }

        .btn-main {
            background: var(--green-dark);
            color: #fff;
            border: none;
            font-weight: 600;
        }

        .btn-main:hover {
            background: var(--green-mid);
            color: #fff;
        }

        .required {
            color: #e24b4a;
        }

        .form-hint {
            font-size: 11px;
            color: var(--green-text);
            margin-top: 3px;
        }

        .custom-card {
            background-color: #212121;
            border: 1px solid #2d2d2d;
            border-radius: 12px;
            overflow: hidden;
        }

        .custom-card-header {
            background-color: #0b5135;
            /* สีเขียวเข้ม */
            color: #ffffff;
            padding: 15px 20px;
            font-size: 1.25rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .upload-box {
            border: 2px dashed var(--green-border);
            border-radius: 8px;
            background-color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .upload-box:hover {
            border-color: var(--green-btn);
            background-color: #fafcfa;
        }

        /* เมื่อมีการเลือกไฟล์สำเร็จ */
        .upload-box.has-file {
            border-color: var(--green-btn);
            background-color: #f5fbf7;
            border-style: solid;
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            background-color: #e8f5e9;
            color: #0b5135;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-size: 1.5rem;
        }

        /* สไตล์ของแท็บระบบลายเซ็น */
        .nav-pills .nav-link {
            color: var(--green-mid);
            border: 1px solid var(--green-border);
            margin-bottom: 10px;
            background-color: #ffffff;
            font-size: 12px;
            font-weight: 600;
        }

        .nav-pills .nav-link.active {
            background-color: var(--green-dark);
            color: white;
            border-color: var(--green-dark);
        }

        /* พื้นที่สำหรับเซ็นชื่อ */
        .canvas-container {
            background-color: #fff;
            border: 2px dashed var(--green-border);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        canvas {
            display: block;
            cursor: crosshair;
            width: 100%;
            height: 155px;
            background: #ffffff;
        }

        .text-muted-custom {
            color: #888;
            font-size: 0.85rem;
        }

        .asterisk {
            color: #dc3545;
        }

        /* สไตล์สำหรับรูปภาพ Preview */
        .preview-img {
            max-height: 140px;
            max-width: 100%;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* ປຸ່ມລົບຮູບ (ມຸມຂວາເທິງຂອງກ່ອງອັບໂຫຼດ) */
       /* ປຸ່ມລົບຮູບ (ມຸມຂວາເທິງຂອງກ່ອງອັບໂຫຼດ) */
.btn-remove-img {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 2;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: #fff !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .2);
}

.btn-remove-img:hover,
.btn-remove-img:focus,
.btn-remove-img:active {
    background-color: #bb2d3b !important;
    border-color: #b02a37 !important;
    color: #fff !important;
}

.btn-remove-img i {
    color: #fff !important;
}
        /* ค้างสีตอนถูกเลือก (Active State) */
        .btn-borrow.active {
            background: linear-gradient(135deg, #1565c0 0%, #0091ea 100%);
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.4), 0 6px 14px rgba(21, 101, 192, 0.5);
            transform: translateY(-2px);
        }

        .btn-borrow.active::before {
            content: "\F26A";
            /* bi-check-circle-fill */
            font-family: "bootstrap-icons";
            position: absolute;
            top: -8px;
            right: -8px;
            background: #fff;
            color: #0091ea;
            border-radius: 50%;
            font-size: 16px;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        }

        .btn-pay.active {
            background: linear-gradient(135deg, #2e7d32 0%, #00c853 100%);
            box-shadow: 0 0 0 3px rgba(67, 233, 123, 0.4), 0 6px 14px rgba(46, 125, 50, 0.5);
            transform: translateY(-2px);
        }

        .btn-pay.active::before {
            content: "\F26A";
            /* bi-check-circle-fill */
            font-family: "bootstrap-icons";
            position: absolute;
            top: -8px;
            right: -8px;
            background: #fff;
            color: #00c853;
            border-radius: 50%;
            font-size: 16px;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        }

        /* ปุ่มที่ไม่ถูกเลือก ให้จางลงเล็กน้อยตอนมีการเลือกอันอื่นแล้ว */
        .custom-action-row.has-selection .btn:not(.active) {
            opacity: 0.55;
        }

        /* ໝາຍເຫດ: CSS ຂອງລະບົບຖ່າຍຮູບ (.file-hidden, .upload-actions, .is-busy)
           ຢູ່ໃນ style.css ເພື່ອໃຊ້ຮ່ວມກັບ vacancy_add.php */
    </style>
</head>
<body>
    <div class="content">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.4rem; flex-wrap:wrap; gap:10px;">
    <div>
        <h5 style="font-size:19px; font-weight:700; color:#0f172a; margin:0;">
            ແກ້ໄຂຂໍ້ມູນສ່ວນຕົວ
        </h5>
    </div>
    <div>
        <a href="../user_menu.php" class="btn btn-secondary  px-3 shadow-sm" style="border-radius: 8px;">
            <i class="bi bi-arrow-left me-1"></i> ກັບຄືນ
        </a>
    </div>
</div>
<div class="card shadow-none" style="max-width:1920px;">

    <form method="POST" id="edit_vacancy" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?= h($row['id']) ?>">
    <input type="hidden" name="sub" value="update">

    <div class="section-head">
        <i class="bi bi-person me-2"></i>ຂໍ້ມູນສ່ວນຕົວ
    </div>
    <div class="p-3">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label">ວັນທີ <span class="required">*</span> : </label>
                <input type="date" name="interview_date" class="form-control form-control" value="<?= h($row['interview_date']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ນາມສະກຸນ (ພາສາອັງກິດ) : </label>
                <input type="text" name="lname_eng" class="form-control form-control" value="<?= h($row['lname_eng']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ຊື່ (ພາສາອັງກິດ) : </label>
                <input type="text" name="fname_eng" class="form-control form-control" value="<?= h($row['fname_eng']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ຊື່ຫຼິ້ນ : </label>
                <input type="text" name="nickname" class="form-control form-control" value="<?= h($row['nickname']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ຊື່ (ພາສາລາວ) : </label>
                <input type="text" name="fname" class="form-control form-control" value="<?= h($row['fname']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ນາມສະກຸນ : </label>
                <input type="text" name="lname" class="form-control form-control" value="<?= h($row['lname']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ເບີໂທລະສັບ : </label>
                <input type="text" name="phone1" class="form-control form-control" value="<?= h($row['phone1']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ເບີໂທລະສັບ 2 : </label>
                <input type="text" name="phone2" class="form-control form-control" value="<?= h($row['phone2']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ເບີໂທຍາດພີ່ນ້ອງ : </label>
                <input type="text" name="fam_phone" class="form-control form-control" value="<?= h($row['fam_phone']) ?>">
            </div>
            <div class="col-12 col-md-4" style="display:none;">
                <label class="form-label">ສັນຊາດ : </label>
                <input type="text" name="nationality" class="form-control form-control" value="<?= h($row['nationality']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ວັນເດືອນປີເກີດ : </label>
                <input type="date" name="dob" id="dob" class="form-control form-control" value="<?= h($row['dob']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ອາຍຸ : </label>
                <input type="text" name="age" id="age" class="form-control form-control" value="<?= h($row['age']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ເພດ : </label>
                <select name="gender" class="form-select form-select">
                    <option value="">ເລືອກ</option>
                    <option value="F" <?= $row['gender'] == 'F' ? 'selected' : '' ?>>ຍິງ</option>
                    <option value="M" <?= $row['gender'] == 'M' ? 'selected' : '' ?>>ຊາຍ</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ສະຖານະການແຕ່ງງານ : </label>
                <input type="hidden" name="status" value="<?= h($row['status']) ?>">
                <select class="form-select form-select" style="pointer-events: none; background-color: #e9ecef;" tabindex="-1" disabled>
                    <option value="">ເລືອກ</option>
                    <option value="SINGLE" <?= $row['status'] == 'SINGLE' ? 'selected' : '' ?>>ໂສດ</option>
                    <option value="MARRIED" <?= $row['status'] == 'MARRIED' ? 'selected' : '' ?>>ແຕ່ງງານແລ້ວ</option>
                    <option value="DIVORCED" <?= $row['status'] == 'DIVORCED' ? 'selected' : '' ?>>ຢ່າຮ້າງ</option>
                    <option value="MARRIED(COUPLE)" <?= $row['status'] == 'MARRIED(COUPLE)' ? 'selected' : '' ?>>ໄປເປັນຄູ່</option>
                </select>
            </div>
            <div class="col-12 col-md-4" id="spouse_id_div" style="<?= $row['status'] == 'MARRIED(COUPLE)' ? '' : 'display:none;' ?>">
                <label class="form-label">ເລກທີ ຜົວ/ເມຍ (Spouse ID) : </label>
                <input type="text" name="spouse_id" id="spouse_id" class="form-control form-control" value="<?= h($row['spouse_id']) ?>" placeholder="ປ້ອນເລກທີຜົວ/ເມຍ">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ນ້ຳໜັກ (Kg) : </label>
                <input type="text" name="weight" class="form-control form-control" value="<?= h($row['weight']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ລວງສູງ (Cm) : </label>
                <input type="text" name="height" class="form-control form-control" value="<?= h($row['height']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ເລກທີສຳມະໂນຄົວ : </label>
                <input type="text" name="family_book_no" class="form-control form-control" value="<?= h($row['family_book_no']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ວັນທີອອກສຳມະໂນຄົວ : </label>
                <input type="date" name="family_book_date" class="form-control form-control" value="<?= h($row['family_book_date']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ຊື່ພໍ່ : </label>
                <input type="text" name="father" class="form-control form-control" value="<?= h($row['father']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ຊື່ແມ່ : </label>
                <input type="text" name="mother" class="form-control form-control" value="<?= h($row['mother']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ໜ່ວຍ : </label>
                <input type="text" name="unit" class="form-control form-control" value="<?= h($row['unit']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ເຮືອນ : </label>
                <input type="text" name="home" class="form-control form-control" value="<?= h($row['home']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ເລກທີປັດສະປອດ : </label>
                <input type="text" name="passport" class="form-control form-control" value="<?= h($row['passport']) ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ວັນທີອອກປັດສະປອດ : </label>
                <input type="date" name="issue_date" class="form-control form-control" value="<?= h($row['issue_date']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ວັນທີໝົດອາຍຸປັດສະປອດ : </label>
                <input type="date" name="exp_date" class="form-control form-control" value="<?= h($row['exp_date']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ໃບຂັບຂີ່ : </label>
                <select name="driver" class="form-select form-select">
                    <option value="NO" <?= $row['driver'] == 'NO' ? 'selected' : '' ?>>NO</option>
                    <option value="A" <?= $row['driver'] == 'A' ? 'selected' : '' ?>>A</option>
                    <option value="AB" <?= $row['driver'] == 'AB' ? 'selected' : '' ?>>AB</option>
                    <option value="ABC" <?= $row['driver'] == 'ABC' ? 'selected' : '' ?>>ABC</option>
                    <option value="ABCD" <?= $row['driver'] == 'ABCD' ? 'selected' : '' ?>>ABCD</option>
                    <option value="B" <?= $row['driver'] == 'B' ? 'selected' : '' ?>>B</option>
                    <option value="C" <?= $row['driver'] == 'C' ? 'selected' : '' ?>>C</option>
                    <option value="D" <?= $row['driver'] == 'D' ? 'selected' : '' ?>>D</option>
                    <option value="BC" <?= $row['driver'] == 'BC' ? 'selected' : '' ?>>BC</option>
                    <option value="CD" <?= $row['driver'] == 'CD' ? 'selected' : '' ?>>CD</option>
                    <option value="BCD" <?= $row['driver'] == 'BCD' ? 'selected' : '' ?>>BCD</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ຂະໜາດເສື້ອ : </label>
                <select name="shirt_size" class="form-select form-select">
                    <option value="S" <?= $row['shirt_size'] == 'S' ? 'selected' : '' ?>>S</option>
                    <option value="M" <?= $row['shirt_size'] == 'M' ? 'selected' : '' ?>>M</option>
                    <option value="L" <?= $row['shirt_size'] == 'L' ? 'selected' : '' ?>>L</option>
                    <option value="XL" <?= $row['shirt_size'] == 'XL' ? 'selected' : '' ?>>XL</option>
                    <option value="XXL" <?= $row['shirt_size'] == 'XXL' ? 'selected' : '' ?>>XXL</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ປະເພດແຮງງານ : </label>
                <select name="labor_type" class="form-select form-select">
                    <option value="New" <?= $row['labor_type'] == 'New' ? 'selected' : '' ?>>New</option>
                    <option value="Re-New" <?= $row['labor_type'] == 'Re-New' ? 'selected' : '' ?>>Re-New</option>
                    <option value="New(RC)" <?= $row['labor_type'] == 'New(RC)' ? 'selected' : '' ?>>New(RC)</option>
                    <option value="Re-entry" <?= $row['labor_type'] == 'Re-entry' ? 'selected' : '' ?>>Re-entry</option>
                    <option value="Re-employment" <?= $row['labor_type'] == 'Re-employment' ? 'selected' : '' ?>>Re-employment</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ຊົນເຜົ່າ : </label>
                <select name="eth" class="form-select form-select">
                    <?php
                    $eth_list = ["ເຜົ່າລາວ","ເຜົ່າມົ້ງ","ເຜົ່າກຶມມຸ","ເຜົ່າຜູ້ໄທ","ເຜົ່າລື້","ເຜົ່າໄທດຳ","ເຜົ່າໄທແດງ","ອື່ນໆ"];
                    foreach ($eth_list as $opt): ?>
                        <option value="<?= h($opt) ?>" <?= $row['eth'] == $opt ? 'selected' : '' ?>><?= h($opt) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ປະສົບການ : </label>
                <input type="text" name="agricu" id="agricu" class="form-control form-control" value="<?= h($row['agricu']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ສະຖານທີ່ສຳພາດ : </label>
                <select name="interview_location" class="form-select form-select" required>
                    <option value="Outside" <?= $row['interview_location'] == 'Outside' ? 'selected' : '' ?>>Outside</option>
                    <option value="Inside" <?= $row['interview_location'] == 'Inside' ? 'selected' : '' ?>>Inside</option>
                    <option value="Re-employment" <?= $row['interview_location'] == 'Re-employment' ? 'selected' : '' ?>>Re-employment</option>
                    <option value="NEW(RC)" <?= $row['interview_location'] == 'NEW(RC)' ? 'selected' : '' ?>>NEW(RC)</option>
                    <option value="Re-New" <?= $row['interview_location'] == 'Re-New' ? 'selected' : '' ?>>Re-New</option>
                    <option value="Online" <?= $row['interview_location'] == 'Online' ? 'selected' : '' ?>>Online</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ອາຊີບ/ວຽກ : </label>
                <input type="text" name="job" class="form-control form-control" value="<?= h($row['job']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ຜູ້ສຳພາດ : </label>
                <input type="text" name="interview_name" class="form-control form-control" value="<?= h($row['interview_name']) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ແຮງງານ ມີຕົວເລືອກ : </label>
                <select name="list" class="form-select form-select">
                    <option value="ຄົນດຽວ" <?= $row['list_type'] == 'ຄົນດຽວ' ? 'selected' : '' ?>>ຄົນດຽວ</option>
                    <option value="ຄູ່ຜົວ-ເມຍ" <?= $row['list_type'] == 'ຄູ່ຜົວ-ເມຍ' ? 'selected' : '' ?>>ຄູ່ຜົວ-ເມຍ</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ປະເພດການເຂົ້າວຽກ : </label>
                <select name="type_in" class="form-select form-select">
                    <option value="">ເລືອກ</option>
                    <option value="ເຂົ້າໃໝ່" <?= ($row['type_in'] ?? '') == 'ເຂົ້າໃໝ່' ? 'selected' : '' ?>>ເຂົ້າໃໝ່</option>
                    <option value="ກັບຄືນໄປອີກ" <?= ($row['type_in'] ?? '') == 'ກັບຄືນໄປອີກ' ? 'selected' : '' ?>>ກັບຄືນໄປອີກ</option>
                </select>
            </div>
            <div class="col-12 col-md-4" id="emp_id_div" style="<?= ($row['type_in'] ?? '') == 'ກັບຄືນໄປອີກ' ? '' : 'display:none;' ?>">
                <label class="form-label">ລະຫັດນາຍຈ້າງ : </label>
                <input type="text" name="emp_id" class="form-control form-control" value="<?= h($row['emp_id'] ?? '') ?>">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ຊ່ວງເວລາພ້ອມເລີ່ມວຽກ : </label>
                <input type="date" name="timezon" class="form-control form-control" value="<?= h(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', (string)($row['timezon'] ?? '')) ? $row['timezon'] : '') ?>">
            </div>
        </div>
    </div>

    <hr class="m-0" style="border-color:var(--green-border);">
    <div class="section-head">
        <i class="bi bi-house-door-fill me-2"></i>ທີ່ຢູ່ປັດຈຸບັນ
    </div>
    <div class="p-3">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label">ແຂວງ <span class="required">*</span></label>
                <select name="pro_id" id="pro_id" class="form-select form-select">
                    <option value="">ເລືອກ</option>
                    <?php foreach ($pro as $proa): ?>
                        <option value="<?= h($proa['pro_id']) ?>" <?= $row['pro_id'] == $proa['pro_id'] ? 'selected' : '' ?>><?= h($proa['pro_name_lao']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ເມືອງ <span class="required">*</span></label>
                <select name="dis_id" id="dis_id" class="form-select form-select" data-selected="<?= h($row['dis_id']) ?>">
                    <option value="<?= h($row['dis_id']) ?>"><?= h($row['dis_name_lao']) ?></option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ບ້ານ <span class="required">*</span></label>
                <select name="vill_id" id="vill_id" class="form-select form-select" data-selected="<?= h($row['vill_id']) ?>">
                    <option value="<?= h($row['vill_id']) ?>"><?= h($row['vill_name_lao']) ?></option>
                </select>
            </div>
        </div>
    </div>

    <hr class="m-0" style="border-color:var(--green-border);">
    <div class="section-head">
        <i class="bi bi-house-door-fill me-2"></i>ທີ່ຢູ່ບ່ອນເກີດ
    </div>
    <div class="p-3">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label">ແຂວງ <span class="required">*</span></label>
                <select name="pro_id_b" id="pro_id_b" class="form-select form-select">
                    <option value="">ເລືອກ</option>
                    <?php foreach ($pro as $proa): ?>
                        <option value="<?= h($proa['pro_id']) ?>" <?= $row['pro_id_b'] == $proa['pro_id'] ? 'selected' : '' ?>><?= h($proa['pro_name_lao']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ເມືອງ <span class="required">*</span></label>
                <select name="dis_id_b" id="dis_id_b" class="form-select form-select" data-selected="<?= h($row['dis_id_b']) ?>">
                    <option value="<?= h($row['dis_id_b']) ?>"><?= h($row['dis_name_b']) ?></option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">ບ້ານ <span class="required">*</span></label>
                <select name="vill_id_b" id="vill_id_b" class="form-select form-select" data-selected="<?= h($row['vill_id_b']) ?>">
                    <option value="<?= h($row['vill_id_b']) ?>"><?= h($row['vill_name_b']) ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="section-head">
        <i class="bi bi-camera me-2" style="font-size: 16px;"></i>ອັບໂຫຼດເອກະສານ / ຮູບພາບ
    </div>
    <div class="p-3">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label fw-bold mb-2">ຮູບຖ່າຍ <span class="asterisk">*</span>: </label>
                <?php $profileUrl = getImgUrl($row['profile'] ?? ''); ?>
                <div class="upload-box <?= !empty($profileUrl) ? 'has-file' : '' ?>" id="box-photo" data-photo-box="photo" data-custom-preview>
                    <button type="button" class="btn  btn-danger btn-remove-img <?= !empty($profileUrl) ? '' : 'd-none' ?>" id="btn-remove-photo" data-target="photo" data-flag="remove_profile">
                        <i class="bi bi-trash"></i> ລົບຮູບ
                    </button>
                    <div class="upload-content text-center <?= !empty($profileUrl) ? 'd-none' : '' ?>" id="content-photo">
                        <div class="icon-circle">
                            <i class="bi bi-camera"></i>
                        </div>
                        <h6 class="mb-1">ຖ່າຍຮູບ ຫຼື Upload</h6>
                        <p class="text-muted-custom mb-2">ເລືອກວິທີອັບໂຫຼດ</p>
                        <div class="upload-actions">
                            <button type="button" class="btn btn-success btn-pick" data-target="photo" data-mode="camera">
                                <i class="bi bi-camera-fill me-1"></i> ຖ່າຍຮູບ
                            </button>
                            <button type="button" class="btn btn-outline-success btn-pick" data-target="photo" data-mode="gallery">
                                <i class="bi bi-images me-1"></i> ເລືອກຈາກຄັງຮູບ
                            </button>
                        </div>
                    </div>
                    <div class="preview-container <?= !empty($profileUrl) ? '' : 'd-none' ?> text-center" id="preview-box-photo">
                        <img <?= !empty($profileUrl) ? 'src="' . htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') . '"' : '' ?> class="preview-img mb-2" id="img-preview-photo">
                        <p class="text-success small mb-2"><i class="bi bi-check-circle-fill"></i> ເລືອກຮູບແລ້ວ</p>
                        <div class="upload-actions">
                            <button type="button" class="btn btn-outline-success btn-pick" data-target="photo" data-mode="camera">
                                <i class="bi bi-camera-fill me-1"></i> ຖ່າຍໃໝ່
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-pick" data-target="photo" data-mode="gallery">
                                <i class="bi bi-images me-1"></i> ເລືອກໃໝ່
                            </button>
                        </div>
                    </div>
                    <!-- ຊ່ອງເລືອກຈາກຄັງຮູບ / ໄຟລ໌ (ໃຊ້ accept="image/*" ລ້ວນ ເພື່ອໃຫ້ iOS ແປງ HEIC ເປັນ JPEG ໃຫ້ອັດຕະໂນມັດ) -->
                    <input type="file" name="profile" id="file-photo" accept="image/*" class="file-hidden">
                    <!-- ຊ່ອງຖ່າຍຮູບໂດຍກົງ: capture ບັງຄັບເປີດກ້ອງຫຼັງ (Android + iOS 14.3 ຂຶ້ນໄປ) -->
                    <input type="file" name="profile" id="cam-photo" accept="image/*" capture="environment" class="file-hidden" disabled>
                    <input type="hidden" name="remove_profile" id="remove_profile" value="0">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-bold mb-2">ຮູບເອກະສານຢືນຢັນຕົວຕົນ <span class="asterisk">*</span>: </label>
                <?php $idProfileUrl = getImgUrl($row['id_profile'] ?? ''); ?>
                <div class="upload-box <?= !empty($idProfileUrl) ? 'has-file' : '' ?>" id="box-interview-form" data-photo-box="interview-form" data-custom-preview>
                    <button type="button" class="btn  btn-danger btn-remove-img <?= !empty($idProfileUrl) ? '' : 'd-none' ?>" id="btn-remove-interview-form" data-target="interview-form" data-flag="remove_id_profile">
                        <i class="bi bi-trash"></i> ລົບຮູບ
                    </button>
                    <div class="upload-content text-center <?= !empty($idProfileUrl) ? 'd-none' : '' ?>" id="content-interview-form">
                        <div class="icon-circle">
                            <i class="bi bi-camera"></i>
                        </div>
                        <h6 class="mb-1">ຖ່າຍຮູບ ຫຼື Upload</h6>
                        <p class="text-muted-custom mb-2">ເລືອກວິທີອັບໂຫຼດ</p>
                        <div class="upload-actions">
                            <button type="button" class="btn btn-success btn-pick" data-target="interview-form" data-mode="camera">
                                <i class="bi bi-camera-fill me-1"></i> ຖ່າຍຮູບ
                            </button>
                            <button type="button" class="btn btn-outline-success btn-pick" data-target="interview-form" data-mode="gallery">
                                <i class="bi bi-images me-1"></i> ເລືອກຈາກຄັງຮູບ
                            </button>
                        </div>
                    </div>
                    <div class="preview-container <?= !empty($idProfileUrl) ? '' : 'd-none' ?> text-center" id="preview-box-interview-form">
                        <img <?= !empty($idProfileUrl) ? 'src="' . htmlspecialchars($idProfileUrl, ENT_QUOTES, 'UTF-8') . '"' : '' ?> class="preview-img mb-2" id="img-preview-interview-form">
                        <p class="text-success small mb-2"><i class="bi bi-check-circle-fill"></i> ເລືອກຮູບແລ້ວ</p>
                        <div class="upload-actions">
                            <button type="button" class="btn btn-outline-success btn-pick" data-target="interview-form" data-mode="camera">
                                <i class="bi bi-camera-fill me-1"></i> ຖ່າຍໃໝ່
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-pick" data-target="interview-form" data-mode="gallery">
                                <i class="bi bi-images me-1"></i> ເລືອກໃໝ່
                            </button>
                        </div>
                    </div>
                    <input type="file" name="id_profile" id="file-interview-form" accept="image/*" class="file-hidden">
                    <input type="file" name="id_profile" id="cam-interview-form" accept="image/*" capture="environment" class="file-hidden" disabled>
                    <input type="hidden" name="remove_id_profile" id="remove_id_profile" value="0">
                </div>
            </div>
        </div>
    </div>

    <div class="section-head">
        <i class="bi bi-chat-left-text-fill me-2"></i>ໝາຍເຫດເພີ່ມເຕີມ
    </div>
    <div class="p-3">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-bold mb-2">ໝາຍເຫດ</label>
                <textarea name="da_remark" rows="3" class="form-control form-control"><?= h($row['da_remark']) ?></textarea>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 px-3 py-2 border-top" style="background:#fafcfa;border-color:var(--green-border)!important;">
        <a href="<?= (isset($_SESSION['customer_id']) || isset($_SESSION['user_id'])) ? '../user_menu.php' : '../list_data_entry.php' ?>" class="btn btn-danger px-4">
            <i class="bi bi-x-lg me-1"></i> ຍົກເລີກ
        </a>
        <button type="submit" class="btn btn-success px-4">
            <i class="bi bi-floppy me-1"></i> ບັນທຶກ
        </button>
    </div>

</form>
</div>
    </div>
    <div class="apims-toasts" id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/data_entry.js?v=<?= filemtime(__DIR__ . '/js/data_entry.js') ?>"></script>
    <script src="js/insert.js?v=<?= filemtime(__DIR__ . '/js/insert.js') ?>"></script>
    <script src="js/photo_upload.js?v=<?= filemtime(__DIR__ . '/js/photo_upload.js') ?>"></script>
    <script>
        function toggleSidebar() {
            const width = window.innerWidth;
            const body = document.body;
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (width > 768) {
                body.classList.toggle('sidebar-collapsed');
            } else {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('show');
            }
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-times-circle'
            };
            const toast = document.createElement('div');
            toast.className = `apims-toast ${type}`;
            toast.innerHTML = `
        <i class="fas ${icons[type] || 'fa-info-circle'} toast-icon"></i>
        <span class="toast-msg">${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">
          <i class="fas fa-times"></i>
        </button>`;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        }

        // ໝາຍເຫດ: ລະບົບຖ່າຍຮູບ / ເລືອກຮູບ / ລົບຮູບ ຢູ່ໃນ js/photo_upload.js
        // ເພື່ອໃຊ້ຮ່ວມກັບ vacancy_add.php

        $(document).ready(function() {

            // ໝາຍເຫດ: ໜ້ານີ້ບໍ່ມີ handler ສະຫຼັບ spouse_id ຕາມສະຖານະການແຕ່ງງານ
            // ເພາະ <select> ສະຖານະຖືກ disabled ໄວ້ໂດຍເຈດຕະນາ (ຄ່າຈິງຢູ່ໃນ hidden input
            // ຊື່ status) ຜູ້ໃຊ້ແກ້ໄຂເອງບໍ່ໄດ້ ຈຶ່ງບໍ່ມີ event change ເກີດຂຶ້ນ
            // ການສະແດງ/ເຊື່ອງ #spouse_id_div ຖືກກຳນົດຄັ້ງດຽວຕອນ render ຈາກ PHP ແລ້ວ
            // (ຖ້າຕໍ່ໄປຢາກໃຫ້ແກ້ໄຂສະຖານະໄດ້ ຕ້ອງເອົາ disabled ອອກ ໃສ່ name="status"
            //  ໃຫ້ select ແລະ ລຶບ hidden input ຖິ້ມ ແລ້ວຄ່ອຍເພີ່ມ handler ຄືນ)

            function searchEmpId() {
                var typeIn = $('select[name="type_in"]').val();
                if (typeIn !== 'ກັບຄືນໄປອີກ') return;

                var familyBookNo = $('input[name="family_book_no"]').val();
                var idNo = $('input[name="id_no"]').val();
                var passport = $('input[name="passport"]').val();

                if (!familyBookNo && !idNo && !passport) return;

                $.ajax({
                    type: 'POST',
                    url: 'get/get_emp_id.php',
                    data: {
                        family_book_no: familyBookNo,
                        id_no: idNo,
                        passport: passport
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.emp_id) {
                            $('input[name="emp_id"]').val(res.emp_id);
                        }
                    }
                });
            }

            // Show/Hide emp_id field based on job entry type
            $('select[name="type_in"]').on('change', function() {
                var empDiv = $('#emp_id_div');
                if ($(this).val() === 'ກັບຄືນໄປອີກ') {
                    empDiv.show();
                    searchEmpId();
                } else {
                    empDiv.hide();
                    $('input[name="emp_id"]').val('');
                }
            });

            // Auto-search emp_id when identification numbers change
            $('input[name="family_book_no"], input[name="id_no"], input[name="passport"]').on('blur change', function() {
                searchEmpId();
            });
        });
    </script>
</body>

</html>