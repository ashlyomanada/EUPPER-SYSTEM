<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Mpdf\Mpdf;

class PdfController extends ResourceController
{

    
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
                ->where('level', 'PPO')
                ->findAll();
    
            if ($existingData) {
                // Data exists, perform update
                $updated = $ratingModel
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('offices', $formattedColumnName)
                    ->where('level', 'PPO')
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
                    'level' => 'PPO',
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
        $html .= '<th rowspan="3">Offices</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">Operational (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">Administrative (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="3">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Ranking</th>';
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
        $operationalRange = [167, 166, 167, 100];
        $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        foreach ($operationalRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        foreach ($administrativeRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        $html .= '</tr>';

        // Calculate total percentages and ranks outside of the loop
        $totalPercentages = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
            $totalPercentages[$columnName] = $totalPercentage;
        }

        // Sort total percentages array and assign ranks
        arsort($totalPercentages); // Sort in descending order to get the highest first
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }

        // Add table rows for operational data
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>';
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
            $html .= '</tr>';
        }

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
                ->where('level', 'RMFB')
                ->findAll();
    
            if ($existingData) {
                // Data exists, perform update
                $updated = $ratingModel
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('offices', $formattedColumnName)
                    ->where('level', 'RMFB')
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
                    'level' => 'RMFB',
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
        $html .= '<h5 style="text-align: center;">REGIONAL MOBILE FORCE BATTALION</h5>';
        $html .= '</div>';
        
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5" style="margin-top:80px;">';

        $html .= '<tr>';
        $html .= '<th rowspan="3">Offices</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">Operational (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">Administrative (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="3">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Ranking</th>';
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
        $operationalRange = [167, 166, 167, 100];
        $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        foreach ($operationalRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        foreach ($administrativeRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        $html .= '</tr>';

        // Calculate total percentages and ranks outside of the loop
        $totalPercentages = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
            $totalPercentages[$columnName] = $totalPercentage;
        }

        // Sort total percentages array and assign ranks
        arsort($totalPercentages); // Sort in descending order to get the highest first
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }

        // Add table rows for operational data
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>';
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
            $html .= '</tr>';
        }

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
                ->where('level', 'Occidental')
                ->findAll();
    
