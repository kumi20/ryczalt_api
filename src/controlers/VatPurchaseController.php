<?php
require_once 'token.php';

class VatPurchaseController {
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

    public function getVatPurchase() {
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
                    v.documentNumber, v.customerId, v.sell23Net as sell23Net, v.sell23Vat as sell23Vat, 
                    v.sell23Gross as sell23Gross, v.sell8Net as sell8Net, v.sell8Vat as sell8Vat, 
                    v.sell8Gross as sell8Gross, v.sell5Net as sell5Net, v.sell5Vat as sell5Vat, 
                    v.sell5Gross as sell5Gross, v.sell23ZWNet as sell23ZWNet, v.sell23ZWVat as sell23ZWVat,
                    v.sell23ZWGross as sell23ZWGross, v.sell8ZWNet as sell8ZWNet, v.sell8ZWVat as sell8ZWVat,
                    v.sell8ZWGross as sell8ZWGross, v.sell5ZWNet as sell5ZWNet, v.sell5ZWVat as sell5ZWVat,
                    v.sell5ZWGross as sell5ZWGross, v.rate0, v.wnt, v.importOutsideUe,
                    v.importServicesUe, v.importServicesOutsideUe, v.deduction50,
                    v.fixedAssets, v.correctFixedAssets, v.MPP, v.purchaseFixedAssets,
                    v.isReverseCharge, v.isThreeSided, v.purchaseMarking,
                    v.isClosed, v.created_by, v.created_at, v.modified_by, v.modified_at,
                    c.customerName,
                    (sell23Gross + sell8Gross + sell5Gross + rate0 + wnt + 
                     importOutsideUe + importServicesUe + importServicesOutsideUe) as grossSum,
                    (CASE 
                        WHEN v.deduction50 = 1 THEN (sell23Vat + sell8Vat + sell5Vat + sell23ZWVat + sell8ZWVat + sell5ZWVat) * 0.5
                        ELSE (sell23Vat + sell8Vat + sell5Vat + sell23ZWVat + sell8ZWVat + sell5ZWVat)
                    END) as vatSum
                    FROM registerVat v
                    LEFT JOIN customers c ON v.customerId = c.customerId
                    WHERE MONTH(v.taxLiabilityDate) = :month 
                    AND YEAR(v.taxLiabilityDate) = :year 
                    AND v.companyId = :companyId
                    AND v.isSell = false
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
                $row['isReverseCharge'] = (bool)$row['isReverseCharge'];
                $row['isThreeSided'] = (bool)$row['isThreeSided'];
                $row['isClosed'] = (bool)$row['isClosed'];
                $row['MPP'] = (bool)$row['MPP'];
                $row['purchaseFixedAssets'] = (bool)$row['purchaseFixedAssets'];
                $row['wnt'] = (bool)$row['wnt'];
                $row['importOutsideUe'] = (bool)$row['importOutsideUe'];
                $row['importServicesUe'] = (bool)$row['importServicesUe'];
                $row['importServicesOutsideUe'] = (bool)$row['importServicesOutsideUe'];
                $row['deduction50'] = (bool)$row['deduction50'];
                $row['fixedAssets'] = (bool)$row['fixedAssets'];
                $row['correctFixedAssets'] = (bool)$row['correctFixedAssets'];
        
                // Konwersja pól numerycznych na float
                $row['sell23Net'] = (float)$row['sell23Net'];
                $row['sell23Vat'] = (float)$row['sell23Vat'];
                $row['sell23Gross'] = (float)$row['sell23Gross'];
                $row['sell8Net'] = (float)$row['sell8Net'];
                $row['sell8Vat'] = (float)$row['sell8Vat'];
                $row['sell8Gross'] = (float)$row['sell8Gross'];
                $row['sell5Net'] = (float)$row['sell5Net'];
                $row['sell5Vat'] = (float)$row['sell5Vat'];
                $row['sell5Gross'] = (float)$row['sell5Gross'];
                $row['rate0'] = (float)$row['rate0'];
                $row['sell23ZWNet'] = (float)$row['sell23ZWNet'];
                $row['sell23ZWVat'] = (float)$row['sell23ZWVat'];
                $row['sell23ZWGross'] = (float)$row['sell23ZWGross'];
                $row['sell8ZWNet'] = (float)$row['sell8ZWNet'];
                $row['sell8ZWVat'] = (float)$row['sell8ZWVat'];
                $row['sell8ZWGross'] = (float)$row['sell8ZWGross'];
                $row['sell5ZWNet'] = (float)$row['sell5ZWNet'];
                $row['sell5ZWVat'] = (float)$row['sell5ZWVat'];
                $row['sell5ZWGross'] = (float)$row['sell5ZWGross'];
        
                $row['grossSum'] = (float)$row['grossSum'];
                $row['vatSum'] = (float)$row['vatSum'];

                // Konwersja pól na integer
                $row['vatRegisterId'] = (int)$row['vatRegisterId'];
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

    public function addVatPurchase() {
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
                documentNumber, customerId, sell23Net, sell23Vat, sell23Gross,
                sell8Net, sell8Vat, sell8Gross, sell5Net, sell5Vat, sell5Gross,
                sell23ZWNet, sell23ZWVat, sell23ZWGross, sell8ZWNet, sell8ZWVat, sell8ZWGross,
                sell5ZWNet, sell5ZWVat, sell5ZWGross, rate0, wnt, importOutsideUe, importServicesUe, 
                importServicesOutsideUe, deduction50, fixedAssets, correctFixedAssets, MPP, purchaseFixedAssets,
                isReverseCharge, isThreeSided, purchaseMarking, companyId, isClosed,
                isSell, created_by
            ) VALUES (
                :documentTypeId, :documentDate, :taxLiabilityDate, :dateOfSell,
                :documentNumber, :customerId, :sell23net, :sell23vat, :sell23gross,
                :sell8net, :sell8vat, :sell8gross, :sell5net, :sell5vat, :sell5gross,
                :sell23ZWnet, :sell23ZWvat, :sell23ZWgross, :sell8ZWnet, :sell8ZWvat, :sell8ZWgross,
                :sell5ZWnet, :sell5ZWvat, :sell5ZWgross, :rate0, :wnt, :importOutsideUe, :importServicesUe, 
                :importServicesOutsideUe, :deduction50, :fixedAssets, :correctFixedAssets, :MPP, :purchaseFixedAssets,
                :isReverseCharge, :isThreeSided, :purchaseMarking, :companyId, :isClosed,
                false, :created_by
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'documentTypeId' => $data['documentTypeId'],
                'documentDate' => $data['documentDate'],
                'taxLiabilityDate' => $data['taxLiabilityDate'],
                'dateOfSell' => $data['dateOfSell'],
                'documentNumber' => $data['documentNumber'],
                'customerId' => $data['customerId'],
                'sell23Net' => $data['sell23Net'],
                'sell23Vat' => $data['sell23Vat'],
                'sell23Gross' => $data['sell23Gross'],
                'sell8Net' => $data['sell8Net'],
                'sell8Vat' => $data['sell8Vat'],
                'sell8Gross' => $data['sell8Gross'],
                'sell5Net' => $data['sell5Net'],
                'sell5Vat' => $data['sell5Vat'],
                'sell5Gross' => $data['sell5Gross'],
                'sell23ZWNet' => $data['sell23ZWNet'],
                'sell23ZWVat' => $data['sell23ZWVat'],
                'sell23ZWGross' => $data['sell23ZWGross'],
                'sell8ZWNet' => $data['sell8ZWNet'],
                'sell8ZWVat' => $data['sell8ZWVat'],
                'sell8ZWGross' => $data['sell8ZWGross'],
                'sell5ZWNet' => $data['sell5ZWNet'],
                'sell5ZWVat' => $data['sell5ZWVat'],
                'sell5ZWGross' => $data['sell5ZWGross'],
                'rate0' => $data['rate0'],
                'wnt' => (int)$data['wnt'],
                'importOutsideUe' => (int)$data['importOutsideUe'],
                'importServicesUe' => (int)$data['importServicesUe'],
                'importServicesOutsideUe' => (int)$data['importServicesOutsideUe'],
                'deduction50' => (int)$data['deduction50'],
                'fixedAssets' => (int)$data['fixedAssets'],
                'correctFixedAssets' => (int)$data['correctFixedAssets'],
                'MPP' => (int)$data['MPP'],
                'purchaseFixedAssets' => (int)$data['purchaseFixedAssets'],
                'isReverseCharge' => (int)$data['isReverseCharge'],
                'isThreeSided' => (int)$data['isThreeSided'],
                'purchaseMarking' => $data['purchaseMarking'],
                'companyId' => $companyId,
                'isClosed' => (int)$data['isClosed'],
                'created_by' => getUserId()
            ]);

