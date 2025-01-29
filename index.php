<?php
use OpenApi\Annotations as OA;
/**
 * @OA\Info(
 *     title="Api ryczat",
 *     version="1.0"
 * )
 */
require_once 'src/controlers/token.php';
require_once 'src/controlers/CustomerController.php';
require_once 'src/controlers/AuthController.php';
require_once 'src/controlers/LicenseController.php';
require_once 'src/controlers/Country.php';
require_once 'src/controlers/DocumentType.php';
require_once 'src/controlers/FlatRateController.php';
require_once 'src/controlers/CloseMonthController.php';
require_once 'src/controlers/VatRegisterController.php';
require_once 'src/controlers/VatPurchaseController.php';
require_once 'src/controlers/InternalEvidenceController.php';
require_once 'src/controlers/zus.php';
require_once 'src/controlers/FlatRateTaxController.php';
require_once 'src/controlers/TaxOfficeController.php';
require_once 'src/controlers/NoteController.php';

// Routing
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);   

//Połączenie z bazą danyc
$dsn = 'mysql:host=localhost;dbname=ryczalt';
$username = 'root';
$password = '';
$db = new PDO($dsn, $username, $password);




if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/api_ryczalt/login') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    $authController = new AuthController($db);
    $authController->login(file_get_contents('php://input'));
    return;

}

// Pobieranie informacji o licencji
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/api_ryczalt/license') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    checkToken();
    $companyId = decodeToken();
    $licenseController = new LicenseController($db);
    $result = $licenseController->getLicenseInfo($companyId);
    return;

}

// Pobieranie informacji o licencji
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/api_ryczalt/country') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    checkToken();
    $licenseController = new CuuntryController($db);
    $result = $licenseController->getCountry();
    return;
}

// Pobieranie listy kontrahentów
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/customers') {
    checkToken();
    $controller = new CustomerController($db);
    $controller->getCustomers();
    return;
}

// Dodawanie nowego kontrahenta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/customers') {
    checkToken();
    $controller = new CustomerController($db);
    $controller->addCustomer();
    return;
}

// aktualizacja danych kontrahenta
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $path === '/api_ryczalt/customers') {
    checkToken();
    $controller = new CustomerController($db);
    $controller->updateCustomer();
    return;
}

//usuwanie kontrahenta
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $path === '/api_ryczalt/customers') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    checkToken(); // Funkcja sprawdzająca token autoryzacyjny
    
    $id = intval($matches[1]); // Wyciągnięcie ID z dopasowanego wzorca (na pewno jako liczba całkowita)
    
    $controller = new CustomerController($db);
    $controller->deleteCustomer($id); // Przekazanie ID do funkcji kontrolera
    return;
}

//pobieranie z gus
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/customers/gus') {
    checkToken();
    $controller = new CustomerController($db);
    $controller->getCompanyDataByNip();
    return;
}

//pobieranie typów dokumentów
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/document-types') {
    checkToken();
    $controller = new DocumentTypeController($db);
    $controller->getDocumentTypes();
    return;
}

// Pobieranie wpisów ryczałtu
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/flat-rate') {
    checkToken();
    $controller = new FlatRateController($db);
    $controller->getFlatRates();
    return;
}

// Dodawanie nowego wpisu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/flat-rate') {
    checkToken();
    $controller = new FlatRateController($db);
    $controller->addFlatRate();
    return;
}

// Aktualizacja wpisu
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $path === '/api_ryczalt/flat-rate') {
    checkToken();
    $controller = new FlatRateController($db);
    $controller->updateFlatRate();
    return;
}

// Usuwanie wpisu
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $path === '/api_ryczalt/flat-rate') {
    checkToken();
    $controller = new FlatRateController($db);
    $controller->deleteFlatRate();
    return;
}

// Pobieranie podsumowania miesiąca
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/flat-rate/summary') {
    checkToken();
    $controller = new FlatRateController($db);
    $controller->getSummaryMonth();
    return;
}

// Zamknięcie miesiąca
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/close-month') {
    checkToken();
    $controller = new CloseMonthController($db);
    $controller->closeMonth();
    return;
}

//otwieranie miesiąca
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/open-month') {
    checkToken();
    $controller = new CloseMonthController($db);
    $controller->openMonth();
    return;
}

//isMonthClosed
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/statusMonth') {
    checkToken();
    $controller = new CloseMonthController($db);
    $controller->isMonthClosed();
    return;
}


// Pobieranie wpisów rejestru vat sprzedazy
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/registeVat/sell') {
    checkToken();
    $controller = new VatRegisterController($db);
    $controller->getVatRegister();
    return;
}

//dodawanie wpisu rejestru vat sprzedazy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/registeVat/sell') {
    checkToken();
    $controller = new VatRegisterController($db);
    $controller->addVatRegister();
    return;
}

//aktualizacja wpisu rejestru vat sprzedazy
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $path === '/api_ryczalt/registeVat/sell') {
    checkToken();
    $controller = new VatRegisterController($db);
    $controller->updateVatRegister();
    return;
}

//usuwanie wpisu rejestru vat sprzedazy
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $path === '/api_ryczalt/registeVat/sell') {
    checkToken();
    $controller = new VatRegisterController($db);
    $controller->deleteVatRegister();
    return;
}

// Pobieranie podsumowania miesiąca
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/registeVat/summaryMonth') {
    checkToken();
    $controller = new VatRegisterController($db);
    $controller->getSummaryMonth();
    return;
}

