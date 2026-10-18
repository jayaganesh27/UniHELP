<?php
require_once '../config.php';
requireVolunteer();

header('Content-Type: application/json');

$raw    = json_decode(file_get_contents('php://input'), true);
$action = $raw['action']     ?? '';
$reqId  = (int)($raw['request_id'] ?? 0);
$csrf   = $raw['csrf']       ?? '';
$sched  = $raw['scheduled_at'] ?? '';

if (!hash_equals(csrfToken(), $csrf)) {
    echo json_encode(['ok' => false, 'error' => 'CSRF mismatch.']);
    exit;
}

$u  = currentUser();
$db = db();

if (!$reqId) {
    echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
    exit;
}

if ($action !== 'accept' || !$sched || strtotime($sched) <= time()) {
    echo json_encode(['ok' => false, 'error' => 'Choose a valid future date and time.']);
    exit;
}
$sched = date('Y-m-d H:i:s', strtotime($sched));

// Verify the request is open and belongs to another student.
$req = $db->prepare(
    'SELECT sr.*
     FROM support_requests sr
     WHERE sr.id = ? AND sr.status = "open" AND sr.student_id != ?
     LIMIT 1'
);
$req->execute([$reqId, $u['id']]);
$req = $req->fetch();

if (!$req) {
    echo json_encode(['ok' => false, 'error' => 'Request not found or already handled.']);
    exit;
}

try {
    $db->beginTransaction();
    $db->prepare(
        'INSERT INTO sessions (request_id, volunteer_id, student_id, scheduled_datetime)
         VALUES (?, ?, ?, ?)'
    )->execute([$reqId, $u['id'], $req['student_id'], $sched]);

    $db->prepare('UPDATE support_requests SET status = "accepted" WHERE id = ? AND status = "open"')
       ->execute([$reqId]);

    $db->commit();
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['ok' => false, 'error' => 'This request could not be accepted.']);
}
