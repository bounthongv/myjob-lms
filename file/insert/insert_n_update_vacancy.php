<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../../connect.php';

// ===================================================
// ຟັງຊັນຊ່ວຍດຶງຄ່າຈາກ POST (ຖ້າບໍ່ມີ ໃຫ້ເປັນ null)
// ===================================================
function getPost($key) {
    if (!isset($_POST[$key]) || $_POST[$key] === "") {
        return null;
    }
    $val = trim($_POST[$key]);
    // ໝາຍເຫດ: ບໍ່ໃສ່ htmlspecialchars() ຢູ່ບ່ອນນີ້ອີກຕໍ່ໄປ — ໃຫ້ escape ຕອນສະແດງຜົນ
    // (ໜ້າ vacancy_edit.php) ແທນ ບໍ່ດັ່ງນັ້ນທຸກຄັ້ງທີ່ບັນທຶກຊ້ຳ ຄ່າຈະຖືກ encode ຊ້ອນກັນ
    // ໄປເລື່ອຍໆ (' -> &#039; -> &amp;#039; -> ...) strip_tags() ຍັງເກັບໄວ້ເພື່ອກັນ
    // ການໃສ່ tag HTML/script ດິບໆເຂົ້າໄປໃນຖານຂໍ້ມູນ
    return strip_tags($val);
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
// ຟັງຊັນຊ່ວຍບີບອັດຮູບພາບ (ຫຼຸດຂະໜາດຮູບຈາກໂທລະສັບ)
// ===================================================
// ນີ້ເປັນ fallback ຝັ່ງ server — ຮູບສ່ວນໃຫຍ່ຖືກບີບອັດຝັ່ງ browser ມາກ່ອນແລ້ວ
// (js/photo_upload.js ບີບໃຫ້ບໍ່ເກີນ 100KB ແລະ ແປງເປັນ JPEG ສະເໝີ)
// ບ່ອນນີ້ຈຶ່ງເຮັດວຽກຈິງສະເພາະກໍລະນີ browser ເກົ່າ ຫຼື ຜູ້ໃຊ້ປິດ JavaScript
// (ບໍ່ໄດ້ຜ່ານການບີບອັດຝັ່ງ browser ມາກ່ອນ) ຫຼືຄ່າ default ຂອງ browser ບີບແລ້ວຍັງໃຫຍ່ຢູ່
function compressImage($sourcePath, $maxWidth = 1600, $targetBytes = 102400) {

    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }

    $info = @getimagesize($sourcePath);
    if (!$info) return false;

    $mime = $info['mime'];
    if ($mime !== 'image/jpeg' && $mime !== 'image/png') return false;

    // ນ້ອຍກວ່າເປົ້າໝາຍ (100KB) ຢູ່ແລ້ວ ບໍ່ຕ້ອງບີບ
    if (filesize($sourcePath) <= $targetBytes) {
        return false;
    }

    list($origW, $origH) = $info;

    // ---------------------------------------------------------------
    // PNG: ໃຊ້ວິທີບີບອັດແບບ lossless (imagepng) ບໍ່ແປງເປັນ JPEG
    // ເພາະນາມສະກຸນໄຟລ໌ (.png) ຖືກຕັດສິນໃຈໄວ້ກ່ອນແລ້ວໃນ uploadFile() ແລະຖືກເກັບ
    // ໄວ້ໃນຖານຂໍ້ມູນ ຖ້າແປງເນື້ອຫາເປັນ JPEG ແຕ່ນາມສະກຸນຍັງເປັນ .png ຈະເຮັດໃຫ້
    // header Content-Type ໃນ get_image.php ຜິດຈາກເນື້ອຫາຈິງ — ຈຶ່ງບໍ່ຮັບປະກັນ
    // ເປົ້າໝາຍ 100KB ສຳລັບ PNG ຮູບຖ່າຍ (ພາບຖ່າຍທີ່ບີບແບບ lossless ມັກຈະໃຫຍ່ກວ່ານັ້ນສະເໝີ)
    // ໃນທາງປະຕິບັດ ກໍລະນີນີ້ພົບໜ້ອຍຫຼາຍ ເພາະ browser ບີບແລະແປງເປັນ JPEG ໃຫ້ກ່ອນສົ່ງມາແລ້ວ
    // ---------------------------------------------------------------
    if ($mime === 'image/png') {
        $ratio = min($maxWidth / $origW, 1);
        $newW = max((int)round($origW * $ratio), 1);
        $newH = max((int)round($origH * $ratio), 1);

        $srcImage = @imagecreatefrompng($sourcePath);
        if (!$srcImage) return false;

        $dstImage = imagecreatetruecolor($newW, $newH);
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagepng($dstImage, $sourcePath, 9);

        imagedestroy($srcImage);
        imagedestroy($dstImage);
        return true;
    }

    // ---------------------------------------------------------------
    // JPEG: ວົນລອງຫຼຸດຂະໜາດ+ຄຸນະພາບເທື່ອລະຂັ້ນ ຈົນກວ່າຈະໄດ້ບໍ່ເກີນ $targetBytes
    // ໃຊ້ ob_start()/imagejpeg(null) ເພື່ອບີບອັດໃນ memory ບໍ່ຕ້ອງຂຽນ disk ຊ້ຳໆ
    // ---------------------------------------------------------------
    $srcImage = @imagecreatefromjpeg($sourcePath);
    if (!$srcImage) return false;

    $scaleSteps   = [1, 0.85, 0.7, 0.55, 0.42, 0.32];
    $qualitySteps = [80, 70, 60, 50, 40, 30];

    $bestData = null;
    $bestSize = null;

    foreach ($scaleSteps as $scale) {
        $ratio = min($maxWidth / $origW, 1) * $scale;
        $newW  = max((int)round($origW * $ratio), 40);
        $newH  = max((int)round($origH * $ratio), 40);

        $dstImage = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $hitTarget = false;

        foreach ($qualitySteps as $q) {
            ob_start();
            imagejpeg($dstImage, null, $q);
            $data = ob_get_clean();
            $size = strlen($data);

            if ($bestSize === null || $size < $bestSize) {
                $bestSize = $size;
                $bestData = $data;
            }

            if ($size <= $targetBytes) {
                $hitTarget = true;
                break;
            }
        }

        imagedestroy($dstImage);
        if ($hitTarget) break;
    }

    imagedestroy($srcImage);

    if ($bestData === null) return false;

    file_put_contents($sourcePath, $bestData);
    return true;
}

