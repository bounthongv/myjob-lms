<?php
require '../../connect.php';

// ===================================================
// ຟັງຊັນຊ່ວຍດຶງຄ່າຈາກ POST (ຖ້າບໍ່ມີ ໃຫ້ເປັນ null)
// ===================================================
function getPost($key) {
    return isset($_POST[$key]) && $_POST[$key] !== "" ? $_POST[$key] : null;
}

// ===================================================
// ຟັງຊັນຊ່ວຍຕັດເຄື່ອງໝາຍ Comma ອອກ ສຳລັບຄ່າຕົວເລກ
// ===================================================
function clearComma($value) {
    if ($value === null) {
        return null;
    }
    return str_replace(",", "", $value);
}

// ===================================================
// ຟັງຊັນຊ່ວຍອັບໂຫລດໄຟລ໌ (ໃຊ້ໄດ້ທັງ Insert ແລະ Update)
// ===================================================
function uploadFile($fieldName, $oldValue = null) {

    $uploadDir = "/var/www/html/job/file/korea/uploads/";
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return $oldValue;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
    $newFileName = $fieldName . "_" . time() . "_" . uniqid() . "." . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {

        if ($oldValue && file_exists($oldValue)) {
            unlink($oldValue);
        }

        return $newFileName;
    }

    return $oldValue;
}

// ===================================================
// ຮັບຄ່າ sub ເພື່ອກຳນົດວ່າຈະ insert ຫຼື update
// ===================================================
$sub = getPost("sub"); // "insert" ຫຼື "update"
$id  = getPost("id");
$passport = getPost("passport");

$sql    = "";
$params = [];
$msg    = "";

// ===================================================
// ກວດສອບ Passport ຊ້ຳກັນ ກ່ອນບັນທຶກ
// ===================================================
if ($passport) {

    if ($sub === "insert") {
        // ກວດວ່າມີ Passport ນີ້ຢູ່ໃນຕາຕະລາງແລ້ວບໍ່
        $sqlCheck = "SELECT id FROM data_entry_korea WHERE passport = :passport";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(":passport", $passport);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            echo json_encode([
                'message' => 'ເລກ Passport ນີ້ຖືກນຳໃຊ້ໄປແລ້ວ ກະລຸນາກວດສອບຄືນ',
                'sts' => 'error'
            ]);
            exit;
        }

    } elseif ($sub === "update") {
        // ກວດວ່າມີ Passport ນີ້ຢູ່ໃນ Row ອື່ນ (ບໍ່ແມ່ນ id ຕົນເອງ) ຫຼືບໍ່
        $sqlCheck = "SELECT id FROM data_entry_korea WHERE passport = :passport AND id != :id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(":passport", $passport);
        $stmtCheck->bindParam(":id", $id);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            echo json_encode([
                'message' => 'ເລກ Passport ນີ້ຖືກນຳໃຊ້ໄປແລ້ວ ກະລຸນາກວດສອບຄືນ',
                'sts' => 'error'
            ]);
            exit;
        }
    }
}

// ===================================================
// ດຶງຂໍ້ມູນເກົ່າຂອງໄຟລ໌ (ໃຊ້ສະເພາະຕອນ update)
// ===================================================
$oldData = [
    'profile' => null, 'file_form' => null, 'doc_passport' => null,
    'doc_farmer_cert' => null, 'doc_labor_contract' => null,
    'doc_census' => null, 'doc_collateral' => null,'id_profile' => null
];

if ($sub === "update" && $id) {
    $sqlOld = "SELECT profile, file_form, doc_passport, doc_farmer_cert,
                      doc_labor_contract, doc_census, doc_collateral,id_profile
               FROM data_entry_korea WHERE id = :id";
    $stmtOld = $conn->prepare($sqlOld);
    $stmtOld->bindParam(":id", $id);
    $stmtOld->execute();
    $fetched = $stmtOld->fetch(PDO::FETCH_ASSOC);

    if ($fetched) {
        $oldData = $fetched;
    }
}

