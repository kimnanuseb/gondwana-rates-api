<?php
header("Content-Type: application/json");

// Read request body
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON input"]);
    exit;
}

// Map Unit Names to Unit Type IDs (for testing use these two IDs)
$unitTypeMap = [
    "Unit A" => -2147483637,
    "Unit B" => -2147483456
];

$unitName = $input["Unit Name"] ?? null;
if (!$unitName || !isset($unitTypeMap[$unitName])) {
    http_response_code(400);
    echo json_encode(["error" => "Unknown or missing Unit Name"]);
    exit;
}

// Helper: convert dd/mm/yyyy to yyyy-mm-dd
function convertDate($dateStr) {
    $d = DateTime::createFromFormat("d/m/Y", $dateStr);
    if (!$d) {
        $d = DateTime::createFromFormat("Y-m-d", $dateStr);
    }
    return $d ? $d->format("Y-m-d") : null;
}

$arrival = convertDate($input["Arrival"] ?? "");
$departure = convertDate($input["Departure"] ?? "");
if (!$arrival || !$departure) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing Arrival/Departure date"]);
    exit;
}

// Convert ages into guest objects
$ages = $input["Ages"] ?? [];
$guests = array_map(function($age) {
    return ["Age Group" => $age >= 12 ? "Adult" : "Child"];
}, $ages);

// Final payload for Gondwana API
$forwardPayload = [
    "Unit Type ID" => $unitTypeMap[$unitName],
    "Arrival" => $arrival,
    "Departure" => $departure,
    "Guests" => $guests
];

// Call Gondwana API
$remoteUrl = "https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php";
$ch = curl_init($remoteUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($forwardPayload));
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Output combined result
echo json_encode([
    "request" => $forwardPayload,
    "response" => json_decode($response, true) ?? $response,
    "status" => $httpcode
]);

