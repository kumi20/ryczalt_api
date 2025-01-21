<?php
/**
 * Kontroler obsługujący typy dokumentów.
 */
require_once 'token.php';

class DocumentTypeController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Pobiera listę typów dokumentów dla danej firmy
     */
    public function getDocumentTypes()
    {
        $companyId = decodeToken();
        

        try {
            $sql = 'SELECT id as documentTypeId, name, signature FROM document_type';

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
    
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            // header('Content-Type: application/json');
            http_response_code(200);

            // Po pobraniu wyników
            foreach ($results as &$row) {
                // Konwersja id na integer
                $row['documentTypeId'] = (int)$row['documentTypeId'];
                // name i signature pozostają jako string, więc nie wymagają konwersji
            }

            echo json_encode($results, JSON_UNESCAPED_UNICODE);
        }
        catch (\PDOException $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Błąd pobierania danych',
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