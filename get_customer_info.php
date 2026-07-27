<?php
ob_start();
session_start();

if (file_exists("connect.php")) {
    require_once "connect.php";
} elseif (file_exists("config.php")) {
    require_once "config.php";
}
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// ป้องกัน XSS / Script Injection
$rawId = isset($_GET['customer_id']) ? trim($_GET['customer_id']) : '';
$customer_id = htmlspecialchars(strip_tags($rawId), ENT_QUOTES, 'UTF-8');

if (empty($customer_id)) {
    echo json_encode(['found' => false]);
    exit;
}

try {
    if (isset($conn) && $conn) {
        // ป้องกัน SQL Injection ด้วย PDO Prepared Statement
        $stmt = $conn->prepare("SELECT data_id, vacancy_check, fname, lname, phone1, home FROM data_entry_korea WHERE vacancy_check = :id OR data_id = :id OR phone1 = :id OR passport = :id LIMIT 1");
        $stmt->execute([':id' => $customer_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $fullName = htmlspecialchars(trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '')), ENT_QUOTES, 'UTF-8');
            $displayName = !empty($user['vacancy_check']) ? $user['vacancy_check'] : $user['data_id'];

            echo json_encode([
                'found' => true,
                'customer_name' => $fullName ? $fullName : htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'),
                'phone' => htmlspecialchars($user['phone1'] ?? '', ENT_QUOTES, 'UTF-8'),
                'address' => htmlspecialchars($user['home'] ?? '', ENT_QUOTES, 'UTF-8')
            ]);
        } else {
            echo json_encode(['found' => false]);
        }
    } else {
        echo json_encode(['found' => false, 'error' => 'Database connection unavailable']);
    }
} catch (Exception $e) {
    echo json_encode(['found' => false, 'error' => 'Server query error']);
}
