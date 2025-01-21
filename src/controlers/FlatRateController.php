<?php

class FlatRateController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Sprawdza czy miesiąc jest zamknięty
     */
    private function isMonthClosed($month, $year, $companyId)
    {
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

    private function convertDate($dateString) {
        return date('Y-m-d', strtotime($dateString));
    }

    /**
     * @OA\Get(
     *     path="/flat-rate",
     *     summary="Pobiera listę wpisów ryczałtu",
     *     tags={"Ryczałt"},
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         required=true,
     *         description="Miesiąc (1-12)",
     *         @OA\Schema(type="integer", minimum=1, maximum=12)
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         required=true,
     *         description="Rok",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="skip",
     *         in="query",
     *         description="Liczba rekordów do pominięcia (paginacja)",
     *         @OA\Schema(type="integer", default=0)
     *     ),
     *     @OA\Parameter(
     *         name="take",
     *         in="query",
     *         description="Liczba rekordów do pobrania (paginacja)",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista wpisów ryczałtu",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="ryczaltId", type="integer"),
     *                 @OA\Property(property="lp", type="integer"),
     *                 @OA\Property(property="dateOfEntry", type="string", format="date"),
     *                 @OA\Property(property="dateOfReceipt", type="string", format="date"),
     *                 @OA\Property(property="documentNumber", type="string"),
     *                 @OA\Property(property="isClose", type="boolean"),
     *                 @OA\Property(property="rate3", type="number"),
     *                 @OA\Property(property="rate5_5", type="number"),
     *                 @OA\Property(property="rate8_5", type="number"),
     *                 @OA\Property(property="rate10", type="number"),
     *                 @OA\Property(property="rate12", type="number"),
     *                 @OA\Property(property="rate12_5", type="number"),
     *                 @OA\Property(property="rate14", type="number"),
     *                 @OA\Property(property="rate15", type="number"),
     *                 @OA\Property(property="rate17", type="number"),
     *                 @OA\Property(property="remarks", type="string"),
     *                 @OA\Property(property="vatRegisterId", type="integer"),
     *                 @OA\Property(property="totalRevenue", type="number")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Błąd walidacji lub przetwarzania",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function getFlatRates()
    {
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
            // Następnie pobieramy konkretne rekordy z paginacją
            $query = "SELECT 
                        ryczalt_id as ryczaltId,
                        lp,
                        dateOfEntry,
                        dateOfReceipt,
                        documentNumber,
                        isClose,
                        rate3,
                        rate5_5,
                        rate8_5,
                        rate10,
                        rate12,
                        rate12_5,
                        rate14,
                        rate15,
                        rate17,
                        remarks,
                        vatRegisterId,
                        (rate3 + rate5_5 + rate8_5 + rate10 + rate12 + rate12_5 + rate14 + rate15 + rate17) as totalRevenue
                     FROM flate_rate 
                     WHERE MONTH(dateOfReceipt) = :month 
                     AND YEAR(dateOfReceipt) = :year 
                     AND companyId = :companyId
                     ORDER BY lp ASC
                     LIMIT :take OFFSET :skip";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':month', $month, PDO::PARAM_INT);
            $stmt->bindValue(':year', $year, PDO::PARAM_INT);
            $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->bindValue(':take', $take, PDO::PARAM_INT);
            $stmt->bindValue(':skip', $skip, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Konwersja pól na odpowiednie typy
            foreach ($results as &$row) {
                // Konwersja pól na odpowiednie typy
                $row['ryczaltId'] = (int)$row['ryczaltId'];
                $row['lp'] = (int)$row['lp'];
                $row['isClose'] = (bool)$row['isClose'];
                $row['vatRegisterId'] = $row['vatRegisterId'] ? (int)$row['vatRegisterId'] : null;
                
                // Konwersja wszystkich pól liczbowych na float
                $row['rate3'] = (float)$row['rate3'];
                $row['rate5_5'] = (float)$row['rate5_5'];
                $row['rate8_5'] = (float)$row['rate8_5'];
                $row['rate10'] = (float)$row['rate10'];
                $row['rate12'] = (float)$row['rate12'];
                $row['rate12_5'] = (float)$row['rate12_5'];
                $row['rate14'] = (float)$row['rate14'];
                $row['rate15'] = (float)$row['rate15'];
                $row['rate17'] = (float)$row['rate17'];
                $row['totalRevenue'] = (float)$row['totalRevenue'];
            }

            echo json_encode([
                'data' => $results,
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas pobierania danych',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @OA\Post(
     *     path="/flat-rate",
     *     summary="Dodaje nowy wpis ryczałtu",
     *     tags={"Ryczałt"},
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         required=true,
     *         description="Miesiąc (1-12)",
     *         @OA\Schema(type="integer", minimum=1, maximum=12)
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         required=true,
     *         description="Rok",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"dateOfEntry","dateOfReceipt","documentNumber","isClose"},
     *             @OA\Property(property="dateOfEntry", type="string", format="date"),
     *             @OA\Property(property="dateOfReceipt", type="string", format="date"),
     *             @OA\Property(property="documentNumber", type="string"),
     *             @OA\Property(property="isClose", type="boolean"),
     *             @OA\Property(property="rate3", type="number"),
     *             @OA\Property(property="rate5_5", type="number"),
     *             @OA\Property(property="rate8_5", type="number"),
     *             @OA\Property(property="rate10", type="number"),
     *             @OA\Property(property="rate12", type="number"),
     *             @OA\Property(property="rate12_5", type="number"),
     *             @OA\Property(property="rate14", type="number"),
     *             @OA\Property(property="rate15", type="number"),
     *             @OA\Property(property="rate17", type="number"),
     *             @OA\Property(property="remarks", type="string"),
     *             @OA\Property(property="vatRegisterId", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wpis został dodany",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="details", type="string"),
     *             @OA\Property(property="flateRateId", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Błąd walidacji lub przetwarzania",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function addFlatRate()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $month = date('n', strtotime($data['dateOfReceipt']));
            $year = date('Y', strtotime($data['dateOfReceipt']));

            if ($this->isMonthClosed($month, $year, $companyId)) {
                http_response_code(400);
                echo json_encode(['details' => 'Ten miesiąc jest zamknięty']);
                return;
            }

            // Rozpocznij transakcję
            $this->db->beginTransaction();

            $query = "INSERT INTO flate_rate (
                dateOfEntry, dateOfReceipt, documentNumber, isClose,
                rate3, rate5_5, rate8_5, rate10, rate12, rate12_5,
                rate14, rate15, rate17, remarks, companyId, vatRegisterId, lp, created_by
            ) VALUES (
                :dateOfEntry, :dateOfReceipt, :documentNumber, :isClose,
                :rate3, :rate5_5, :rate8_5, :rate10, :rate12, :rate12_5,
                :rate14, :rate15, :rate17, :remarks, :companyId, :vatRegisterId, :lp, :created_by
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'dateOfEntry' => $this->convertDate($data['dateOfEntry']),
                'dateOfReceipt' => $this->convertDate($data['dateOfReceipt']),
                'documentNumber' => $data['documentNumber'],
                'isClose' => (int)$data['isClose'],
                'rate3' => $data['rate3'],
                'rate5_5' => $data['rate5_5'],
                'rate8_5' => $data['rate8_5'],
                'rate10' => $data['rate10'],
                'rate12' => $data['rate12'],
                'rate12_5' => $data['rate12_5'],
                'rate14' => $data['rate14'],
                'rate15' => $data['rate15'],
                'rate17' => $data['rate17'],
                'remarks' => $data['remarks'],
                'companyId' => $companyId,
                'vatRegisterId' => $data['vatRegisterId'],
                'lp' => $data['lp'],
                'created_by' => getUserId()
            ]);

            $lastInsertId = $this->db->lastInsertId();

            // Jeśli vatRegisterId jest ustawiony, zaktualizuj powiązany wpis w registerVat
            if ($data['vatRegisterId']) {
                $updateVatRegister = "UPDATE registerVat 
                                     SET ryczaltId = :ryczaltId 
                                     WHERE vatRegisterId = :vatRegisterId 
                                     AND companyId = :companyId";
                
                $stmtVat = $this->db->prepare($updateVatRegister);
                $stmtVat->execute([
                    'ryczaltId' => $lastInsertId,
                    'vatRegisterId' => $data['vatRegisterId'],
                    'companyId' => $companyId
                ]);
            }

            $this->db->commit();
            $this->renumberEntries($year);

            echo json_encode([
                'success' => true,
                'details' => 'Dodano nowy wpis ryczałtu',
                'flateRateId' => $lastInsertId
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas dodawania wpisu',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @OA\Put(
     *     path="/flat-rate/{id}",
     *     summary="Aktualizuje wpis ryczałtu",
     *     tags={"Ryczałt"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID wpisu ryczałtu",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"dateOfEntry","dateOfReceipt","documentNumber"},
     *             @OA\Property(property="dateOfEntry", type="string", format="date"),
     *             @OA\Property(property="dateOfReceipt", type="string", format="date"),
     *             @OA\Property(property="documentNumber", type="string"),
     *             @OA\Property(property="rate3", type="number"),
     *             @OA\Property(property="rate5_5", type="number"),
     *             @OA\Property(property="rate8_5", type="number"),
     *             @OA\Property(property="rate10", type="number"),
     *             @OA\Property(property="rate12", type="number"),
     *             @OA\Property(property="rate12_5", type="number"),
     *             @OA\Property(property="rate14", type="number"),
     *             @OA\Property(property="rate15", type="number"),
     *             @OA\Property(property="rate17", type="number"),
     *             @OA\Property(property="remarks", type="string"),
     *             @OA\Property(property="vatRegisterId", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wpis został zaktualizowany",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="details", type="string"),
     *             @OA\Property(property="flateRateId", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Błąd walidacji lub przetwarzania",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nie znaleziono wpisu",
     *         @OA\JsonContent(
     *             @OA\Property(property="details", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function updateFlatRate()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $_GET['id'] ?? null;
            
            // Pobieranie month i year z dateOfReceipt
            $month = date('n', strtotime($data['dateOfReceipt']));
            $year = date('Y', strtotime($data['dateOfReceipt']));

            if ($this->isMonthClosed($month, $year, $companyId)) {
                http_response_code(400);
                echo json_encode(['details' => 'Ten miesiąc jest zamknięty']);
                return;
            }

            // Rozpocznij transakcję
            $this->db->beginTransaction();

            $query = "UPDATE flate_rate SET 
                dateOfEntry = :dateOfEntry,
                dateOfReceipt = :dateOfReceipt,
                documentNumber = :documentNumber,
                rate3 = :rate3,
                rate5_5 = :rate5_5,
                rate8_5 = :rate8_5,
                rate10 = :rate10,
                rate12 = :rate12,
                rate12_5 = :rate12_5,
                rate14 = :rate14,
                rate15 = :rate15,
                rate17 = :rate17,
                remarks = :remarks,
                vatRegisterId = :vatRegisterId,
                lp = :lp,
                updated_by = :updated_by,
                updated_at = NOW()
            WHERE ryczalt_id = :id AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'dateOfEntry' => $data['dateOfEntry'],
                'dateOfReceipt' => $data['dateOfReceipt'],
                'documentNumber' => $data['documentNumber'],
                'rate3' => $data['rate3'],
                'rate5_5' => $data['rate5_5'],
                'rate8_5' => $data['rate8_5'],
                'rate10' => $data['rate10'],
                'rate12' => $data['rate12'],
                'rate12_5' => $data['rate12_5'],
                'rate14' => $data['rate14'],
                'rate15' => $data['rate15'],
                'rate17' => $data['rate17'],
                'remarks' => $data['remarks'],
                'vatRegisterId' => $data['vatRegisterId'],
                'id' => $id,
                'companyId' => $companyId,
                'updated_by' => getUserId(),
                'lp' => $data['lp']
            ]);

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                http_response_code(404);
                echo json_encode(['details' => 'Nie znaleziono wpisu do aktualizacji']);
                return;
            }

            // Jeśli vatRegisterId jest ustawiony, zaktualizuj powiązany wpis w registerVat
            if ($data['vatRegisterId']) {
                $updateVatRegister = "UPDATE registerVat 
                                     SET ryczaltId = :ryczaltId 
                                     WHERE vatRegisterId = :vatRegisterId 
                                     AND companyId = :companyId";
                
                $stmtVat = $this->db->prepare($updateVatRegister);
                $stmtVat->execute([
                    'ryczaltId' => $id,
                    'vatRegisterId' => $data['vatRegisterId'],
                    'companyId' => $companyId
                ]);
            }

            $this->db->commit();
            $this->renumberEntries($year);

            echo json_encode([
                'success' => true,
                'details' => 'Zaktualizowano wpis ryczałtu',
                'flateRateId' => $id
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas aktualizacji wpisu',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @OA\Delete(
     *     path="/flat-rate/{id}",
     *     summary="Usuwa wpis ryczałtu",
     *     tags={"Ryczałt"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID wpisu ryczałtu",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wpis został usunięty",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Błąd przetwarzania lub wpis jest zamknięty",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nie znaleziono wpisu",
     *         @OA\JsonContent(
     *             @OA\Property(property="details", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function deleteFlatRate()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            // Pobieranie month i year z dateOfReceipt
            $id = $_GET['id'] ?? null;

            // Najpierw sprawdzamy czy rekord istnieje i czy nie jest zamknięty
            $checkQuery = "SELECT isClose, dateOfReceipt
                          FROM flate_rate 
                          WHERE ryczalt_id = :id 
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
            
            if ($record['isClose']) {
                http_response_code(400);
                echo json_encode(['details' => 'Nie można usunąć zamkniętego wpisu']);
                return;
            }

            $year = date('Y', strtotime($record['dateOfReceipt']));
            
            $query = "DELETE FROM flate_rate 
                     WHERE ryczalt_Id = :id 
                     AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'companyId' => $companyId
            ]);
            $this->renumberEntries($year);

            echo json_encode([
                'success' => true,
                'details' => 'Usunięto wpis ryczałtu'
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
     * @OA\Get(
     *     path="/flat-rate/summary",
     *     summary="Pobiera podsumowanie stawek dla danego miesiąca",
     *     tags={"Ryczałt"},
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         required=true,
     *         description="Miesiąc (1-12)",
     *         @OA\Schema(type="integer", minimum=1, maximum=12)
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         required=true,
     *         description="Rok",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Podsumowanie stawek",
     *         @OA\JsonContent(
     *             @OA\Property(property="sum_rate17", type="number"),
     *             @OA\Property(property="sum_rate15", type="number"),
     *             @OA\Property(property="sum_rate14", type="number"),
     *             @OA\Property(property="sum_rate12_5", type="number"),
     *             @OA\Property(property="sum_rate12", type="number"),
     *             @OA\Property(property="sum_rate10", type="number"),
     *             @OA\Property(property="sum_rate8_5", type="number"),
     *             @OA\Property(property="sum_rate5_5", type="number"),
     *             @OA\Property(property="sum_rate3", type="number"),
     *             @OA\Property(property="total_sum", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Błąd walidacji lub przetwarzania",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function getSummaryMonth()
    {
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
                        SUM(rate17) as sum_rate17,
                        SUM(rate15) as sum_rate15,
                        SUM(rate14) as sum_rate14,
                        SUM(rate12_5) as sum_rate12_5,
                        SUM(rate12) as sum_rate12,
                        SUM(rate10) as sum_rate10,
                        SUM(rate8_5) as sum_rate8_5,
                        SUM(rate5_5) as sum_rate5_5,
                        SUM(rate3) as sum_rate3,
                        SUM(rate17 + rate15 + rate14 + rate12_5 + rate12 + 
                            rate10 + rate8_5 + rate5_5 + rate3) as total_sum
                     FROM flate_rate 
                     WHERE MONTH(dateOfReceipt) = :month 
                     AND YEAR(dateOfReceipt) = :year 
                     AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':month', $month, PDO::PARAM_INT);
            $stmt->bindValue(':year', $year, PDO::PARAM_INT);
            $stmt->bindValue(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Konwertuj wartości null na 0
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

    /**
     * @OA\Post(
     *     path="/flat-rate/renumber/{year}",
     *     summary="Przenumerowuje wszystkie wpisy ryczałtu dla danego roku",
     *     tags={"Ryczałt"},
     *     @OA\Parameter(
     *         name="year",
     *         in="path",
     *         required=true,
     *         description="Rok",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wpisy zostały przenumerowane",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="details", type="string"),
     *             @OA\Property(property="updatedCount", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Błąd przetwarzania",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function renumberEntries($year)
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

            if (!$year) {
                http_response_code(400);
                echo json_encode(['details' => 'Brak wymaganego parametru year']);
                return;
            }

            // Rozpocznij transakcję
            $this->db->beginTransaction();

            // Pobierz wszystkie wpisy z danego roku, posortowane według daty dokumentu
            $selectQuery = "SELECT ryczalt_id 
                           FROM flate_rate 
                           WHERE YEAR(dateOfReceipt) = :year 
                           AND companyId = :companyId 
                           ORDER BY dateOfEntry ASC, ryczalt_id ASC";

            $selectStmt = $this->db->prepare($selectQuery);
            $selectStmt->execute([
                'year' => $year,
                'companyId' => $companyId
            ]);

            $entries = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
            $updateCount = 0;
            $newLp = 1;

            // Aktualizuj LP dla każdego wpisu
            foreach ($entries as $entry) {
                $updateQuery = "UPDATE flate_rate 
                               SET lp = :lp,
                                   updated_at = NOW(),
                                   updated_by = :userId
                               WHERE ryczalt_id = :id 
                               AND companyId = :companyId";

                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->execute([
                    'lp' => $newLp,
                    'id' => $entry['ryczalt_id'],
                    'companyId' => $companyId,
                    'userId' => getUserId()
                ]);

                $updateCount += $updateStmt->rowCount();
                $newLp++;
            }

            $this->db->commit();
    }
} 