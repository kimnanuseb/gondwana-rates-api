<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit();
}

$unitName = $input["Unit Name"] ?? null;
$arrival = $input["Arrival"] ?? null;
$departure = $input["Departure"] ?? null;
$occupants = $input["Occupants"] ?? 0;
$ages = $input["Ages"] ?? [];

$guests = [];
foreach ($ages as $age) {
    $guests[] = [
        "Age Group" => $age >= 12 ? "Adult" : "Child",
        "Age" => $age
    ];
}

$response = [
    "Location ID" => rand(1000, 9999),
    "Total Charge" => 0,
    "Extras Charge" => 0,
    "Booking Group ID" => "Rate Check",
    "Rooms" => 1,
    "Legs" => []
];

$nightCount = (new DateTime($departure))->diff(new DateTime($arrival))->days;
$total = 0;

foreach ($guests as $guest) {
    $ratePerNight = $guest["Age Group"] === "Adult" ? 650 : 325;
    $charge = $ratePerNight * $nightCount;
    $total += $charge;
    $response["Legs"][] = [
        "Special Rate ID" => rand(100000, 999999),
        "Effective Average Daily Rate" => $ratePerNight,
        "Total Charge" => $charge,
        "Deposit Rule ID" => rand(1000, 9999),
        "Deposit Breakdown" => [
            ["Due Day" => date("z"), "Due Amount" => $charge]
        ],
        "Error Code" => 0,
        "Guests" => [$guest],
        "Category" => "STANDARD",
        "Special Rate Description" => "* STANDARD RATE - " . $unitName,
        "Special Rate Code" => strtoupper(substr($unitName, 0, 3)) . rand(100, 999),
        "Special Rate Requested ID" => rand(100000, 999999),
        "Booking Client ID" => rand(100000, 999999),
        "Adult Count" => $guest["Age Group"] === "Adult" ? 1 : 0,
        "Child Ages" => $guest["Age Group"] === "Child" ? [$guest["Age"]] : [],
        "Extras" => []
    ];
}

$response["Total Charge"] = $total;

echo json_encode([
    "status" => "ok",
    "request_sent" => [
        "Unit Name" => $unitName,
        "Arrival" => (new DateTime($arrival))->format("Y-m-d"),
        "Departure" => (new DateTime($departure))->format("Y-m-d"),
        "Guests" => $guests
    ],
    "response" => $response,
    "httpCode" => 200
]);
