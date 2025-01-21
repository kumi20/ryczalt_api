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


class CuuntryController
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getCountry()
    {
        $companyId = decodeToken();

        try{
            $sql = 'SELECT * FROM countries WHERE companyId IS NULL OR companyId = :companyId';

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->execute();
    
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode($results, JSON_UNESCAPED_UNICODE);
        }
        catch (\PDOException $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Błąd ponieranie danych',
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