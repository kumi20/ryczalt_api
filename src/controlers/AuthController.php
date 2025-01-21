<?php
// Obsługa CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Obsługa żądań OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Kontroler logowania użytkownika
 */
class AuthController
{
    private $db;
    private $secretKey = "hggs53234jd"; // Klucz do generowania JWT

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Logowanie użytkownika i generowanie tokenu JWT
     *
     * @OA\Post(
     *     path="/login",
     *     summary="Logowanie użytkownika",
     *     tags={"Autoryzacja"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login", "password"},
     *             @OA\Property(property="login", type="string", example="user123"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Zalogowano pomyślnie",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Błędne dane logowania",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Błędne dane logowania")
     *         )
     *     )
     * )
     */
    public function login($request)
    {
       // Odczytanie danych z body requestu
        $data = json_decode($request, true);

        $login = $data['login'] ?? null;
        $password = $data['password'] ?? null;
  

        // // Walidacja wejściowych danych
        if (!$login || !$password) {
            
            http_response_code(400);
            echo json_encode(["error" => "Błędne dane logowania"]);
            return;
        }

        // Sprawdzenie czy login istnieje w bazie
        $query = "SELECT * FROM users WHERE login = :login";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // // Weryfikacja hasła
        if (!$user || $password !== $user['password']) {
            http_response_code(400);
            echo json_encode(["error" => "Błędne dane logowania"]);
            return;
        }

        // Zapytanie SQL do aktualizacji pola last_login na aktualną datę i godzinę
        $query = "UPDATE users SET last_login = NOW() WHERE id = :userId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':userId', $user['id']);
        $stmt->execute();

        // // Generowanie tokenu JWT ważnego przez 8 godzin
        $payload = [
            "iss" => "kumi20.webd.pl", // Issuer
            "sub" => $user['id'],       // Subject (ID użytkownika)
            "company_id" => $user['company_id'],
            "first_name" => $user['first_name'],
            "last_name" => $user['last_name'],
            "iat" => time(),            // Issued at
            "exp" => time() + 8 * 3600  // Expiration time (8 godzin)
        ];
     

        $jwt = JWT::encode($payload, $this->secretKey, 'HS256');
        // // Wysłanie odpowiedzi z tokenem
        http_response_code(200);
        echo json_encode(["token" => $jwt]);
    }
}
