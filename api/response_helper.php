<?php

function jsonSuccess($data = [], $message = "Success", $code = 200) {
    http_response_code($code);
    echo json_encode(['status' => 'success', 'message' => $message, 'data' => $data]);
    exit;
}

function jsonError($message = "Error", $code = 400) {
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}