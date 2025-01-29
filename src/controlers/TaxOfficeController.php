<?php
/**
 * Kontroler obsługujący urzędy skarbowe.
 */
require_once 'token.php';

class TaxOfficeController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Pobiera listę urzędów skarbowych
     */
    public function getTaxOffices()
    {
        $companyId = decodeToken();

        try {
            $sql = 'SELECT 
                ID_URZAD_SKARBOWY as taxOfficeId,
                WOJEWODZTWO as voivodeship,
                TYP as type,
                NAZWA as name,
                KOD_POCZTOWY as postalCode,
                MIEJSCOWOSC as city,
                ADRES as address,
                TELEFON as phone,
                FAX as fax,
                EMAIL as email,
                KOD as code
                FROM URZAD_SKARBOWY';

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
    
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            http_response_code(200);

            // Konwersja typów danych
            foreach ($results as &$row) {
                // Konwersja id na integer
                $row['taxOfficeId'] = (int)$row['taxOfficeId'];
                
                // Upewniamy się że pola tekstowe nie są null
                $row['voivodeship'] = $row['voivodeship'] ?? '';
                $row['type'] = $row['type'] ?? '';
                $row['name'] = $row['name'] ?? '';
                $row['postalCode'] = $row['postalCode'] ?? '';
                $row['city'] = $row['city'] ?? '';
                $row['address'] = $row['address'] ?? '';
                $row['phone'] = $row['phone'] ?? '';
                $row['fax'] = $row['fax'] ?? '';
                $row['email'] = $row['email'] ?? '';
                $row['code'] = $row['code'] ?? '';
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