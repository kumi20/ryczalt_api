<?php
/**
 * Kontroler obsługujący kontrahentów.
 */
require_once 'token.php';


require_once 'vendor/autoload.php';

use GusApi\Exception\InvalidUserKeyException;
use GusApi\Exception\NotFoundException;
use GusApi\GusApi;
use GusApi\ReportTypes;
use GusApi\BulkReportTypes;


class CustomerController
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Zwraca listę kontrahentów.
     *
     * @OA\Get(
     *     path="/customers",
     *     summary="Pobierz listę kontrahentów",
     *     tags={"Kontrahenci"},
     *     security={{"bearerAuth": {}}},  // Dodanie informacji o wymaganej autoryzacji
     *     @OA\Parameter(
     *         name="skip",
     *         in="query",
     *         description="Liczba elementów do pominięcia w liście kontrahentów (np. dla paginacji).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=0
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="take",
     *         in="query",
     *         description="Liczba elementów do pobrania (np. dla paginacji).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=50
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="customerName",
     *         in="query",
     *         description="Filtr na podstawie nazwy kontrahenta (częściowe dopasowanie).",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="Firma A"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Filtr na podstawie miasta (częściowe dopasowanie).",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="Warszawa"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="orderBy",
     *         in="query",
     *         description="Kolumna do sortowania wyników.",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="customerName"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Kierunek sortowania (ASC lub DESC).",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"ASC", "DESC"},
     *             example="ASC"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="isSupplier",
     *         in="query",
     *         description="Filtruj tylko kontrahentów będących dostawcami (1 = tak, 0 = nie).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="isRecipient",
     *         in="query",
     *         description="Filtruj tylko kontrahentów będących odbiorcami (1 = tak, 0 = nie).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=0
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="isOffice",
     *         in="query",
     *         description="Filtruj tylko kontrahentów będących biurami (1 = tak, 0 = nie).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista kontrahentów.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="customerId", type="integer", example=1),
     *                 @OA\Property(property="customerName", type="string", example="Firma A"),
     *                 @OA\Property(property="customerVat", type="string", example="PL1234567890"),
     *                 @OA\Property(property="street", type="string", example="Main Street 1"),
     *                 @OA\Property(property="city", type="string", example="Warszawa"),
     *                 @OA\Property(property="postalCode", type="string", example="00-001"),
     *                 @OA\Property(property="country", type="string", example="Polska"),
     *                 @OA\Property(property="accountNumber", type="string", example="123456789"),
     *                 @OA\Property(property="isSupplier", type="integer", example=1),
     *                 @OA\Property(property="isRecipient", type="integer", example=0),
     *                 @OA\Property(property="isOffice", type="integer", example=1),
     *                 @OA\Property(
     *                     property="addressDetails",
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Główne Biuro"),
     *                     @OA\Property(property="street", type="string", example="Main Street 1"),
     *                     @OA\Property(property="city", type="string", example="Warszawa"),
     *                     @OA\Property(property="postalCode", type="string", example="00-001"),
     *                     @OA\Property(property="country", type="string", example="Polska")
     *                 ),
     *                 @OA\Property(
     *                     property="contactDetails",
     *                     type="object",
     *                     @OA\Property(property="email", type="string", example="contact@firma-a.com"),
     *                     @OA\Property(property="phone", type="string", example="+48123456789"),
     *                     @OA\Property(property="website", type="string", example="https://www.firma-a.com"),
     *                     @OA\Property(property="fax", type="string", example="+48123456789")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Brak autoryzacji (token jest wymagany)."
     *     )
     * )
     */
    public function getCustomers()
    {
        $companyId = decodeToken();

        // Pobranie parametru skip z GET
        $skip = isset($_GET['skip']) ? (int) $_GET['skip'] : 0;

        // Pobranie parametru take z GET
        $take = isset($_GET['take']) ? (int) $_GET['take'] : 200;

        $customerName = isset($_GET['customerName']) ? $_GET['customerName'] : '';
        $city = isset($_GET['city']) ? $_GET['city'] : '';

        // Pobranie parametrów z URL
        $sortBy = $_GET['orderBy'] ?? 'customerName'; // Domyślnie 'customerName'
        $sortOrder = strtoupper($_GET['order'] ?? 'ASC'); // Domyślnie 'ASC'

        // Pobranie parametrów isSupplier, isRecipient, isOffice
        $isSupplier = isset($_GET['isSupplier']) ? (int) $_GET['isSupplier'] : null;
        $isRecipient = isset($_GET['isRecipient']) ? (int) $_GET['isRecipient'] : null;
        $isOffice = isset($_GET['isOffice']) ? (int) $_GET['isOffice'] : null;
        $customerVat = isset($_GET['customerVat']) ? $_GET['customerVat'] : '';

        // Budowanie dynamicznej klauzuli WHERE
        $whereClauses = [
            "c.companyId = :companyId",
            "c.customerName LIKE :customerName",
            "c.customerVat LIKE :customerVat",
            "c.city LIKE :city"
        ];

        if ($isSupplier !== null) {
            $whereClauses[] = "c.isSupplier = :isSupplier";
        }
        if ($isRecipient !== null) {
            $whereClauses[] = "c.isRecipient = :isRecipient";
        }
        if ($isOffice !== null) {
            $whereClauses[] = "c.isOffice = :isOffice";
        }

        // Łączenie klauzul WHERE
        $whereSql = implode(' AND ', $whereClauses);


        $sql = "
            SELECT 
                c.customerId,
                c.customerName,
                c.customerVat,
                c.street,
                c.city,
                c.postalCode,
                c.country AS countryId,
                c.accountNumber,
                c.isSupplier,
                c.isRecipient,
                c.isOffice,
                JSON_OBJECT(
                    'name', cad.name,
                    'street', cad.street,
                    'city', cad.city,
                    'postalCode', cad.postalCode,
                    'countryId', cad.country
                ) AS addressDetails,
                JSON_OBJECT(
                    'contactPerson', ccd.contactPerson,
                    'email', ccd.email,
                    'phone', ccd.phone,
                    'website', ccd.website,
                    'fax', ccd.fax
                ) AS contactDetails
            FROM 
                customers c
            LEFT JOIN 
                customerAddressDetails cad ON c.customerId = cad.customerId
            LEFT JOIN 
                customerContactDetails ccd ON c.customerId = ccd.customerId
            WHERE 
                $whereSql
            ORDER BY $sortBy $sortOrder
            LIMIT :skip, :take;
        ";

        $stmt = $this->db->prepare($sql);
        // Przypisanie parametrów do zapytania
        $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
        $stmt->bindValue(':customerName', '%' . $customerName . '%', PDO::PARAM_STR);
        $stmt->bindValue(':customerVat', '%' . $customerVat . '%', PDO::PARAM_STR);
        $stmt->bindValue(':city', '%' . $city . '%', PDO::PARAM_STR);
        $stmt->bindParam(':skip', $skip, PDO::PARAM_INT);
        $stmt->bindParam(':take', $take, PDO::PARAM_INT);

        if ($isSupplier !== null) {
            $stmt->bindValue(':isSupplier', 1, PDO::PARAM_INT);
        }
        if ($isRecipient !== null) {
            $stmt->bindValue(':isRecipient', 1, PDO::PARAM_INT);
        }
        if ($isOffice !== null) {
            $stmt->bindValue(':isOffice', 1, PDO::PARAM_INT);
        }
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Przetwarzanie wyników
        foreach ($results as &$result) {
            if (!empty($result['addressDetails'])) {
                // Parsowanie JSON na tablicę, a następnie zamiana z powrotem na natywną strukturę PHP
                $result['addressDetails'] = json_decode($result['addressDetails'], true);
            }
            if (!empty($result['contactDetails'])) {
                // Parsowanie JSON na tablicę, a następnie zamiana z powrotem na natywną strukturę PHP
                $result['contactDetails'] = json_decode($result['contactDetails'], true);
            }
        }

        // Wysyłanie odpowiedzi JSON
        header('Content-Type: application/json');
        echo json_encode($results, JSON_UNESCAPED_UNICODE);
    }
    /**
     * @openapi
     * /api_ryczalt/customers:
     *   post:
     *     summary: Dodaje nowego kontrahenta
     *     tags: [Kontrahenci]
     *     security:
     *       - bearerAuth: []
     *     description: Dodaje nowego kontrahenta wraz z jego szczegółami adresowymi i kontaktowymi
     *     requestBody:
     *       required: true
     *       content:
     *         application/json:
     *           schema:
     *             type: object
     *             required:
     *               - customerName
     *               - customerVat
     *               - street
     *               - city
     *               - postalCode
     *               - country
     *               - accountNumber
     *             properties:
     *               customerName:
     *                 type: string
     *                 example: "Firma A"
     *                 description: Nazwa kontrahenta
     *               customerVat:
     *                 type: string
     *                 example: "PL1234567890"
     *                 description: Numer VAT kontrahenta
     *               street:
     *                 type: string
     *                 example: "Main Street 1"
     *                 description: Ulica
     *               city:
     *                 type: string
     *                 example: "Warszawa"
     *                 description: Miasto
     *               postalCode:
     *                 type: string
     *                 example: "00-001"
     *                 description: Kod pocztowy
     *               country:
     *                 type: string
     *                 example: "Polska"
     *                 description: Kraj
     *               accountNumber:
     *                 type: string
     *                 example: "123456789"
     *                 description: Numer konta bankowego
     *               isSupplier:
     *                 type: integer
     *                 enum: [0, 1]
     *                 example: 1
     *                 description: Czy jest dostawcą
     *               isRecipient:
     *                 type: integer
     *                 enum: [0, 1]
     *                 example: 0
     *                 description: Czy jest odbiorcą
     *               isOffice:
     *                 type: integer
     *                 enum: [0, 1]
     *                 example: 1
     *                 description: Czy jest biurem
     *               addressDetails:
     *                 type: object
     *                 properties:
     *                   name:
     *                     type: string
     *                     example: "Główne Biuro"
     *                     description: Nazwa lokalizacji
     *                   street:
     *                     type: string
     *                     example: "Main Street 1"
     *                     description: Ulica
     *                   city:
     *                     type: string
     *                     example: "Warszawa"
     *                     description: Miasto
     *                   postalCode:
     *                     type: string
     *                     example: "00-001"
     *                     description: Kod pocztowy
     *                   country:
     *                     type: string
     *                     example: "Polska"
     *                     description: Kraj
     *               contactDetails:
     *                 type: object
     *                 properties:
     *                   email:
     *                     type: string
     *                     example: "contact@firma-a.com"
     *                     description: Adres email
     *                   phone:
     *                     type: string
     *                     example: "48123456789"
     *                     description: Numer telefonu
     *                   website:
     *                     type: string
     *                     example: "https://www.firma-a.com"
     *                     description: Strona internetowa
     *                   fax:
     *                     type: string
     *                     example: "48123456789"
     *                     description: Numer faxu
     *     responses:
     *       200:
     *         description: Kontrahent został pomyślnie dodany
     *         content:
     *           application/json:
     *             schema:
     *               type: object
     *               properties:
     *                 success:
     *                   type: boolean
     *                   example: true
     *                 customerId:
     *                   type: integer
     *                   example: 123
     *                   description: ID dodanego kontrahenta
     *       400:
     *         description: Błąd walidacji lub dodawania danych
     *         content:
     *           application/json:
     *             schema:
     *               type: object
     *               properties:
     *                 error:
     *                   type: string
     *                   example: "Nieprawidłowe dane klienta"
     *                   description: Opis błędu
     *       401:
     *         description: Brak autoryzacji
     */
    public function addCustomer(): void
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            // Pobierz dane z body requestu
            $data = json_decode(file_get_contents('php://input'), true);

            // Sprawdź czy dane są poprawne
            if (!$this->validateCustomerData($data)) {
                http_response_code(400);
                echo json_encode(['details' => 'Nieprawidłowe dane klienta'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Usuń znaki '-' z numeru VAT
            $cleanVat = str_replace('-', '', $data['customerVat']);

            // Sprawdź czy kontrahent o podanym VAT już istnieje dla tej firmy
            $query = "
                SELECT COUNT(*) as count 
                FROM customers 
                WHERE customerVat = :customerVat 
                AND companyId = :companyId
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'customerVat' => $cleanVat,
                'companyId' => $companyId // zakładam, że companyId jest dostępne w klasie
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'details' => 'Kontrahent o podanym numerze VAT już istnieje w bazie'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $this->db->beginTransaction();

            // Dodaj główny rekord klienta
            $query = "
                INSERT INTO customers (
                    customerName, customerVat, street, city, 
                    postalCode, country, accountNumber,
                    isSupplier, isRecipient, isOffice, companyId
                ) VALUES (
                    :customerName, :customerVat, :street, :city,
                    :postalCode, :country, :accountNumber,
                    :isSupplier, :isRecipient, :isOffice, :companyId
                )
            ";


            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'customerName' => $data['customerName'],
                'customerVat' => $cleanVat,
                'street' => $data['street'],
                'city' => $data['city'],
                'postalCode' => $data['postalCode'],
                'country' => $data['countryId'],
                'accountNumber' => $data['accountNumber'],
                'isSupplier' => $data['isSupplier'],
                'isRecipient' => $data['isRecipient'],
                'isOffice' => $data['isOffice'],
                'companyId' => $companyId
            ]);

            $customerId = $this->db->lastInsertId();

            // Dodaj szczegóły adresu
            if (isset($data['addressDetails'])) {
                $query = "
                    INSERT INTO customerAddressDetails (
                        customerId, name, street, city, 
                        postalCode, country
                    ) VALUES (
                        :customerId, :name, :street, :city,
                        :postalCode, :country
                    )
                ";

                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    'customerId' => $customerId,
                    'name' => $data['addressDetails']['name'],
                    'street' => $data['addressDetails']['street'],
                    'city' => $data['addressDetails']['city'],
                    'postalCode' => $data['addressDetails']['postalCode'],
                    'country' => $data['addressDetails']['countryId']
                ]);
            }

            // Dodaj szczegóły kontaktu
            if (isset($data['contactDetails'])) {
                $query = "
                    INSERT INTO customerContactDetails (
                        customerId, email, phone, website, fax, contactPerson
                    ) VALUES (
                        :customerId, :email, :phone, :website, :fax, :contactPerson
                    )
                ";

                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    'customerId' => $customerId,
                    'email' => $data['contactDetails']['email'],
                    'phone' => $data['contactDetails']['phone'],
                    'website' => $data['contactDetails']['website'],
                    'fax' => $data['contactDetails']['fax'],
                    'contactPerson' => $data['contactDetails']['contactPerson']
                ]);
            }

            $this->db->commit();

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'customerId' => $customerId
            ], JSON_UNESCAPED_UNICODE);
            return;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Błąd podczas dodawania klienta do bazy danych',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            return;
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił nieoczekiwany błąd',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

    public function updateCustomer()
    {
        header('Content-Type: application/json');

        $companyId = decodeToken();

        try {
            // Pobierz id kontrahenta z URL
            $customerId = $_GET['id'];
            if (!$customerId) {
                http_response_code(400); // Bad Request
                echo json_encode(["details" => "Missing or invalid 'id' parameter"]);
                return;
            }

            // Pobierz dane z body requestu
            $data = json_decode(file_get_contents('php://input'), true);

            // Sprawdź czy dane są poprawne
            if (!$this->validateCustomerData($data)) {
                http_response_code(400);
                echo json_encode(['details' => 'Nieprawidłowe dane klienta'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Usuń znaki '-' z numeru VAT
            $cleanVat = str_replace('-', '', $data['customerVat']);

            $this->db->beginTransaction();

            // Zaktualizuj główny rekord klienta
            $query = "
                UPDATE customers SET
                    customerName = :customerName,
                    customerVat = :customerVat,
                    street = :street,
                    city = :city,
                    postalCode = :postalCode,
                    country = :country,
                    accountNumber = :accountNumber,
                    isSupplier = :isSupplier,
                    isRecipient = :isRecipient,
                    isOffice = :isOffice
                WHERE customerId = :customerId AND companyId = :companyId
            ";


            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'customerName' => $data['customerName'],
                'customerVat' => $cleanVat,
                'street' => $data['street'],
                'city' => $data['city'],
                'postalCode' => $data['postalCode'],
                'country' => $data['countryId'],
                'accountNumber' => $data['accountNumber'],
                'isSupplier' => $data['isSupplier'],
                'isRecipient' => $data['isRecipient'],
                'isOffice' => $data['isOffice'],
                'customerId' => $customerId,
                'companyId' => $companyId
            ]);

            // Zaktualizuj szczegóły adresu, jeśli są podane
            if (isset($data['addressDetails'])) {
                $query = "
                    UPDATE customerAddressDetails SET
                        name = :name,
                        street = :street,
                        city = :city,
                        postalCode = :postalCode,
                        country = :country
                    WHERE customerId = :customerId
                ";

                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    'name' => $data['addressDetails']['name'],
                    'street' => $data['addressDetails']['street'],
                    'city' => $data['addressDetails']['city'],
                    'postalCode' => $data['addressDetails']['postalCode'],
                    'country' => $data['addressDetails']['countryId'],
                    'customerId' => $customerId
                ]);
            }

            // Zaktualizuj szczegóły kontaktu, jeśli są podane
            if (isset($data['contactDetails'])) {
                $query = "
                    UPDATE customerContactDetails SET
                        email = :email,
                        phone = :phone,
                        website = :website,
                        fax = :fax,
                        contactPerson = :contactPerson
                    WHERE customerId = :customerId
                ";

                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    'email' => $data['contactDetails']['email'],
                    'phone' => $data['contactDetails']['phone'],
                    'website' => $data['contactDetails']['website'],
                    'fax' => $data['contactDetails']['fax'],
                    'contactPerson' => $data['contactDetails']['contactPerson'],
                    'customerId' => $customerId
                ]);
            }

            $this->db->commit();

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'customerId' => $customerId
            ], JSON_UNESCAPED_UNICODE);
            return;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Błąd podczas aktualizacji danych klienta',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            return;
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił nieoczekiwany błąd',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

    private function validateCustomerData($data)
    {
        $requiredFields = [
            'customerName',
            'customerVat',
            'street',
            'city',
            'postalCode',
            'countryId'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    public function getCompanyDataByNip()
    {
        $gus = new GusApi('fbe5fda9041d40249a77');

        try {
            // Sprawdzenie czy NIP został przekazany w URL
            if (!isset($_GET['nip'])) {
                http_response_code(400);
                echo json_encode([
                    'details' => 'Brak parametru NIP',
                    'code' => 400
                ], JSON_UNESCAPED_UNICODE);
            }

            $nipToCheck = $_GET['nip']; //change to valid nip value
            $nipToCheck = str_replace('-', '', $nipToCheck);

            // Usuń wszystkie niecyfrowe znaki (np. spacje, myślniki)
            $nipToCheck = preg_replace('/\D/', '', $nipToCheck);

            // Sprawdzenie czy NIP ma dokładnie 10 cyfr
            if (strlen($nipToCheck) !== 10) {
                http_response_code(400);
                echo json_encode([
                    'details' => 'NIP musi mieć dokładnie 10 cyfr.',
                    'code' => 400
                ], JSON_UNESCAPED_UNICODE);
            }

            // Wagi do walidacji NIPu
            $weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];

            // Obliczanie sumy kontrolnej
            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                // Rzutowanie znaku na liczbę
                $digit = (int) $nipToCheck[$i];
                $sum += $weights[$i] * $digit;
            }

            // Sprawdzenie cyfry kontrolnej
            $checksum = $sum % 11;
            if ($checksum === 10) {
                $checksum = 0;
            }

            if ($checksum !== (int) $nipToCheck[9]) {
                http_response_code(400);
                echo json_encode([
                    'details' => 'Nieprawidłowy NIP - błędna suma kontrolna.',
                    'code' => 400
                ], JSON_UNESCAPED_UNICODE);
            }


            $gus->login();
            $gusReports = $gus->getByNip($nipToCheck);

            // Jeśli znaleziono dane
            if ($gusReports) {

                $gusReport = $gusReports[0]; // Bierzemy pierwszy wynik


                // Formatowanie danych według wymaganego schematu
                $result = [
                    'customerName' => $gusReport->getName(),
                    'customerVat' => 'PL' . $nipToCheck,
                    'street' => $gusReport->getStreet() . ' ' . $gusReport->getPropertyNumber() .
                        ($gusReport->getApartmentNumber() ? '/' . $gusReport->getApartmentNumber() : ''),
                    'city' => $gusReport->getCity(),
                    'postalCode' => $gusReport->getZipCode(),
                    'country' => 'Polska',
                    'email' => '', // GUS nie udostępnia tych danych
                    'phone' => '', // GUS nie udostępnia tych danych
                ];
                http_response_code(200);
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
            }

            // foreach ($gusReports as $gusReport) {
            //     //you can change report type to other one
            //     $reportType = ReportTypes::REPORT_PERSON;
            //     echo $gusReport->getName();
            //     // echo 'Address: ' . $gusReport->getStreet() . ' ' . $gusReport->getPropertyNumber() . '/' . $gusReport->getApartmentNumber();

            //     $fullReport = $gus->getFullReport($gusReport, $reportType);
            //     var_dump($fullReport);
            // }
        } catch (InvalidUserKeyException $e) {
            http_response_code(400);
            return json_encode([
                'details' => 'Nieprawidłowy klucz API',
                'code' => 400
            ]);
        } catch (NotFoundException $e) {
            http_response_code(400);
            return json_encode([
                'error' => 'Nie znaleziono danych dla podanego NIP',
                'code' => 400,
                'details' => [
                    'sessionStatus' => $gus->getSessionStatus(),
                    'messageCode' => $gus->getMessageCode(),
                    'message' => $gus->getMessage()
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(400);
            return json_encode([
                'error' => 'Wystąpił błąd podczas przetwarzania zapytania',
                'code' => 400,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteCustomer($id)
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;

        if (!$id) {
            http_response_code(400); // Bad Request
            echo json_encode(["details" => "Missing or invalid 'id' parameter"]);
            return;
        }

        try {
            $companyId = decodeToken();
            // Validate input
            if (!is_numeric($id)) {
                http_response_code(400);
                echo json_encode([

                    'details' => 'Będny identyfikator'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $query = "DELETE FROM customers WHERE customerId = :id and companyId = :companyId";
            $stmtMain = $this->db->prepare($query);

            $result = $stmtMain->execute([
                'id' => $id,
                'companyId' => $companyId // zakładam, że companyId jest dostępne w klasie
            ]);

            $query = "DELETE FROM customerAddressDetails WHERE customerId = :id";
            $stmt = $this->db->prepare($query);
            // Przypisanie parametrów do zapytania
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $query = "DELETE FROM customerContactDetails WHERE customerId = :id";
            $stmt = $this->db->prepare($query);
            // Przypisanie parametrów do zapytania
            $stmt->bindParam(':id', $id);
            $stmt->execute();


            if (!$result || $stmtMain->rowCount() == 0) {
                http_response_code(400);
                echo json_encode([

                    'details' => 'nie znalezino kontrahenta'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }


            http_response_code(200);
            echo json_encode(['details' => 'Usunięto kontrahenta'], JSON_UNESCAPED_UNICODE);
            return;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Błąd podczas usuwania klienta',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            return;
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił nieoczekiwany błąd',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
    }
}