// ===================================================
// ຟັງຊັນຊ່ວຍລຶບໄຟລ໌ເກົ່າອອກຈາກໂຟນເດີ uploads
// ===================================================
function deleteUploadedFile($fileName) {

    if (empty($fileName)) {
        return;
    }

    // ປ້ອງກັນການລຶບໄຟລ໌ນອກໂຟນເດີ uploads (path traversal)
    if (basename($fileName) !== $fileName) {
        return;
    }

    $dirs = [
        "/var/www/html/job/file/korea/uploads/",
        __DIR__ . "/../uploads/",
        __DIR__ . "/../korea/uploads/",
    ];

    foreach ($dirs as $dir) {
        $path = $dir . $fileName;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

// ===================================================
// ຟັງຊັນຊ່ວຍອັບໂຫລດໄຟລ໌ (ໃຊ້ໄດ້ທັງ Insert ແລະ Update)
// ===================================================
function uploadFile($fieldName, $oldValue = null) {

    $uploadDir = "/var/www/html/job/file/korea/uploads/";
    if (!is_dir($uploadDir)) {
        $uploadDir = __DIR__ . "/../uploads/";
    }

    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {

        // ກໍລະນີຜູ້ໃຊ້ກົດປຸ່ມລຶບຮູບ ແລະ ບໍ່ໄດ້ເລືອກໄຟລ໌ໃໝ່
        if (isset($_POST["remove_" . $fieldName]) && $_POST["remove_" . $fieldName] === "1") {
            deleteUploadedFile($oldValue);
            return null;
        }

        return $oldValue;
    }

    // ກວດສອບนามสกุลไฟล์ (Whitelist)
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx'];
    $ext = strtolower(pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExts)) {
        return $oldValue; // ປະເສດໄຟລ໌ທີ່ບໍ່ແມ່ນຮູບ ຫຼື ເອກະສານທີ່ອະນຸຍາດ
    }

    // ກວດສອບ MIME type
    $tmpFile = $_FILES[$fieldName]['tmp_name'];
    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo && $tmpFile) {
        $mime = finfo_file($finfo, $tmpFile);
        finfo_close($finfo);
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!in_array($mime, $allowedMimes)) {
            return $oldValue;
        }
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = $fieldName . "_" . time() . "_" . uniqid() . "." . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($tmpFile, $targetPath)) {

        // ບີບອັດຮູບຖ້າໃຫຍ່ເກີນໄປ (ເປົ້າໝາຍ ≤100KB — ຄ່າ default ຂອງ compressImage())
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            compressImage($targetPath);
        }

        if ($oldValue && file_exists($uploadDir . $oldValue)) {
            unlink($uploadDir . $oldValue);
        }

        return $newFileName;
    }

    return $oldValue;
}