// Pobieranie wpisów rejestru VAT zakupu
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/registeVat/buy') {
    checkToken();
    $controller = new VatPurchaseController($db);
    $controller->getVatPurchase();
    return;
}

// Dodawanie wpisu rejestru VAT zakupu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/registeVat/buy') {
    checkToken();
    $controller = new VatPurchaseController($db);
    $controller->addVatPurchase();
    return;
}

// Aktualizacja wpisu rejestru VAT zakupu
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $path === '/api_ryczalt/registeVat/buy') {
    checkToken();
    $controller = new VatPurchaseController($db);
    $controller->updateVatPurchase();
    return;
}

// Usuwanie wpisu rejestru VAT zakupu
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $path === '/api_ryczalt/registeVat/buy') {
    checkToken();
    $controller = new VatPurchaseController($db);
    $controller->deleteVatPurchase();
    return;
}

// Pobieranie podsumowania miesiąca dla rejestru VAT zakupu
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/registeVat/summaryMonthBuy') {
    checkToken();
    $controller = new VatPurchaseController($db);
    $controller->getSummaryMonth();
    return;
}

// Pobieranie wpisów wewnętrznego dowodu
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/internalEvidence') {
    checkToken();
    $controller = new InternalEvidenceController($db);
    $controller->getInternalEvidence();
    return;
}

// Dodawanie nowego wpisu wewnętrznego dowodu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/internalEvidence') {
    checkToken();
    $controller = new InternalEvidenceController($db);
    $controller->addInternalEvidence();
    return;
}

// Aktualizacja wpisu wewnętrznego dowodu
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $path === '/api_ryczalt/internalEvidence') {
    checkToken();
    $controller = new InternalEvidenceController($db);
    $controller->updateInternalEvidence();
    return;
}

// Usuwanie wpisu wewnętrznego dowodu
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $path === '/api_ryczalt/internalEvidence') {
    checkToken();
    $controller = new InternalEvidenceController($db);
    $controller->deleteInternalEvidence();
    return;
}

// Pobieranie wpisów ZUS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/zus') {
    checkToken();
    $controller = new ZusController($db);
    $controller->getContributionsWithIncome();
    return;
}

//pobranie zaplaconych skladek
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/zus/contributions') {
    checkToken();
    $controller = new ZusController($db);
    $controller->getContributions();
    return;
}

//dodawanie wpisu zaplaconych skladek
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/zus/contributions') {
    checkToken();
    $controller = new ZusController($db);
    $controller->createContribution();
    return;
}

//aktualizacja wpisu zaplaconych skladek
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $path === '/api_ryczalt/zus/contributions') {
    checkToken();
    $controller = new ZusController($db);
    $controller->updateContribution();
    return;
}

//usuwanie wpisu zaplaconych skladek
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $path === '/api_ryczalt/zus/contributions') {
    checkToken();
    $controller = new ZusController($db);
    $controller->deleteContribution();
    return;
}

// Pobieranie podatków ryczałtowych
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/flat-rate-tax') {
    checkToken();
    $controller = new FlatRateTaxController($db);
    $controller->getFlatRateTax();
    return;
}

// Dodawanie nowego podatku ryczałtowego
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/flat-rate-tax') {
    checkToken();
    $controller = new FlatRateTaxController($db);
    $controller->createFlatRateTax();
    return;
}

// Aktualizacja podatku ryczałtowego
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $path === '/api_ryczalt/flat-rate-tax') {
    checkToken();
    $controller = new FlatRateTaxController($db);
    $controller->updateFlatRateTax();
    return;
}

// Usuwanie podatku ryczałtowego
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $path === '/api_ryczalt/flat-rate-tax') {
    checkToken();
    $controller = new FlatRateTaxController($db);
    $controller->deleteFlatRateTax();
    return;
}

// Obliczanie podatku ryczałtowego
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/flat-rate-tax/calculate') {
    checkToken();
    $controller = new FlatRateTaxController($db);
    $controller->calculateFlatRateTax();
    return;
}

// Pobieranie listy urzędów skarbowych
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/tax-offices') {
    checkToken();
    $controller = new TaxOfficeController($db);
    $controller->getTaxOffices();
    return;
}

// Pobieranie uwag
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/api_ryczalt/notes') {
    checkToken();
    $controller = new NoteController($db);
    $controller->getNotes();
    return;
}

// Dodawanie nowej uwagi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/api_ryczalt/notes') {
    checkToken();
    $controller = new NoteController($db);
    $controller->addNote();
    return;
}

// Aktualizacja uwagi
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && $path === '/api_ryczalt/notes') {
    checkToken();
    $controller = new NoteController($db);
    $controller->updateNote();
    return;
}

// Usuwanie uwagi
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $path === '/api_ryczalt/notes') {
    checkToken();
    $controller = new NoteController($db);
    $controller->deleteNote();
    return;
}

function checkToken() {
    // Pobranie tokenu z nagłówków
    $headers = getallheaders();
   
    $token = $headers['Authorization'] ?? null;
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}




http_response_code(404);
echo json_encode(["error" => "Not Found"]);

// switch ($path) {
//     case '/api_ryczalt/customers':
//         $controller = new CustomerController();
//         $controller->getCustomers();
//         break;

//     case '/docs':
//         // Przekierowanie do pliku z dokumentacją Redoc
//         header("Location: ./docs.html");
//         break;

//     default:
//         http_response_code(404);
//         echo json_encode(["error" => "Not Found"]);
//         break;
// }
