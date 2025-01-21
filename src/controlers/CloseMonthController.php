<?php

class CloseMonthController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Sprawdza czy dany miesiąc jest zamknięty
     */
    public function isMonthClosed()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $month = (int)($_GET['month'] ?? null);
            $year = (int)($_GET['year'] ?? null);

            if (!$month || !$year) {
                http_response_code(400);
                echo json_encode(['detail' => 'Brak wymaganego parametru month lub year'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Walidacja miesiąca
            if ($month < 1 || $month > 12) {
                http_response_code(400);
                echo json_encode(['detail' => 'Nieprawidłowy numer miesiąca (1-12)'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $query = "SELECT COUNT(*) as count FROM close_month 
                     WHERE month = :month 
                     AND year = :year 
                     AND company_id = :companyId";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'month' => $month,
                'year' => $year,
                'companyId' => $companyId
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode([
                'isClosed' => (bool)($result['count'] > 0)
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas sprawdzania statusu miesiąca',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Zamyka wybrany miesiąc
     */
    public function closeMonth()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
            $month = (int)($requestData['month'] ?? null);
            $year = (int)($requestData['year'] ?? null);

            if (!$month || !$year) {
                http_response_code(400);
                echo json_encode(['error' => 'Brak wymaganego parametru month lub year'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Walidacja miesiąca
            if ($month < 1 || $month > 12) {
                http_response_code(400);
                echo json_encode(['error' => 'Nieprawidłowy numer miesiąca (1-12)'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Sprawdź czy miesiąc nie jest już zamknięty
            $query = "SELECT COUNT(*) as count FROM close_month 
                     WHERE month = :month 
                     AND year = :year 
                     AND company_id = :companyId";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'month' => $month,
                'year' => $year,
                'companyId' => $companyId
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Ten miesiąc jest już zamknięty'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Dodaj wpis zamknięcia miesiąca
            $query = "INSERT INTO close_month (month, year, company_id) 
                     VALUES (:month, :year, :companyId)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'month' => $month,
                'year' => $year,
                'companyId' => $companyId
            ]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'details' => 'Miesiąc został zamknięty'
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas zamykania miesiąca',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Otwiera miesiąc (usuwa wpis zamknięcia)
     */
    public function openMonth()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
            $month = (int)($requestData['month'] ?? null);
            $year = (int)($requestData['year'] ?? null);

            if (!$month || !$year) {
                http_response_code(400);
                echo json_encode(['detail' => 'Brak wymaganego parametru month lub year'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Walidacja miesiąca
            if ($month < 1 || $month > 12) {
                http_response_code(400);
                echo json_encode(['detail' => 'Nieprawidłowy numer miesiąca (1-12)'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Sprawdź czy miesiąc jest zamknięty
            $query = "SELECT COUNT(*) as count FROM close_month 
                     WHERE month = :month 
                     AND year = :year 
                     AND company_id = :companyId";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'month' => $month,
                'year' => $year,
                'companyId' => $companyId
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] == 0) {
                http_response_code(400);
                echo json_encode(['detail' => 'Ten miesiąc nie jest zamknięty'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Usuń wpis zamknięcia miesiąca
            $query = "DELETE FROM close_month 
                     WHERE month = :month 
                     AND year = :year 
                     AND company_id = :companyId";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'month' => $month,
                'year' => $year,
                'companyId' => $companyId
            ]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'details' => 'Zamknięcie miesiąca zostało usunięte'
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas otwierania miesiąca',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
} 