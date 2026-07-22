<?php
// ກຳນົດ Header ໃຫ້ຕອບກັບເປັນ JSON
header("Content-Type: application/json; charset=utf-8");

require_once "../connect.php";

// ຮັບຄ່າ passport ທີ່ສົ່ງມາຈາກ AJAX (ຮອງຮັບທັງ POST)
$passport = isset($_POST['passport']) ? trim($_POST['passport']) : "";

// ກວດສອບຄ່າວ່າງ
if ($passport === "") {
    echo json_encode([
        "status"  => "empty",
        "message" => "ກະລຸນາປ້ອນ ລະຫັດລະບຸຕົວຕົນ"
    ]);
    exit;
}

// ຄົ້ນຫາຂໍ້ມູນຈາກຕາຕະລາງ workers ດ້ວຍເລກ passport
$sql = "SELECT id, vacancy_check, fname, lname FROM data_entry_korea WHERE vacancy_check = :vacancy_check AND sts_tb = 'vacancy' LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bindParam(":vacancy_check", $passport, PDO::PARAM_STR);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);

// ຖ້າພົບຂໍ້ມູນ ໃຫ້ສົ່ງ status found ພ້ອມຂໍ້ມູນຜູ້ໃຊ້
if ($row) {
    echo json_encode([
        "status"   => "found",
        "message"  => "ພົບຂໍ້ມູນໃນລະບົບແລ້ວ",
        "vacancy_check" => $row['vacancy_check'],
        "name"     => $row['fname'] ?? "",
        "surname"  => $row['lname'] ?? ""
    ]);
} else {
    // ຖ້າບໍ່ພົບຂໍ້ມູນ
    echo json_encode([
        "status"  => "not_found",
        "message" => "ບໍ່ພົບຂໍ້ມູນນີ້ໃນລະບົບ"
    ]);
}
?>
