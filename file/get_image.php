<?php
// ຟັງຊັນຊ່ວຍດຶງຮູບພາບຂ້າມโปรเจกต์ หรือจากโฟลเดอร์ต่างๆ
if (empty($_GET['f'])) {
    http_response_code(400);
    exit('File name required');
}

$file = basename($_GET['f']);

$possiblePaths = [
    '/var/www/html/job/file/korea/uploads/' . $file,
    __DIR__ . '/uploads/' . $file,
    __DIR__ . '/korea/uploads/' . $file,
];

foreach ($possiblePaths as $filePath) {
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'pdf'  => 'application/pdf'
        ];
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

http_response_code(404);
echo "Image not found";
