<?php

class NoteController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Pobieranie listy uwag
     */
    public function getNotes() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $query = "SELECT ID_UWAGA, TRESC, DLAPRZYCHODU, DLAROZCHODU 
                     FROM UWAGA 
                     WHERE COMPANY_ID = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['companyId' => $companyId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Konwersja wartości na odpowiednie typy
            foreach ($results as &$row) {
                $row['ID_UWAGA'] = (int)$row['ID_UWAGA'];
                $row['DLAPRZYCHODU'] = (bool)$row['DLAPRZYCHODU'];
                $row['DLAROZCHODU'] = (bool)$row['DLAROZCHODU'];
            }

            echo json_encode(['data' => $results]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas pobierania uwag',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Dodawanie nowej uwagi
     */
    public function addNote() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['TRESC'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Brak wymaganego pola TRESC']);
                return;
            }

            $query = "INSERT INTO UWAGA (TRESC, DLAPRZYCHODU, DLAROZCHODU, COMPANY_ID) 
                     VALUES (:tresc, :dlaPrzychodu, :dlaRozchodu, :companyId)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'tresc' => $data['TRESC'],
                'dlaPrzychodu' => $data['DLAPRZYCHODU'] ?? false,
                'dlaRozchodu' => $data['DLAROZCHODU'] ?? false,
                'companyId' => $companyId
            ]);

            $newId = $this->db->lastInsertId();
            echo json_encode([
                'message' => 'Uwaga została dodana',
                'id' => $newId
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas dodawania uwagi',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Aktualizacja uwagi
     */
    public function updateNote() {
        header('Content-Type: application/json');
        $companyId = decodeToken();
        $id = $_GET['id'] ?? null;

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Sprawdzenie czy uwaga istnieje i należy do firmy
            $checkQuery = "SELECT ID_UWAGA FROM UWAGA 
                         WHERE ID_UWAGA = :id AND COMPANY_ID = :companyId";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute(['id' => $id, 'companyId' => $companyId]);
            
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Uwaga nie została znaleziona']);
                return;
            }

            $query = "UPDATE UWAGA 
                     SET TRESC = :tresc,
                         DLAPRZYCHODU = :dlaPrzychodu,
                         DLAROZCHODU = :dlaRozchodu
                     WHERE ID_UWAGA = :id 
                     AND COMPANY_ID = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'tresc' => $data['TRESC'],
                'dlaPrzychodu' => $data['DLAPRZYCHODU'] ?? false,
                'dlaRozchodu' => $data['DLAROZCHODU'] ?? false,
                'companyId' => $companyId
            ]);

            echo json_encode(['message' => 'Uwaga została zaktualizowana']);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas aktualizacji uwagi',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Usuwanie uwagi
     */
    public function deleteNote() {
        header('Content-Type: application/json');
        $companyId = decodeToken();
        $id = $_GET['id'] ?? null;

        try {
            $query = "DELETE FROM UWAGA 
                     WHERE ID_UWAGA = :id 
                     AND COMPANY_ID = :companyId";
                     
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'companyId' => $companyId
            ]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Uwaga nie została znaleziona']);
                return;
            }

            echo json_encode(['message' => 'Uwaga została usunięta']);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas usuwania uwagi',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
} 