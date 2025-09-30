<?php
// api/index.php

// CORS + JSON
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Helper: read body
$raw = file_get_contents("php://input");
if (!$raw) {
    http_response_code(400);
    echo json_encode(["error" => "No input received"]);
    exit;
}

$input = json_decode($raw, true);
if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON"]);
    exit;
}

// Required fields check
$required = ["Unit Name","Arrival","Departure","Occupants","Ages"];
foreach ($required as $r) {
    if (!array_key_exists($r, $input)) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required field: $r"]);
        exit;
    }
}

// Unit name -> Unit Type ID mapping (assignment test IDs)
$unitTypeMap = [
    "Unit A" => -2147483637,
    "Unit B" => -2147483456
];

// Accept either the Unit Name or accept a Unit Type ID override
$unitTypeId = null;
if (isset($input['Unit Type ID']) && is_numeric($input['Unit Type ID'])) {
    $unitTypeId = (int)$input['Unit Type ID'];
} else {
    $unitName = $input["Unit Name"] ?? '';
    if (!isset($unitTypeMap[$unitName])) {
        http_response_code(400);
        echo json_encode(["error" => "Unknown or missing Unit Name. Allowed: " . implode(", ", array_keys($unitTypeMap))]);
        exit;
    }
    $unitTypeId = $unitTypeMap[$unitName];
}

// Parse date helper: accepts dd/mm/yyyy or yyyy-mm-dd
function parseDateToYmd($dateStr) {
    $dateStr = trim($dateStr);
    // Try dd/mm/yyyy
    $d = DateTime::createFromFormat('d/m/Y', $dateStr);
    if ($d && $d->format('d/m/Y') === $dateStr) {
        return $d->format('Y-m-d');
    }
    // Try yyyy-mm-dd
    $d = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($d && $d->format('Y-m-d') === $dateStr) {
        return $d->format('Y-m-d');
    }
    // Try strtotime fallback
    $t = strtotime($dateStr);
    if ($t !== false) return date('Y-m-d', $t);
    return null;
}

$arrival = parseDateToYmd($input['Arrival']);
$departure = parseDateToYmd($input['Departure']);
if (!$arrival || !$departure) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid Arrival or Departure. Expected dd/mm/yyyy or yyyy-mm-dd."]);
    exit;
}

// Build Guests (assignment: Age Group Adult/Child). Use threshold 12 (assignment discussion).
$ages = is_array($input['Ages']) ? $input['Ages'] : [];
function classifyAge($age) {
    return ($age !== null && is_numeric($age) && (int)$age >= 12) ? "Adult" : "Child";
}
$guests = array_map(function($a){
    return ["Age Group" => classifyAge($a)];
}, $ages);

// Final payload to Gondwana
$forwardPayload = [
    "Unit Type ID" => (int)$unitTypeId,
    "Arrival" => $arrival,
    "Departure" => $departure,
    "Guests" => $guests
];

// Curl to remote API
$remote = "https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php";
$ch = curl_init($remote);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($forwardPayload));
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErr) {
    http_response_code(502);
    echo json_encode(["error" => "cURL error forwarding to Gondwana API", "details" => $curlErr]);
    exit;
}

$decoded = json_decode($response, true);
if ($decoded === null) {
    // Return raw for debugging but still as JSON object so frontend doesn't choke
    echo json_encode(["status" => "ok", "response_raw" => $response, "forward_payload" => $forwardPayload, "httpCode" => $httpCode]);
    exit;
}

// Successful relay
echo json_encode(["status" => "ok", "request_sent" => $forwardPayload, "response" => $decoded, "httpCode" => $httpCode]);
