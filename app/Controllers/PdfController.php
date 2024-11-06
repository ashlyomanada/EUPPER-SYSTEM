<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Mpdf\Mpdf;
use App\Models\OfficerModel;

class PdfController extends ResourceController
{

    public function generatePdf() {
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;
        $table = $json->level;
        $officeLevel = $json->officeName;
        $db = \Config\Database::connect();
        // $table = 'ppo_cpo';
    
        $ratingModel = new \App\Models\RatingModel();
        $percentageData = $ratingModel->findAll();
    
        // Describe the table to get columns
        $query = $db->query("DESCRIBE $table");
        $mimaropaColumns = $query->getResultArray();
        $sums = [];
        $sums2 = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
    
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
            $sums2[$columnName] = 0;
        }
    
        // Get operational and administrative offices
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
    
        $operationalOfficeCount = count($operationalOffice); 
        $administrativeOfficeCount = count($administrativeOffice);
        $tableColumnsCount = $operationalOfficeCount + $administrativeOfficeCount + 5;
    
        // Build office names strings for queries
        $officeNamesString = "'" . implode("', '", array_column($operationalOffice, 'office')) . "'";
        $officeNamesString2 = "'" . implode("', '", array_column($administrativeOffice, 'office')) . "'";
    
        // Build SQL queries for operational and administrative offices
        $queryOperation = "
        SELECT $table.*, tbl_users.office
        FROM $table
        INNER JOIN tbl_users ON $table.userid = tbl_users.user_id
        WHERE $table.month = ? AND $table.year = ?
        AND tbl_users.office IN ($officeNamesString)
        ";

        $queryAdministrative = "
        SELECT $table.*, tbl_users.office
        FROM $table
        INNER JOIN tbl_users ON $table.userid = tbl_users.user_id
        WHERE $table.month = ? AND $table.year = ?
        AND tbl_users.office IN ($officeNamesString2)
        ";

