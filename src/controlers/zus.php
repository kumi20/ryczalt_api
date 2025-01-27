<?php
require_once 'token.php';

class ZusController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getContributions()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        $year = $_GET['year'] ?? null;

        try {
            $query = "SELECT 
                contributionsZUSId,
                month,
                year,
                isContributionHolidays,
                social,
                isSocialPaid,
                dateSocialPaid,
                contributionHealth,
                isHealthPaid,
                dateHealthPaid,
                fpfgsw,
                isFpfgswPaid,
                dateFpfgswPaid,
                fp,
                isFpPaid,
                dateFpPaid,
                companyId,
                dateCreated,
                dateModified,
                userIdCreated,
                userIdModified
            FROM contributionsZUS 
            WHERE companyId = :companyId AND year = :year";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['companyId' => $companyId, 'year' => $year]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatowanie kwot i dat
            foreach ($results as &$row) {
                $row['social'] = number_format((float) $row['social'], 2, '.', '');
                $row['contributionHealth'] = number_format((float) $row['contributionHealth'], 2, '.', '');
                $row['fpfgsw'] = number_format((float) $row['fpfgsw'], 2, '.', '');
                $row['fp'] = number_format((float) $row['fp'], 2, '.', '');
                $row['month'] = (int) $row['month'];
                $row['year'] = (int) $row['year'];
                
                // Konwersja wartości logicznych
                $row['isContributionHolidays'] = (bool) $row['isContributionHolidays'];
                $row['isSocialPaid'] = (bool) $row['isSocialPaid'];
                $row['isHealthPaid'] = (bool) $row['isHealthPaid'];
                $row['isFpfgswPaid'] = (bool) $row['isFpfgswPaid'];
                $row['isFpPaid'] = (bool) $row['isFpPaid'];

                // Formatowanie dat do yyyy-MM-dd
                if ($row['dateSocialPaid']) {
                    $row['dateSocialPaid'] = date('Y-m-d', strtotime($row['dateSocialPaid']));
                }
                if ($row['dateHealthPaid']) {
                    $row['dateHealthPaid'] = date('Y-m-d', strtotime($row['dateHealthPaid']));
                }
                if ($row['dateFpfgswPaid']) {
                    $row['dateFpfgswPaid'] = date('Y-m-d', strtotime($row['dateFpfgswPaid']));
                }
                if ($row['dateFpPaid']) {
                    $row['dateFpPaid'] = date('Y-m-d', strtotime($row['dateFpPaid']));
                }
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

    public function createContribution()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['month']) || !isset($data['year'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Brak wymaganych pól month i year']);
                return;
            }

            $query = "INSERT INTO contributionsZUS (
                month,
                year,
                isContributionHolidays,
                social,
                isSocialPaid,
                dateSocialPaid,
                contributionHealth,
                isHealthPaid,
                dateHealthPaid,
                fpfgsw,
                isFpfgswPaid,
                dateFpfgswPaid,
                fp,
                isFpPaid,
                dateFpPaid,
                companyId,
                dateCreated,
                userIdCreated
            ) VALUES (
                :month,
                :year,
                :isContributionHolidays,
                :social,
                :isSocialPaid,
                :dateSocialPaid,
                :contributionHealth,
                :isHealthPaid,
                :dateHealthPaid,
                :fpfgsw,
                :isFpfgswPaid,
                :dateFpfgswPaid,
                :fp,
                :isFpPaid,
                :dateFpPaid,
                :companyId,
                NOW(),
                :userIdCreated
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'month' => $data['month'],
                'year' => $data['year'],
                'isContributionHolidays' => (int)$data['isContributionHolidays'] ?? false,
                'social' => $data['social'] ?? 0,
                'isSocialPaid' => (int)$data['isSocialPaid'] ?? false,
                'dateSocialPaid' => $data['dateSocialPaid'] ? date('Y-m-d', strtotime($data['dateSocialPaid'])) : null,
                'contributionHealth' => $data['contributionHealth'] ?? 0,
                'isHealthPaid' => (int)$data['isHealthPaid'] ?? false,
                'dateHealthPaid' => $data['dateHealthPaid'] ? date('Y-m-d', strtotime($data['dateHealthPaid'])) : null,
                'fpfgsw' => $data['fpfgsw'] ?? 0,
                'isFpfgswPaid' => (int)$data['isFpfgswPaid'] ?? false,
                'dateFpfgswPaid' => $data['dateFpfgswPaid'] ? date('Y-m-d', strtotime($data['dateFpfgswPaid'])) : null,
                'fp' => $data['fp'] ?? 0,
                'isFpPaid' => (int)$data['isFpPaid'] ?? false,
                'dateFpPaid' => $data['dateFpPaid'] ? date('Y-m-d', strtotime($data['dateFpPaid'])) : null,
                'companyId' => $companyId,
                'userIdCreated' => getUserId()
            ]);

            $newId = $this->db->lastInsertId();
            echo json_encode(['message' => 'Składka ZUS została dodana', 'id' => $newId]);

        } catch (\Exception $e) {
            http_response_code(400);
                echo json_encode([
                'error' => 'Wystąpił błąd podczas dodawania składki ZUS',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateContribution()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        $id = $_GET['id'] ?? null;
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Sprawdzenie czy składka istnieje i należy do firmy
            $checkQuery = "SELECT contributionsZUSId FROM contributionsZUS WHERE contributionsZUSId = :id AND companyId = :companyId";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute(['id' => $id, 'companyId' => $companyId]);
            
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Składka ZUS nie została znaleziona']);
                return;
            }

            $query = "UPDATE contributionsZUS SET
                month = :month,
                year = :year,
                isContributionHolidays = :isContributionHolidays,
                social = :social,
                isSocialPaid = :isSocialPaid,
                dateSocialPaid = :dateSocialPaid,
                contributionHealth = :contributionHealth,
                isHealthPaid = :isHealthPaid,
                dateHealthPaid = :dateHealthPaid,
                fpfgsw = :fpfgsw,
                isFpfgswPaid = :isFpfgswPaid,
                dateFpfgswPaid = :dateFpfgswPaid,
                fp = :fp,
                isFpPaid = :isFpPaid,
                dateFpPaid = :dateFpPaid,
                dateModified = NOW(),
                userIdModified = :userIdModified
            WHERE contributionsZUSId = :id AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'month' => $data['month'],
                'year' => $data['year'],
                'isContributionHolidays' => (int)$data['isContributionHolidays'] ?? false,
                'social' => $data['social'] ?? 0,
                'isSocialPaid' => (int)$data['isSocialPaid'] ?? false,
                'dateSocialPaid' => $data['dateSocialPaid'] ? date('Y-m-d', strtotime($data['dateSocialPaid'])) : null,
                'contributionHealth' => $data['contributionHealth'] ?? 0,
                'isHealthPaid' => (int)$data['isHealthPaid'] ?? false,
                'dateHealthPaid' => $data['dateHealthPaid'] ? date('Y-m-d', strtotime($data['dateHealthPaid'])) : null,
                'fpfgsw' => $data['fpfgsw'] ?? 0,
                'isFpfgswPaid' => (int)$data['isFpfgswPaid'] ?? false,
                'dateFpfgswPaid' => $data['dateFpfgswPaid'] ? date('Y-m-d', strtotime($data['dateFpfgswPaid'])) : null,
                'fp' => $data['fp'] ?? 0,
                'isFpPaid' => (int)$data['isFpPaid'] ?? false,
                'dateFpPaid' => $data['dateFpPaid'] ? date('Y-m-d', strtotime($data['dateFpPaid'])) : null,
                'companyId' => $companyId,
                'userIdModified' => getUserId()
            ]);

            echo json_encode(['message' => 'Składka ZUS została zaktualizowana']);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas aktualizacji składki ZUS',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteContribution()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        $id = $_GET['id'] ?? null;
        
        try {
            $query = "DELETE FROM contributionsZUS WHERE contributionsZUSId = :id AND companyId = :companyId";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id, 'companyId' => $companyId]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Składka ZUS nie została znaleziona']);
                return;
            }

            echo json_encode(['message' => 'Składka ZUS została usunięta']);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas usuwania składki ZUS',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getContributionsWithIncome()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $year = $_GET['year'] ?? null;
            $month = $_GET['month'] ?? null;

            if (!$year) {
                http_response_code(400);
                echo json_encode(['details' => 'Brak wymaganego parametru year']);
                return;
            }

            // Pobieranie składek
            $query = "SELECT 
                c.id,
                c.base,
                c.year,
                c.social,
                c.sickness,
                c.social_u,
                c.sickness_u,
                c.FGSP,
                COALESCE(
                    (SELECT SUM(
                        COALESCE(rate3, 0) + 
                        COALESCE(rate5_5, 0) + 
                        COALESCE(rate8_5, 0) + 
                        COALESCE(rate10, 0) + 
                        COALESCE(rate12, 0) + 
                        COALESCE(rate12_5, 0) + 
                        COALESCE(rate14, 0) + 
                        COALESCE(rate15, 0) + 
                        COALESCE(rate17, 0)
                    )
                    FROM flate_rate fr 
                    WHERE YEAR(fr.dateOfReceipt) = :year
                    AND MONTH(fr.dateOfReceipt) <= :month
                    AND fr.companyId = :companyId
                    ), 0) as totalIncome,
                COALESCE(
                    (SELECT social 
                    FROM contributionsZUS 
                    WHERE (year = :year AND month = :month - 1)
                    OR (year = :year - 1 AND month = 12 AND :month = 1)
                    AND isSocialPaid = 1
                    LIMIT 1
                    ), 0) as previousMonthSocial
            FROM ZUS c
            WHERE c.year = :year";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'year' => $year,
                'month' => $month,
                'companyId' => $companyId
            ]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Konwersja typów danych
            foreach ($results as &$row) {
                $row['id'] = (int) $row['id'];
                $row['base'] = number_format((float) $row['base'], 2, '.', '');      
                $row['year'] = (int) $row['year'];
                $row['social'] = number_format((float) $row['social'], 2, '.', '');
                $row['sickness'] = number_format((float) $row['sickness'], 2, '.', '');
                $row['social_u'] = number_format((float) $row['social_u'], 2, '.', '');
                $row['sickness_u'] = number_format((float) $row['sickness_u'], 2, '.', '');
                $row['FGSP'] = number_format((float) $row['FGSP'], 2, '.', '');
                $row['totalIncome'] = number_format((float) $row['totalIncome'], 2, '.', '');
                $row['previousMonthSocial'] = number_format((float) $row['previousMonthSocial'], 2, '.', '');
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
}