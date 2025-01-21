<?php
require_once 'token.php';

class VatRegisterController {
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

    public function getVatRegister() {
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

            $query = "SELECT v.vatRegisterId, v.documentTypeId, v.documentDate, v.taxLiabilityDate, v.dateOfSell,
                    v.documentNumber, v.customerId, v.rate23Gross, v.rate8Gross, v.rate5Gross, v.rate0, v.export0,
                    v.rate23Net, v.rate8Net, v.rate5Net, v.rate0, v.export0, v.rate23Vat, v.rate8Vat, v.rate5Vat,
                    v.wdt0, v.wsu, v.exemptSales, v.reverseCharge, 
                    v.isDelivery as isDelivery, 
                    v.isServices as isServices, 
                    v.isCustomerPayer as isCustomerPayer, 
                    v.isThreeSided as isThreeSided,
                    v.ryczaltId, v.isClosed, v.created_by, v.created_at, v.modified_by, v.modified_at,
                    c.customerName,
                    (rate23Gross + rate8Gross + rate5Gross + rate0 + export0 + 
                     wdt0 + wsu + exemptSales + reverseCharge) as grossSum,
                    (rate23Vat + rate8Vat + rate5Vat) as vatSum
                    FROM registerVat v
                    LEFT JOIN customers c ON v.customerId = c.customerId
                    WHERE MONTH(v.taxLiabilityDate) = :month 
                    AND YEAR(v.taxLiabilityDate) = :year 
                    AND v.companyId = :companyId
                    AND v.isSell = true
                    ORDER BY v.documentDate ASC
                    LIMIT :take OFFSET :skip";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':month', $month, PDO::PARAM_INT);
            $stmt->bindValue(':year', $year, PDO::PARAM_INT);
            $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->bindValue(':take', $take, PDO::PARAM_INT);
            $stmt->bindValue(':skip', $skip, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as &$row) {
                // Konwersja pól na boolean
                $row['isDelivery'] = (bool)$row['isDelivery'];
                $row['isServices'] = (bool)$row['isServices'];
                $row['isCustomerPayer'] = (bool)$row['isCustomerPayer'];
                $row['isThreeSided'] = (bool)$row['isThreeSided'];
                $row['isClosed'] = (bool)$row['isClosed'];             
                // Konwersja pól numerycznych na float
                $row['rate23Gross'] = (float)$row['rate23Gross'];
                $row['rate8Gross'] = (float)$row['rate8Gross'];
                $row['rate5Gross'] = (float)$row['rate5Gross'];
                $row['rate0'] = (float)$row['rate0'];
                $row['export0'] = (float)$row['export0'];
                $row['rate23Net'] = (float)$row['rate23Net'];
                $row['rate8Net'] = (float)$row['rate8Net'];
                $row['rate5Net'] = (float)$row['rate5Net'];
                $row['rate23Vat'] = (float)$row['rate23Vat'];
                $row['rate8Vat'] = (float)$row['rate8Vat'];
                $row['rate5Vat'] = (float)$row['rate5Vat'];
                $row['wdt0'] = (float)$row['wdt0'];
                $row['wsu'] = (float)$row['wsu'];
                $row['exemptSales'] = (float)$row['exemptSales'];
                $row['reverseCharge'] = (float)$row['reverseCharge'];
                $row['grossSum'] = (float)$row['grossSum'];
                $row['vatSum'] = (float)$row['vatSum'];
                $row['vatRegisterId'] = (int)$row['vatRegisterId'];
                $row['ryczaltId'] = (int)$row['ryczaltId'];
                $row['documentTypeId'] = (int)$row['documentTypeId'];
                $row['customerId'] = (int)$row['customerId'];
                $row['created_by'] = (int)$row['created_by'];
                $row['modified_by'] = (int)$row['modified_by'];
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

    public function addVatRegister() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Formatowanie dat do formatu yyyy-MM-dd
            $data['documentDate'] = date('Y-m-d', strtotime($data['documentDate']));
            $data['taxLiabilityDate'] = date('Y-m-d', strtotime($data['taxLiabilityDate']));
            $data['dateOfSell'] = date('Y-m-d', strtotime($data['dateOfSell']));
            
            $month = date('n', strtotime($data['taxLiabilityDate']));
            $year = date('Y', strtotime($data['taxLiabilityDate']));

            if ($this->isMonthClosed($month, $year, $companyId)) {
                http_response_code(400);
                echo json_encode(['details' => 'Ten miesiąc jest zamknięty']);
                return;
            }

            $query = "INSERT INTO registerVat (
                documentTypeId, documentDate, taxLiabilityDate, dateOfSell,
                documentNumber, customerId, rate23Net, rate23Vat, rate23Gross,
                rate8Net, rate8Vat, rate8Gross, rate5Net, rate5Vat, rate5Gross,
                rate0, export0, wdt0, wsu, exemptSales, reverseCharge,
                isDelivery, isServices, isCustomerPayer, isThreeSided,
                companyId, isClosed, ryczaltId, isSell, created_by
            ) VALUES (
                :documentTypeId, :documentDate, :taxLiabilityDate, :dateOfSell,
                :documentNumber, :customerId, :rate23Net, :rate23Vat, :rate23Gross,
                :rate8Net, :rate8Vat, :rate8Gross, :rate5Net, :rate5Vat, :rate5Gross,
                :rate0, :export0, :wdt0, :wsu, :exemptSales, :reverseCharge,
                :isDelivery, :isServices, :isCustomerPayer, :isThreeSided,
                :companyId, :isClosed, :ryczaltId, true, :created_by
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'documentTypeId' => $data['documentTypeId'],
                'documentDate' => $data['documentDate'],
                'taxLiabilityDate' => $data['taxLiabilityDate'],
                'dateOfSell' => $data['dateOfSell'],
                'documentNumber' => $data['documentNumber'],
                'customerId' => $data['customerId'],
                'rate23Net' => $data['rate23Net'],
                'rate23Vat' => $data['rate23Vat'],
                'rate23Gross' => $data['rate23Gross'],
                'rate8Net' => $data['rate8Net'],
                'rate8Vat' => $data['rate8Vat'],
                'rate8Gross' => $data['rate8Gross'],
                'rate5Net' => $data['rate5Net'],
                'rate5Vat' => $data['rate5Vat'],
                'rate5Gross' => $data['rate5Gross'],
                'rate0' => $data['rate0'],
                'export0' => $data['export0'],
                'wdt0' => $data['wdt0'],
                'wsu' => $data['wsu'],
                'exemptSales' => $data['exemptSales'],
                'reverseCharge' => $data['reverseCharge'],
                'isDelivery' => (int)$data['isDelivery'],
                'isServices' => (int)$data['isServices'],
                'isCustomerPayer' => (int)$data['isCustomerPayer'],
                'isThreeSided' => (int)$data['isThreeSided'],
                'companyId' => $companyId,
                'isClosed' => (int)$data['isClosed'],
                'ryczaltId' => $data['ryczaltId'],
                'created_by' => getUserId()
            ]);