// ===================================================
// ຟັງຊັນອອກລະຫັດ data_id (ຮູບແບບ: YY + ລຳດັບ 5 ຫຼັກ ເຊັ່ນ 2600001)
// ===================================================
// ຕ້ອງເອີ້ນຕອນຈະ INSERT ຈິງເທົ່ານັ້ນ ຫ້າມເອີ້ນຕອນເປີດໜ້າຟອມ
// ເພາະຖ້າອອກເລກໄວ້ລ່ວງໜ້າ ຄົນທີ່ເປີດໜ້າພ້ອມກັນຈະໄດ້ເລກດຽວກັນ
function generateDataId(PDO $conn) {
    $y = date('y');

    // ໃຊ້ ORDER BY ... DESC LIMIT 1 ແທນ MAX(CAST(SUBSTRING(...)))
    // ເຫດຜົນ: ຖ້າໃສ່ຟັງຊັນ (SUBSTRING/CAST) ໃສ່ column ໃນ WHERE/SELECT
    //         MySQL ຈະໃຊ້ index ບໍ່ໄດ້ ຕ້ອງອ່ານທັງຕາຕະລາງ (full scan)
    //         ພໍຂໍ້ມູນຫຼາຍຂຶ້ນ ຈະຊ້າຂຶ້ນເລື້ອຍໆ ແລະ ເປັນເວລາທີ່ຖື lock ຢູ່ນຳ
    //         ວັດແທ້ທີ່ 200,000 ແຖວ: ແບບເກົ່າ 73ms → ແບບນີ້ 0.5ms (ໄວກວ່າ ~140 ເທົ່າ)
    //
    // ໝາຍເຫດ: ຈະໄວແທ້ຕ້ອງມີ index ໃນ column data_id (ເບິ່ງ sql/add_index.sql)
    //          ຖ້າບໍ່ມີ index ກໍ່ຍັງເຮັດວຽກຖືກຕ້ອງ ພຽງແຕ່ຊ້າກວ່າ
    $stmt = $conn->prepare(
        "SELECT cid FROM candidate_korea
         WHERE cid LIKE :prefix
         ORDER BY cid DESC
         LIMIT 1"
    );
    $stmt->execute([':prefix' => $y . '%']);
    $last = $stmt->fetchColumn();

    // ຕັດ 2 ໂຕໜ້າ (ປີ) ອອກ ເຫຼືອລຳດັບ ແລ້ວບວກ 1
    $next = ($last !== false && $last !== null) ? ((int) substr($last, 2)) + 1 : 1;

    return $y . str_pad($next, 5, '0', STR_PAD_LEFT);
}