// ===================================================
// ດຶງຄ່າຈາກຟອມທັງໝົດ (ໃຊ້ຮ່ວມກັນທັງ insert ແລະ update)
// ===================================================
$data = [
    "interview_date"       => getPost("interview_date"),
    "lname_eng"           => getPost("lname_eng"),
    "fname_eng"           => getPost("fname_eng"),
    "nickname"            => getPost("nickname"),
    "fname"               => getPost("fname"),
    "lname"               => getPost("lname"),
    "phone1"              => getPost("phone1"),
    "phone2"              => getPost("phone2"),
    "fam_phone"           => getPost("fam_phone"),
    "nationality"         => getPost("nationality"),
    "dob"                 => getPost("dob"),
    "age"                 => getPost("age"),
    "gender"              => getPost("gender"),
    "status"              => getPost("status"),
    "weight"              => getPost("weight"),
    "height"              => getPost("height"),
    "family_book_no"      => getPost("family_book_no"),
    "family_book_date"    => getPost("family_book_date"),
    "father"              => getPost("father"),
    "mother"              => getPost("mother"),
    "unit"                => getPost("unit"),
    "home"                => getPost("home"),
    "passport"            => $passport,
    "issue_date"          => getPost("issue_date"),
    "exp_date"            => getPost("exp_date"),
    "driver"              => getPost("driver"),
    "shirt_size"          => getPost("shirt_size"),
    "labor_type"          => getPost("labor_type"),
    "eth"                 => getPost("eth"),
    "agricu"              => getPost("agricu"),
    "interview_location"  => getPost("interview_location"),
    "job"                 => getPost("job"),
    "interview_name"      => getPost("interview_name"),
    "list_type"           => getPost("list"),

    "pro_id"    => getPost("pro_id"),
    "dis_id"    => getPost("dis_id"),
    "vill_id"   => getPost("vill_id"),
    "pro_id_b"  => getPost("pro_id_b"),
    "dis_id_b"  => getPost("dis_id_b"),
    "vill_id_b" => getPost("vill_id_b"),

    "profile"            => uploadFile("profile", $oldData['profile']),
    "file_form"          => uploadFile("file_form", $oldData['file_form']),
    "doc_passport"       => uploadFile("doc_passport", $oldData['doc_passport']),
    "doc_farmer_cert"    => uploadFile("doc_farmer_cert", $oldData['doc_farmer_cert']),
    "doc_labor_contract" => uploadFile("doc_labor_contract", $oldData['doc_labor_contract']),
    "doc_census"         => uploadFile("doc_census", $oldData['doc_census']),
    "doc_collateral"     => uploadFile("doc_collateral", $oldData['doc_collateral']),
    "id_profile"     => uploadFile("id_profile", $oldData['id_profile']),

    "heal_date"   => getPost("heal_date"),
    "diagnose"    => getPost("diagnose"),
    "clinic"      => getPost("clinic"),
    "cli_date"    => getPost("cli_date"),
    "check_up"    => clearComma(getPost("check_up")),
    "heal_date2"  => getPost("heal_date2"),
    "check_up2"   => clearComma(getPost("check_up2")),
    "heal_date3"  => getPost("heal_date3"),
    "check_up3"   => clearComma(getPost("check_up3")),
    "heal_remark" => getPost("heal_remark"),
    "heal_sts"    => getPost("heal_sts"),

    "pay_sts"   => getPost("pay_sts"),
    "labor_fee" => clearComma(getPost("labor_fee")),

    "coll_sts"   => getPost("coll_sts"),
    "coll_type"  => getPost("coll_type"),
    "coll_owner" => getPost("coll_owner"),
    "coll_area"  => getPost("coll_area"),
    "coll_no"    => getPost("coll_no"),
    "coll_date"  => getPost("coll_date"),
    "coll_value" => clearComma(getPost("coll_value")),
    "coll_pro"   => getPost("coll_pro"),
    "coll_dis"   => getPost("coll_dis"),
    "coll_vill"  => getPost("coll_vill"),
    "coll_unit"  => getPost("coll_unit"),
    "coll_map"   => getPost("coll_map"),

    "gua_relation"    => getPost("gua_relation"),
    "gua_fname"       => getPost("gua_fname"),
    "gua_phone"       => getPost("gua_phone"),
    "gua_dob"         => getPost("gua_dob"),
    "gua_nationality" => getPost("gua_nationality"),
    "gua_job"         => getPost("gua_job"),
    "gua_age"         => getPost("gua_age"),
    "gua_gender"      => getPost("gua_gender"),
    "gua_pro"         => getPost("gua_pro"),
    "gua_book"        => getPost("gua_book"),
    "gua_book_date"   => getPost("gua_book_date"),
    "gua_dis"         => getPost("gua_dis"),
    "gua_unit"        => getPost("gua_unit"),
    "gua_home"        => getPost("gua_home"),
    "gua_vill"        => getPost("gua_vill"),

    "da_remark" => getPost("da_remark"),
    // new update
    "data_id" => getPost("data_id"),
    "type_check" => getPost("type_check"),
    "vacancy_check" => getPost("vacancy_check"),
    "id_no" => getPost("id_no"),
    "type_job" => getPost("type_job"),
    "place_job" => getPost("place_job"),
    "type_in" => getPost("type_in"),
    "race" => getPost("race"),
    "religion" => getPost("religion"),
    "timezon" => getPost("timezon"),
    "emp_id" => getPost("emp_id"),
    "sts_tb" => "vacancy",
];

// ===================================================
// 1. ກໍລະນີ Insert (ຂໍ້ມູນໃໝ່)
// ===================================================
if ($sub === "insert") {

    $columns      = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));

    $sql = "INSERT INTO data_entry_korea ($columns) VALUES ($placeholders)";

    foreach ($data as $key => $value) {
        $params[":" . $key] = $value;
    }

    $msg = "ບັນທຶກຂໍ້ມູນສຳເລັດ";

// ===================================================
// 2. ກໍລະນີ Update (ແກ້ໄຂຂໍ້ມູນເກົ່າ)
// ===================================================
} elseif ($sub === "update") {

    if (!$id) {
        echo json_encode(['message' => 'ບໍ່ພົບ ID ຂໍ້ມູນທີ່ຈະແກ້ໄຂ', 'sts' => 'error']);
        exit;
    }

    $setClause = "";
    foreach ($data as $key => $value) {
        $setClause .= "$key = :$key, ";
        $params[":" . $key] = $value;
    }
    $setClause = rtrim($setClause, ", ");

    $sql = "UPDATE data_entry_korea SET $setClause WHERE id = :id";
    $params[":id"] = $id;

    $msg = "ແກ້ໄຂຂໍ້ມູນສຳເລັດ";
}

// ===================================================
// 3. ທຳງານຄຳສັ່ງ SQL ທີ່ເລືອກຜ່ານຕົວແປ $sub
// ===================================================
if (!empty($sql)) {

    try {
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            echo json_encode([
                'message' => $msg,
                'sts' => 'success'
            ]);
        } else {
            echo json_encode([
                'message' => 'ບໍ່ສາມາດບັນທຶກຂໍ້ມູນໄດ້',
                'sts' => 'error'
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode([
            'message' => $e->getMessage(),
            'sts' => 'error'
        ]);
    }

} else {
    echo json_encode([
        'message' => 'ບໍ່ພົບຄຳສັ່ງ sub (insert/update) ທີ່ຖືກຕ້ອງ',
        'sts' => 'error'
    ]);
}
?>