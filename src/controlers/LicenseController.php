<?php

require_once 'vendor/autoload.php';

use DateTime;

/**
 * @openapi
 * tags:
 *   name: License
 *   description: Zarządzanie licencjami
 */
class LicenseController
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @openapi
     * /api/license:
     *   get:
     *     summary: Pobiera informacje o licencji dla firmy
     *     tags: [License]
     *     security:
     *       - bearerAuth: []
     *     description: Zwraca szczegóły aktualnej licencji dla firmy na podstawie companyId z tokenu
     *     responses:
     *       200:
     *         description: Sukces
     *         content:
     *           application/json:
     *             schema:
     *               type: object
     *               properties:
     *                 licenseNumber:
     *                   type: string
     *                   example: "LIC-2024-XK5792"
     *                   description: Numer licencji
     *                 dataStart:
     *                   type: string
     *                   format: date
     *                   example: "2024-01-01"
     *                   description: Data rozpoczęcia licencji
     *                 dataEnd:
     *                   type: string
     *                   format: date
     *                   example: "2024-12-31"
     *                   description: Data zakończenia licencji
     *                 isActive:
     *                   type: boolean
     *                   example: true
     *                   description: Status aktywności licencji
     *       400:
     *         description: Błąd
     *         content:
     *           application/json:
     *             schema:
     *               type: object
     *               properties:
     *                 error:
     *                   type: string
     *                   example: "Nie znaleziono licencji dla tej firmy"
     *                   description: Opis błędu
     */
    public function getLicenseInfo($companyId)
    {
        header('Content-Type: application/json');
        
        try {
            if (!$companyId) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Brak identyfikatora firmy'
                ]);
                return;
            }

            $query = "
                SELECT 
                    licenseNumber,
                    dataStart,
                    dataEnd
                FROM license 
                WHERE companyId = :companyId
                ORDER BY dataEnd DESC
                LIMIT 1
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['companyId' => $companyId]);
            $license = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$license) {
                http_response_code(400);
                echo json_encode([
                    'error' => 'Nie znaleziono licencji dla tej firmy'
                ]);
                return;
            }

            $currentDate = new DateTime();
            $endDate = new DateTime($license['dataEnd']);
            
            http_response_code(200);
            echo json_encode([
                'licenseNumber' => $license['licenseNumber'],
                'dataStart' => $license['dataStart'],
                'dataEnd' => $license['dataEnd'],
                'isActive' => $endDate > $currentDate
            ]);
            return;

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Wystąpił błąd podczas pobierania danych licencji'
            ]);
            return;
        }
    }
} 