// ===================================================
// ຟັງຊັນອ່ານວ່າ error duplicate key ເກີດຢູ່ column ໃດ
// ===================================================
// MySQL ຈະບອກຊື່ index ມາໃນຂໍ້ຄວາມ error ເຊັ່ນ:
//   Duplicate entry '2600001' for key 'data_entry_korea.uniq_data_id'
// ຈຶ່ງຕັ້ງຊື່ index ໃຫ້ມີຊື່ column ຢູ່ນຳ (ເບິ່ງ sql/add_unique_index.sql)
function duplicateColumn($errMsg) {
    foreach (['cid', 'vacancy_check', 'passport'] as $col) {
        if (stripos($errMsg, $col) !== false) {
            return $col;
        }
    }
    return null;
}

// ===================================================
// ຟັງຊັນລັອກ (ໃຊ້ GET_LOCK ຂອງ MySQL)
// ===================================================
// ເປັນຫຍັງຕ້ອງມີ:
//   ການກວດຊ້ຳດ້ວຍ SELECT ກ່ອນ INSERT ກັນບໍ່ໄດ້ 100%
//   ຖ້າ 2 ຄົນກົດບັນທຶກພ້ອມກັນ ທັງຄູ່ຈະ SELECT ບໍ່ພົບ ແລ້ວ INSERT ໄດ້ທັງຄູ່
//   GET_LOCK ເຮັດໃຫ້ຂັ້ນຕອນ (ກວດຊ້ຳ → ອອກເລກ → INSERT) ເປັນ atomic
//   ຄື ມີແຕ່ 1 request ເທົ່ານັ້ນທີ່ເຂົ້າໄປໄດ້ໃນເວລາດຽວ ຄົນອື່ນຕ້ອງລໍຖ້າ
//
// ຂໍ້ດີ: ໃຊ້ໄດ້ທັນທີ ບໍ່ຕ້ອງແກ້ໂຄງສ້າງຕາຕະລາງ (ບໍ່ຕ້ອງເພີ່ມ UNIQUE index)
// ຂໍ້ຈຳກັດ: ໃຊ້ໄດ້ກັບ MySQL/MariaDB ແລະ ຕ້ອງເປັນ DB server ດຽວກັນ
//           ຖ້າໃຊ້ບໍ່ໄດ້ ລະບົບຈະເຮັດວຽກຕໍ່ໄປໂດຍບໍ່ມີລັອກ (ບໍ່ໃຫ້ຜູ້ໃຊ້ຄ້າງ)
//           ຈຶ່ງແນະນຳໃຫ້ເພີ່ມ UNIQUE index ນຳ (sql/add_unique_index.sql)
//           ເປັນການປ້ອງກັນ 2 ຊັ້ນ

define('VACANCY_LOCK_NAME', 'vacancy_insert_lock');

function acquireInsertLock(PDO $conn, $timeoutSec = 10) {
    try {
        $stmt = $conn->prepare("SELECT GET_LOCK(:name, :timeout)");
        $stmt->execute([':name' => VACANCY_LOCK_NAME, ':timeout' => $timeoutSec]);
        // ຄືນ 1 = ໄດ້ລັອກ, 0 = ລໍຖ້າຈົນໝົດເວລາ, NULL = ຜິດພາດ
        return ((string) $stmt->fetchColumn()) === '1';
    } catch (PDOException $e) {
        // DB ບໍ່ຮອງຮັບ GET_LOCK (ເຊັ່ນ ບໍ່ແມ່ນ MySQL) → ເຮັດວຽກຕໍ່ໂດຍບໍ່ມີລັອກ
        error_log('GET_LOCK unavailable: ' . $e->getMessage());
        return false;
    }
}

function releaseInsertLock(PDO $conn) {
    try {
        $stmt = $conn->prepare("SELECT RELEASE_LOCK(:name)");
        $stmt->execute([':name' => VACANCY_LOCK_NAME]);
        $stmt->fetchColumn();
    } catch (PDOException $e) {
        // ລັອກຈະຖືກປ່ອຍເອງຕອນ connection ຂາດ ຈຶ່ງບໍ່ຕ້ອງເຮັດຫຍັງເພີ່ມ
        error_log('RELEASE_LOCK failed: ' . $e->getMessage());
    }
}

