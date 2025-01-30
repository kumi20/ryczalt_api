<?php
require_once 'token.php';

class FlatRateTaxController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getFlatRateTax()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $year = $_GET['year'] ?? null;

            if (!$year) {
                http_response_code(400);
                echo json_encode(['error' => 'Brak wymaganego parametru year']);
                return;
            }

            $query = "SELECT 
                flatRateTaxId,
                month,
                year,
                income,
                reductionAmountPreviousMonth,
                socialInsurance,
                reductionAmountHealt,
                baseTax,
                reduceTaxPreviousMonth,
                reduceTaxNextMonth,
                transferHealt,
                amountFlatRateTax,
                dataPayment,
                isPaid
            FROM FlatRateTax 
            WHERE companyId = :companyId 
            AND year = :year";

            $stmt = $this->db->prepare($query);
            $params = ['companyId' => $companyId, 'year' => $year];
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatowanie danych
            foreach ($results as &$row) {
                $row['flatRateTaxId'] = (int) $row['flatRateTaxId'];
                $row['month'] = (int) $row['month'];
                $row['year'] = (int) $row['year'];
                $row['income'] = number_format((float) $row['income'], 2, '.', '');
                $row['reductionAmountPreviousMonth'] = number_format((float) $row['reductionAmountPreviousMonth'], 2, '.', '');
                $row['socialInsurance'] = number_format((float) $row['socialInsurance'], 2, '.', '');
                $row['reductionAmountHealt'] = number_format((float) $row['reductionAmountHealt'], 2, '.', '');
                $row['baseTax'] = number_format((float) $row['baseTax'], 2, '.', '');
                $row['reduceTaxPreviousMonth'] = number_format((float) $row['reduceTaxPreviousMonth'], 2, '.', '');
                $row['reduceTaxNextMonth'] = number_format((float) $row['reduceTaxNextMonth'], 2, '.', '');
                $row['transferHealt'] = number_format((float) $row['transferHealt'], 2, '.', '');
                $row['amountFlatRateTax'] = number_format((float) $row['amountFlatRateTax'], 2, '.', '');
                $row['isPaid'] = (bool) $row['isPaid'];
                
                if ($row['dataPayment']) {
                    $row['dataPayment'] = date('Y-m-d', strtotime($row['dataPayment']));
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

    public function createFlatRateTax()
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

            $query = "INSERT INTO FlatRateTax (
                companyId,
                month,
                year,
                income,
                reductionAmountPreviousMonth,
                socialInsurance,
                reductionAmountHealt,
                baseTax,
                reduceTaxPreviousMonth,
                reduceTaxNextMonth,
                transferHealt,
                amountFlatRateTax,
                dataPayment,
                isPaid
            ) VALUES (
                :companyId,
                :month,
                :year,
                :income,
                :reductionAmountPreviousMonth,
                :socialInsurance,
                :reductionAmountHealt,
                :baseTax,
                :reduceTaxPreviousMonth,
                :reduceTaxNextMonth,
                :transferHealt,
                :amountFlatRateTax,
                :dataPayment,
                :isPaid
            )";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'companyId' => $companyId,
                'month' => $data['month'],
                'year' => $data['year'],
                'income' => $data['income'] ?? 0,
                'reductionAmountPreviousMonth' => $data['reductionAmountPreviousMonth'] ?? 0,
                'socialInsurance' => $data['socialInsurance'] ?? 0,
                'reductionAmountHealt' => $data['reductionAmountHealt'] ?? 0,
                'baseTax' => $data['baseTax'] ?? 0,
                'reduceTaxPreviousMonth' => $data['reduceTaxPreviousMonth'] ?? 0,
                'reduceTaxNextMonth' => $data['reduceTaxNextMonth'] ?? 0,
                'transferHealt' => $data['transferHealt'] ?? 0,
                'amountFlatRateTax' => $data['amountFlatRateTax'] ?? 0,
                'dataPayment' => $data['dataPayment'] ? date('Y-m-d', strtotime($data['dataPayment'])) : null,
                'isPaid' => (int)($data['isPaid'] ?? 0)
            ]);

            $newId = $this->db->lastInsertId();
            echo json_encode([
                'message' => 'Podatek ryczałtowy został dodany',
                'id' => $newId
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas dodawania podatku ryczałtowego',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function updateFlatRateTax()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();
        $id = $_GET['id'] ?? null;

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Sprawdzenie czy wpis istnieje i należy do firmy
            $checkQuery = "SELECT flatRateTaxId FROM FlatRateTax 
                         WHERE flatRateTaxId = :id AND companyId = :companyId";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute(['id' => $id, 'companyId' => $companyId]);
            
            if (!$checkStmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Podatek ryczałtowy nie został znaleziony']);
                return;
            }

            $query = "UPDATE FlatRateTax SET
                month = :month,
                year = :year,
                income = :income,
                reductionAmountPreviousMonth = :reductionAmountPreviousMonth,
                socialInsurance = :socialInsurance,
                reductionAmountHealt = :reductionAmountHealt,
                baseTax = :baseTax,
                reduceTaxPreviousMonth = :reduceTaxPreviousMonth,
                reduceTaxNextMonth = :reduceTaxNextMonth,
                transferHealt = :transferHealt,
                amountFlatRateTax = :amountFlatRateTax,
                dataPayment = :dataPayment,
                isPaid = :isPaid
            WHERE flatRateTaxId = :id AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id' => $id,
                'companyId' => $companyId,
                'month' => $data['month'],
                'year' => $data['year'],
                'income' => $data['income'] ?? 0,
                'reductionAmountPreviousMonth' => $data['reductionAmountPreviousMonth'] ?? 0,
                'socialInsurance' => $data['socialInsurance'] ?? 0,
                'reductionAmountHealt' => $data['reductionAmountHealt'] ?? 0,
                'baseTax' => $data['baseTax'] ?? 0,
                'reduceTaxPreviousMonth' => $data['reduceTaxPreviousMonth'] ?? 0,
                'reduceTaxNextMonth' => $data['reduceTaxNextMonth'] ?? 0,
                'transferHealt' => $data['transferHealt'] ?? 0,
                'amountFlatRateTax' => $data['amountFlatRateTax'] ?? 0,
                'dataPayment' => $data['dataPayment'] ? date('Y-m-d', strtotime($data['dataPayment'])) : null,
                'isPaid' => (int)($data['isPaid'] ?? 0)
            ]);

            echo json_encode(['message' => 'Podatek ryczałtowy został zaktualizowany']);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas aktualizacji podatku ryczałtowego',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function deleteFlatRateTax()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();
        $id = $_GET['id'] ?? null;

        try {
            $query = "DELETE FROM FlatRateTax 
                     WHERE flatRateTaxId = :id AND companyId = :companyId";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $id, 'companyId' => $companyId]);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Podatek ryczałtowy nie został znaleziony']);
                return;
            }

            echo json_encode(['message' => 'Podatek ryczałtowy został usunięty']);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas usuwania podatku ryczałtowego',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Oblicza podatek ryczałtowy dla danego miesiąca i roku
     */
    public function calculateFlatRateTax()
    {
        header('Content-Type: application/json');
        $companyId = decodeToken();

        try {
            $month = $_GET['month'] ?? null;
            $year = $_GET['year'] ?? null;

            if (!$month || !$year) {
                http_response_code(400);
                echo json_encode(['error' => 'Brak wymaganych parametrów month i year']);
                return;
            }

            // Pobierz przychody z danego miesiąca z tabeli flate_rate
            $query = "SELECT 
                SUM(rate17) as income17,
                SUM(rate15) as income15,
                SUM(rate14) as income14,
                SUM(rate12_5) as income12_5,
                SUM(rate12) as income12,
                SUM(rate10) as income10,
                SUM(rate8_5) as income8_5,
                SUM(rate5_5) as income5_5,
                SUM(rate3) as income3
            FROM flate_rate 
            WHERE MONTH(dateOfReceipt) = :month 
            AND YEAR(dateOfReceipt) = :year 
            AND companyId = :companyId";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'month' => $month,
                'year' => $year,
                'companyId' => $companyId
            ]);
            
            $incomes = $stmt->fetch(PDO::FETCH_ASSOC);

            // Pobierz składki ZUS dla poprzedniego miesiąca
            $prevMonth = $month == 1 ? 12 : $month - 1;
            $prevYear = $month == 1 ? $year - 1 : $year;
            
            $zusQuery = "SELECT 
                social,
                contributionHealth
            FROM contributionsZUS 
            WHERE month = :month 
            AND year = :year 
            AND isSocialPaid = 1
            AND isHealthPaid = 1
            AND companyId = :companyId";

            $zusStmt = $this->db->prepare($zusQuery);
            $zusStmt->execute([
                'month' => $prevMonth,
                'year' => $prevYear,
                'companyId' => $companyId
            ]);

            $zusData = $zusStmt->fetch(PDO::FETCH_ASSOC);
            
            // Obliczanie podstawy opodatkowania
            $totalIncome = array_sum(array_map('floatval', $incomes));
            
            // Obliczanie podstawy opodatkowania
            $baseTax = round(max(0, $totalIncome - $zusData['social'] - $zusData['contributionHealth']/2));

            // Obliczanie proporcji dla każdej stawki
            $tax17 = $totalIncome > 0 ? ($incomes['income17'] ?? 0) / $totalIncome * $baseTax * 0.17 : 0;
            $tax15 = $totalIncome > 0 ? ($incomes['income15'] ?? 0) / $totalIncome * $baseTax * 0.15 : 0;
            $tax14 = $totalIncome > 0 ? ($incomes['income14'] ?? 0) / $totalIncome * $baseTax * 0.14 : 0;
            $tax12_5 = $totalIncome > 0 ? ($incomes['income12_5'] ?? 0) / $totalIncome * $baseTax * 0.125 : 0;
            $tax12 = $totalIncome > 0 ? ($incomes['income12'] ?? 0) / $totalIncome * $baseTax * 0.12 : 0;
            $tax10 = $totalIncome > 0 ? ($incomes['income10'] ?? 0) / $totalIncome * $baseTax * 0.10 : 0;
            $tax8_5 = $totalIncome > 0 ? ($incomes['income8_5'] ?? 0) / $totalIncome * $baseTax * 0.085 : 0;
            $tax5_5 = $totalIncome > 0 ? ($incomes['income5_5'] ?? 0) / $totalIncome * $baseTax * 0.055 : 0;
            $tax3 = $totalIncome > 0 ? ($incomes['income3'] ?? 0) / $totalIncome * $baseTax * 0.03 : 0;

            // Suma podatku przed odliczeniami
            $totalTax = round($tax17 + $tax15 + $tax14 + $tax12_5 + $tax12 + $tax10 + $tax8_5 + $tax5_5 + $tax3);

            // Pobierz kwotę zmniejszenia z poprzedniego miesiąca
            $prevMonthQuery = "SELECT transferHealt
            FROM FlatRateTax 
            WHERE month = :prevMonth 
            AND year = :prevYear 
            AND companyId = :companyId
            AND isPaid = 1";

            $prevStmt = $this->db->prepare($prevMonthQuery);
            $prevStmt->execute([
                'prevMonth' => $prevMonth,
                'prevYear' => $prevYear,
                'companyId' => $companyId
            ]);

            $prevMonthReduction = $prevStmt->fetch(PDO::FETCH_ASSOC);
            $reduceTaxPreviousMonth = floatval($prevMonthReduction['transferHealt'] ?? 0);

            // Składki ZUS
            $socialInsurance = floatval($zusData['social'] ?? 0);
            $healthContribution = floatval($zusData['contributionHealth'] ?? 0);

            // Obliczanie podstawy opodatkowania
            $baseTax = round(max(0, $totalIncome - $socialInsurance - $healthContribution/2));
            
            
            // Jeśli podatek po odliczeniach jest mniejszy niż kwota zmniejszenia,
            // nadwyżka przechodzi na następny miesiąc
            $reduceTaxNextMonth = max(0, $reduceTaxPreviousMonth + $healthContribution - $baseTax);

            echo json_encode([
                'income' => number_format($totalIncome, 2, '.', ''),
                'reductionAmountPreviousMonth' => number_format($reduceTaxPreviousMonth, 2, '.', ''),
                'socialInsurance' => number_format($socialInsurance, 2, '.', ''),
                'reductionAmountHealt' => number_format($healthContribution/2, 2, '.', ''),
                'baseTax' => number_format($baseTax, 2, '.', ''),
                'reduceTaxPreviousMonth' => number_format($reduceTaxPreviousMonth, 2, '.', ''),
                'reduceTaxNextMonth' => number_format($reduceTaxNextMonth, 2, '.', ''),
                'transferHealt' => number_format($healthContribution, 2, '.', ''),
                'amountFlatRateTax' => number_format($totalTax - $reduceTaxPreviousMonth, 2, '.', ''),
                'details' => [
                    'tax17' => number_format($incomes['income17'], 2, '.', ''),
                    'tax15' => number_format($incomes['income15'], 2, '.', ''),
                    'tax14' => number_format($incomes['income14'], 2, '.', ''),
                    'tax12_5' => number_format($incomes['income12_5'], 2, '.', ''),
                    'tax12' => number_format($incomes['income12'], 2, '.', ''),
                    'tax10' => number_format($incomes['income10'], 2, '.', ''),
                    'tax8_5' => number_format($incomes['income8_5'], 2, '.', ''),
                    'tax5_5' => number_format($incomes['income5_5'], 2, '.', ''),
                    'tax3' => number_format($incomes['income3'], 2, '.', '')
                ]
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas obliczania podatku ryczałtowego',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
} 