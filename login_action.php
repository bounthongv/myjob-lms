<?php
session_start();

if (file_exists("connect.php")) {
    require_once "connect.php";
} elseif (file_exists("config.php")) {
    require_once "config.php";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. ป้องกัน XSS & Script Injection
    $rawCustomer = isset($_POST['customer_id']) ? trim($_POST['customer_id']) : '';
    $customer_id = htmlspecialchars(strip_tags($rawCustomer), ENT_QUOTES, 'UTF-8');
    
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($customer_id)) {
        $_SESSION['login_error'] = "ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້ (Username)";
        header("Location: login.php?mode=login");
        exit;
    }

    try {
        if (isset($conn) && $conn) {
            // 2. ป้องกัน SQL Injection ด้วย PDO Prepared Statement
            $stmt = $conn->prepare("SELECT * FROM candidate_korea WHERE vacancy_check = :id OR cid = :id OR phone1 = :id OR passport = :id LIMIT 1");
            $stmt->execute([':id' => $customer_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // 3. ตรวจสอบรหัสผ่านที่ปลอดภัย (รองรับทั้ง Hash password_verify และ fallback)
                if (isset($user['password']) && $user['password'] !== '') {
                    $dbPassword = $user['password'];
                    $passwordValid = false;

                    if (password_verify($password, $dbPassword)) {
                        $passwordValid = true;
                    } elseif ($password === $dbPassword) {
                        $passwordValid = true;
                    }

                    if (!$passwordValid) {
                        $_SESSION['login_error'] = "ລະຫັດຜ່ານ ບໍ່ຖືກຕ້ອງ (Password ບໍ່ຖືກຕ້ອງ)";
                        header("Location: login.php?mode=login");
                        exit;
                    }
                }

                // 4. ป้องกัน Session Fixation / Hijacking โดยการสร้าง Session ID ใหม่เมื่อล็อกอินสำเร็จ
                session_regenerate_id(true);

                $userId = !empty($user['vacancy_check']) ? $user['vacancy_check'] : $user['data_id'];
                $userName = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));

                $_SESSION['customer_id'] = htmlspecialchars($userId, ENT_QUOTES, 'UTF-8');
                $_SESSION['customer_name'] = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
                $_SESSION['user_phone'] = htmlspecialchars($user['phone1'] ?? '', ENT_QUOTES, 'UTF-8');

                header("Location: user_menu.php");
                exit;
            } else {
                $_SESSION['login_error'] = "ບໍ່ພົບຂໍ້ມູນຜູ້ໃຊ້ນີ້ ໃນລະບົບ (ບໍ່ພົບ Username)";
                header("Location: login.php?mode=login");
                exit;
            }
        } else {
            $_SESSION['login_error'] = "ບໍ່ສາມາດເຊື່ອມຕໍ່ຖານຂໍ້ມູນໄດ້";
            header("Location: login.php?mode=login");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['login_error'] = "ເກີດຂໍ້ຜິດພາດໃນການເຊື່ອມຕໍ່ລະບົບ";
        header("Location: login.php?mode=login");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
