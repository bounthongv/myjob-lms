<?php
require '../../connect.php';
header('Content-Type: application/json; charset=utf-8');

$vacancy_check  = trim($_POST['vacancy_check'] ?? '');
$family_book_no = trim($_POST['family_book_no'] ?? '');
$id_no          = trim($_POST['id_no'] ?? '');
$passport       = trim($_POST['passport'] ?? '');

$emp_id = '';

if (!empty($vacancy_check) || !empty($family_book_no) || !empty($id_no) || !empty($passport)) {
    $sql = $conn->prepare("
        SELECT emp_id 
        FROM data_entry_korea 
        WHERE (
            (vacancy_check = :vc AND vacancy_check != '') OR
            (family_book_no = :fam AND family_book_no != '') OR
            (id_no = :id_no AND id_no != '') OR
            (passport = :pass AND passport != '')
        )
        AND emp_id IS NOT NULL AND emp_id != ''
        ORDER BY id DESC LIMIT 1
    ");

    $sql->execute([
        ':vc'    => $vacancy_check,
        ':fam'   => $family_book_no,
        ':id_no' => $id_no,
        ':pass'  => $passport
    ]);

    $row = $sql->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['emp_id'])) {
        $emp_id = $row['emp_id'];
    }
}

echo json_encode([
    'status' => !empty($emp_id) ? 'success' : 'not_found',
    'emp_id' => $emp_id
]);