        // Execute queries with prepared statements
        $userRate1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $userRate2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();
    
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();
    
        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] += $result1[$columnName];
                }
            }
        }
    
        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += $result2[$columnName];
                }
            }
        }
    
        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60; // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }
    
        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40; // Multiply by 40%
            $averageSums2[$avgCol2] = $result2;
        }
    
        // ---- START: Column update/insertion logic ----
        $counter = 1;
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
    
            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }
    
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    
            // Check if data already exists for the given month and year
            $existingData = $ratingModel
                ->where('month', $month)
                ->where('year', $year)
                ->where('offices', $formattedColumnName)
                ->where('level', $table)
                ->findAll();
    
            if ($existingData) {
                // Data exists, perform update
                $updated = $ratingModel
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('offices', $formattedColumnName)
                    ->where('level', $table)
                    ->set([
                        'total' => $totalPercentage,
                        'percentage_60' => $averageSums[$columnName],
                        'percentage_40' => $averageSums2[$columnName],
                        'foreignOfficeId' => $counter,
                    ])
                    ->update();
    
                if (!$updated) {
                    return $this->failServerError('Failed to update data in rates table.');
                }
            } else {
                // Data doesn't exist, perform insert
                $data = [
                    'month' => $month,
                    'year' => $year,
                    'offices' => $formattedColumnName,
                    'total' => $totalPercentage,
                    'percentage_60' => $averageSums[$columnName],
                    'percentage_40' => $averageSums2[$columnName],
                    'level' => $table,
                    'foreignOfficeId' => $counter,
                ];
    
                // Perform insert
                $inserted = $ratingModel->insert($data);
    
                if (!$inserted) {
                    return $this->failServerError('Failed to insert data into rates table.');
                }
            }
    
            $counter++;
        }
        // ---- END: Column update/insertion logic ----
    
        // Initialize the $totalPercentages array
        $totalPercentages = [];

        // Calculate total percentages for each column
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }

            $totalPercentages[$columnName] = $averageSums[$columnName] + $averageSums2[$columnName]; // Sum operational and administrative percentages
        }

      
        arsort($totalPercentages);
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }
    
        // Load mPDF library
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => __DIR__ . '/tmp',  // Ensure you have a writable temp directory
            // 'orientation' => 'L' // Set orientation to Landscape
        ]);
    
        // Use local file path for the image
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.png';
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData;
        } else {
            die('Image not found.');
        }

        $imageFooterPath = $_SERVER['DOCUMENT_ROOT'] . '/footerLogo.png';
        if (file_exists($imageFooterPath)) {
            $imageFooterData = base64_encode(file_get_contents($imageFooterPath));
            $imageSrc2 = 'data:image/png;base64,' . $imageFooterData;
        } else {
            die('Image not found.');
        }
    
        // Set header and footer HTML content
        $header = '';
        $footer = '<div style="text-align: center;">
        <img src="' . $imageSrc2 . '" alt="Footer Logo" style="width: 60px; " />
        <p style="font-size:8px;margin-top:-8px;"><i>"Sa Bagong Pilipinas, Ang Gusto ng Pulis, Ligtas Ka!"</i></p>
        </div>';
    
        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
    
        $html = '';
        $html .= '<img src="' . $imageSrc . '" alt="Logo" style="height:120px;position:absolute;left:0;top:0;">';
    
        $html .= '<div style="display:flex;flex-direction:column;align-items:center;position:absolute;left:31%;top:6%;transform:translateX(-31%);">';
        $html .= '<h3 style="text-align: center;">PRO MIMAROPA</h3>';
        $html .= '<h3 style="text-align: center;">Unit Performance Evaluation Ratings</h3>';
        $html .= '<h4 style="text-align: center;">(' . $month . ' ' . $year . ')</h4>';
        $html .= '<h5 style="text-align: center;">'.$officeLevel.'</h5>';
        $html .= '</div>';
    
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:20px;">';
        $tableColumnsCount = $operationalOfficeCount + $administrativeOfficeCount + 4;
        $html .= '<tr style="background-color: #ffff00;">';
        $html .= '<th colspan="2" style="border-right:none;border-bottom:none;border-right:1px solid black;"></th>';
        $html .= '<th colspan="'.$tableColumnsCount.'" style="text-align: center;"> Unit Performance Evaluation Ratings</th>';
        $html .= '</tr>';
        $html .= '<tr style="background-color: #ffff00;">';
        $html .= '<th style="border-right:none;border-bottom:none;border-top:none;"></th>';
        $html .= '<th rowspan="3" style="border-left:none;padding-right:30px;padding-left:10px;border-top:none;padding-bottom:30px;">OFFICE/UNIT</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">OPERATIONAL (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">ADMINISTRATIVE (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="2">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Rank</th>';
        $html .= '</tr>';
    
        $html .= '<tr style="background-color: #ffff00;">';
        $html .= '<th style="border-top: none;border-right:none;border-bottom:none;"></th>';
        foreach ($operationalOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        foreach ($administrativeOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr style="background-color: #ffff00;">';
        // $operationalRange = [167, 166, 167, 100];
        // $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        $operationalMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
        $html .= '<th style="border-top: none;border-right:none;"></th>';
        foreach ($operationalMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }
        
        foreach ($administrativeMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }

        $html .= '<th>100%</th>'; 
        
        $html .= '</tr>';
    
        $countNumber = 1;
        // Add table rows for operational and administrative data based on sorted total percentages
        foreach ($totalPercentages as $columnName => $totalPercentage) {
            // Get formatted column name and skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            
            $formattedColumnName = str_replace('_', ' ', $columnName); // Format column name for display
    
            // Start the row
           
            $html .= '<tr>';
            $html .= '<td>' .$countNumber. '</td>'; // Column name (office/unit)
            $html .= '<td>' . $formattedColumnName . '</td>'; // Column name (office/unit)
    
            // Add operational office data for the current column
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_60 for operational office
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
    
            // Add administrative office data for the current column
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_40 for administrative office
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
    
            // Add the total percentage (sum of operational and administrative percentages)
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
    
            // Add the rank for the current column
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
    
            // Close the row
            $html .= '</tr>';
            $countNumber++;
        }
    
        $html .= '</table>';

        $officerModel = new OfficerModel();
        $data = $officerModel->findAll();

        $html .= '<table style="width: 100%; border-collapse: collapse; padding: 10px; margin-top: 5rem; font-size:8px;">';
       
        $html .= '<tr>';
        $html .= '<td style="padding-bottom: 5rem;width:25%;">Recommended by:</td>';
        $html .= '<td style="padding-bottom: 5rem;width:25%;">Noted by:</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<th style="padding: 5px; text-align: left;width:25%;">' . $datas['name'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<td style="padding: 5px; text-align: left;width:25%;">' . $datas['office'] . '</td>';
        }
        $html .= '</tr>';


        $html .= '</table>';
    
        // Write HTML content to PDF
        $mpdf->WriteHTML($html);
    
        // Output the PDF
        return $mpdf->Output($table . '.pdf', 'I');
    }

    public function generatePdfPPO() {
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;
        $db = \Config\Database::connect();
        $table = 'ppo_cpo';
    
        $ratingModel = new \App\Models\RatingModel();
        $percentageData = $ratingModel->findAll();
    
        // Describe the table to get columns
        $query = $db->query("DESCRIBE $table");
        $mimaropaColumns = $query->getResultArray();
        $sums = [];
        $sums2 = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
    
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
            $sums2[$columnName] = 0;
        }
    
        // Get operational and administrative offices
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
    
        $operationalOfficeCount = count($operationalOffice); 
        $administrativeOfficeCount = count($administrativeOffice);
        $tableColumnsCount = $operationalOfficeCount + $administrativeOfficeCount + 5;
    
        // Build office names strings for queries
        $officeNamesString = "'" . implode("', '", array_column($operationalOffice, 'office')) . "'";
        $officeNamesString2 = "'" . implode("', '", array_column($administrativeOffice, 'office')) . "'";
    
        // Build SQL queries for operational and administrative offices
        $queryOperation = "
            SELECT ppo_cpo.*, tbl_users.office
            FROM ppo_cpo
            INNER JOIN tbl_users ON ppo_cpo.userid = tbl_users.user_id
            WHERE ppo_cpo.month = ? AND ppo_cpo.year = ?
            AND tbl_users.office IN ($officeNamesString)
        ";
    
        $queryAdministrative = "
            SELECT ppo_cpo.*, tbl_users.office
            FROM ppo_cpo
            INNER JOIN tbl_users ON ppo_cpo.userid = tbl_users.user_id
            WHERE ppo_cpo.month = ? AND ppo_cpo.year = ?
            AND tbl_users.office IN ($officeNamesString2)
        ";
    
        // Execute queries with prepared statements
        $userRate1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $userRate2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();
    
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();
    
        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] += $result1[$columnName];
                }
            }
        }
    
        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += $result2[$columnName];
                }
            }
        }
    
        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60; // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }
    
        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40; // Multiply by 40%
            $averageSums2[$avgCol2] = $result2;
        }
    
        // ---- START: Column update/insertion logic ----
        $counter = 1;
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
    
            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }
    
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    
            // Check if data already exists for the given month and year
            $existingData = $ratingModel
                ->where('month', $month)
                ->where('year', $year)
                ->where('offices', $formattedColumnName)
                ->where('level', 'ppo_cpo')
                ->findAll();
    
            if ($existingData) {
                // Data exists, perform update
                $updated = $ratingModel
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('offices', $formattedColumnName)
                    ->where('level', 'ppo_cpo')
                    ->set([
                        'total' => $totalPercentage,
                        'percentage_60' => $averageSums[$columnName],
                        'percentage_40' => $averageSums2[$columnName],
                        'foreignOfficeId' => $counter,
                    ])
                    ->update();
    
                if (!$updated) {
                    return $this->failServerError('Failed to update data in rates table.');
                }
            } else {
                // Data doesn't exist, perform insert
                $data = [
                    'month' => $month,
                    'year' => $year,
                    'offices' => $formattedColumnName,
                    'total' => $totalPercentage,
                    'percentage_60' => $averageSums[$columnName],
                    'percentage_40' => $averageSums2[$columnName],
                    'level' => 'ppo_cpo',
                    'foreignOfficeId' => $counter,
                ];
    
                // Perform insert
                $inserted = $ratingModel->insert($data);
    
                if (!$inserted) {
                    return $this->failServerError('Failed to insert data into rates table.');
                }
            }
    
            $counter++;
        }
        // ---- END: Column update/insertion logic ----
    
        // Initialize the $totalPercentages array
        $totalPercentages = [];

        // Calculate total percentages for each column
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }

            $totalPercentages[$columnName] = $averageSums[$columnName] + $averageSums2[$columnName]; // Sum operational and administrative percentages
        }

      
        arsort($totalPercentages);
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }
    
        // Load mPDF library
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => __DIR__ . '/tmp',  // Ensure you have a writable temp directory
            'orientation' => 'L' // Set orientation to Landscape
        ]);
    
        // Use local file path for the image
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.png';
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData;
        } else {
            die('Image not found.');
        }

        $imageFooterPath = $_SERVER['DOCUMENT_ROOT'] . '/footerLogo.png';
        if (file_exists($imageFooterPath)) {
            $imageFooterData = base64_encode(file_get_contents($imageFooterPath));
            $imageSrc2 = 'data:image/png;base64,' . $imageFooterData;
        } else {
            die('Image not found.');
        }
    
        // Set header and footer HTML content
        $header = '';
        $footer = '<div style="text-align: center;">
        <img src="' . $imageSrc2 . '" alt="Footer Logo" style="width: 60px; " />
        <p style="font-size:8px;margin-top:-8px;"><i>"Sa Bagong Pilipinas, Ang Gusto ng Pulis, Ligtas Ka!"</i></p>
        </div>';
    
        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
    
        $html = '';
        $html .= '<img src="' . $imageSrc . '" alt="Logo" style="height:120px;position:absolute;left:0;top:0;">';
    
        $html .= '<div style="display:flex;flex-direction:column;align-items:center;position:absolute;left:36%;top:6%;transform:translateX(-36%);">';
        $html .= '<h3 style="text-align: center;">PRO MIMAROPA</h3>';
        $html .= '<h3 style="text-align: center;">Unit Performance Evaluation Ratings</h3>';
        $html .= '<h4 style="text-align: center;">(' . $month . ' ' . $year . ')</h4>';
        $html .= '<h5 style="text-align: center;">PROVINCIAL/CITY POLICE OFFICES</h5>';
        $html .= '</div>';
    
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:20px;">';
        $tableColumnsCount = $operationalOfficeCount + $administrativeOfficeCount + 4;
        $html .= '<tr style="background-color: #ffff00;">';
        $html .= '<th colspan="2" style="border-right:none;border-bottom:none;border-right:1px solid black;"></th>';
        $html .= '<th colspan="'.$tableColumnsCount.'" style="text-align: center;"> Unit Performance Evaluation Ratings</th>';
        $html .= '</tr>';
        $html .= '<tr style="background-color: #ffff00;">';
        $html .= '<th style="border-right:none;border-bottom:none;border-top:none;"></th>';
        $html .= '<th rowspan="3" style="border-left:none;padding-right:30px;padding-left:10px;border-top:none;padding-bottom:30px;">OFFICE/UNIT</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">OPERATIONAL (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">ADMINISTRATIVE (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="2">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Rank</th>';
        $html .= '</tr>';
    
        $html .= '<tr style="background-color: #ffff00;">';
        $html .= '<th style="border-top: none;border-right:none;border-bottom:none;"></th>';
        foreach ($operationalOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        foreach ($administrativeOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr style="background-color: #ffff00;">';
        // $operationalRange = [167, 166, 167, 100];
        // $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        $operationalMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
        $html .= '<th style="border-top: none;border-right:none;"></th>';
        foreach ($operationalMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }
        
        foreach ($administrativeMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }

        $html .= '<th>100%</th>'; 
        
        $html .= '</tr>';
    
        $countNumber = 1;
        // Add table rows for operational and administrative data based on sorted total percentages
        foreach ($totalPercentages as $columnName => $totalPercentage) {
            // Get formatted column name and skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            
            $formattedColumnName = str_replace('_', ' ', $columnName); // Format column name for display
    
            // Start the row
           
            $html .= '<tr>';
            $html .= '<td>' .$countNumber. '</td>'; // Column name (office/unit)
            $html .= '<td>' . $formattedColumnName . '</td>'; // Column name (office/unit)
    
            // Add operational office data for the current column
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_60 for operational office
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
    
            // Add administrative office data for the current column
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_40 for administrative office
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
    
            // Add the total percentage (sum of operational and administrative percentages)
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
    
            // Add the rank for the current column
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
    
            // Close the row
            $html .= '</tr>';
            $countNumber++;
        }
    
        $html .= '</table>';

        $officerModel = new OfficerModel();
        $data = $officerModel->findAll();

        $html .= '<table style="width: 100%; border-collapse: collapse; padding: 10px; margin-top: 5rem; font-size:8px;">';
       
        $html .= '<tr>';
        $html .= '<td style="padding-bottom: 5rem;">Recommended by:</td>';
        $html .= '<td style="padding-bottom: 5rem;">Noted by:</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<th style="padding: 5px; text-align: left;">' . $datas['name'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<td style="padding: 5px; text-align: left;">' . $datas['office'] . '</td>';
        }
        $html .= '</tr>';


        $html .= '</table>';
    
        // Write HTML content to PDF
        $mpdf->WriteHTML($html);
    
        // Output the PDF
        return $mpdf->Output('ppo_cpo.pdf', 'I');
    }

    public function generatePdfRmfb() {
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;
        $db = \Config\Database::connect();
        $table = 'rmfb_tbl';

        $ratingModel = new \App\Models\RatingModel();
        $percentageData = $ratingModel->findAll();

        // Describe the table to get columns
        $query = $db->query("DESCRIBE $table");
        $mimaropaColumns = $query->getResultArray();
        $sums = [];
        $sums2 = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
            $sums2[$columnName] = 0;
        }

        // Get operational and administrative offices
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();

        $operationalOfficeCount = count($operationalOffice); 
        $administrativeOfficeCount = count($administrativeOffice);

        // Build office names strings for queries
        $officeNamesString = "'" . implode("', '", array_column($operationalOffice, 'office')) . "'";
        $officeNamesString2 = "'" . implode("', '", array_column($administrativeOffice, 'office')) . "'";

        // Build SQL queries for operational and administrative offices
        $queryOperation = "
            SELECT rmfb_tbl.*, tbl_users.office
            FROM rmfb_tbl
            INNER JOIN tbl_users ON rmfb_tbl.userid = tbl_users.user_id
            WHERE rmfb_tbl.month = ? AND rmfb_tbl.year = ?
            AND tbl_users.office IN ($officeNamesString)
        ";

        $queryAdministrative = "
            SELECT rmfb_tbl.*, tbl_users.office
            FROM rmfb_tbl
            INNER JOIN tbl_users ON rmfb_tbl.userid = tbl_users.user_id
            WHERE rmfb_tbl.month = ? AND rmfb_tbl.year = ?
            AND tbl_users.office IN ($officeNamesString2)
        ";

        // Execute queries with prepared statements
        $userRate1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $userRate2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] += $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60; // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40; // Multiply by 40%
            $averageSums2[$avgCol2] = $result2;
        }

        // ---- START: Column update/insertion logic ----
        $counter = 1;
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
    
            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }
    
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    
            // Check if data already exists for the given month and year
            $existingData = $ratingModel
                ->where('month', $month)
                ->where('year', $year)
                ->where('offices', $formattedColumnName)
                ->where('level', 'rmfb_tbl')
                ->findAll();
    
            if ($existingData) {
                // Data exists, perform update
                $updated = $ratingModel
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('offices', $formattedColumnName)
                    ->where('level', 'rmfb_tbl')
                    ->set([
                        'total' => $totalPercentage,
                        'percentage_60' => $averageSums[$columnName],
                        'percentage_40' => $averageSums2[$columnName],
                        'foreignOfficeId' => $counter,
                    ])
                    ->update();
    
                if (!$updated) {
                    return $this->failServerError('Failed to update data in rates table.');
                }
            } else {
                // Data doesn't exist, perform insert
                $data = [
                    'month' => $month,
                    'year' => $year,
                    'offices' => $formattedColumnName,
                    'total' => $totalPercentage,
                    'percentage_60' => $averageSums[$columnName],
                    'percentage_40' => $averageSums2[$columnName],
                    'level' => 'rmfb_tbl',
                    'foreignOfficeId' => $counter,
                ];
    
                // Perform insert
                $inserted = $ratingModel->insert($data);
    
                if (!$inserted) {
                    return $this->failServerError('Failed to insert data into rates table.');
                }
            }
    
            $counter++;
        }
        // ---- END: Column update/insertion logic ----

        // Initialize the $totalPercentages array
        $totalPercentages = [];

        // Calculate total percentages for each column
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }

            $totalPercentages[$columnName] = $averageSums[$columnName] + $averageSums2[$columnName]; // Sum operational and administrative percentages
        }

      
        arsort($totalPercentages);
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }
    
        // Load mPDF library
        $mpdf = new \Mpdf\Mpdf();
    
        // Use local file path for the image
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.png';
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData; // Adjust MIME type if necessary
        } else {
            die('Image not found.');
        }
    
        // Set header and footer HTML content
        $header = '';
        $footer = '<h5 style="text-align: center; font-style: italic;">PRO MIMAROPA EUPPER SYSTEM</h5>';
    
        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
    
        $html = '';
        $html .= '<img src="' . $imageSrc . '" alt="Logo" style="height:120px;position:absolute;left:0;top:0;">';
    
        $html .= '<div style="display:flex;flex-direction:column;align-items:center;position:absolute;left:31%;top:6%;transform:translateX(-31%);">';
        $html .= '<h3 style="text-align: center;">PRO MIMAROPA</h3>';
        $html .= '<h3 style="text-align: center;">Unit Performance Evaluation Ratings</h3>';
        $html .= '<h4 style="text-align: center;">(' . $month . ' ' . $year . ')</h4>';
        $html .= '<h5 style="text-align: center;">RMFB AND PMFC</h5>';
        $html .= '</div>';
    
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:80px;">';
    
        $html .= '<tr>';
        $html .= '<th rowspan="3">OFFICE/UNIT</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">OPERATIONAL (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">ADMINISTRATIVE (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="2">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Rank</th>';
        $html .= '</tr>';
    
        $html .= '<tr>';
        foreach ($operationalOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        foreach ($administrativeOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        // $operationalRange = [167, 166, 167, 100];
        // $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        $operationalMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
        foreach ($operationalMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }
        
        foreach ($administrativeMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }

        $html .= '<th>100%</th>'; 
        
        $html .= '</tr>';
    
        // Add table rows for operational and administrative data based on sorted total percentages
        foreach ($totalPercentages as $columnName => $totalPercentage) {
            // Get formatted column name and skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            
            $formattedColumnName = str_replace('_', ' ', $columnName); // Format column name for display
    
            // Start the row
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>'; // Column name (office/unit)
    
            // Add operational office data for the current column
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_60 for operational office
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
    
            // Add administrative office data for the current column
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_40 for administrative office
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
    
            // Add the total percentage (sum of operational and administrative percentages)
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
    
            // Add the rank for the current column
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
    
            // Close the row
            $html .= '</tr>';
        }
    
        $html .= '</table>';

        $officerModel = new OfficerModel();
        $data = $officerModel->findAll();

        $html .= '<table style="width: 100%; border-collapse: collapse; padding: 10px; margin-top: 5rem; font-size:8px;">';
       
        $html .= '<tr>';
        $html .= '<td style="padding-bottom: 5rem;">Recommended by:</td>';
        $html .= '<td style="padding-bottom: 5rem;">Noted by:</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<th style="padding: 5px; text-align: left;">' . $datas['name'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<td style="padding: 5px; text-align: left;">' . $datas['office'] . '</td>';
        }
        $html .= '</tr>';


        $html .= '</table>';

        // Add content to PDF
        $mpdf->WriteHTML($html);

        // Get the PDF content as a string
        $pdfContent = $mpdf->Output('', 'S'); // 'S' returns the PDF as a string

        // Send appropriate headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="output.pdf"');
        header('Content-Length: ' . strlen($pdfContent));

        // Output the PDF content
        echo $pdfContent;
    }

    public function generatePdfOccidental() {
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;
        $db = \Config\Database::connect();
        $table = 'occidental_cps';

        $ratingModel = new \App\Models\RatingModel();
        $percentageData = $ratingModel->findAll();

        // Describe the table to get columns
        $query = $db->query("DESCRIBE $table");
        $mimaropaColumns = $query->getResultArray();
        $sums = [];
        $sums2 = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
            $sums2[$columnName] = 0;
        }

        // Get operational and administrative offices
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();

        $operationalOfficeCount = count($operationalOffice); 
        $administrativeOfficeCount = count($administrativeOffice);

        // Build office names strings for queries
        $officeNamesString = "'" . implode("', '", array_column($operationalOffice, 'office')) . "'";
        $officeNamesString2 = "'" . implode("', '", array_column($administrativeOffice, 'office')) . "'";

        // Build SQL queries for operational and administrative offices
        $queryOperation = "
            SELECT occidental_cps.*, tbl_users.office
            FROM occidental_cps
            INNER JOIN tbl_users ON occidental_cps.userid = tbl_users.user_id
            WHERE occidental_cps.month = ? AND occidental_cps.year = ?
            AND tbl_users.office IN ($officeNamesString)
        ";

        $queryAdministrative = "
            SELECT occidental_cps.*, tbl_users.office
            FROM occidental_cps
            INNER JOIN tbl_users ON occidental_cps.userid = tbl_users.user_id
            WHERE occidental_cps.month = ? AND occidental_cps.year = ?
            AND tbl_users.office IN ($officeNamesString2)
        ";

        // Execute queries with prepared statements
        $userRate1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $userRate2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] += $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60; // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40; // Multiply by 40%
            $averageSums2[$avgCol2] = $result2;
        }

        // ---- START: Column update/insertion logic ----
        $counter = 1;
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
    
            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }
    
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    
            // Check if data already exists for the given month and year
            $existingData = $ratingModel
                ->where('month', $month)
                ->where('year', $year)
                ->where('offices', $formattedColumnName)
                ->where('level', 'occidental_cps')
                ->findAll();
    
            if ($existingData) {
                // Data exists, perform update
                $updated = $ratingModel
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('offices', $formattedColumnName)
                    ->where('level', 'occidental_cps')
                    ->set([
                        'total' => $totalPercentage,
                        'percentage_60' => $averageSums[$columnName],
                        'percentage_40' => $averageSums2[$columnName],
                        'foreignOfficeId' => $counter,
                    ])
                    ->update();
    
                if (!$updated) {
                    return $this->failServerError('Failed to update data in rates table.');
                }
            } else {
                // Data doesn't exist, perform insert
                $data = [
                    'month' => $month,
                    'year' => $year,
                    'offices' => $formattedColumnName,
                    'total' => $totalPercentage,
                    'percentage_60' => $averageSums[$columnName],
                    'percentage_40' => $averageSums2[$columnName],
                    'level' => 'occidental_cps',
                    'foreignOfficeId' => $counter,
                ];
    
                // Perform insert
                $inserted = $ratingModel->insert($data);
    
                if (!$inserted) {
                    return $this->failServerError('Failed to insert data into rates table.');
                }
            }
    
            $counter++;
        }
        // ---- END: Column update/insertion logic ----

        // Initialize the $totalPercentages array
        $totalPercentages = [];

        // Calculate total percentages for each column
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }

            $totalPercentages[$columnName] = $averageSums[$columnName] + $averageSums2[$columnName]; // Sum operational and administrative percentages
        }

      
        arsort($totalPercentages);
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }
    
        // Load mPDF library
        $mpdf = new \Mpdf\Mpdf();
    
        // Use local file path for the image
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.png';
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData; // Adjust MIME type if necessary
        } else {
            die('Image not found.');
        }
    
        // Set header and footer HTML content
        $header = '';
        $footer = '<h5 style="text-align: center; font-style: italic;">PRO MIMAROPA EUPPER SYSTEM</h5>';
    
        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
    
        $html = '';
        $html .= '<img src="' . $imageSrc . '" alt="Logo" style="height:120px;position:absolute;left:0;top:0;">';
    
        $html .= '<div style="display:flex;flex-direction:column;align-items:center;position:absolute;left:31%;top:6%;transform:translateX(-31%);">';
        $html .= '<h3 style="text-align: center;">PRO MIMAROPA</h3>';
        $html .= '<h3 style="text-align: center;">Unit Performance Evaluation Ratings</h3>';
        $html .= '<h4 style="text-align: center;">(' . $month . ' ' . $year . ')</h4>';
        $html .= '<h5 style="text-align: center;">PROVINCIAL/CITY POLICE OFFICES</h5>';
        $html .= '</div>';
    
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:80px;">';
    
        $html .= '<tr>';
        $html .= '<th rowspan="3">OFFICE/UNIT</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">OPERATIONAL (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">ADMINISTRATIVE (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="2">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Rank</th>';
        $html .= '</tr>';
    
        $html .= '<tr>';
        foreach ($operationalOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        foreach ($administrativeOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        // $operationalRange = [167, 166, 167, 100];
        // $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        $operationalMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
        foreach ($operationalMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }
        
        foreach ($administrativeMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }

        $html .= '<th>100%</th>'; 
        
        $html .= '</tr>';
    
        // Add table rows for operational and administrative data based on sorted total percentages
        foreach ($totalPercentages as $columnName => $totalPercentage) {
            // Get formatted column name and skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            
            $formattedColumnName = str_replace('_', ' ', $columnName); // Format column name for display
    
            // Start the row
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>'; // Column name (office/unit)
    
            // Add operational office data for the current column
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_60 for operational office
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
    
            // Add administrative office data for the current column
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_40 for administrative office
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
    
            // Add the total percentage (sum of operational and administrative percentages)
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
    
            // Add the rank for the current column
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
    
            // Close the row
            $html .= '</tr>';
        }
    
        $html .= '</table>';

        $officerModel = new OfficerModel();
        $data = $officerModel->findAll();

        $html .= '<table style="width: 100%; border-collapse: collapse; padding: 10px; margin-top: 5rem; font-size:8px;">';
       
        $html .= '<tr>';
        $html .= '<td style="padding-bottom: 5rem;">Recommended by:</td>';
        $html .= '<td style="padding-bottom: 5rem;">Noted by:</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<th style="padding: 5px; text-align: left;">' . $datas['name'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<td style="padding: 5px; text-align: left;">' . $datas['office'] . '</td>';
        }
        $html .= '</tr>';


        $html .= '</table>';

        // Add content to PDF
        $mpdf->WriteHTML($html);

        // Get the PDF content as a string
        $pdfContent = $mpdf->Output('', 'S'); // 'S' returns the PDF as a string

        // Send appropriate headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="output.pdf"');
        header('Content-Length: ' . strlen($pdfContent));

        // Output the PDF content
        echo $pdfContent;
    }

    public function generatePdfOriental() {
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;
        $db = \Config\Database::connect();
        $table = 'oriental_cps';

        $ratingModel = new \App\Models\RatingModel();
        $percentageData = $ratingModel->findAll();

        // Describe the table to get columns
        $query = $db->query("DESCRIBE $table");
        $mimaropaColumns = $query->getResultArray();
        $sums = [];
        $sums2 = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
            $sums2[$columnName] = 0;
        }

        // Get operational and administrative offices
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();

        $operationalOfficeCount = count($operationalOffice); 
        $administrativeOfficeCount = count($administrativeOffice);

        // Build office names strings for queries
        $officeNamesString = "'" . implode("', '", array_column($operationalOffice, 'office')) . "'";
        $officeNamesString2 = "'" . implode("', '", array_column($administrativeOffice, 'office')) . "'";

        // Build SQL queries for operational and administrative offices
        $queryOperation = "
            SELECT oriental_cps.*, tbl_users.office
            FROM oriental_cps
            INNER JOIN tbl_users ON oriental_cps.userid = tbl_users.user_id
            WHERE oriental_cps.month = ? AND oriental_cps.year = ?
            AND tbl_users.office IN ($officeNamesString)
        ";

        $queryAdministrative = "
            SELECT oriental_cps.*, tbl_users.office
            FROM oriental_cps
            INNER JOIN tbl_users ON oriental_cps.userid = tbl_users.user_id
            WHERE oriental_cps.month = ? AND oriental_cps.year = ?
            AND tbl_users.office IN ($officeNamesString2)
        ";

        // Execute queries with prepared statements
        $userRate1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $userRate2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] += $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60; // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40; // Multiply by 40%
            $averageSums2[$avgCol2] = $result2;
        }

         // ---- START: Column update/insertion logic ----
         $counter = 1;
         foreach ($mimaropaColumns as $column) {
             $columnName = $column['Field'];
     
             // Skip irrelevant columns
             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                 continue;
             }
     
             $formattedColumnName = str_replace('_', ' ', $columnName);
             $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
     
             // Check if data already exists for the given month and year
             $existingData = $ratingModel
                 ->where('month', $month)
                 ->where('year', $year)
                 ->where('offices', $formattedColumnName)
                 ->where('level', 'oriental_cps')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'oriental_cps')
                     ->set([
                         'total' => $totalPercentage,
                         'percentage_60' => $averageSums[$columnName],
                         'percentage_40' => $averageSums2[$columnName],
                         'foreignOfficeId' => $counter,
                     ])
                     ->update();
     
                 if (!$updated) {
                     return $this->failServerError('Failed to update data in rates table.');
                 }
             } else {
                 // Data doesn't exist, perform insert
                 $data = [
                     'month' => $month,
                     'year' => $year,
                     'offices' => $formattedColumnName,
                     'total' => $totalPercentage,
                     'percentage_60' => $averageSums[$columnName],
                     'percentage_40' => $averageSums2[$columnName],
                     'level' => 'oriental_cps',
                     'foreignOfficeId' => $counter,
                 ];
     
                 // Perform insert
                 $inserted = $ratingModel->insert($data);
     
                 if (!$inserted) {
                     return $this->failServerError('Failed to insert data into rates table.');
                 }
             }
     
             $counter++;
         }
         // ---- END: Column update/insertion logic ----

        // Initialize the $totalPercentages array
        $totalPercentages = [];

        // Calculate total percentages for each column
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }

            $totalPercentages[$columnName] = $averageSums[$columnName] + $averageSums2[$columnName]; // Sum operational and administrative percentages
        }

      
        arsort($totalPercentages);
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }
    
        // Load mPDF library
        $mpdf = new \Mpdf\Mpdf();
    
        // Use local file path for the image
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.png';
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData; // Adjust MIME type if necessary
        } else {
            die('Image not found.');
        }
    
        // Set header and footer HTML content
        $header = '';
        $footer = '<h5 style="text-align: center; font-style: italic;">PRO MIMAROPA EUPPER SYSTEM</h5>';
    
        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
    
        $html = '';
        $html .= '<img src="' . $imageSrc . '" alt="Logo" style="height:120px;position:absolute;left:0;top:0;">';
    
        $html .= '<div style="display:flex;flex-direction:column;align-items:center;position:absolute;left:31%;top:6%;transform:translateX(-31%);">';
        $html .= '<h3 style="text-align: center;">PRO MIMAROPA</h3>';
        $html .= '<h3 style="text-align: center;">Unit Performance Evaluation Ratings</h3>';
        $html .= '<h4 style="text-align: center;">(' . $month . ' ' . $year . ')</h4>';
        $html .= '<h5 style="text-align: center;">PROVINCIAL/CITY POLICE OFFICES</h5>';
        $html .= '</div>';
    
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:80px;">';
    
        $html .= '<tr>';
        $html .= '<th rowspan="3">OFFICE/UNIT</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">OPERATIONAL (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">ADMINISTRATIVE (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="2">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Rank</th>';
        $html .= '</tr>';
    
        $html .= '<tr>';
        foreach ($operationalOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        foreach ($administrativeOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        // $operationalRange = [167, 166, 167, 100];
        // $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        $operationalMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
        foreach ($operationalMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }
        
        foreach ($administrativeMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }

        $html .= '<th>100%</th>'; 
        
        $html .= '</tr>';
    
        // Add table rows for operational and administrative data based on sorted total percentages
        foreach ($totalPercentages as $columnName => $totalPercentage) {
            // Get formatted column name and skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            
            $formattedColumnName = str_replace('_', ' ', $columnName); // Format column name for display
    
            // Start the row
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>'; // Column name (office/unit)
    
            // Add operational office data for the current column
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_60 for operational office
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
    
            // Add administrative office data for the current column
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_40 for administrative office
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
    
            // Add the total percentage (sum of operational and administrative percentages)
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
    
            // Add the rank for the current column
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
    
            // Close the row
            $html .= '</tr>';
        }
    
        $html .= '</table>';

        $officerModel = new OfficerModel();
        $data = $officerModel->findAll();

        $html .= '<table style="width: 100%; border-collapse: collapse; padding: 10px; margin-top: 5rem; font-size:8px;">';
       
        $html .= '<tr>';
        $html .= '<td style="padding-bottom: 5rem;">Recommended by:</td>';
        $html .= '<td style="padding-bottom: 5rem;">Noted by:</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<th style="padding: 5px; text-align: left;">' . $datas['name'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<td style="padding: 5px; text-align: left;">' . $datas['office'] . '</td>';
        }
        $html .= '</tr>';


        $html .= '</table>';

        // Add content to PDF
        $mpdf->WriteHTML($html);

        // Get the PDF content as a string
        $pdfContent = $mpdf->Output('', 'S'); // 'S' returns the PDF as a string

        // Send appropriate headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="output.pdf"');
        header('Content-Length: ' . strlen($pdfContent));

        // Output the PDF content
        echo $pdfContent;
    }

    public function generatePdfMarinduque() {
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;
        $db = \Config\Database::connect();
        $table = 'marinduque_cps';

        $ratingModel = new \App\Models\RatingModel();
        $percentageData = $ratingModel->findAll();

        // Describe the table to get columns
        $query = $db->query("DESCRIBE $table");
        $mimaropaColumns = $query->getResultArray();
        $sums = [];
        $sums2 = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
            $sums2[$columnName] = 0;
        }

        // Get operational and administrative offices
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();

        $operationalOfficeCount = count($operationalOffice); 
        $administrativeOfficeCount = count($administrativeOffice);

        // Build office names strings for queries
        $officeNamesString = "'" . implode("', '", array_column($operationalOffice, 'office')) . "'";
        $officeNamesString2 = "'" . implode("', '", array_column($administrativeOffice, 'office')) . "'";

        // Build SQL queries for operational and administrative offices
        $queryOperation = "
            SELECT marinduque_cps.*, tbl_users.office
            FROM marinduque_cps
            INNER JOIN tbl_users ON marinduque_cps.userid = tbl_users.user_id
            WHERE marinduque_cps.month = ? AND marinduque_cps.year = ?
            AND tbl_users.office IN ($officeNamesString)
        ";

        $queryAdministrative = "
            SELECT marinduque_cps.*, tbl_users.office
            FROM marinduque_cps
            INNER JOIN tbl_users ON marinduque_cps.userid = tbl_users.user_id
            WHERE marinduque_cps.month = ? AND marinduque_cps.year = ?
            AND tbl_users.office IN ($officeNamesString2)
        ";

        // Execute queries with prepared statements
        $userRate1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $userRate2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] += $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60; // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40; // Multiply by 40%
            $averageSums2[$avgCol2] = $result2;
        }

         // ---- START: Column update/insertion logic ----
         $counter = 1;
         foreach ($mimaropaColumns as $column) {
             $columnName = $column['Field'];
     
             // Skip irrelevant columns
             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                 continue;
             }
     
             $formattedColumnName = str_replace('_', ' ', $columnName);
             $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
     
             // Check if data already exists for the given month and year
             $existingData = $ratingModel
                 ->where('month', $month)
                 ->where('year', $year)
                 ->where('offices', $formattedColumnName)
                 ->where('level', 'marinduque_cps')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'marinduque_cps')
                     ->set([
                         'total' => $totalPercentage,
                         'percentage_60' => $averageSums[$columnName],
                         'percentage_40' => $averageSums2[$columnName],
                         'foreignOfficeId' => $counter,
                     ])
                     ->update();
     
                 if (!$updated) {
                     return $this->failServerError('Failed to update data in rates table.');
                 }
             } else {
                 // Data doesn't exist, perform insert
                 $data = [
                     'month' => $month,
                     'year' => $year,
                     'offices' => $formattedColumnName,
                     'total' => $totalPercentage,
                     'percentage_60' => $averageSums[$columnName],
                     'percentage_40' => $averageSums2[$columnName],
                     'level' => 'marinduque_cps',
                     'foreignOfficeId' => $counter,
                 ];
     
                 // Perform insert
                 $inserted = $ratingModel->insert($data);
     
                 if (!$inserted) {
                     return $this->failServerError('Failed to insert data into rates table.');
                 }
             }
     
             $counter++;
         }
         // ---- END: Column update/insertion logic ----
        // Initialize the $totalPercentages array
        $totalPercentages = [];

        // Calculate total percentages for each column
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }

            $totalPercentages[$columnName] = $averageSums[$columnName] + $averageSums2[$columnName]; // Sum operational and administrative percentages
        }

      
        arsort($totalPercentages);
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }
    
        // Load mPDF library
        $mpdf = new \Mpdf\Mpdf();
    
        // Use local file path for the image
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.png';
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData; // Adjust MIME type if necessary
        } else {
            die('Image not found.');
        }
    
        // Set header and footer HTML content
        $header = '';
        $footer = '<h5 style="text-align: center; font-style: italic;">PRO MIMAROPA EUPPER SYSTEM</h5>';
    
        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
    
        $html = '';
        $html .= '<img src="' . $imageSrc . '" alt="Logo" style="height:120px;position:absolute;left:0;top:0;">';
    
        $html .= '<div style="display:flex;flex-direction:column;align-items:center;position:absolute;left:31%;top:6%;transform:translateX(-31%);">';
        $html .= '<h3 style="text-align: center;">PRO MIMAROPA</h3>';
        $html .= '<h3 style="text-align: center;">Unit Performance Evaluation Ratings</h3>';
        $html .= '<h4 style="text-align: center;">(' . $month . ' ' . $year . ')</h4>';
        $html .= '<h5 style="text-align: center;">PROVINCIAL/CITY POLICE OFFICES</h5>';
        $html .= '</div>';
    
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:80px;">';
    
        $html .= '<tr>';
        $html .= '<th rowspan="3">OFFICE/UNIT</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">OPERATIONAL (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">ADMINISTRATIVE (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="2">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Rank</th>';
        $html .= '</tr>';
    
        $html .= '<tr>';
        foreach ($operationalOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        foreach ($administrativeOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        // $operationalRange = [167, 166, 167, 100];
        // $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        $operationalMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
        foreach ($operationalMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }
        
        foreach ($administrativeMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }

        $html .= '<th>100%</th>'; 
        
        $html .= '</tr>';
    
        // Add table rows for operational and administrative data based on sorted total percentages
        foreach ($totalPercentages as $columnName => $totalPercentage) {
            // Get formatted column name and skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            
            $formattedColumnName = str_replace('_', ' ', $columnName); // Format column name for display
    
            // Start the row
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>'; // Column name (office/unit)
    
            // Add operational office data for the current column
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_60 for operational office
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
    
            // Add administrative office data for the current column
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_40 for administrative office
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
    
            // Add the total percentage (sum of operational and administrative percentages)
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
    
            // Add the rank for the current column
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
    
            // Close the row
            $html .= '</tr>';
        }
    
        $html .= '</table>';

        $officerModel = new OfficerModel();
        $data = $officerModel->findAll();

        $html .= '<table style="width: 100%; border-collapse: collapse; padding: 10px; margin-top: 5rem; font-size:8px;">';
       
        $html .= '<tr>';
        $html .= '<td style="padding-bottom: 5rem;">Recommended by:</td>';
        $html .= '<td style="padding-bottom: 5rem;">Noted by:</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<th style="padding: 5px; text-align: left;">' . $datas['name'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<td style="padding: 5px; text-align: left;">' . $datas['office'] . '</td>';
        }
        $html .= '</tr>';


        $html .= '</table>';

        // Add content to PDF
        $mpdf->WriteHTML($html);

        // Get the PDF content as a string
        $pdfContent = $mpdf->Output('', 'S'); // 'S' returns the PDF as a string

        // Send appropriate headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="output.pdf"');
        header('Content-Length: ' . strlen($pdfContent));

        // Output the PDF content
        echo $pdfContent;
    }

    public function generatePdfRomblon() {
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;
        $db = \Config\Database::connect();
        $table = 'romblon_cps';

        $ratingModel = new \App\Models\RatingModel();
        $percentageData = $ratingModel->findAll();

        // Describe the table to get columns
        $query = $db->query("DESCRIBE $table");
        $mimaropaColumns = $query->getResultArray();
        $sums = [];
        $sums2 = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
            $sums2[$columnName] = 0;
        }

        // Get operational and administrative offices
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();

        $operationalOfficeCount = count($operationalOffice); 
        $administrativeOfficeCount = count($administrativeOffice);

        // Build office names strings for queries
        $officeNamesString = "'" . implode("', '", array_column($operationalOffice, 'office')) . "'";
        $officeNamesString2 = "'" . implode("', '", array_column($administrativeOffice, 'office')) . "'";

        // Build SQL queries for operational and administrative offices
        $queryOperation = "
            SELECT romblon_cps.*, tbl_users.office
            FROM romblon_cps
            INNER JOIN tbl_users ON romblon_cps.userid = tbl_users.user_id
            WHERE romblon_cps.month = ? AND romblon_cps.year = ?
            AND tbl_users.office IN ($officeNamesString)
        ";

        $queryAdministrative = "
            SELECT romblon_cps.*, tbl_users.office
            FROM romblon_cps
            INNER JOIN tbl_users ON romblon_cps.userid = tbl_users.user_id
            WHERE romblon_cps.month = ? AND romblon_cps.year = ?
            AND tbl_users.office IN ($officeNamesString2)
        ";

        // Execute queries with prepared statements
        $userRate1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $userRate2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] += $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60; // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40; // Multiply by 40%
            $averageSums2[$avgCol2] = $result2;
        }

         // ---- START: Column update/insertion logic ----
         $counter = 1;
         foreach ($mimaropaColumns as $column) {
             $columnName = $column['Field'];
     
             // Skip irrelevant columns
             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                 continue;
             }
     
             $formattedColumnName = str_replace('_', ' ', $columnName);
             $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
     
             // Check if data already exists for the given month and year
             $existingData = $ratingModel
                 ->where('month', $month)
                 ->where('year', $year)
                 ->where('offices', $formattedColumnName)
                 ->where('level', 'romblon_cps')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'romblon_cps')
                     ->set([
                         'total' => $totalPercentage,
                         'percentage_60' => $averageSums[$columnName],
                         'percentage_40' => $averageSums2[$columnName],
                         'foreignOfficeId' => $counter,
                     ])
                     ->update();
     
                 if (!$updated) {
                     return $this->failServerError('Failed to update data in rates table.');
                 }
             } else {
                 // Data doesn't exist, perform insert
                 $data = [
                     'month' => $month,
                     'year' => $year,
                     'offices' => $formattedColumnName,
                     'total' => $totalPercentage,
                     'percentage_60' => $averageSums[$columnName],
                     'percentage_40' => $averageSums2[$columnName],
                     'level' => 'romblon_cps',
                     'foreignOfficeId' => $counter,
                 ];
     
                 // Perform insert
                 $inserted = $ratingModel->insert($data);
     
                 if (!$inserted) {
                     return $this->failServerError('Failed to insert data into rates table.');
                 }
             }
     
             $counter++;
         }
         // ---- END: Column update/insertion logic ----

        // Initialize the $totalPercentages array
        $totalPercentages = [];

        // Calculate total percentages for each column
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }

            $totalPercentages[$columnName] = $averageSums[$columnName] + $averageSums2[$columnName]; // Sum operational and administrative percentages
        }

      
        arsort($totalPercentages);
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }
    
        // Load mPDF library
        $mpdf = new \Mpdf\Mpdf();
    
        // Use local file path for the image
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.png';
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData; // Adjust MIME type if necessary
        } else {
            die('Image not found.');
        }
    
        // Set header and footer HTML content
        $header = '';
        $footer = '<h5 style="text-align: center; font-style: italic;">PRO MIMAROPA EUPPER SYSTEM</h5>';
    
        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
    
        $html = '';
        $html .= '<img src="' . $imageSrc . '" alt="Logo" style="height:120px;position:absolute;left:0;top:0;">';
    
        $html .= '<div style="display:flex;flex-direction:column;align-items:center;position:absolute;left:31%;top:6%;transform:translateX(-31%);">';
        $html .= '<h3 style="text-align: center;">PRO MIMAROPA</h3>';
        $html .= '<h3 style="text-align: center;">Unit Performance Evaluation Ratings</h3>';
        $html .= '<h4 style="text-align: center;">(' . $month . ' ' . $year . ')</h4>';
        $html .= '<h5 style="text-align: center;">PROVINCIAL/CITY POLICE OFFICES</h5>';
        $html .= '</div>';
    
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:80px;">';
    
        $html .= '<tr>';
        $html .= '<th rowspan="3">OFFICE/UNIT</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">OPERATIONAL (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">ADMINISTRATIVE (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="2">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Rank</th>';
        $html .= '</tr>';
    
        $html .= '<tr>';
        foreach ($operationalOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        foreach ($administrativeOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        // $operationalRange = [167, 166, 167, 100];
        // $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        $operationalMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
        foreach ($operationalMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }
        
        foreach ($administrativeMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }

        $html .= '<th>100%</th>'; 
        
        $html .= '</tr>';
    
        // Add table rows for operational and administrative data based on sorted total percentages
        foreach ($totalPercentages as $columnName => $totalPercentage) {
            // Get formatted column name and skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            
            $formattedColumnName = str_replace('_', ' ', $columnName); // Format column name for display
    
            // Start the row
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>'; // Column name (office/unit)
    
            // Add operational office data for the current column
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_60 for operational office
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
    
            // Add administrative office data for the current column
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_40 for administrative office
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
    
            // Add the total percentage (sum of operational and administrative percentages)
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
    
            // Add the rank for the current column
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
    
            // Close the row
            $html .= '</tr>';
        }
    
        $html .= '</table>';
        
        $officerModel = new OfficerModel();
        $data = $officerModel->findAll();

        $html .= '<table style="width: 100%; border-collapse: collapse; padding: 10px; margin-top: 5rem; font-size:8px;">';
       
        $html .= '<tr>';
        $html .= '<td style="padding-bottom: 5rem;">Recommended by:</td>';
        $html .= '<td style="padding-bottom: 5rem;">Noted by:</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<th style="padding: 5px; text-align: left;">' . $datas['name'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<td style="padding: 5px; text-align: left;">' . $datas['office'] . '</td>';
        }
        $html .= '</tr>';

        $html .= '</table>';

        // Add content to PDF
        $mpdf->WriteHTML($html);

        // Get the PDF content as a string
        $pdfContent = $mpdf->Output('', 'S'); // 'S' returns the PDF as a string

        // Send appropriate headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="output.pdf"');
        header('Content-Length: ' . strlen($pdfContent));

        // Output the PDF content
        echo $pdfContent;
    }

    public function generatePdfPalawan() {
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;
        $db = \Config\Database::connect();
        $table = 'palawan_cps';

        $ratingModel = new \App\Models\RatingModel();
        $percentageData = $ratingModel->findAll();

        // Describe the table to get columns
        $query = $db->query("DESCRIBE $table");
        $mimaropaColumns = $query->getResultArray();
        $sums = [];
        $sums2 = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
            $sums2[$columnName] = 0;
        }

        // Get operational and administrative offices
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();

        $operationalOfficeCount = count($operationalOffice); 
        $administrativeOfficeCount = count($administrativeOffice);

        // Build office names strings for queries
        $officeNamesString = "'" . implode("', '", array_column($operationalOffice, 'office')) . "'";
        $officeNamesString2 = "'" . implode("', '", array_column($administrativeOffice, 'office')) . "'";

        // Build SQL queries for operational and administrative offices
        $queryOperation = "
            SELECT palawan_cps.*, tbl_users.office
            FROM palawan_cps
            INNER JOIN tbl_users ON palawan_cps.userid = tbl_users.user_id
            WHERE palawan_cps.month = ? AND palawan_cps.year = ?
            AND tbl_users.office IN ($officeNamesString)
        ";

        $queryAdministrative = "
            SELECT palawan_cps.*, tbl_users.office
            FROM palawan_cps
            INNER JOIN tbl_users ON palawan_cps.userid = tbl_users.user_id
            WHERE palawan_cps.month = ? AND palawan_cps.year = ?
            AND tbl_users.office IN ($officeNamesString2)
        ";

        // Execute queries with prepared statements
        $userRate1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $userRate2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] += $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60; // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40; // Multiply by 40%
            $averageSums2[$avgCol2] = $result2;
        }

         // ---- START: Column update/insertion logic ----
         $counter = 1;
         foreach ($mimaropaColumns as $column) {
             $columnName = $column['Field'];
     
             // Skip irrelevant columns
             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                 continue;
             }
     
             $formattedColumnName = str_replace('_', ' ', $columnName);
             $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
     
             // Check if data already exists for the given month and year
             $existingData = $ratingModel
                 ->where('month', $month)
                 ->where('year', $year)
                 ->where('offices', $formattedColumnName)
                 ->where('level', 'palawan_cps')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'palawan_cps')
                     ->set([
                         'total' => $totalPercentage,
                         'percentage_60' => $averageSums[$columnName],
                         'percentage_40' => $averageSums2[$columnName],
                         'foreignOfficeId' => $counter,
                     ])
                     ->update();
     
                 if (!$updated) {
                     return $this->failServerError('Failed to update data in rates table.');
                 }
             } else {
                 // Data doesn't exist, perform insert
                 $data = [
                     'month' => $month,
                     'year' => $year,
                     'offices' => $formattedColumnName,
                     'total' => $totalPercentage,
                     'percentage_60' => $averageSums[$columnName],
                     'percentage_40' => $averageSums2[$columnName],
                     'level' => 'palawan_cps',
                     'foreignOfficeId' => $counter,
                 ];
     
                 // Perform insert
                 $inserted = $ratingModel->insert($data);
     
                 if (!$inserted) {
                     return $this->failServerError('Failed to insert data into rates table.');
                 }
             }
     
             $counter++;
         }
         // ---- END: Column update/insertion logic ----

        // Initialize the $totalPercentages array
        $totalPercentages = [];

        // Calculate total percentages for each column
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            // Skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                continue;
            }

            $totalPercentages[$columnName] = $averageSums[$columnName] + $averageSums2[$columnName]; // Sum operational and administrative percentages
        }

      
        arsort($totalPercentages);
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }
    
        // Load mPDF library
        $mpdf = new \Mpdf\Mpdf();
    
        // Use local file path for the image
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.png';
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageSrc = 'data:image/png;base64,' . $imageData; // Adjust MIME type if necessary
        } else {
            die('Image not found.');
        }
    
        // Set header and footer HTML content
        $header = '';
        $footer = '<h5 style="text-align: center; font-style: italic;">PRO MIMAROPA EUPPER SYSTEM</h5>';
    
        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
    
        $html = '';
        $html .= '<img src="' . $imageSrc . '" alt="Logo" style="height:120px;position:absolute;left:0;top:0;">';
    
        $html .= '<div style="display:flex;flex-direction:column;align-items:center;position:absolute;left:31%;top:6%;transform:translateX(-31%);">';
        $html .= '<h3 style="text-align: center;">PRO MIMAROPA</h3>';
        $html .= '<h3 style="text-align: center;">Unit Performance Evaluation Ratings</h3>';
        $html .= '<h4 style="text-align: center;">(' . $month . ' ' . $year . ')</h4>';
        $html .= '<h5 style="text-align: center;">PROVINCIAL/CITY POLICE OFFICES</h5>';
        $html .= '</div>';
    
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:80px;">';
    
        $html .= '<tr>';
        $html .= '<th rowspan="3">OFFICE/UNIT</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">OPERATIONAL (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">ADMINISTRATIVE (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="2">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Rank</th>';
        $html .= '</tr>';
    
        $html .= '<tr>';
        foreach ($operationalOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        foreach ($administrativeOffice as $office) {
            $html .= '<th>' . $office['office'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        // $operationalRange = [167, 166, 167, 100];
        // $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        $operationalMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
        foreach ($operationalMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }
        
        foreach ($administrativeMaxRate as $office) {
            $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
        }

        $html .= '<th>100%</th>'; 
        
        $html .= '</tr>';
    
        // Add table rows for operational and administrative data based on sorted total percentages
        foreach ($totalPercentages as $columnName => $totalPercentage) {
            // Get formatted column name and skip irrelevant columns
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            
            $formattedColumnName = str_replace('_', ' ', $columnName); // Format column name for display
    
            // Start the row
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>'; // Column name (office/unit)
    
            // Add operational office data for the current column
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_60 for operational office
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
    
            // Add administrative office data for the current column
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
    
            // Add percentage_40 for administrative office
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
    
            // Add the total percentage (sum of operational and administrative percentages)
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
    
            // Add the rank for the current column
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
    
            // Close the row
            $html .= '</tr>';
        }
    
        $html .= '</table>';

        $officerModel = new OfficerModel();
        $data = $officerModel->findAll();

        $html .= '<table style="width: 100%; border-collapse: collapse; padding: 10px; margin-top: 5rem; font-size:8px;">';
       
        $html .= '<tr>';
        $html .= '<td style="padding-bottom: 5rem;">Recommended by:</td>';
        $html .= '<td style="padding-bottom: 5rem;">Noted by:</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<th style="padding: 5px; text-align: left;">' . $datas['name'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<td style="padding: 5px; text-align: left;">' . $datas['office'] . '</td>';
        }
        $html .= '</tr>';


        $html .= '</table>';

        // Add content to PDF
        $mpdf->WriteHTML($html);

        // Get the PDF content as a string
        $pdfContent = $mpdf->Output('', 'S'); // 'S' returns the PDF as a string

        // Send appropriate headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="output.pdf"');
        header('Content-Length: ' . strlen($pdfContent));

        // Output the PDF content
        echo $pdfContent;
    }

    public function generatePdfPuerto() {
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;
        $db = \Config\Database::connect();
        $table = 'puertop_cps';

        $ratingModel = new \App\Models\RatingModel();
        $percentageData = $ratingModel->findAll();

        // Describe the table to get columns
        $query = $db->query("DESCRIBE $table");
        $mimaropaColumns = $query->getResultArray();
        $sums = [];
        $sums2 = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
            $sums2[$columnName] = 0;
        }

        // Get operational and administrative offices
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();

        $operationalOfficeCount = count($operationalOffice); 
        $administrativeOfficeCount = count($administrativeOffice);

        // Build office names strings for queries
        $officeNamesString = "'" . implode("', '", array_column($operationalOffice, 'office')) . "'";
        $officeNamesString2 = "'" . implode("', '", array_column($administrativeOffice, 'office')) . "'";

        // Build SQL queries for operational and administrative offices
        $queryOperation = "
            SELECT puertop_cps.*, tbl_users.office
            FROM puertop_cps
            INNER JOIN tbl_users ON puertop_cps.userid = tbl_users.user_id
            WHERE puertop_cps.month = ? AND puertop_cps.year = ?
            AND tbl_users.office IN ($officeNamesString)
        ";

        $queryAdministrative = "
            SELECT puertop_cps.*, tbl_users.office
            FROM puertop_cps
            INNER JOIN tbl_users ON puertop_cps.userid = tbl_users.user_id
            WHERE puertop_cps.month = ? AND puertop_cps.year = ?
            AND tbl_users.office IN ($officeNamesString2)
        ";

        // Execute queries with prepared statements
        $userRate1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $userRate2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] += $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60; // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40; // Multiply by 40%
            $averageSums2[$avgCol2] = $result2;
        }

         // ---- START: Column update/insertion logic ----
         $counter = 1;
         foreach ($mimaropaColumns as $column) {
             $columnName = $column['Field'];
     
             // Skip irrelevant columns
             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                 continue;
             }
     
             $formattedColumnName = str_replace('_', ' ', $columnName);
             $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
     
             // Check if data already exists for the given month and year
             $existingData = $ratingModel
                 ->where('month', $month)
                 ->where('year', $year)
                 ->where('offices', $formattedColumnName)
                 ->where('level', 'puertop_cps')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'puertop_cps')
                     ->set([
                         'total' => $totalPercentage,
                         'percentage_60' => $averageSums[$columnName],
                         'percentage_40' => $averageSums2[$columnName],
                         'foreignOfficeId' => $counter,
                     ])
                     ->update();
     
                 if (!$updated) {
                     return $this->failServerError('Failed to update data in rates table.');
                 }
             } else {
                 // Data doesn't exist, perform insert
                 $data = [
                     'month' => $month,
                     'year' => $year,
                     'offices' => $formattedColumnName,
                     'total' => $totalPercentage,
                     'percentage_60' => $averageSums[$columnName],
                     'percentage_40' => $averageSums2[$columnName],
                     'level' => 'puertop_cps',
                     'foreignOfficeId' => $counter,
                 ];
     
                 // Perform insert
                 $inserted = $ratingModel->insert($data);
     
                 if (!$inserted) {
                     return $this->failServerError('Failed to insert data into rates table.');
                 }
             }
     
             $counter++;
         }
         // ---- END: Column update/insertion logic ----

         // Initialize the $totalPercentages array
         $totalPercentages = [];

         // Calculate total percentages for each column
         foreach ($mimaropaColumns as $column) {
             $columnName = $column['Field'];
 
             // Skip irrelevant columns
             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                 continue;
             }
 
             $totalPercentages[$columnName] = $averageSums[$columnName] + $averageSums2[$columnName]; // Sum operational and administrative percentages
         }
 
       
         arsort($totalPercentages);
         $ranks = [];
         $rank = 1;
         foreach ($totalPercentages as $columnName => $percentage) {
             $ranks[$columnName] = $rank++;
         }
     
         // Load mPDF library
         $mpdf = new \Mpdf\Mpdf();
     
         // Use local file path for the image
         $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/logo.png';
         if (file_exists($imagePath)) {
             $imageData = base64_encode(file_get_contents($imagePath));
             $imageSrc = 'data:image/png;base64,' . $imageData; // Adjust MIME type if necessary
         } else {
             die('Image not found.');
         }
     
         // Set header and footer HTML content
         $header = '';
         $footer = '<h5 style="text-align: center; font-style: italic;">PRO MIMAROPA EUPPER SYSTEM</h5>';
     
         // Set header and footer
         $mpdf->SetHTMLHeader($header);
         $mpdf->SetHTMLFooter($footer);
     
         $html = '';
         $html .= '<img src="' . $imageSrc . '" alt="Logo" style="height:120px;position:absolute;left:0;top:0;">';
     
         $html .= '<div style="display:flex;flex-direction:column;align-items:center;position:absolute;left:31%;top:6%;transform:translateX(-31%);">';
         $html .= '<h3 style="text-align: center;">PRO MIMAROPA</h3>';
         $html .= '<h3 style="text-align: center;">Unit Performance Evaluation Ratings</h3>';
         $html .= '<h4 style="text-align: center;">(' . $month . ' ' . $year . ')</h4>';
         $html .= '<h5 style="text-align: center;">PROVINCIAL/CITY POLICE OFFICES</h5>';
         $html .= '</div>';
     
         // Add a table
         $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:80px;">';
     
         $html .= '<tr>';
         $html .= '<th rowspan="3">OFFICE/UNIT</th>';
         $html .= '<th colspan="' . $operationalOfficeCount . '">OPERATIONAL (60%)</th>';
         $html .= '<th rowspan="3">60%</th>';
         $html .= '<th colspan="' . $administrativeOfficeCount . '">ADMINISTRATIVE (40%)</th>';
         $html .= '<th rowspan="3">40%</th>';
         $html .= '<th rowspan="2">Total Percentages Rating</th>';
         $html .= '<th rowspan="3">Rank</th>';
         $html .= '</tr>';
     
         $html .= '<tr>';
         foreach ($operationalOffice as $office) {
             $html .= '<th>' . $office['office'] . '</th>';
         }
         foreach ($administrativeOffice as $office) {
             $html .= '<th>' . $office['office'] . '</th>';
         }
         $html .= '</tr>';
 
         $html .= '<tr>';
         // $operationalRange = [167, 166, 167, 100];
         // $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
         $operationalMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Operational Office'")->getResultArray();
         $administrativeMaxRate = $db->query("SELECT maxRate FROM tbl_users WHERE officeType = 'Administrative Office'")->getResultArray();
         foreach ($operationalMaxRate as $office) {
             $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
         }
         
         foreach ($administrativeMaxRate as $office) {
             $html .= '<th>' . number_format($office['maxRate'], 0) . '</th>'; // number_format to format without decimals
         }
 
         $html .= '<th>100%</th>'; 
         
         $html .= '</tr>';
     
         // Add table rows for operational and administrative data based on sorted total percentages
         foreach ($totalPercentages as $columnName => $totalPercentage) {
             // Get formatted column name and skip irrelevant columns
             if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                 continue;
             }
             
             $formattedColumnName = str_replace('_', ' ', $columnName); // Format column name for display
     
             // Start the row
             $html .= '<tr>';
             $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>'; // Column name (office/unit)
     
             // Add operational office data for the current column
             foreach ($userRate1 as $rate) {
                 $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
             }
     
             // Add percentage_60 for operational office
             $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
     
             // Add administrative office data for the current column
             foreach ($userRate2 as $rate) {
                 $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
             }
     
             // Add percentage_40 for administrative office
             $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
     
             // Add the total percentage (sum of operational and administrative percentages)
             $sum = $averageSums[$columnName] + $averageSums2[$columnName];
             $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
     
             // Add the rank for the current column
             $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
     
             // Close the row
             $html .= '</tr>';
         }
     
         $html .= '</table>';

        $officerModel = new OfficerModel();
        $data = $officerModel->findAll();

        $html .= '<table style="width: 100%; border-collapse: collapse; padding: 10px; margin-top: 5rem; font-size:8px;">';
       
        $html .= '<tr>';
        $html .= '<td style="padding-bottom: 5rem;">Recommended by:</td>';
        $html .= '<td style="padding-bottom: 5rem;">Noted by:</td>';
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<th style="padding: 5px; text-align: left;">' . $datas['name'] . '</th>';
        }
        $html .= '</tr>';

        $html .= '<tr>';
        foreach ($data as $datas) {
            $html .= '<td style="padding: 5px; text-align: left;">' . $datas['office'] . '</td>';
        }
        $html .= '</tr>';


        $html .= '</table>';

        // Add content to PDF
        $mpdf->WriteHTML($html);

        // Get the PDF content as a string
        $pdfContent = $mpdf->Output('', 'S'); // 'S' returns the PDF as a string

        // Send appropriate headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="output.pdf"');
        header('Content-Length: ' . strlen($pdfContent));

        // Output the PDF content
        echo $pdfContent;
    }
}