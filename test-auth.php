<?php

/**
 * Test script for authentication API
 * Run this script to test the authentication endpoints
 */

$baseUrl = 'http://localhost/auth.api.php';
$taskUrl = 'http://localhost/task.api.php';

echo "=== Softplan Tasks API Authentication Test ===\n\n";

// Test data
$testUser = [
    'username' => 'testuser_' . time(),
    'email' => 'test_' . time() . '@example.com',
    'password' => 'TestPass123'
];

echo "1. Testing User Registration...\n";
$registerData = json_encode($testUser);
$registerResponse = makeRequest('POST', $baseUrl . '?action=register', $registerData);

if ($registerResponse['success']) {
    echo "✓ Registration successful\n";
    $token = $registerResponse['data']['token'];
    $user = $registerResponse['data']['user'];
    echo "  User ID: {$user['id']}\n";
    echo "  Username: {$user['username']}\n";
    echo "  Token: " . substr($token, 0, 20) . "...\n\n";
} else {
    echo "✗ Registration failed: " . $registerResponse['error'] . "\n";
    exit(1);
}

echo "2. Testing Token Validation...\n";
$validateResponse = makeRequest('GET', $baseUrl . '?action=validate', null, $token);

if ($validateResponse['success']) {
    echo "✓ Token validation successful\n";
    echo "  Validated user: {$validateResponse['user']['username']}\n\n";
} else {
    echo "✗ Token validation failed: " . $validateResponse['error'] . "\n";
}

echo "3. Testing Protected Task API...\n";
$taskResponse = makeRequest('GET', $taskUrl, null, $token);

if (isset($taskResponse['tasks'])) {
    echo "✓ Task API access successful\n";
    echo "  Found " . count($taskResponse['tasks']) . " tasks\n";
    echo "  Authenticated as: {$taskResponse['user']['username']}\n\n";
} else {
    echo "✗ Task API access failed\n";
    if (isset($taskResponse['error'])) {
        echo "  Error: " . $taskResponse['error'] . "\n";
    }
}

echo "4. Testing Logout...\n";
$logoutResponse = makeRequest('POST', $baseUrl . '?action=logout', '{}', $token);

if ($logoutResponse['success']) {
    echo "✓ Logout successful\n\n";
} else {
    echo "✗ Logout failed: " . ($logoutResponse['message'] ?? 'Unknown error') . "\n";
}

echo "5. Testing Login with Same Credentials...\n";
$loginData = json_encode([
    'username' => $testUser['username'],
    'password' => $testUser['password']
]);
$loginResponse = makeRequest('POST', $baseUrl . '?action=login', $loginData);

if ($loginResponse['success']) {
    echo "✓ Login successful\n";
    $newToken = $loginResponse['data']['token'];
    echo "  New token: " . substr($newToken, 0, 20) . "...\n\n";
} else {
    echo "✗ Login failed: " . $loginResponse['error'] . "\n";
}

echo "6. Testing Invalid Token Access...\n";
$invalidResponse = makeRequest('GET', $taskUrl, null, 'invalid_token_123');

if (isset($invalidResponse['error'])) {
    echo "✓ Invalid token properly rejected\n";
    echo "  Error: {$invalidResponse['error']}\n\n";
} else {
    echo "✗ Invalid token was accepted (security issue!)\n";
}

echo "=== Test Complete ===\n";

function makeRequest($method, $url, $data = null, $token = null) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            $token ? "Authorization: Bearer $token" : ''
        ],
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  HTTP $httpCode: $method $url\n";
    
    $decoded = json_decode($response, true);
    return $decoded ?: ['error' => 'Invalid JSON response'];
}
