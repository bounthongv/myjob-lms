<!DOCTYPE html>
<?php
// include('../header.php');
include('../connect.php');
$vacancy_check = $_GET['vacancy_check'];
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
data.pro_id_b,
data.dis_id_b,
data.vill_id_b,
-- ຄ້ຳປະກັນ
pro_c.pro_name_lao as pro_name_c,
dis_c.dis_name_lao as dis_name_c,
vill_c.vill_name_lao as vill_name_c,
data.coll_pro,
data.coll_dis,
data.coll_vill,
-- ຜູ້ຄຳປະກັນ
pro_d.pro_name_lao as pro_name_d,
dis_d.dis_name_lao as dis_name_d,
vill_d.vill_name_lao as vill_name_d,
data.gua_pro,
data.gua_dis,
data.gua_vill
FROM data_entry_korea as data
LEFT JOIN province as pro ON data.pro_id=pro.pro_id
LEFT JOIN district as dis ON data.dis_id=dis.dis_id
LEFT JOIN village as vill ON data.vill_id=vill.vill_id

LEFT JOIN province as pro_b ON data.pro_id_b=pro_b.pro_id
LEFT JOIN district as dis_b ON data.dis_id_b=dis_b.dis_id
LEFT JOIN village as vill_b ON data.vill_id_b=vill_b.vill_id

LEFT JOIN province as pro_c ON data.coll_pro=pro_c.pro_id
LEFT JOIN district as dis_c ON data.coll_dis=dis_c.dis_id
LEFT JOIN village as vill_c ON data.coll_vill=vill_c.vill_id