            $lastInsertId = $this->db->lastInsertId();

            // Dodajemy aktualizację tabeli flat_rate jeśli ryczaltId jest ustawione
            if (!empty($data['ryczaltId'])) {
                $updateFlatRateQuery = "UPDATE flate_rate 
                                      SET vatRegisterId = :vatRegisterId 
                                      WHERE ryczalt_id = :ryczaltId 
                                      AND companyId = :companyId";
                
                $updateStmt = $this->db->prepare($updateFlatRateQuery);
                $updateStmt->execute([
                    'vatRegisterId' => $lastInsertId,
                    'ryczaltId' => $data['ryczaltId'],
                    'companyId' => $companyId
                ]);
            }

            echo json_encode([
                'success' => true,
                'details' => 'Dodano wpis do rejestru VAT',
                'vatRegisterId' => $lastInsertId
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas dodawania wpisu',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateVatRegister() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Formatowanie dat do formatu yyyy-MM-dd
            $data['documentDate'] = date('Y-m-d', strtotime($data['documentDate']));
            $data['taxLiabilityDate'] = date('Y-m-d', strtotime($data['taxLiabilityDate']));
            $data['dateOfSell'] = date('Y-m-d', strtotime($data['dateOfSell']));
            
            $id = $_GET['id'] ?? null;
            $month = date('n', strtotime($data['taxLiabilityDate']));
            $year = date('Y', strtotime($data['taxLiabilityDate']));

            if ($this->isMonthClosed($month, $year, $companyId)) {
                http_response_code(400);
                echo json_encode(['details' => 'Ten miesiąc jest zamknięty']);
                return;
            }

            $query = "UPDATE registerVat SET 
                documentTypeId = :documentTypeId,
                documentDate = :documentDate,
                taxLiabilityDate = :taxLiabilityDate,
                dateOfSell = :dateOfSell,
                documentNumber = :documentNumber,
                customerId = :customerId,
                rate23Net = :rate23Net,
                rate23Vat = :rate23Vat,
                rate23Gross = :rate23Gross,
                rate8Net = :rate8Net,
                rate8Vat = :rate8Vat,
                rate8Gross = :rate8Gross,
                rate5Net = :rate5Net,
                rate5Vat = :rate5Vat,
                rate5Gross = :rate5Gross,
                rate0 = :rate0,
                export0 = :export0,
                wdt0 = :wdt0,
                wsu = :wsu,
                exemptSales = :exemptSales,
                reverseCharge = :reverseCharge,
                isDelivery = :isDelivery,
                isServices = :isServices,
                isCustomerPayer = :isCustomerPayer,
                isThreeSided = :isThreeSided,
                ryczaltId = :ryczaltId,
                modified_by = :updated_by,
                modified_at = NOW()
            WHERE vatRegisterId = :id AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'documentTypeId' => $data['documentTypeId'],
                'documentDate' => $data['documentDate'],
                'taxLiabilityDate' => $data['taxLiabilityDate'],
                'dateOfSell' => $data['dateOfSell'],
                'documentNumber' => $data['documentNumber'],
                'customerId' => $data['customerId'],
                'rate23Net' => $data['rate23Net'],
                'rate23Vat' => $data['rate23Vat'],
                'rate23Gross' => $data['rate23Gross'],
                'rate8Net' => $data['rate8Net'],
                'rate8Vat' => $data['rate8Vat'],
                'rate8Gross' => $data['rate8Gross'],
                'rate5Net' => $data['rate5Net'],
                'rate5Vat' => $data['rate5Vat'],
                'rate5Gross' => $data['rate5Gross'],
                'rate0' => $data['rate0'],
                'export0' => $data['export0'],
                'wdt0' => $data['wdt0'],
                'wsu' => $data['wsu'],
                'exemptSales' => $data['exemptSales'],
                'reverseCharge' => $data['reverseCharge'],
                'isDelivery' => (int)$data['isDelivery'],
                'isServices' => (int)$data['isServices'],
                'isCustomerPayer' => (int)$data['isCustomerPayer'],
                'isThreeSided' => (int)$data['isThreeSided'],
                'ryczaltId' => $data['ryczaltId'],
                'id' => $id,
                'companyId' => $companyId,
                'updated_by' => getUserId()
            ]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['details' => 'Nie znaleziono wpisu do aktualizacji']);
                return;
            }

            // Aktualizujemy powiązanie w tabeli flat_rate
            if (!empty($data['ryczaltId'])) {
                // Najpierw czyścimy stare powiązanie jeśli istnieje
                $clearOldQuery = "UPDATE flate_rate 
                                SET vatRegisterId = NULL 
                                WHERE vatRegisterId = :vatRegisterId 
                                AND companyId = :companyId";
                
                $clearStmt = $this->db->prepare($clearOldQuery);
                $clearStmt->execute([
                    'vatRegisterId' => $id,
                    'companyId' => $companyId
                ]);

                // Ustawiamy nowe powiązanie
                $updateFlatRateQuery = "UPDATE flate_rate 
                                      SET vatRegisterId = :vatRegisterId 
                                      WHERE ryczalt_id = :ryczaltId 
                                      AND companyId = :companyId";
                
                $updateStmt = $this->db->prepare($updateFlatRateQuery);
                $updateStmt->execute([
                    'vatRegisterId' => $id,
                    'ryczaltId' => $data['ryczaltId'],
                    'companyId' => $companyId
                ]);
            }

            echo json_encode([
                'success' => true,
                'details' => 'Zaktualizowano wpis w rejestrze VAT',
                'vatRegisterId' => $id
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas aktualizacji wpisu',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteVatRegister() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $id = $_GET['id'] ?? null;

            // Sprawdzamy czy wpis istnieje i pobieramy datę dokumentu
            $checkQuery = "SELECT documentDate FROM registerVat 
                         WHERE vatRegisterId = :id AND companyId = :companyId";
            
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

            $month = date('n', strtotime($record['taxLiabilityDate']));
            $year = date('Y', strtotime($record['taxLiabilityDate']));

            if ($this->isMonthClosed($month, $year, $companyId)) {
                http_response_code(400);
                echo json_encode(['details' => 'Ten miesiąc jest zamknięty']);
                return;
            }
            
            $query = "DELETE FROM registerVat 
                     WHERE vatRegisterId = :id 
                     AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'companyId' => $companyId
            ]);

            echo json_encode([
                'success' => true,
                'details' => 'Usunięto wpis z rejestru VAT'
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas usuwania wpisu',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Pobiera podsumowanie rejestru VAT dla danego miesiąca
     */
    public function getSummaryMonth() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $month = $_GET['month'] ?? null;
            $year = $_GET['year'] ?? null;

            if (!$month || !$year) {
                http_response_code(400);
                echo json_encode(['details' => 'Brak wymaganego parametru month lub year']);
                return;
            }

            $query = "SELECT 
                    SUM(rate23Net) as Net23,
                    SUM(rate23Vat) as Vat23,
                    SUM(rate8Net) as Net8,
                    SUM(rate8Vat) as Vat8,
                    SUM(rate5Net) as Net5,
                    SUM(rate5Vat) as Vat5,
                    SUM(rate0) as Net0,
                    SUM(export0) as Export0,
                    SUM(wdt0) as WDT0,
                    SUM(wsu) as WSU,
                    SUM(exemptSales) as ExemptSales,
                    SUM(reverseCharge) as ReverseCharge,
                    SUM(rate23Net + rate8Net + rate5Net + rate0 + export0 + 
                        wdt0 + wsu + exemptSales + reverseCharge) as TotalNetSales,
                    SUM(rate23Vat + rate8Vat + rate5Vat) as TotalVat,
                    SUM(rate23Gross + rate8Gross + rate5Gross + rate0 + export0 + 
                        wdt0 + wsu + exemptSales + reverseCharge) as TotalGrossSales
                FROM registerVat 
                WHERE MONTH(taxLiabilityDate) = :month 
                AND YEAR(taxLiabilityDate) = :year 
                AND companyId = :companyId
                AND isSell = true";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':month', $month, PDO::PARAM_INT);
            $stmt->bindValue(':year', $year, PDO::PARAM_INT);
            $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Konwertuj wartości null na 0 i wszystkie wartości na liczby
            $summary = array_map(function($value) {
                return $value === null ? 0 : (float)$value;
            }, $result);

            echo json_encode($summary);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas pobierania podsumowania',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
} 