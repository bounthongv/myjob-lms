<!DOCTYPE html>
<?php
// include('../header.php');
include('../connect.php');
$sql_pro = $conn->prepare("SELECT * FROM province ORDER BY pro_id ASC");
$sql_pro->execute();
$pro = $sql_pro->fetchAll(PDO::FETCH_ASSOC);
$y = date('y');
$sql_max = $conn->prepare("SELECT SUBSTRING(MAX(data_id),3,5) FROM data_entry_korea WHERE SUBSTRING(data_id,1,2) = '$y'");
$sql_max->execute();
$max = $sql_max->fetchColumn();
$number = 1;
$number = $max ? (int) $max + 1 : 1;
$data_id = $y.str_pad($number,5,'0',STR_PAD_LEFT);
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
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>Vacancy Add
                </h5>
            </div>
        </div>
        <div class="card shadow-none" style="max-width:1920px;">

            <form method="POST" id="save_vacancy" enctype="multipart/form-data">

                <div class="section-head">
                    <i class="bi bi-person me-2"></i>ຂໍ້ມູນສ່ວນຕົວ
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ເລກທີລົງທະບຽນ <span class="required">*</span></label>
                            <input type="text" name="data_id" class="form-control form-control-sm" value="<?= $data_id ?>" readonly>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ວັນທີ <span class="required">*</span></label>
                            <input type="date" name="interview_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ປະເພດລະບຸຕົວຕົນ </label>
                            <select name="type_check" id="type_check" class="form-select form-select-sm">
                                <option value="ສຳມະໂນຄົວ">ສຳມະໂນຄົວ</option>
                                <option value="ບັດປະຈຳຕົວ">ບັດປະຈຳຕົວ</option>
                                <option value="ພາດສະປອດ">ພາດສະປອດ</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ລະຫັດລະບຸຕົວຕົນ </label>
                            <input type="text" name="vacancy_check" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ຊື່ (ພາສາລາວ) </label>
                            <input type="text" name="fname" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ນາມສະກຸນ </label>
                            <input type="text" name="lname" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ຊື່ (ພາສາອັງກິດ) </label>
                            <input type="text" name="fname_eng" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ນາມສະກຸນ (ພາສາອັງກິດ) </label>
                            <input type="text" name="lname_eng" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ວັນເດືອນປີເກີດ </label>
                            <input type="date" name="dob" id="dob" class="form-control form-control-sm">
                            <input type="hidden" name="age" id="age" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ເພດ </label>
                            <select name="gender" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                                <option value="F">ຍິງ</option>
                                <option value="M">ຊາຍ</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ເບີໂທລະສັບ </label>
                            <input type="text" name="phone1" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ສັນຊາດ </label>
                            <input type="text" name="nationality" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ເຊື້ອຊາດ </label>
                            <input type="text" name="race" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ຊົນເຜົ່າ </label>
                            <select name="eth" class="form-select form-select-sm">
                                <option value="ລາວລຸ່ມ">ລາວລຸ່ມ</option>
                                <option value="ລາວເທິງ">ລາວເທິງ</option>
                                <option value="ລາວສູງ">ລາວສູງ</option>
                                <option value="ມົ້ງ">ມົ້ງ</option>
                                <option value="ໄຕ">ໄຕ</option>
                                <option value="ຜູ້ໄທ">ຜູ້ໄທ</option>
                                <option value="ລື້">ລື້</option>
                                <option value="ຍວນ">ຍວນ</option>
                                <option value="ຢັ້ງ">ຢັ້ງ</option>
                                <option value="ແຊກ">ແຊກ</option>
                                <option value="ໄທເໜືອ">ໄທເໜືອ</option>
                                <option value="ກຶມມຸ">ກຶມມຸ</option>
                                <option value="ກະຕາງ">ກະຕາງ</option>
                                <option value="ກະຕູ">ກະຕູ</option>
                                <option value="ກຣຽງ">ກຣຽງ</option>
                                <option value="ກຣີ">ກຣີ</option>
                                <option value="ຂະແມ">ຂະແມ</option>
                                <option value="ງວນ">ງວນ</option>
                                <option value="ສາມຕ່າວ">ສາມຕ່າວ</option>
                                <option value="ເຈັງ">ເຈັງ</option>
                                <option value="ສະດາງ">ສະດາງ</option>
                                <option value="ຊ່ວຍ">ຊ່ວຍ</option>
                                <option value="ຊິງມູນ">ຊິງມູນ</option>
                                <option value="ຍະເຫີນ">ຍະເຫີນ</option>
                                <option value="ຕະໂອ້ຍ">ຕະໂອ້ຍ</option>
                                <option value="ຕຣຽງ">ຕຣຽງ</option>
                                <option value="ຕຣີ">ຕຣີ</option>
                                <option value="ຕູມ">ຕູມ</option>
                                <option value="ແທ່ນ">ແທ່ນ</option>
                                <option value="ບິດ">ບິດ</option>
                                <option value="ບຣູ">ບຣູ</option>
                                <option value="ເບຣົາ">ເບຣົາ</option>
                                <option value="ປະໂກະ">ປະໂກະ</option>
                                <option value="ໄປຣ">ໄປຣ</option>
                                <option value="ຜ້ອງ">ຜ້ອງ</option>
                                <option value="ມະກອງ">ມະກອງ</option>
                                <option value="ມ້ອຍ">ມ້ອຍ</option>
                                <option value="ຢຣຸ">ຢຣຸ</option>
                                <option value="ແຢະ">ແຢະ</option>
                                <option value="ລະເມດ">ລະເມດ</option>
                                <option value="ລະວີ">ລະວີ</option>
                                <option value="ໂອຍ">ໂອຍ</option>
                                <option value="ເອີດູ">ເອີດູ</option>
                                <option value="ຮ່າຣັກ">ຮ່າຣັກ</option>
                                <option value="ລາຫູ">ລາຫູ</option>
                                <option value="ສີລາ">ສີລາ</option>
                                <option value="ຮ່າຍີ່">ຮ່າຍີ່</option>
                                <option value="ໂລໂລ">ໂລໂລ</option>
                                <option value="ຫໍ້">ຫໍ້</option>
                                <option value="ໂລໂລ">ໂລໂລ</option>
                                <option value="ສິງສີລິ/ພູນ້ອຍ">ສິງສີລິ/ພູນ້ອຍ</option>
                                <option value="ອິວມ້ຽນ">ອິວມ້ຽນ</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ສາສະໜາ </label>
                            <input type="text" name="religion" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ແຂວງ ປັດຈຸບັນ</label>
                            <select name="pro_id" id="pro_id" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                                <?php foreach ($pro as $proa): ?>
                                    <option value="<?= $proa['pro_id'] ?>"><?= $proa['pro_name_lao'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ເມືອງ </label>
                            <select name="dis_id" id="dis_id" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ບ້ານ </label>
                            <select name="vill_id" id="vill_id" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ເລກທີສຳມະໂນຄົວ</label>
                            <input type="text" name="family_book_no" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ເລກບັດປະຈຳຕົວ </label>
                            <input type="text" name="id_no" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ເລກທີປັດສະປອດ</label>
                            <input type="text" name="passport" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ແຂວງ ເກີດ</label>
                            <select name="pro_id_b" id="pro_id_b" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                                <?php foreach ($pro as $proa): ?>
                                    <option value="<?= $proa['pro_id'] ?>"><?= $proa['pro_name_lao'] ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ເມືອງ </label>
                            <select name="dis_id_b" id="dis_id_b" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ບ້ານ </label>
                            <select name="vill_id_b" id="vill_id_b" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-4">
                            <label class="form-label">ນ້ຳໜັກ </label>
                            <input type="text" name="weight" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ລວງສູງ </label>
                            <input type="text" name="height" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ສະຖານະການແຕ່ງງານ </label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                                <option value="SINGLE">SINGLE</option>
                                <option value="MARRIED">MARRIED</option>
                                <option value="DIVORCED">DIVORCED</option>
                                <option value="MARRIED(COUPLE)">MARRIED(COUPLE)</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ປະເພດວຽກ </label>
                            <select name="type_job" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                                <option value="ກະສິກຳ">ກະສິກຳ</option>
                                <option value="ອຸດສາຫະກຳ">ອຸດສາຫະກຳ</option>
                                <option value="ບໍລິການ">ບໍລິການ</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ສະຖານທີ່ເຮັດວຽກ </label>
                            <select name="place_job" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                                <option value="ການແຈ້ງ">ການແຈ້ງ</option>
                                <option value="ໃນຮົ່ມ">ໃນຮົ່ມ</option>
                                <option value="ໃນອາຄານ">ໃນອາຄານ</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-4">
                            <label class="form-label">ປະສົບການ </label>
                            <input type="text" name="agricu" id="agricu" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ປະເພດການເຂົ້າວຽກ </label>
                            <select name="place_job" class="form-select form-select-sm">
                                <option value="">ເລືອກ</option>
                                <option value="ເຂົ້າໃໝ່">ເຂົ້າໃໝ່</option>
                                <option value="ກັບຄືນໄປອີກ">ກັບຄືນໄປອີກ</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ລະຫັດນາຍຈ້າງ </label>
                            <input type="text" name="emp_id" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label">ຊ່ວງເວລາ </label>
                            <input type="text" name="timezon" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <div class="section-head">
                    <i class="bi bi-camera me-2"></i>Upload File
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold mb-2">ຮູບຖ່າຍເຄິ່ງຄີງ <span class="asterisk">*</span></label>
                            <div class="upload-box" id="box-photo" onclick="document.getElementById('file-photo').click()">
                                <div class="upload-content text-center" id="content-photo">
                                    <div class="icon-circle">
                                        <i class="bi bi-camera"></i>
                                    </div>
                                    <h6 class="mb-1">ຖ່າຍຮູບ ຫຼື Upload</h6>
                                    <p class="text-muted-custom mb-0">ຄລິກເພື່ອເລືອກ</p>
                                </div>
                                <div class="preview-container d-none text-center" id="preview-box-photo">
                                    <img src="" class="preview-img mb-2" id="img-preview-photo">
                                    <p class="text-success small mb-0"><i class="bi bi-check-circle-fill"></i> ເລືອກຮູບແລ້ວ (ຄລິກເພື່ອປ່ຽນ)</p>
                                </div>
                                <input type="file" name="profile" id="file-photo" accept="image/*" class="d-none">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-bold mb-2">ຮູບເອກະສານຢືນຢັນຕົວຕົນ <span class="asterisk">*</span></label>
                            <div class="upload-box" id="box-interview-form" onclick="document.getElementById('file-interview-form').click()">
                                <div class="upload-content text-center" id="content-interview-form">
                                    <div class="icon-circle">
                                        <i class="bi bi-camera"></i>
                                    </div>
                                    <h6 class="mb-1">ຖ່າຍຮູບ ຫຼື Upload</h6>
                                    <p class="text-muted-custom mb-0">ຄລິກເພື່ອເລືອກ</p>
                                </div>
                                <div class="preview-container d-none text-center" id="preview-box-interview-form">
                                    <img src="" class="preview-img mb-2" id="img-preview-interview-form">
                                    <p class="text-success small mb-0"><i class="bi bi-check-circle-fill"></i> ເລືອກຮູບແລ້ວ (ຄລິກເພື່ອປ່ຽນ)</p>
                                </div>
                                <input type="file" name="id_profile" id="file-interview-form" accept="image/*" class="d-none">
                            </div>
                        </div>

                    </div>
                </div>


                <div class="d-flex justify-content-end gap-2 px-3 py-2 border-top" style="background:#fafcfa;border-color:var(--green-border)!important;">
                    <a href="../" class="btn btn-sm btn-outline-secondary px-4">
                        <i class="bi bi-x-lg me-1"></i> ຍົກເລີກ
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary px-4">
                        <i class="bi bi-floppy me-1"></i> ບັນທຶกຂໍ້ມູນ
                    </button>
                </div>

            </form>
        </div>
    </div>
    <div class="apims-toasts" id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/data_entry.js?v=<?= filemtime('js/data_entry.js') ?>"></script>
    <script src="js/insert.js?v=<?= filemtime('js/insert.js') ?>"></script>
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