<?php

use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    private string $apiUrl;

    protected function setUp(): void
    {
        $this->apiUrl = "http://localhost:8080/api/index.php";
    }

    public function testApiReturnsOkStatus(): void
    {
        $payload = json_encode([
            "Arrival"   => "2025-10-05",
            "Departure" => "2025-10-07",
            "Ages"      => [30, 30]
        ]);

        $opts = [
            "http" => [
                "method"  => "POST",
                "header"  => "Content-Type: application/json",
                "content" => $payload
            ]
        ];

        $context = stream_context_create($opts);
        $response = file_get_contents($this->apiUrl, false, $context);
        $data = json_decode($response, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey("status", $data);
        $this->assertSame("ok", $data["status"]);
    }

    public function testApiReturnsResults(): void
    {
        $payload = json_encode([
            "Arrival"   => "2025-10-02",
            "Departure" => "2025-10-05",
            "Ages"      => [25, 28]
        ]);

        $opts = [
            "http" => [
                "method"  => "POST",
                "header"  => "Content-Type: application/json",
                "content" => $payload
            ]
        ];

        $context = stream_context_create($opts);
        $response = file_get_contents($this->apiUrl, false, $context);
        $data = json_decode($response, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey("results", $data);
        $this->assertIsArray($data["results"]);
    }

    public function testHandlesEmptyPayloadGracefully(): void
    {
        $payload = json_encode([
            "Arrival"   => "",
            "Departure" => "",
            "Ages"      => []
        ]);

        $opts = [
            "http" => [
                "method"  => "POST",
                "header"  => "Content-Type: application/json",
                "content" => $payload
            ]
        ];

        $context = stream_context_create($opts);
        $response = file_get_contents($this->apiUrl, false, $context);
        $data = json_decode($response, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey("status", $data);
        // just ensure status exists — don’t fail on "ok"
        $this->assertContains($data["status"], ["ok", "error"]);
    }
}
