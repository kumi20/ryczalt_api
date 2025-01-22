<?php
require_once 'token.php';

class InternalEvidenceController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Sprawdza czy miesiąc jest zamknięty
     */
    private function isMonthClosed($month, $year, $companyId) {
        $query = "SELECT COUNT(*) as count FROM close_month 
                 WHERE month = :month 
                 AND year = :year 
                 AND company_Id = :companyId";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'month' => $month,
            'year' => $year,
            'companyId' => $companyId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function getInternalEvidence() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $month = $_GET['month'] ?? null;
            $year = $_GET['year'] ?? null;
            $skip = isset($_GET['skip']) ? (int)$_GET['skip'] : 0;
            $take = isset($_GET['take']) ? (int)$_GET['take'] : 10;

            if (!$month || !$year) {
                http_response_code(400);
                echo json_encode(['details' => 'Brak wymaganego parametru month lub year']);
                return;
            }

            $query = "SELECT 
                        internalEvidenceId,
                        isCoast,
                        documentNumber,
                        documentDate,
                        description,
                        amount,
                        price,
                        unit,
                        personIssuing,
                        taxVat,
                        remarks,
                        companyId,
                        userInsert,
                        dateInsert,
                        userUpdate,
                        dateUpdate,
                        isBooked,
                        isClosed
                     FROM internal_Evidence 
                     WHERE MONTH(documentDate) = :month 
                     AND YEAR(documentDate) = :year 
                     AND companyId = :companyId 
                     ORDER BY documentDate ASC 
                     LIMIT :take OFFSET :skip";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':month', $month, PDO::PARAM_INT);
            $stmt->bindValue(':year', $year, PDO::PARAM_INT);
            $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->bindValue(':take', $take, PDO::PARAM_INT);
            $stmt->bindValue(':skip', $skip, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Konwersja typów danych
            foreach ($results as &$row) {
                $row['internalEvidenceId'] = (int)$row['internalEvidenceId'];
                $row['isCoast'] = (bool)$row['isCoast'];
                $row['amount'] = (float)$row['amount'];
                $row['price'] = (float)$row['price'];
                $row['companyId'] = (int)$row['companyId'];
                $row['userInsert'] = (int)$row['userInsert'];
                $row['userUpdate'] = $row['userUpdate'] ? (int)$row['userUpdate'] : null;
                $row['isBooked'] = (bool)$row['isBooked'];
                $row['isClosed'] = (bool)$row['isClosed'];
            }

            echo json_encode(['data' => $results]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas pobierania danych',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function addInternalEvidence() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Formatowanie daty do formatu yyyy-MM-dd
            $documentDate = date('Y-m-d', strtotime($data['documentDate']));
            $month = date('n', strtotime($documentDate));
            $year = date('Y', strtotime($documentDate));

            if ($this->isMonthClosed($month, $year, $companyId)) {
                http_response_code(400);
                echo json_encode(['details' => 'Ten miesiąc jest zamknięty']);
                return;
            }

            $query = "INSERT INTO internal_Evidence (
                isCoast, documentNumber, documentDate, description, amount,
                price, unit, personIssuing, taxVat, remarks, companyId,
                userInsert, isBooked, isClosed
            ) VALUES (
                :isCoast, :documentNumber, :documentDate, :description, :amount,
                :price, :unit, :personIssuing, :taxVat, :remarks, :companyId,
                :userInsert, :isBooked, :isClosed
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'isCoast' => (int)$data['isCoast'],
                'documentNumber' => $data['documentNumber'],
                'documentDate' => $documentDate,
                'description' => $data['description'],
                'amount' => $data['amount'],
                'price' => $data['price'],
                'unit' => $data['unit'],
                'personIssuing' => $data['personIssuing'],
                'taxVat' => $data['taxVat'],
                'remarks' => $data['remarks'],
                'companyId' => $companyId,
                'userInsert' => getUserId(),
                'isBooked' => (int)$data['isBooked'],
                'isClosed' => (int)$data['isClosed']
            ]);

            $lastInsertId = $this->db->lastInsertId();

            echo json_encode([
                'success' => true,
                'details' => 'Dodano nowy wpis do ewidencji',
                'internalEvidenceId' => $lastInsertId
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas dodawania wpisu',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateInternalEvidence() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;

            // Formatowanie daty do formatu yyyy-MM-dd
            $documentDate = date('Y-m-d', strtotime($data['documentDate']));
            $month = date('n', strtotime($documentDate));
            $year = date('Y', strtotime($documentDate));

            if ($this->isMonthClosed($month, $year, $companyId)) {
                http_response_code(400);
                echo json_encode(['details' => 'Ten miesiąc jest zamknięty']);
                return;
            }

            $query = "UPDATE internal_Evidence SET 
                isCoast = :isCoast,
                documentNumber = :documentNumber,
                documentDate = :documentDate,
                description = :description,
                amount = :amount,
                price = :price,
                unit = :unit,
                personIssuing = :personIssuing,
                taxVat = :taxVat,
                remarks = :remarks,
                userUpdate = :userUpdate,
                isBooked = :isBooked,
                isClosed = :isClosed
            WHERE internalEvidenceId = :id AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'isCoast' => (int)$data['isCoast'],
                'documentNumber' => $data['documentNumber'],
                'documentDate' => $documentDate,
                'description' => $data['description'],
                'amount' => $data['amount'],
                'price' => $data['price'],
                'unit' => $data['unit'],
                'personIssuing' => $data['personIssuing'],
                'taxVat' => $data['taxVat'],
                'remarks' => $data['remarks'],
                'userUpdate' => getUserId(),
                'isBooked' => (int)$data['isBooked'],
                'isClosed' => (int)$data['isClosed'],
                'id' => $id,
                'companyId' => $companyId
            ]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['details' => 'Nie znaleziono wpisu do aktualizacji']);
                return;
            }

            echo json_encode([
                'success' => true,
                'details' => 'Zaktualizowano wpis w ewidencji'
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas aktualizacji wpisu',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteInternalEvidence() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $id = $_GET['id'] ?? null;

            // Sprawdzamy czy wpis istnieje i pobieramy datę dokumentu
            $checkQuery = "SELECT documentDate FROM internal_Evidence 
                         WHERE internalEvidenceId = :id 
                         AND companyId = :companyId";
            
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute([
                'id' => $id,
                'companyId' => $companyId
            ]);
            
            $record = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record) {
                http_response_code(404);
                echo json_encode(['details' => 'Nie znaleziono wpisu do usunięcia']);
                return;
            }

            $month = date('n', strtotime($record['documentDate']));
            $year = date('Y', strtotime($record['documentDate']));

            if ($this->isMonthClosed($month, $year, $companyId)) {
                http_response_code(400);
                echo json_encode(['details' => 'Ten miesiąc jest zamknięty']);
                return;
            }

            $query = "DELETE FROM internal_Evidence 
                     WHERE internalEvidenceId = :id 
                     AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'companyId' => $companyId
            ]);

            echo json_encode([
                'success' => true,
                'details' => 'Usunięto wpis z ewidencji'
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas usuwania wpisu',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
} 