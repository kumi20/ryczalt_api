<?php
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function decodeToken(){
    $headers = getallheaders();
    $token = $headers['Authorization']?? null;

    // Klucz tajny użyty do podpisu tokena
    $secretKey = 'hggs53234jd';

    // Sprawdzenie, czy nagłówek zaczyna się od "Bearer"
    if (str_starts_with($token, 'Bearer ')) {
        // Usunięcie prefiksu "Bearer "
        $token = substr($token, 7);

    } 

    try {
        // Rozkodowanie tokena JWT
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        $decodedArray = (array) $decoded;
        $companyId = $decodedArray['company_id'] ?? null;
    
        // echo $decoded;
    
    } catch (Exception $e) {
        // Obsługa błędów
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token', 'message' => $e->getMessage()]);
        exit;
    }
    return $companyId;

    
}

function getUserId() {
    $headers = getallheaders();
    $token = $headers['Authorization']?? null;

    // Klucz tajny użyty do podpisu tokena
    $secretKey = 'hggs53234jd';

    // Sprawdzenie, czy nagłówek zaczyna się od "Bearer"
    if (str_starts_with($token, 'Bearer ')) {
        // Usunięcie prefiksu "Bearer "
        $token = substr($token, 7);
    } 

    try {
        // Rozkodowanie tokena JWT
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        $decodedArray = (array) $decoded;
        $userId = $decodedArray['sub'] ?? null;
    
    } catch (Exception $e) {
        // Obsługa błędów
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token', 'message' => $e->getMessage()]);
        exit;
    }
    return $userId;
}