            $lastInsertId = $this->db->lastInsertId();

            echo json_encode([
                'success' => true,
                'details' => 'Dodano wpis do rejestru VAT zakupu',
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

    public function updateVatPurchase() {
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
                sell23Net = :sell23Net,
                sell23Vat = :sell23Vat,
                sell23Gross = :sell23Gross,
                sell8Net = :sell8Net,
                sell8Vat = :sell8Vat,
                sell8Gross = :sell8Gross,
                sell5Net = :sell5Net,
                sell5Vat = :sell5Vat,
                sell5Gross = :sell5Gross,
                sell23ZWNet = :sell23ZWNet,
                sell23ZWVat = :sell23ZWVat,
                sell23ZWGross = :sell23ZWGross,
                sell8ZWNet = :sell8ZWNet,
                sell8ZWVat = :sell8ZWVat,
                sell8ZWGross = :sell8ZWGross,
                sell5ZWNet = :sell5ZWNet,
                sell5ZWVat = :sell5ZWVat,
                sell5ZWGross = :sell5ZWGross,
                rate0 = :rate0,
                wnt = :wnt,
                importOutsideUe = :importOutsideUe,
                importServicesUe = :importServicesUe,
                importServicesOutsideUe = :importServicesOutsideUe,
                deduction50 = :deduction50,
                fixedAssets = :fixedAssets,
                correctFixedAssets = :correctFixedAssets,
                MPP = :MPP,
                purchaseFixedAssets = :purchaseFixedAssets,
                isReverseCharge = :isReverseCharge,
                isThreeSided = :isThreeSided,
                purchaseMarking = :purchaseMarking,
                modified_by = :updated_by,
                modified_at = NOW()
            WHERE vatRegisterId = :id AND companyId = :companyId AND isSell = false";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'documentTypeId' => $data['documentTypeId'],
                'documentDate' => $data['documentDate'],
                'taxLiabilityDate' => $data['taxLiabilityDate'],
                'dateOfSell' => $data['dateOfSell'],
                'documentNumber' => $data['documentNumber'],
                'customerId' => $data['customerId'],
                'sell23Net' => $data['sell23Net'],
                'sell23Vat' => $data['sell23Vat'],
                'sell23Gross' => $data['sell23Gross'],
                'sell8Net' => $data['sell8Net'],
                'sell8Vat' => $data['sell8Vat'],
                'sell8Gross' => $data['sell8Gross'],
                'sell5Net' => $data['sell5Net'],
                'sell5Vat' => $data['sell5Vat'],
                'sell5Gross' => $data['sell5Gross'],
                'sell23ZWNet' => $data['sell23ZWNet'],
                'sell23ZWVat' => $data['sell23ZWVat'],
                'sell23ZWGross' => $data['sell23ZWGross'],
                'sell8ZWNet' => $data['sell8ZWNet'],
                'sell8ZWVat' => $data['sell8ZWVat'],
                'sell8ZWGross' => $data['sell8ZWGross'],
                'sell5ZWNet' => $data['sell5ZWNet'],
                'sell5ZWVat' => $data['sell5ZWVat'],
                'sell5ZWGross' => $data['sell5ZWGross'],
                'rate0' => $data['rate0'],
                'wnt' => (int)$data['wnt'],
                'importOutsideUe' => (int)$data['importOutsideUe'],
                'importServicesUe' => (int)$data['importServicesUe'],
                'importServicesOutsideUe' => (int)$data['importServicesOutsideUe'],
                'deduction50' => (int)$data['deduction50'],
                'fixedAssets' => (int)$data['fixedAssets'],
                'correctFixedAssets' => (int)$data['correctFixedAssets'],
                'MPP' => (int)$data['MPP'],
                'purchaseFixedAssets' => (int)$data['purchaseFixedAssets'],
                'isReverseCharge' => (int)$data['isReverseCharge'],
                'isThreeSided' => (int)$data['isThreeSided'],
                'purchaseMarking' => $data['purchaseMarking'],
                'id' => $id,
                'companyId' => $companyId,
                'updated_by' => getUserId()
            ]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['details' => 'Nie znaleziono wpisu do aktualizacji']);
                return;
            }