// ===================================================
// ຟັງຊັນກວດວ່າ ລະຫັດລະບຸຕົວຕົນ / passport ຊ້ຳກັບແຖວອື່ນບໍ່
// ===================================================
// ຄືນຊື່ column ທີ່ຊ້ຳ ຫຼື null ຖ້າບໍ່ຊ້ຳ
// $excludeId ໃຊ້ຕອນ update (ບໍ່ຕ້ອງນັບແຖວຂອງຕົນເອງ)
function findDuplicateField(PDO $conn, $vacancyCheck, $passport, $excludeId = null) {

    foreach (['vacancy_check' => $vacancyCheck, 'passport' => $passport] as $col => $val) {
        if (empty($val)) continue;

        if ($excludeId) {
            $stmt = $conn->prepare("SELECT id FROM candidate_korea WHERE $col = :val AND id != :id LIMIT 1");
            $stmt->execute([':val' => $val, ':id' => $excludeId]);
        } else {
            $stmt = $conn->prepare("SELECT id FROM candidate_korea WHERE $col = :val LIMIT 1");
            $stmt->execute([':val' => $val]);
        }

        if ($stmt->fetchColumn() !== false) {
            return $col;
        }
    }
    return null;
}

// ຂໍ້ຄວາມແຈ້ງເຕືອນຕາມ column ທີ່ຊ້ຳ
function duplicateMessage($col) {
    if ($col === 'vacancy_check') return 'ລະຫັດລະບຸຕົວຕົນນີ້ຖືກນຳໃຊ້ໄປແລ້ວ ກະລຸນາກວດສອບຄືນ';
    if ($col === 'passport')      return 'ເລກ Passport ນີ້ຖືກນຳໃຊ້ໄປແລ້ວ ກະລຸນາກວດສອບຄືນ';
    if ($col === 'cid')       return 'ເລກທີລົງທະບຽນຊ້ຳກັນ ກະລຸນາກົດບັນທຶກອີກຄັ້ງ';
    return 'ບໍ່ສາມາດບັນທຶກຂໍ້ມູນໄດ້ ກະລຸນາລອງໃໝ່ອີກຄັ້ງ';
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
// ຄວາມປອດໄພ: sub=update ຕ້ອງ login ແລະ ຕ້ອງເປັນເຈົ້າຂອງແຖວນັ້ນຈິງໆ
// (ກ່ອນໜ້ານີ້ file ນີ້ບໍ່ມີການກວດ session ເລີຍ ໃຜກໍ່ສົ່ງ POST id ໃດກໍ່ໄດ້
// ແລ້ວແກ້ໄຂ/overwrite ຂໍ້ມູນຄົນອື່ນໄດ້ໂດຍກົງ)
// sub=insert ຍັງເປີດໃຫ້ສາທາລະນະໃຊ້ໄດ້ຕໍ່ໄປ ເພາະເປັນຟອມລົງທະບຽນຄົນໃໝ່ທີ່ຍັງບໍ່ທັນມີບັນຊີ
// (vacancy_add.php ບໍ່ໄດ້ບັງຄັບ login)
// ===================================================
if ($sub === "update") {
    $sessionId = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? '';

    if (empty($sessionId)) {
        echo json_encode([
            'message' => 'ກະລຸນາເຂົ້າສູ່ລະບົບກ່ອນແກ້ໄຂຂໍ້ມູນ',
            'sts' => 'error'
        ]);
        exit;
    }

    if (empty($id)) {
        echo json_encode(['message' => 'ບໍ່ພົບ ID ຂໍ້ມູນທີ່ຈະແກ້ໄຂ', 'sts' => 'error']);
        exit;
    }

    $sqlOwner = "SELECT id FROM candidate_korea
                 WHERE id = :id
                 AND (vacancy_check = :sid OR cid = :sid OR phone1 = :sid OR passport = :sid)
                 LIMIT 1";
    $stmtOwner = $conn->prepare($sqlOwner);
    $stmtOwner->execute([':id' => $id, ':sid' => $sessionId]);

    if ($stmtOwner->rowCount() === 0) {
        echo json_encode([
            'message' => 'ທ່ານບໍ່ມີສິດແກ້ໄຂຂໍ້ມູນນີ້',
            'sts' => 'error'
        ]);
        exit;
    }
}

// ===================================================
// ກວດສອບຂໍ້ມູນຊ້ຳ ຮອບທຳອິດ (ກ່ອນອັບໂຫຼດໄຟລ໌)
// ===================================================
// ສຳຄັນ: vacancy_check ຄືລະຫັດທີ່ໃຊ້ log in (ເບິ່ງ login_action.php)
// ຖ້າຊ້ຳກັນ 2 ຄົນ ຄົນໜຶ່ງຈະ log in ແລ້ວເຫັນຂໍ້ມູນຂອງອີກຄົນ (ເພາະ query ໃຊ້ LIMIT 1)
//
// ການກວດຮອບນີ້ເຮັດ "ນອກລັອກ" ເພື່ອຕອບຜູ້ໃຊ້ໄວ ແລະ ບໍ່ຕ້ອງເສຍເວລາອັບໂຫຼດຮູບ
// ຖ້າຮູ້ຢູ່ແລ້ວວ່າຊ້ຳ ແຕ່ຍັງກັນຄົນທີ່ກົດພ້ອມກັນຈິງໆບໍ່ໄດ້
// → ຈຶ່ງມີການກວດຮອບສອງ "ໃນລັອກ" ອີກເທື່ອ ກ່ອນ INSERT (ເບິ່ງລຸ່ມ)
$vacancyCheck = getPost("vacancy_check");
$dupField = findDuplicateField($conn, $vacancyCheck, $passport, ($sub === "update") ? $id : null);

if ($dupField !== null) {
    echo json_encode([
        'message' => duplicateMessage($dupField),
        'sts' => 'error'
    ]);
    exit;
}

// ===================================================
// ດຶງຂໍ້ມູນເກົ່າຂອງໄຟລ໌ (ໃຊ້ສະເພາະຕອນ update)
// ===================================================
$oldData = [
    'profile' => null, 'id_profile' => null
];

if ($sub === "update" && $id) {
    $sqlOld = "SELECT profile, id_profile
               FROM candidate_korea WHERE id = :id";
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
    "register_date"       => getPost("register_date"),
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
    "id_profile"     => uploadFile("id_profile", $oldData['id_profile']),

    // new update
    "cid" => getPost("cid"),
    "type_check" => getPost("type_check"),
    "vacancy_check" => getPost("vacancy_check"),
    "password" => (isset($_POST['password']) && $_POST['password'] !== '') ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null,
    "id_no" => getPost("id_no"),
    "type_job" => getPost("type_job"),
    "place_job" => getPost("place_job"),
    "type_in" => getPost("type_in"),
    "race" => getPost("race"),
    "religion" => getPost("religion"),
    "timezon" => getPost("timezon"),
    "emp_id" => getPost("emp_id"),
    "spouse_id" => getPost("spouse_id"),
];

// ===================================================
// ກໍລະນີ Update: ໃຫ້ອັບເດດສະເພາະຊ່ອງທີ່ຟອມສົ່ງມາຈິງ
// ປ້ອງກັນຊ່ອງທີ່ບໍ່ມີໃນຟອມ ຖືກລ້າງເປັນ NULL
// ===================================================
if ($sub === "update") {

    // ຊ່ອງທີ່ຊື່ໃນ $data ບໍ່ກົງກັບຊື່ຊ່ອງໃນຟອມ
    $postKeyMap = ["list_type" => "list"];

    // ຊ່ອງໄຟລ໌ ຕ້ອງເກັບໄວ້ສະເໝີ (ຖ້າບໍ່ມີໄຟລ໌ໃໝ່ uploadFile() ຈະຄືນຄ່າເກົ່າຢູ່ແລ້ວ)
    $fileFields = [
        "profile", "id_profile"
    ];

    foreach ($data as $key => $value) {
        if (in_array($key, $fileFields)) {
            continue;
        }
        $postKey = isset($postKeyMap[$key]) ? $postKeyMap[$key] : $key;
        if (!array_key_exists($postKey, $_POST)) {
            unset($data[$key]);
        }
    }

    // ຖ້າບໍ່ໄດ້ປ້ອນລະຫັດຜ່ານໃໝ່ ຢ່າແກ້ໄຂລະຫັດຜ່ານເກົ່າ
    if (!isset($_POST['password']) || $_POST['password'] === '') {
        unset($data['password']);
    }
}

// ===================================================
// 1. ກໍລະນີ Insert (ຂໍ້ມູນໃໝ່)
// ===================================================
if ($sub === "insert") {

    // ---------------------------------------------------------------
    // ລະຫັດ data_id ຕ້ອງອອກຈາກ server ຕອນຈະ INSERT ຈິງເທົ່ານັ້ນ
    //
    // ແບບເກົ່າ (ມີບັນຫາ): vacancy_add.php ຄຳນວນເລກຕອນ "ເປີດໜ້າ"
    //   ແລ້ວໃສ່ໄວ້ໃນ <input readonly> ສົ່ງມາກັບຟອມ
    //   → 2 ຄົນເປີດໜ້າພ້ອມກັນ ໄດ້ເລກດຽວກັນ → ບັນທຶກແລ້ວຊ້ຳກັນ
    //   → ເປີດໜ້າຄ້າງໄວ້ດົນ ແລ້ວມີຄົນລົງທະບຽນກ່ອນ → ຊ້ຳແນ່ນອນ
    //   → readonly ກັນໄດ້ແຕ່ໜ້າຈໍ ຜູ້ໃຊ້ແກ້ຄ່າຜ່ານ DevTools ໄດ້
    //
    // ແບບໃໝ່: ບໍ່ເຊື່ອຄ່າ data_id ຈາກຟອມເລີຍ — server ອອກເລກເອງ
    //   ແລະ ຖ້າຊົນກັບຄົນອື່ນ (UNIQUE index ຈັບໄດ້) ໃຫ້ອອກເລກໃໝ່ແລ້ວລອງອີກ
    // ---------------------------------------------------------------

    // ຂໍລັອກກ່ອນ ເພື່ອໃຫ້ຂັ້ນຕອນ (ກວດຊ້ຳ → ອອກເລກ → INSERT) ເປັນ atomic
    // ລັອກຢູ່ບ່ອນນີ້ ບໍ່ແມ່ນຕັ້ງແຕ່ຕົ້ນໄຟລ໌ ເພາະການອັບໂຫຼດ+ບີບອັດຮູບໃຊ້ເວລາຫຼາຍວິນາທີ
    // ຖ້າລັອກຄຸມທັງໝົດ ຄົນອື່ນຈະຕ້ອງລໍຖ້າດົນໂດຍບໍ່ຈຳເປັນ
    if (acquireInsertLock($conn, 10)) {
        // ໃຊ້ register_shutdown_function ເພື່ອໃຫ້ປ່ອຍລັອກແນ່ນອນ
        // (try/finally ໃຊ້ບໍ່ໄດ້ ເພາະ finally ຈະບໍ່ເຮັດວຽກເມື່ອເອີ້ນ exit)
        register_shutdown_function('releaseInsertLock', $conn);
    }

    // ກວດຊ້ຳຮອບສອງ "ພາຍໃນລັອກ" — ຮອບທຳອິດເຮັດໄປແລ້ວກ່ອນອັບໂຫຼດຮູບ
    // ແຕ່ໃນຊ່ວງເວລານັ້ນອາດມີຄົນອື່ນບັນທຶກຂໍ້ມູນຊ້ຳກັນເຂົ້າມາກ່ອນ
    $dupNow = findDuplicateField($conn, $vacancyCheck, $passport, null);
    if ($dupNow !== null) {
        echo json_encode([
            'message' => duplicateMessage($dupNow),
            'sts' => 'error'
        ]);
        exit;
    }

    $maxAttempts = 5;
    $inserted    = false;
    $dupColumn   = null;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {

        $data['cid'] = generateDataId($conn);

        $columns      = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sqlInsert    = "INSERT INTO candidate_korea ($columns) VALUES ($placeholders)";

        $paramsInsert = [];
        foreach ($data as $key => $value) {
            $paramsInsert[":" . $key] = $value;
        }

        try {
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->execute($paramsInsert);
            $inserted = true;
            break;

        } catch (PDOException $e) {

            // 23000 = integrity constraint violation (ລວມ duplicate key)
            if ($e->getCode() !== '23000') {
                error_log('Insert vacancy failed: ' . $e->getMessage());
                echo json_encode([
                    'message' => 'ບໍ່ສາມາດບັນທຶກຂໍ້ມູນໄດ້ ກະລຸນາລອງໃໝ່ອີກຄັ້ງ',
                    'sts' => 'error'
                ]);
                exit;
            }

            $dupColumn = duplicateColumn($e->getMessage());

            // ຊ້ຳທີ່ data_id → ເປັນເລກທີ່ລະບົບອອກເອງ ອອກໃໝ່ແລ້ວລອງອີກ
            if ($dupColumn === 'cid') {
                // ຫ່າງກັນເລັກນ້ອຍແບບສຸ່ມ ຫຼຸດໂອກາດ 2 request ຊົນກັນຊ້ຳ
                usleep(random_int(10000, 60000));
                continue;
            }

            // ຊ້ຳທີ່ passport / vacancy_check → ເປັນຂໍ້ມູນທີ່ຜູ້ໃຊ້ປ້ອນ ລອງໃໝ່ໄປກໍ່ຊ້ຳຢູ່
            error_log('Insert vacancy duplicate: ' . $e->getMessage());
            break;
        }
    }

    if ($inserted) {
        echo json_encode([
            'message' => 'ບັນທຶກຂໍ້ມູນສຳເລັດ',
            'sts'     => 'success',
            'cid' => $data['cid']
        ]);
    } elseif ($dupColumn === 'passport') {
        echo json_encode([
            'message' => 'ເລກ Passport ນີ້ຖືກນຳໃຊ້ໄປແລ້ວ ກະລຸນາກວດສອບຄືນ',
            'sts' => 'error'
        ]);
    } elseif ($dupColumn === 'vacancy_check') {
        echo json_encode([
            'message' => 'ລະຫັດລະບຸຕົວຕົນນີ້ຖືກນຳໃຊ້ໄປແລ້ວ ກະລຸນາກວດສອບຄືນ',
            'sts' => 'error'
        ]);
    } else {
        // ລອງຄົບ 5 ຮອບແລ້ວຍັງຊົນ (ຄົນລົງທະບຽນພ້ອມກັນຫຼາຍຫຼາຍ)
        echo json_encode([
            'message' => 'ລະບົບກຳລັງມີຜູ້ໃຊ້ພ້ອມກັນຫຼາຍ ກະລຸນາກົດບັນທຶກອີກຄັ້ງ',
            'sts' => 'error'
        ]);
    }
    exit;

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

    $sql = "UPDATE candidate_korea SET $setClause WHERE id = :id";
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
        // ຫ້າມສົ່ງ $e->getMessage() ອອກໄປ ເພາະມີຊື່ຕາຕະລາງ/column/query ຢູ່ນຳ
        error_log('Update vacancy failed: ' . $e->getMessage());

        $dupColumn = ($e->getCode() === '23000') ? duplicateColumn($e->getMessage()) : null;

        if ($dupColumn === 'passport') {
            $errMsg = 'ເລກ Passport ນີ້ຖືກນຳໃຊ້ໄປແລ້ວ ກະລຸນາກວດສອບຄືນ';
        } elseif ($dupColumn === 'vacancy_check') {
            $errMsg = 'ລະຫັດລະບຸຕົວຕົນນີ້ຖືກນຳໃຊ້ໄປແລ້ວ ກະລຸນາກວດສອບຄືນ';
        } else {
            $errMsg = 'ບໍ່ສາມາດບັນທຶກຂໍ້ມູນໄດ້ ກະລຸນາລອງໃໝ່ອີກຄັ້ງ';
        }

        echo json_encode([
            'message' => $errMsg,
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