LEFT JOIN province as pro_d ON data.gua_pro=pro_d.pro_id
LEFT JOIN district as dis_d ON data.gua_dis=dis_d.dis_id
LEFT JOIN village as vill_d ON data.gua_vill=vill_d.vill_id
WHERE vacancy_check = ?");
$sql->execute([$vacancy_check]);
$row = $sql->fetch(PDO::FETCH_ASSOC);
$sql_pro = $conn->prepare("SELECT * FROM province ORDER BY pro_id ASC");
$sql_pro->execute();
$pro = $sql_pro->fetchAll(PDO::FETCH_ASSOC);
?>
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
    <link rel="stylesheet" href="style.css?v=<?= fileatime('style.css') ?>">
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
            font-size: 12px;
            font-weight: 700;
            color: var(--green-mid);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .form-label {
            font-size: 12px;
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
    </style>
</head>
<body>
    <?php include('../menu.php'); ?>
    <div class="content">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.4rem; flex-wrap:wrap; gap:10px;">
    <div>
        <h5 style="font-size:19px; font-weight:700; color:#0f172a; margin:0;">
            <i class="bi bi-file-earmark-text me-2 text-primary"></i>Vacancy View
        </h5>
    </div>
</div>
<div class="card shadow-none" style="max-width:1920px;">

    <form method="POST" id="edit_vacancy" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?= $row['id'] ?>">
    <input type="hidden" name="sub" value="update">

    <div class="section-head">
        <i class="bi bi-person me-2"></i>ຂໍ້ມູນສ່ວນຕົວ
    </div>
    <div class="p-3">
        <div class="row g-3">
            <div class="col-12 col-sm-4">
                <label class="form-label">Register Date <span class="required">*</span></label>
                <input type="date" name="interview_date" class="form-control form-control-sm" value="<?= $row['interview_date'] ?>">
                <input type="hidden" name="id" class="form-control form-control-sm" value="<?= $row['id'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Eng Sure Name <span class="required">*</span></label>
                <input type="text" name="lname_eng" class="form-control form-control-sm" value="<?= $row['lname_eng'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Eng Name <span class="required">*</span></label>
                <input type="text" name="fname_eng" class="form-control form-control-sm" value="<?= $row['fname_eng'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Nickname <span class="required">*</span></label>
                <input type="text" name="nickname" class="form-control form-control-sm" value="<?= $row['nickname'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Lao Name <span class="required">*</span></label>
                <input type="text" name="fname" class="form-control form-control-sm" value="<?= $row['fname'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Lao Sure Name <span class="required">*</span></label>
                <input type="text" name="lname" class="form-control form-control-sm" value="<?= $row['lname'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Phone NO1 <span class="required">*</span></label>
                <input type="text" name="phone1" class="form-control form-control-sm" value="<?= $row['phone1'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Phone NO2 <span class="required">*</span></label>
                <input type="text" name="phone2" class="form-control form-control-sm" value="<?= $row['phone2'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Fam Phone NO <span class="required">*</span></label>
                <input type="text" name="fam_phone" class="form-control form-control-sm" value="<?= $row['fam_phone'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Nationality <span class="required">*</span></label>
                <input type="text" name="nationality" class="form-control form-control-sm" value="<?= $row['nationality'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Date of birth <span class="required">*</span></label>
                <input type="date" name="dob" id="dob" class="form-control form-control-sm" value="<?= $row['dob'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Age <span class="required">*</span></label>
                <input type="text" name="age" id="age" class="form-control form-control-sm" value="<?= $row['age'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Gender <span class="required">*</span></label>
                <select name="gender" class="form-select form-select-sm">
                    <option value="">ເລືອກ</option>
                    <option value="F" <?= $row['gender'] == 'F' ? 'selected' : '' ?>>ຍິງ</option>
                    <option value="M" <?= $row['gender'] == 'M' ? 'selected' : '' ?>>ຊາຍ</option>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Status <span class="required">*</span></label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">ເລືອກ</option>
                    <option value="SINGLE" <?= $row['status'] == 'SINGLE' ? 'selected' : '' ?>>SINGLE</option>
                    <option value="MARRIED" <?= $row['status'] == 'MARRIED' ? 'selected' : '' ?>>MARRIED</option>
                    <option value="DIVORCED" <?= $row['status'] == 'DIVORCED' ? 'selected' : '' ?>>DIVORCED</option>
                    <option value="MARRIED(COUPLE)" <?= $row['status'] == 'MARRIED(COUPLE)' ? 'selected' : '' ?>>MARRIED(COUPLE)</option>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Weight <span class="required">*</span></label>
                <input type="text" name="weight" class="form-control form-control-sm" value="<?= $row['weight'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Height <span class="required">*</span></label>
                <input type="text" name="height" class="form-control form-control-sm" value="<?= $row['height'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">family book NO <span class="required">*</span></label>
                <input type="text" name="family_book_no" class="form-control form-control-sm" value="<?= $row['family_book_no'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">family book Date <span class="required">*</span></label>
                <input type="date" name="family_book_date" class="form-control form-control-sm" value="<?= $row['family_book_date'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Father Name <span class="required">*</span></label>
                <input type="text" name="father" class="form-control form-control-sm" value="<?= $row['father'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Mother Name <span class="required">*</span></label>
                <input type="text" name="mother" class="form-control form-control-sm" value="<?= $row['mother'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Unit <span class="required">*</span></label>
                <input type="text" name="unit" class="form-control form-control-sm" value="<?= $row['unit'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Home NO <span class="required">*</span></label>
                <input type="text" name="home" class="form-control form-control-sm" value="<?= $row['home'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Passport NO <span class="required">*</span></label>
                <input type="text" name="passport" class="form-control form-control-sm" value="<?= $row['passport'] ?>" required>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Issue Date <span class="required">*</span></label>
                <input type="date" name="issue_date" class="form-control form-control-sm" value="<?= $row['issue_date'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Exp date <span class="required">*</span></label>
                <input type="date" name="exp_date" class="form-control form-control-sm" value="<?= $row['exp_date'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Driver License <span class="required">*</span></label>
                <select name="driver" class="form-select form-select-sm">
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
            <div class="col-12 col-sm-4">
                <label class="form-label">Shirt Size <span class="required">*</span></label>
                <select name="shirt_size" class="form-select form-select-sm">
                    <option value="S" <?= $row['shirt_size'] == 'S' ? 'selected' : '' ?>>S</option>
                    <option value="M" <?= $row['shirt_size'] == 'M' ? 'selected' : '' ?>>M</option>
                    <option value="L" <?= $row['shirt_size'] == 'L' ? 'selected' : '' ?>>L</option>
                    <option value="XL" <?= $row['shirt_size'] == 'XL' ? 'selected' : '' ?>>XL</option>
                    <option value="XXL" <?= $row['shirt_size'] == 'XXL' ? 'selected' : '' ?>>XXL</option>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Labor type <span class="required">*</span></label>
                <select name="labor_type" class="form-select form-select-sm">
                    <option value="New" <?= $row['labor_type'] == 'New' ? 'selected' : '' ?>>New</option>
                    <option value="Re-New" <?= $row['labor_type'] == 'Re-New' ? 'selected' : '' ?>>Re-New</option>
                    <option value="New(RC)" <?= $row['labor_type'] == 'New(RC)' ? 'selected' : '' ?>>New(RC)</option>
                    <option value="Re-entry" <?= $row['labor_type'] == 'Re-entry' ? 'selected' : '' ?>>Re-entry</option>
                    <option value="Re-employment" <?= $row['labor_type'] == 'Re-employment' ? 'selected' : '' ?>>Re-employment</option>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">ຊົນເຜົ່າ <span class="required">*</span></label>
                <select name="eth" class="form-select form-select-sm">
                    <?php
                    $eth_list = ["ລາວລຸ່ມ","ລາວເທິງ","ລາວສູງ","ມົ້ງ","ໄຕ","ຜູ້ໄທ","ລື້","ຍວນ","ຢັ້ງ","ແຊກ","ໄທເໜືອ","ກຶມມຸ","ກະຕາງ","ກະຕູ","ກຣຽງ","ກຣີ","ຂະແມ","ງວນ","ສາມຕ່າວ","ເຈັງ","ສະດາງ","ຊ່ວຍ","ຊິງມູນ","ຍະເຫີນ","ຕະໂອ້ຍ","ຕຣຽງ","ຕຣີ","ຕູມ","ແທ່ນ","ບິດ","ບຣູ","ເບຣົາ","ປະໂກະ","ໄປຣ","ຜ້ອງ","ມະກອງ","ມ້ອຍ","ຢຣຸ","ແຢະ","ລະເມດ","ລະວີ","ໂອຍ","ເອີດູ","ຮ່າຣັກ","ລາຫູ","ສີລາ","ຮ່າຍີ່","ໂລໂລ","ຫໍ້","ສິງສີລິ/ພູນ້ອຍ","ອິວມ້ຽນ"];
                    foreach ($eth_list as $opt): ?>
                        <option value="<?= $opt ?>" <?= $row['eth'] == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Agricultural experience <span class="required">*</span></label>
                <input type="text" name="agricu" id="agricu" class="form-control form-control-sm" value="<?= $row['agricu'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Interview Location <span class="required">*</span></label>
                <select name="interview_location" class="form-select form-select-sm" required>
                    <option value="Outside" <?= $row['interview_location'] == 'Outside' ? 'selected' : '' ?>>Outside</option>
                    <option value="Inside" <?= $row['interview_location'] == 'Inside' ? 'selected' : '' ?>>Inside</option>
                    <option value="Re-employment" <?= $row['interview_location'] == 'Re-employment' ? 'selected' : '' ?>>Re-employment</option>
                    <option value="NEW(RC)" <?= $row['interview_location'] == 'NEW(RC)' ? 'selected' : '' ?>>NEW(RC)</option>
                    <option value="Re-New" <?= $row['interview_location'] == 'Re-New' ? 'selected' : '' ?>>Re-New</option>
                    <option value="Online" <?= $row['interview_location'] == 'Online' ? 'selected' : '' ?>>Online</option>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Job <span class="required">*</span></label>
                <input type="text" name="job" class="form-control form-control-sm" value="<?= $row['job'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">Interview Name <span class="required">*</span></label>
                <input type="text" name="interview_name" class="form-control form-control-sm" value="<?= $row['interview_name'] ?>">
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">ແຮງງານ ມີຕົວເລືອກ <span class="required">*</span></label>
                <select name="list" class="form-select form-select-sm">
                    <option value="ຄົນດຽວ" <?= $row['list_type'] == 'ຄົນດຽວ' ? 'selected' : '' ?>>ຄົນດຽວ</option>
                    <option value="ຄູ່ຜົວ-ເມຍ" <?= $row['list_type'] == 'ຄູ່ຜົວ-ເມຍ' ? 'selected' : '' ?>>ຄູ່ຜົວ-ເມຍ</option>
                </select>
            </div>
        </div>
    </div>

    <hr class="m-0" style="border-color:var(--green-border);">
    <div class="section-head">
        <i class="bi bi-house-door-fill me-2"></i>ທີ່ຢູ່ປัดຈຸບັນ
    </div>
    <div class="p-3">
        <div class="row g-3">
            <div class="col-12 col-sm-4">
                <label class="form-label">ແຂວງ <span class="required">*</span></label>
                <select name="pro_id" id="pro_id" class="form-select form-select-sm">
                    <option value="">ເລືອກ</option>
                    <?php foreach ($pro as $proa): ?>
                        <option value="<?= $proa['pro_id'] ?>" <?= $row['pro_id'] == $proa['pro_id'] ? 'selected' : '' ?>><?= $proa['pro_name_lao'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">ເມືອງ <span class="required">*</span></label>
                <select name="dis_id" id="dis_id" class="form-select form-select-sm" data-selected="<?= $row['dis_id'] ?>">
                    <option value="<?= $row['dis_id'] ?>"><?= $row['dis_name_lao'] ?></option>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">ບ້ານ <span class="required">*</span></label>
                <select name="vill_id" id="vill_id" class="form-select form-select-sm" data-selected="<?= $row['vill_id'] ?>">
                    <option value="<?= $row['vill_id'] ?>"><?= $row['vill_name_lao'] ?></option>
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
            <div class="col-12 col-sm-4">
                <label class="form-label">ແຂວງ <span class="required">*</span></label>
                <select name="pro_id_b" id="pro_id_b" class="form-select form-select-sm">
                    <option value="">ເລືອກ</option>
                    <?php foreach ($pro as $proa): ?>
                        <option value="<?= $proa['pro_id'] ?>" <?= $row['pro_id_b'] == $proa['pro_id'] ? 'selected' : '' ?>><?= $proa['pro_name_lao'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">ເມືອງ <span class="required">*</span></label>
                <select name="dis_id_b" id="dis_id_b" class="form-select form-select-sm" data-selected="<?= $row['dis_id_b'] ?>">
                    <option value="<?= $row['dis_id_b'] ?>"><?= $row['dis_name_b'] ?></option>
                </select>
            </div>
            <div class="col-12 col-sm-4">
                <label class="form-label">ບ້ານ <span class="required">*</span></label>
                <select name="vill_id_b" id="vill_id_b" class="form-select form-select-sm" data-selected="<?= $row['vill_id_b'] ?>">
                    <option value="<?= $row['vill_id_b'] ?>"><?= $row['vill_name_b'] ?></option>
                </select>
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
                <textarea name="da_remark" rows="3" class="form-control form-control-sm"><?= $row['da_remark'] ?></textarea>
            </div>
        </div>
    </div>

    <!-- <div class="d-flex justify-content-end gap-2 px-3 py-2 border-top" style="background:#fafcfa;border-color:var(--green-border)!important;">
        <a href="../list_data_entry.php" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-x-lg me-1"></i> ຍົກເລີກ
        </a>
        <button type="submit" class="btn btn-sm btn-primary px-4">
            <i class="bi bi-floppy me-1"></i> ບັນທຶกຂໍ້ມູນ
        </button>
    </div> -->

</form>
</div>
    </div>
    <div class="apims-toasts" id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/data_entry.js?v=<?= filemtime('js/data_entry.js') ?>"></script>
    <!-- <script src="js/insert.js?v=<?= filemtime('js/insert.js') ?>"></script> -->
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
    </script>
</body>

</html>