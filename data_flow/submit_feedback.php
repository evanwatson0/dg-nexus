<?php
// feedback/submit_feedback.php

header('Content-Type: application/json');

// TODO: 這裡要改成你們實際的 DB 連線檔案路徑
// 例如如果你們是放在 config/db_connect.php，而且裡面已經建立好 $pdo：
// require __DIR__ . '/../config/db_connect.php';

// 如果你把連線檔貼給我，我可以幫你寫成正確的 require 路徑
require __DIR__ . '/../config/db_connect.php'; // ← 請依實際情況調整

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Invalid JSON payload'
    ]);
    exit;
}

$rating       = isset($data['rating']) ? (int)$data['rating'] : null;
$comment      = trim($data['comment'] ?? '');
$gene         = $data['gene'] ?? null;
$drug         = $data['drug'] ?? null;
$relationType = $data['relation_type'] ?? null;

if ($rating === null || $rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Rating must be between 1 and 5.'
    ]);
    exit;
}

try {
    $sql = "INSERT INTO USER_FEEDBACK (Gene, Drug, RelationType, Rating, Comment)
            VALUES (:gene, :drug, :relation_type, :rating, :comment)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':gene'          => $gene,
        ':drug'          => $drug,
        ':relation_type' => $relationType,
        ':rating'        => $rating,
        ':comment'       => $comment ?: null
    ]);

    echo json_encode([
        'success' => true
    ]);
} catch (PDOException $e) {
    // 開發時可以暫時 log error message，正式版可以簡化
    // error_log('Feedback insert error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Database error.'
    ]);
}
