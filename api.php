<?php
session_start();

$xml_dir = __DIR__ . '/xml/';

// Ensure the xml directory exists
if (!is_dir($xml_dir)) {
    mkdir($xml_dir, 0755, true);
}

$users = file_exists(__DIR__ . '/users.php') ? include('users.php') : [];

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// ── LIST: returns all .xml filenames ──
if ($action === 'list') {
    $files = array_values(array_filter(
        array_diff(scandir($xml_dir), ['.', '..']),
        fn($f) => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'xml'
    ));
    echo json_encode($files);
    exit;
}

// ── GET: serves a single XML file by name ──
if ($action === 'get') {
    $filename = basename($_GET['file'] ?? '');
    $path = $xml_dir . $filename;
    if ($filename === '' || !file_exists($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'xml') {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
        exit;
    }
    header('Content-Type: application/xml; charset=utf-8');
    readfile($path);
    exit;
}

// ── LOGIN ──
if ($action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    if ($data && isset($users[$data['user']]) && password_verify($data['pass'], $users[$data['user']])) {
        $_SESSION['admin'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// ── PROTECTED ACTIONS ──
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if ($action === 'upload' && isset($_FILES['xml_file'])) {
    $filename = basename($_FILES['xml_file']['name']);
    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'xml') {
        http_response_code(400);
        echo json_encode(['error' => 'Only .xml files are allowed']);
        exit;
    }
    move_uploaded_file($_FILES['xml_file']['tmp_name'], $xml_dir . $filename);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'delete') {
    $data = json_decode(file_get_contents('php://input'), true);
    $filename = basename($data['filename'] ?? '');
    $path = $xml_dir . $filename;
    if ($filename && file_exists($path)) {
        unlink($path);
    }
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
