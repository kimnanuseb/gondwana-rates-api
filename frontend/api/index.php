<?php
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

$allowedOrigins = [
    "http://localhost:8080",
    "https://your-username.github.io"
];
$origin = $_SERVER["HTTP_ORIGIN"] ?? "";
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit();
}

$arrival   = $input["Arrival"]   ?? null;
$departure = $input["Departure"] ?? null;
$ages      = $input["Ages"]      ?? [];

$guests = [];
foreach ($ages as $age) {
    $guests[] = ["Age Group" => $age >= 12 ? "Adult" : "Child"];
}

$unitTypes = [
    ["unitId" => -2147483637, "unitName" => "Kalahari Farmhouse"],
    ["unitId" => -2147483456, "unitName" => "Klipspringer Camps"],
];

$results = [];
foreach ($unitTypes as $unit) {
    $payload = [
        "Unit Type ID" => $unit["unitId"],
        "Arrival" => $arrival,
        "Departure" => $departure,
        "Guests" => $guests
    ];

    $ch = curl_init("https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    curl_close($ch);

    $results[] = [
        "unitName" => $unit["unitName"],
        "unitId"   => $unit["unitId"],
        "apiResponse" => json_decode($response, true)
    ];
}

echo json_encode([
    "status" => "ok",
    "arrival" => $arrival,
    "departure" => $departure,
    "guests" => $guests,
    "results" => $results
]);