            if ($existingData) {
                // Data exists, perform update
                $updated = $ratingModel
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('offices', $formattedColumnName)
                    ->where('level', 'Occidental')
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
                    'level' => 'Occidental',
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
        $html .= '<th rowspan="3">Offices</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">Operational (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">Administrative (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="3">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Ranking</th>';
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
        $operationalRange = [167, 166, 167, 100];
        $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        foreach ($operationalRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        foreach ($administrativeRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        $html .= '</tr>';

        // Calculate total percentages and ranks outside of the loop
        $totalPercentages = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
            $totalPercentages[$columnName] = $totalPercentage;
        }

        // Sort total percentages array and assign ranks
        arsort($totalPercentages); // Sort in descending order to get the highest first
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }

        // Add table rows for operational data
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>';
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
            $html .= '</tr>';
        }

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
                 ->where('level', 'Oriental')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'Oriental')
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
                     'level' => 'Oriental',
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
        $html .= '<th rowspan="3">Offices</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">Operational (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">Administrative (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="3">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Ranking</th>';
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
        $operationalRange = [167, 166, 167, 100];
        $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        foreach ($operationalRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        foreach ($administrativeRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        $html .= '</tr>';

        // Calculate total percentages and ranks outside of the loop
        $totalPercentages = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
            $totalPercentages[$columnName] = $totalPercentage;
        }

        // Sort total percentages array and assign ranks
        arsort($totalPercentages); // Sort in descending order to get the highest first
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }

        // Add table rows for operational data
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>';
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
            $html .= '</tr>';
        }

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
                 ->where('level', 'Marinduque')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'Marinduque')
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
                     'level' => 'Marinduque',
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
        $html .= '<th rowspan="3">Offices</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">Operational (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">Administrative (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="3">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Ranking</th>';
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
        $operationalRange = [167, 166, 167, 100];
        $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        foreach ($operationalRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        foreach ($administrativeRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        $html .= '</tr>';

        // Calculate total percentages and ranks outside of the loop
        $totalPercentages = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
            $totalPercentages[$columnName] = $totalPercentage;
        }

        // Sort total percentages array and assign ranks
        arsort($totalPercentages); // Sort in descending order to get the highest first
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }

        // Add table rows for operational data
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>';
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
            $html .= '</tr>';
        }

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
                 ->where('level', 'Romblon')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'Romblon')
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
                     'level' => 'Romblon',
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
        $html .= '<th rowspan="3">Offices</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">Operational (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">Administrative (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="3">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Ranking</th>';
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
        $operationalRange = [167, 166, 167, 100];
        $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        foreach ($operationalRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        foreach ($administrativeRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        $html .= '</tr>';

        // Calculate total percentages and ranks outside of the loop
        $totalPercentages = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
            $totalPercentages[$columnName] = $totalPercentage;
        }

        // Sort total percentages array and assign ranks
        arsort($totalPercentages); // Sort in descending order to get the highest first
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }

        // Add table rows for operational data
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>';
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
            $html .= '</tr>';
        }

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
                 ->where('level', 'Palawan')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'Palawan')
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
                     'level' => 'Palawan',
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
        $html .= '<th rowspan="3">Offices</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">Operational (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">Administrative (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="3">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Ranking</th>';
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
        $operationalRange = [167, 166, 167, 100];
        $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        foreach ($operationalRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        foreach ($administrativeRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        $html .= '</tr>';

        // Calculate total percentages and ranks outside of the loop
        $totalPercentages = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
            $totalPercentages[$columnName] = $totalPercentage;
        }

        // Sort total percentages array and assign ranks
        arsort($totalPercentages); // Sort in descending order to get the highest first
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }

        // Add table rows for operational data
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>';
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
            $html .= '</tr>';
        }

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
                 ->where('level', 'Puerto')
                 ->findAll();
     
             if ($existingData) {
                 // Data exists, perform update
                 $updated = $ratingModel
                     ->where('month', $month)
                     ->where('year', $year)
                     ->where('offices', $formattedColumnName)
                     ->where('level', 'Puerto')
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
                     'level' => 'Puerto',
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
        $html .= '<th rowspan="3">Offices</th>';
        $html .= '<th colspan="' . $operationalOfficeCount . '">Operational (60%)</th>';
        $html .= '<th rowspan="3">60%</th>';
        $html .= '<th colspan="' . $administrativeOfficeCount . '">Administrative (40%)</th>';
        $html .= '<th rowspan="3">40%</th>';
        $html .= '<th rowspan="3">Total Percentages Rating</th>';
        $html .= '<th rowspan="3">Ranking</th>';
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
        $operationalRange = [167, 166, 167, 100];
        $administrativeRange = [80, 80, 80, 80, 35, 25, 20];
        foreach ($operationalRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        foreach ($administrativeRange as $office) {
            $html .= '<th>' . $office . '</th>';
        }
        $html .= '</tr>';

        // Calculate total percentages and ranks outside of the loop
        $totalPercentages = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
            $totalPercentages[$columnName] = $totalPercentage;
        }

        // Sort total percentages array and assign ranks
        arsort($totalPercentages); // Sort in descending order to get the highest first
        $ranks = [];
        $rank = 1;
        foreach ($totalPercentages as $columnName => $percentage) {
            $ranks[$columnName] = $rank++;
        }

        // Add table rows for operational data
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $formattedColumnName = str_replace('_', ' ', $columnName);
            $html .= '<tr>';
            $html .= '<td style="text-align: center;">' . $formattedColumnName . '</td>';
            foreach ($userRate1 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums[$columnName], 2) . '</td>';
            foreach ($userRate2 as $rate) {
                $html .= '<td style="text-align: center;">' . $rate[$columnName] . '</td>';
            }
            $html .= '<td style="text-align: center;">' . number_format($averageSums2[$columnName], 2) . '</td>';
            $sum = $averageSums[$columnName] + $averageSums2[$columnName];
            $html .= '<td style="text-align: center;">' . number_format($sum, 2) . '</td>';
            $html .= '<td style="text-align: center;">' . $ranks[$columnName] . '</td>'; // Display the rank
            $html .= '</tr>';
        }

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