            echo json_encode([
                'success' => true,
                'details' => 'Zaktualizowano wpis w rejestrze VAT zakupu',
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

    public function deleteVatPurchase() {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $id = $_GET['id'] ?? null;

            // Sprawdzamy czy wpis istnieje i pobieramy datę dokumentu
            $checkQuery = "SELECT taxLiabilityDate FROM registerVat 
                         WHERE vatRegisterId = :id 
                         AND companyId = :companyId 
                         AND isSell = false";
            
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
                     AND companyId = :companyId 
                     AND isSell = false";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'companyId' => $companyId
            ]);

            echo json_encode([
                'success' => true,
                'details' => 'Usunięto wpis z rejestru VAT zakupu'
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
     * Pobiera podsumowanie rejestru VAT zakupu dla danego miesiąca
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
                    SUM(CASE WHEN deduction50 = 0 THEN sell23Net ELSE sell23Net * 0.5 END) as total_net_23,
                    SUM(CASE WHEN deduction50 = 0 THEN sell23Vat ELSE sell23Vat * 0.5 END) as total_vat_23,
                    SUM(CASE WHEN deduction50 = 0 THEN sell8Net ELSE sell8Net * 0.5 END) as total_net_8,
                    SUM(CASE WHEN deduction50 = 0 THEN sell8Vat ELSE sell8Vat * 0.5 END) as total_vat_8,
                    SUM(CASE WHEN deduction50 = 0 THEN sell5Net ELSE sell5Net * 0.5 END) as total_net_5,
                    SUM(CASE WHEN deduction50 = 0 THEN sell5Vat ELSE sell5Vat * 0.5 END) as total_vat_5,
                    
                    SUM(CASE WHEN deduction50 = 0 THEN sell23ZWNet ELSE sell23ZWNet * 0.5 END) as total_zw_net_23,
                    SUM(CASE WHEN deduction50 = 0 THEN sell23ZWVat ELSE sell23ZWVat * 0.5 END) as total_zw_vat_23,
                    SUM(CASE WHEN deduction50 = 0 THEN sell8ZWNet ELSE sell8ZWNet * 0.5 END) as total_zw_net_8,
                    SUM(CASE WHEN deduction50 = 0 THEN sell8ZWVat ELSE sell8ZWVat * 0.5 END) as total_zw_vat_8,
                    SUM(CASE WHEN deduction50 = 0 THEN sell5ZWNet ELSE sell5ZWNet * 0.5 END) as total_zw_net_5,
                    SUM(CASE WHEN deduction50 = 0 THEN sell5ZWVat ELSE sell5ZWVat * 0.5 END) as total_zw_vat_5,
                    
                    SUM(CASE WHEN deduction50 = 0 THEN sell23Net ELSE sell23Net * 0.5 END) + SUM(CASE WHEN deduction50 = 0 THEN sell8Net ELSE sell8Net * 0.5 END) + SUM(CASE WHEN deduction50 = 0 THEN sell5Net ELSE sell5Net * 0.5 END) + SUM(CASE WHEN deduction50 = 0 THEN sell23ZWNet ELSE sell23ZWNet * 0.5 END) + SUM(CASE WHEN deduction50 = 0 THEN sell8ZWNet ELSE sell8ZWNet * 0.5 END) + SUM(CASE WHEN deduction50 = 0 THEN sell5ZWNet ELSE sell5ZWNet * 0.5 END) as total_net,
                    SUM(rate0) as total_net_not_deductible,
                    SUM(sell23gross + sell8gross + sell5gross + rate0 + sell23ZWgross + sell8ZWgross + sell5ZWgross) as total_gross,
                    SUM(sell23net + sell8net + sell5net + rate0) as total_net_deductible,
                    SUM(CASE 
                        WHEN deduction50 = 1 THEN (sell23vat + sell8vat + sell5vat + sell23ZWvat + sell8ZWvat + sell5ZWvat) * 0.5
                        ELSE (sell23vat + sell8vat + sell5vat + sell23ZWvat + sell8ZWvat + sell5ZWvat)
                    END) as total_vat_deductible
                FROM registerVat 
                WHERE MONTH(taxLiabilityDate) = :month 
                AND YEAR(taxLiabilityDate) = :year 
                AND companyId = :companyId
                AND isSell = false";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':month', $month, PDO::PARAM_INT);
            $stmt->bindValue(':year', $year, PDO::PARAM_INT);
            $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Konwertuj wartości null na 0 i wszystkie wartości na float
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