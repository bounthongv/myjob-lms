<?php
// ບໍ່ສະແດງ error ດິບໆອອກທາງໜ້າຈໍ (path ຂອງ server, query SQL, ຂໍ້ຄວາມ exception
// ອາດຮົ່ວອອກໄປໄດ້) — ບັນທຶກລົງ log ຝັ່ງ server ແທນ ໃຫ້ admin ໄປກວດເບິ່ງເອງ
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "admin";
$password = "Sql_admin@#2024";
date_default_timezone_set('Asia/bangkok');

try {
    $conn = new PDO("mysql:host=$servername;dbname=job", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // ບັນທຶກລາຍລະອຽດຈິງໄວ້ໃນ log ຂອງ server (ບໍ່ສະແດງໃຫ້ຜູ້ໃຊ້ເຫັນ)
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(503);

    // ຢຸດການເຮັດວຽກທັນທີ ບໍ່ດັ່ງນັ້ນໜ້າທີ່ include ໄຟລ໌ນີ້ຈະໄປ error ຊ້ຳ
    // ຕອນເອີ້ນ $conn->prepare(...) ເພາະ $conn ບໍ່ໄດ້ຖືກສ້າງ (fatal error ດິບໆອອກໜ້າຈໍ)
    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    if ($isAjax) {
        // ໜ້າທີ່ເອີ້ນຜ່ານ AJAX ລໍຖ້າ JSON ຢູ່ ຖ້າສົ່ງ HTML ໄປຈະ parse ບໍ່ໄດ້
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'message' => 'ລະບົບບໍ່ສາມາດເຊື່ອມຕໍ່ຖານຂໍ້ມູນໄດ້ໃນຂະນະນີ້',
            'sts' => 'error',
            'status' => 'error',
            'found' => false
        ]);
        exit;
    }

    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>ລະບົບບໍ່ສາມາດເຊື່ອມຕໍ່ຖານຂໍ້ມູນໄດ້ໃນຂະນະນີ້</h2><p>ກະລຸນາລອງໃໝ່ພາຍຫຼັງ</p></div>");
}
?>