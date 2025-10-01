<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["Arrival"], $input["Departure"], $input["Ages"])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$arrival = $input["Arrival"];
$departure = $input["Departure"];
$ages = $input["Ages"];

$guests = array_map(function ($age) {
    return ["Age Group" => $age >= 18 ? "Adult" : "Child"];
}, $ages);

$units = [
    ["unitId" => -2147483637, "unitName" => "Kalahari Farmhouse"],
    ["unitId" => -2147483456, "unitName" => "Klipspringer Camps"],
];

$results = [];

foreach ($units as $unit) {
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

    $decoded = json_decode($response, true);
    if ($decoded) {
        $results[] = [
            "unitId" => $unit["unitId"],
            "unitName" => $unit["unitName"],
            "apiResponse" => $decoded
        ];
    }
}

echo json_encode([
    "status" => "ok",
    "arrival" => $arrival,
    "departure" => $departure,
    "guests" => $guests,
    "results" => $results
]);
