<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelController extends ResourceController
{
   
    public function generatePPOOffice()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;

        // Load the database connection
        $db = \Config\Database::connect();

        // Fetch column names from the database table
        $table = 'ppo_cpo';
        $query = $db->query("DESCRIBE $table");

        // Check if the query was successful
        if (!$query) {
            // Return error message if query fails
            return $this->failServerError('Failed to fetch column names.');
        }

        $columns = $query->getResultArray();
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

        // Check if columns were fetched successfully
        if (empty($columns)) {
            // Return error message if no columns are found
            return $this->failNotFound('No columns found in the database table.');
        }

        // Fetch results based on dynamic office names
        $queryDistinctOffices = "
            SELECT DISTINCT office
            FROM tbl_users
            LIMIT 4
        ";
        $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
        $officeNames = array_column($distinctOffices, 'office');
        $officeNamesString = "'" . implode("', '", $officeNames) . "'";

         // Fetch results based on dynamic office names
        $queryDistinctOffices2 = "
        SELECT DISTINCT office
        FROM tbl_users
        LIMIT 7 OFFSET 4
        ";
        $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
        $officeNames2 = array_column($distinctOffices2, 'office');
        $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


        // Build the SQL query with placeholders for month and year
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
        // Use the database connection to execute the query with prepared statements
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] +=  $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] +=  $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60;    // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40;    // Multiply by 60%
            $averageSums2[$avgCol2] = $result2;
        }

        // Extract column names
        $columnNames = array_column($columns, 'Field');

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set PRO MIMAROPA in the first added row and center it
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'UNIT PERFORMANCE EVALUATION RATING');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3','('.$month.' '.$year.')');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:P4');
        $sheet->setCellValue('A4', 'PROVINCIAL/CITY POLICE OFFICES');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      // Set the header for 'Office' and center align it
        $sheet->mergeCells('A7:A8');
        $sheet->setCellValue('A7', 'OFFICE/UNIT');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Adjust the width of column A
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed


        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B6', 'Operational (60%)');
        $style = $sheet->getStyle('B6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G6:M6');
        $sheet->setCellValue('G6', 'Administrative (40%)');
        $style = $sheet->getStyle('G6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('O7:O8');
        $sheet->setCellValue('O7', 'Total Percentage Ratings');
        $sheet->getStyle('O7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('O')->setWidth(20); 
        $sheet->getStyle('O7')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('P7:P8');
        $sheet->setCellValue('P7', 'Ranking');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('P')->setWidth(10); 

        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:P100'); // Adjust the range as needed

        // Set scaling to fit all columns on one page
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        // Insert page break after row 50
        $spreadsheet->getActiveSheet()->setBreak('A50', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        $additionalOperational = [167, 166, 167, 100];
        $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }
            
            $colIndexAd = 2;
            foreach ($additionalOperational as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexAd,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexAd,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexAd++;
            }
            
            // Set the header for "60%" column and center align it
            $sheet->mergeCells('F7:F8');
            $sheet->setCellValueByColumnAndRow($colIndex, 7, '60%');
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex2++;
            }
            
            $colIndexOp = 7;
            foreach ($additionalAdministrative as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexOp,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexOp,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexOp++;
            }
            

            // Add headers for "40%" columns
            
           // Set the header for '40%' and center align it
           $sheet->mergeCells('N7:N8');
            $sheet->setCellValueByColumnAndRow($colIndex2, 7, '40%');
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Set headers for other data dynamically based on column names
            $rowIndex = 9;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }

                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);

                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
                
                // Center align the header
                $sheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowIndex++;
            }

            

            // Execute the query with prepared statements to fetch user rates
            $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
            $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

            // If user rates are found, proceed to populate Excel report
            if (!empty($userRates1) && !empty($userRates2)) {
                // Populate data
                foreach ($userRates1 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // Apply number formatting to "60%" column
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        // Apply number formatting to "40%" column
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

                        $rowIndex++;
                    }

                }

                                // Step 2: Sort the totalPercentage array by percentage values in descending order
                usort($totalPercentageArray, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });

                // Initialize rank
                $rank = 1;
                $prevPercentage = null; // To track if the current percentage is equal to the previous one

                foreach ($totalPercentageArray as $percentageData) {
                    $totalPercentage = $percentageData['percentage'];
                    $rowIndex = $percentageData['rowIndex'];

                    // Check if the current percentage is different from the previous one
                    if ($totalPercentage != $prevPercentage) {
                        // If different, update the rank and store the current percentage as previous
                        $prevPercentage = $totalPercentage;
                        // Set the rank and center the text
                        $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank);
                        $sheet->getStyleByColumnAndRow(16, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $rank++; // Increment the rank only when the percentage changes
                    }
                }


                $counter = 1;
                foreach ($columnNames as $columnName) {
                    // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                    if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                        continue;
                    }
                
                    $formattedColumnName = str_replace('_', ' ', $columnName);
                    $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                
                    $ratingModel = new \App\Models\RatingModel();
                
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
                            ->set(['total' => $totalPercentage, 
                                   'percentage_60' => $averageSums[$columnName], 
                                   'percentage_40' => $averageSums2[$columnName],
                                   'foreignOfficeId' => $counter,])
                            ->update();
                
                        if (!$updated) {
                            // Handle update failure if needed
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
                            // Handle insertion failure if needed
                            return $this->failServerError('Failed to insert data into rates table.');
                        }
                    }
                    $counter++;
                }
                
                

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $rowIndex++;
                    }
                }

                // Set the header and filename for the download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
                header('Cache-Control: max-age=0');

                // Save Excel 2007 file
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');

                // Exit script to prevent any further output
                exit;
            } else {
                // If no user rates are found, return failure message
                return $this->failNotFound('User rates not found');
            }
        } else {
            // If no user offices are found, return failure message
            return $this->failNotFound('User offices not found');
        }

    }

    // public function generateRMFBOffice()
    // {
    //     // Get JSON data from the request
    //     $json = $this->request->getJSON();
    //     $month = $json->month;
    //     $year = $json->year;

    //     // Load the database connection
    //     $db = \Config\Database::connect();

    //     // Fetch column names from the database table
    //     $table = 'rmfb_tbl';
    //     $query = $db->query("DESCRIBE $table");

    //     // Check if the query was successful
    //     if (!$query) {
    //         // Return error message if query fails
    //         return $this->failServerError('Failed to fetch column names.');
    //     }

    //     $columns = $query->getResultArray();
    //     $mimaropaColumns = $query->getResultArray();

    //     $sums = [];
    //     $sums2 = [];
    //     foreach ($mimaropaColumns as $column) {
    //         $columnName = $column['Field'];

    //         if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
    //             continue;
    //         }
    //         $sums[$columnName] = 0;
    //         $sums2[$columnName] = 0;
            
    //     }

    //     // Check if columns were fetched successfully
    //     if (empty($columns)) {
    //         // Return error message if no columns are found
    //         return $this->failNotFound('No columns found in the database table.');
    //     }

    //     // Fetch results based on dynamic office names
    //     $queryDistinctOffices = "
    //         SELECT DISTINCT office
    //         FROM tbl_users
    //         LIMIT 4
    //     ";
    //     $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
    //     $officeNames = array_column($distinctOffices, 'office');
    //     $officeNamesString = "'" . implode("', '", $officeNames) . "'";

    //      // Fetch results based on dynamic office names
    //     $queryDistinctOffices2 = "
    //     SELECT DISTINCT office
    //     FROM tbl_users
    //     LIMIT 7 OFFSET 4
    //     ";
    //     $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
    //     $officeNames2 = array_column($distinctOffices2, 'office');
    //     $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


    //     // Build the SQL query with placeholders for month and year
    //     $queryOperation = "
    //     SELECT rmfb_tbl.*, tbl_users.office
    //     FROM rmfb_tbl
    //     INNER JOIN tbl_users ON rmfb_tbl.userid = tbl_users.user_id
    //     WHERE rmfb_tbl.month = ? AND rmfb_tbl.year = ?
    //     AND tbl_users.office IN ($officeNamesString)
    //     ";

       
    //     $queryAdministrative = "
    //     SELECT rmfb_tbl.*, tbl_users.office
    //     FROM rmfb_tbl
    //     INNER JOIN tbl_users ON rmfb_tbl.userid = tbl_users.user_id
    //     WHERE rmfb_tbl.month = ? AND rmfb_tbl.year = ?
    //     AND tbl_users.office IN ($officeNamesString2)
    //     ";
    //     // Use the database connection to execute the query with prepared statements
    //     $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //     $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //     // Compute sums for each column dynamically
    //     foreach ($results1 as $result1) {
    //         foreach ($sums as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result1)) {
    //                 $sums[$columnName] +=  $result1[$columnName];
    //             }
    //         }
    //     }

    //     foreach ($results2 as $result2) {
    //         foreach ($sums2 as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result2)) {
    //                 $sums2[$columnName] +=  $result2[$columnName];
    //             }
    //         }
    //     }

    //     // Calculate average of sums and multiply by 60%
    //     $averageSums = [];
    //     foreach ($sums as $avgCol => $sum) {
    //         $average = $sum / 600; // Calculate average
    //         $result1 = $average * 60;    // Multiply by 60%
    //         $averageSums[$avgCol] = $result1;
    //     }

    //     $averageSums2 = [];
    //     foreach ($sums2 as $avgCol2 => $sum) {
    //         $average2 = $sum / 400; // Calculate average
    //         $result2 = $average2 * 40;    // Multiply by 60%
    //         $averageSums2[$avgCol2] = $result2;
    //     }

    //     // Extract column names
    //     $columnNames = array_column($columns, 'Field');

    //     // Create new Spreadsheet object
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // Set PRO MIMAROPA in the first added row and center it
    //     $sheet->mergeCells('A1:O1');
    //     $sheet->setCellValue('A1', 'PRO MIMAROPA');
    //     $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Unit Performance Evaluation Rating in the second added row and center it
    //     $sheet->mergeCells('A2:O2');
    //     $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
    //     $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set the header for office and adjust column width
    //     $sheet->setCellValue('A4', 'Office');
    //     $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

    //     // Set Operational (60%) in the third row and center it
    //     $sheet->mergeCells('B3:E3');
    //     $sheet->setCellValue('B3', 'Operational (60%)');
    //     $style = $sheet->getStyle('B3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Administrative (40%) in the third row and center it
    //     $sheet->mergeCells('G3:M3');
    //     $sheet->setCellValue('G3', 'Administrative (40%)');
    //     $style = $sheet->getStyle('G3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     $sheet->setCellValue('O4', 'Total Percentage Ratings');
    //     $sheet->getColumnDimension('O')->setWidth(30); 
        

    //     $sheet->setCellValue('P4', 'Ranking');
    //     $sheet->getColumnDimension('P')->setWidth(20); 

    //     // Use the database connection to execute the query to fetch user offices
        
    //     $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
    //     $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
    //     $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
    //     $userOffices =$usersAllOffices->getResultArray();
    //     $userOffices1 =$operationalOffice->getResultArray();
    //     $userOffices2 =$administrativeOffice->getResultArray();

    //     // If user offices are found, proceed to populate Excel report
    //     $additionalOperational = [167, 166, 167, 100];
    //     $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

    //     // If user offices are found, proceed to populate Excel report
    //     if (!empty($userOffices1 && $userOffices2)) {
    //         // Populate office names as column headers
    //         $colIndex = 2;
    //         foreach ($userOffices1 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
    //             $colIndex++;
    //         }
    //         $colIndexAd = 2;
    //         foreach($additionalOperational as $office){
    //             $sheet->setCellValueByColumnAndRow($colIndexAd, 5, $office);
    //             $colIndexAd++;
    //         }

    //         $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

    //         $colIndex2 = 7;
    //         foreach ($userOffices2 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
    //             $colIndex2++;
    //         }

    //         $colIndexOp = 7;
    //         foreach($additionalAdministrative as $office){
    //             $sheet->setCellValueByColumnAndRow($colIndexOp, 5, $office);
    //             $colIndexOp++;
    //         }

    //         // Add headers for "40%" columns
            
    //         $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
    //        // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

    //         // Set headers for other data dynamically based on column names
    //         $rowIndex = 6;
    //         foreach ($columnNames as $columnName) {
    //             // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                 continue;
    //             }
            
    //             // Replace underscores with spaces in the column name
    //             $formattedColumnName = str_replace('_', ' ', $columnName);
            
    //             // Set the formatted column name in the spreadsheet
    //             $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
    //             $rowIndex++;
    //         }
            

    //         // Execute the query with prepared statements to fetch user rates
    //         $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //         $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //         // If user rates are found, proceed to populate Excel report
    //         if (!empty($userRates1) && !empty($userRates2)) {
    //             // Populate data
    //             foreach ($userRates1 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
    //                 // Populate rate data for each office
    //                 $rowIndex = 6; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }

    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
    //                     $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
    //                     $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data

    //                     // Apply number formatting to "60%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     // Apply number formatting to "40%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
    //                     $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    //                     $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
    //                     $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
    //                     $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

    //                     $rowIndex++;
    //                 }

    //             }

    //                             // Step 2: Sort the totalPercentage array by percentage values in descending order
    //             usort($totalPercentageArray, function($a, $b) {
    //                 return $b['percentage'] <=> $a['percentage'];
    //             });

    //             // Initialize rank
    //             $rank = 1;
    //             $prevPercentage = null; // To track if the current percentage is equal to the previous one

    //             foreach ($totalPercentageArray as $percentageData) {
    //                 $totalPercentage = $percentageData['percentage'];
    //                 $rowIndex = $percentageData['rowIndex'];

    //                 // Check if the current percentage is different from the previous one
    //                 if ($totalPercentage != $prevPercentage) {
    //                     // If different, update the rank and store the current percentage as previous
    //                     $prevPercentage = $totalPercentage;
    //                     $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank); // Place the rank beside the total percentage
    //                     $rank++; // Increment the rank only when the percentage changes
    //                 }
    //             }



    //             foreach ($columnNames as $columnName) {
    //                 // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                 if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                     continue;
    //                 }
                
    //                 $formattedColumnName = str_replace('_', ' ', $columnName);
    //                 $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                    
    //                 $ratingModel = new \App\Models\RatingModel();
                
    //                 // Check if data already exists for the given month and year
    //                 $existingData = $ratingModel
    //                     ->where('month', $month)
    //                     ->where('year', $year)
    //                     ->where('offices', $formattedColumnName)
    //                     ->where('level', 'RMFB')
    //                     ->findAll();

                
    //                 if ($existingData) {
    //                     // Data exists, perform update
    //                     $updated = $ratingModel
    //                         ->where('month', $month)
    //                         ->where('year', $year)
    //                         ->where('offices', $formattedColumnName)
    //                         ->where('level', 'RMFB')
    //                         ->set(['total' => $totalPercentage])
    //                         ->update();
                
    //                     if (!$updated) {
    //                         // Handle update failure if needed
    //                         return $this->failServerError('Failed to update data in rates table.');
    //                     }
    //                 } else {
    //                     // Data doesn't exist, perform insert
    //                     $data = [
    //                         'month' => $month,
    //                         'year' => $year,
    //                         'offices' => $formattedColumnName,
    //                         'total' => $totalPercentage,
    //                         'level' => 'RMFB',
    //                     ];
                
    //                     // Perform insert
    //                     $inserted = $ratingModel->insert($data);
                
    //                     if (!$inserted) {
    //                         // Handle insertion failure if needed
    //                         return $this->failServerError('Failed to insert data into rates table.');
    //                     }
    //                 }
    //             }
                

    //             foreach ($userRates2 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
    //                 // Populate rate data for each office
    //                 $rowIndex = 6; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }
    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
    //                     $rowIndex++;
    //                 }
    //             }

    //             // Set the header and filename for the download
    //             header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //             header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
    //             header('Cache-Control: max-age=0');

    //             // Save Excel 2007 file
    //             $writer = new Xlsx($spreadsheet);
    //             $writer->save('php://output');

    //             // Exit script to prevent any further output
    //             exit;
    //         } else {
    //             // If no user rates are found, return failure message
    //             return $this->failNotFound('User rates not found');
    //         }
    //     } else {
    //         // If no user offices are found, return failure message
    //         return $this->failNotFound('User offices not found');
    //     }

    // }

    public function generateRMFBOffice()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;

        // Load the database connection
        $db = \Config\Database::connect();

        // Fetch column names from the database table
        $table = 'rmfb_tbl';
        $query = $db->query("DESCRIBE $table");

        // Check if the query was successful
        if (!$query) {
            // Return error message if query fails
            return $this->failServerError('Failed to fetch column names.');
        }

        $columns = $query->getResultArray();
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

        // Check if columns were fetched successfully
        if (empty($columns)) {
            // Return error message if no columns are found
            return $this->failNotFound('No columns found in the database table.');
        }

        // Fetch results based on dynamic office names
        $queryDistinctOffices = "
            SELECT DISTINCT office
            FROM tbl_users
            LIMIT 4
        ";
        $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
        $officeNames = array_column($distinctOffices, 'office');
        $officeNamesString = "'" . implode("', '", $officeNames) . "'";

         // Fetch results based on dynamic office names
        $queryDistinctOffices2 = "
        SELECT DISTINCT office
        FROM tbl_users
        LIMIT 7 OFFSET 4
        ";
        $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
        $officeNames2 = array_column($distinctOffices2, 'office');
        $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


        // Build the SQL query with placeholders for month and year
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
        // Use the database connection to execute the query with prepared statements
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] +=  $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] +=  $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60;    // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40;    // Multiply by 60%
            $averageSums2[$avgCol2] = $result2;
        }

        // Extract column names
        $columnNames = array_column($columns, 'Field');

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set PRO MIMAROPA in the first added row and center it
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'UNIT PERFORMANCE EVALUATION RATING');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3','('.$month.' '.$year.')');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:P4');
        $sheet->setCellValue('A4', 'REGIONAL MOBILE FORCE BATTALION');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      // Set the header for 'Office' and center align it
        $sheet->mergeCells('A7:A8');
        $sheet->setCellValue('A7', 'OFFICE/UNIT');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Adjust the width of column A
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed


        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B6', 'Operational (60%)');
        $style = $sheet->getStyle('B6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G6:M6');
        $sheet->setCellValue('G6', 'Administrative (40%)');
        $style = $sheet->getStyle('G6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('O7:O8');
        $sheet->setCellValue('O7', 'Total Percentage Ratings');
        $sheet->getStyle('O7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('O')->setWidth(20); 
        $sheet->getStyle('O7')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('P7:P8');
        $sheet->setCellValue('P7', 'Ranking');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('P')->setWidth(10); 

        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:P100'); // Adjust the range as needed

        // Set scaling to fit all columns on one page
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        // Insert page break after row 50
        $spreadsheet->getActiveSheet()->setBreak('A50', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        $additionalOperational = [167, 166, 167, 100];
        $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }
            
            $colIndexAd = 2;
            foreach ($additionalOperational as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexAd,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexAd,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexAd++;
            }
            
            // Set the header for "60%" column and center align it
            $sheet->mergeCells('F7:F8');
            $sheet->setCellValueByColumnAndRow($colIndex, 7, '60%');
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex2++;
            }
            
            $colIndexOp = 7;
            foreach ($additionalAdministrative as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexOp,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexOp,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexOp++;
            }
            

            // Add headers for "40%" columns
            
           // Set the header for '40%' and center align it
           $sheet->mergeCells('N7:N8');
            $sheet->setCellValueByColumnAndRow($colIndex2, 7, '40%');
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Set headers for other data dynamically based on column names
            $rowIndex = 9;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }

                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);

                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
                
                // Center align the header
                $sheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowIndex++;
            }

            

            // Execute the query with prepared statements to fetch user rates
            $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
            $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

            // If user rates are found, proceed to populate Excel report
            if (!empty($userRates1) && !empty($userRates2)) {
                // Populate data
                foreach ($userRates1 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // Apply number formatting to "60%" column
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        // Apply number formatting to "40%" column
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

                        $rowIndex++;
                    }

                }

                                // Step 2: Sort the totalPercentage array by percentage values in descending order
                usort($totalPercentageArray, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });

                // Initialize rank
                $rank = 1;
                $prevPercentage = null; // To track if the current percentage is equal to the previous one

                foreach ($totalPercentageArray as $percentageData) {
                    $totalPercentage = $percentageData['percentage'];
                    $rowIndex = $percentageData['rowIndex'];

                    // Check if the current percentage is different from the previous one
                    if ($totalPercentage != $prevPercentage) {
                        // If different, update the rank and store the current percentage as previous
                        $prevPercentage = $totalPercentage;
                        // Set the rank and center the text
                        $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank);
                        $sheet->getStyleByColumnAndRow(16, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $rank++; // Increment the rank only when the percentage changes
                    }
                }



                $counter = 1;
                foreach ($columnNames as $columnName) {
                    // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                    if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                        continue;
                    }
                
                    $formattedColumnName = str_replace('_', ' ', $columnName);
                    $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                
                    $ratingModel = new \App\Models\RatingModel();
                
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
                            ->set(['total' => $totalPercentage, 
                                   'percentage_60' => $averageSums[$columnName], 
                                   'percentage_40' => $averageSums2[$columnName],
                                   'foreignOfficeId' => $counter,])
                            ->update();
                
                        if (!$updated) {
                            // Handle update failure if needed
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
                            // Handle insertion failure if needed
                            return $this->failServerError('Failed to insert data into rates table.');
                        }
                    }
                    $counter++;
                }
                
                

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $rowIndex++;
                    }
                }

                // Set the header and filename for the download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
                header('Cache-Control: max-age=0');

                // Save Excel 2007 file
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');

                // Exit script to prevent any further output
                exit;
            } else {
                // If no user rates are found, return failure message
                return $this->failNotFound('User rates not found');
            }
        } else {
            // If no user offices are found, return failure message
            return $this->failNotFound('User offices not found');
        }

    }


    // public function generateOcciReport()
    // {
    //     // Get JSON data from the request
    //     $json = $this->request->getJSON();
    //     $month = $json->month;
    //     $year = $json->year;

    //     // Load the database connection
    //     $db = \Config\Database::connect();

    //     // Fetch column names from the database table
    //     $table = 'occidental_cps';
    //     $query = $db->query("DESCRIBE $table");

    //     // Check if the query was successful
    //     if (!$query) {
    //         // Return error message if query fails
    //         return $this->failServerError('Failed to fetch column names.');
    //     }

    //     $columns = $query->getResultArray();
    //     $mimaropaColumns = $query->getResultArray();

    //     $sums = [];
    //     $sums2 = [];
    //     foreach ($mimaropaColumns as $column) {
    //         $columnName = $column['Field'];

    //         if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
    //             continue;
    //         }
    //         $sums[$columnName] = 0;
    //         $sums2[$columnName] = 0;
            
    //     }

    //     // Check if columns were fetched successfully
    //     if (empty($columns)) {
    //         // Return error message if no columns are found
    //         return $this->failNotFound('No columns found in the database table.');
    //     }

    //     // Fetch results based on dynamic office names
    //     $queryDistinctOffices = "
    //         SELECT DISTINCT office
    //         FROM tbl_users
    //         LIMIT 4
    //     ";
    //     $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
    //     $officeNames = array_column($distinctOffices, 'office');
    //     $officeNamesString = "'" . implode("', '", $officeNames) . "'";

    //      // Fetch results based on dynamic office names
    //     $queryDistinctOffices2 = "
    //     SELECT DISTINCT office
    //     FROM tbl_users
    //     LIMIT 7 OFFSET 4
    //     ";
    //     $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
    //     $officeNames2 = array_column($distinctOffices2, 'office');
    //     $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


    //     // Build the SQL query with placeholders for month and year
    //     $queryOperation = "
    //     SELECT occidental_cps.*, tbl_users.office
    //     FROM occidental_cps
    //     INNER JOIN tbl_users ON occidental_cps.userid = tbl_users.user_id
    //     WHERE occidental_cps.month = ? AND occidental_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString)
    //     ";

       
    //     $queryAdministrative = "
    //     SELECT occidental_cps.*, tbl_users.office
    //     FROM occidental_cps
    //     INNER JOIN tbl_users ON occidental_cps.userid = tbl_users.user_id
    //     WHERE occidental_cps.month = ? AND occidental_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString2)
    //     ";
    //     // Use the database connection to execute the query with prepared statements
    //     $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //     $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //     // Compute sums for each column dynamically
    //     foreach ($results1 as $result1) {
    //         foreach ($sums as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result1)) {
    //                 $sums[$columnName] +=  $result1[$columnName];
    //             }
    //         }
    //     }

    //     foreach ($results2 as $result2) {
    //         foreach ($sums2 as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result2)) {
    //                 $sums2[$columnName] +=  $result2[$columnName];
    //             }
    //         }
    //     }

    //     // Calculate average of sums and multiply by 60%
    //     $averageSums = [];
    //     foreach ($sums as $avgCol => $sum) {
    //         $average = $sum / 600; // Calculate average
    //         $result1 = $average * 60;    // Multiply by 60%
    //         $averageSums[$avgCol] = $result1;
    //     }

    //     $averageSums2 = [];
    //     foreach ($sums2 as $avgCol2 => $sum) {
    //         $average2 = $sum / 400; // Calculate average
    //         $result2 = $average2 * 40;    // Multiply by 60%
    //         $averageSums2[$avgCol2] = $result2;
    //     }

    //     // Extract column names
    //     $columnNames = array_column($columns, 'Field');

    //     // Create new Spreadsheet object
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // Set PRO MIMAROPA in the first added row and center it
    //     $sheet->mergeCells('A1:O1');
    //     $sheet->setCellValue('A1', 'PRO MIMAROPA');
    //     $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Unit Performance Evaluation Rating in the second added row and center it
    //     $sheet->mergeCells('A2:O2');
    //     $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
    //     $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set the header for office and adjust column width
    //     $sheet->setCellValue('A4', 'Office');
    //     $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

    //     // Set Operational (60%) in the third row and center it
    //     $sheet->mergeCells('B3:E3');
    //     $sheet->setCellValue('B3', 'Operational (60%)');
    //     $style = $sheet->getStyle('B3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Administrative (40%) in the third row and center it
    //     $sheet->mergeCells('G3:M3');
    //     $sheet->setCellValue('G3', 'Administrative (40%)');
    //     $style = $sheet->getStyle('G3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     $sheet->setCellValue('O4', 'Total Percentage Ratings');
    //     $sheet->getColumnDimension('O')->setWidth(30); 

    //     $sheet->setCellValue('P4', 'Ranking');
    //     $sheet->getColumnDimension('P')->setWidth(20); 

    //     // Use the database connection to execute the query to fetch user offices
        
    //     $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
    //     $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
    //     $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
    //     $userOffices =$usersAllOffices->getResultArray();
    //     $userOffices1 =$operationalOffice->getResultArray();
    //     $userOffices2 =$administrativeOffice->getResultArray();

    //     $additionalOperational = [167, 166, 167, 100];
    //     $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

    //     // If user offices are found, proceed to populate Excel report
    //     if (!empty($userOffices1 && $userOffices2)) {
    //         // Populate office names as column headers
    //         $colIndex = 2;
    //         foreach ($userOffices1 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
    //             $colIndex++;
    //         }
    //         $colIndexAd = 2;
    //         foreach($additionalOperational as $office){
    //             $sheet->setCellValueByColumnAndRow($colIndexAd, 5, $office);
    //             $colIndexAd++;
    //         }

    //         $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

    //         $colIndex2 = 7;
    //         foreach ($userOffices2 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
    //             $colIndex2++;
    //         }

    //         $colIndexOp = 7;
    //         foreach($additionalAdministrative as $office){
    //             $sheet->setCellValueByColumnAndRow($colIndexOp, 5, $office);
    //             $colIndexOp++;
    //         }

    //         // Add headers for "40%" columns
            
    //         $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
    //        // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

    //         // Set headers for other data dynamically based on column names
    //         $rowIndex = 6;
    //         foreach ($columnNames as $columnName) {
    //             // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                 continue;
    //             }
            
    //             // Replace underscores with spaces in the column name
    //             $formattedColumnName = str_replace('_', ' ', $columnName);
            
    //             // Set the formatted column name in the spreadsheet
    //             $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
    //             $rowIndex++;
    //         }
            

    //         // Execute the query with prepared statements to fetch user rates
    //         $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //         $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //         // If user rates are found, proceed to populate Excel report
    //         if (!empty($userRates1) && !empty($userRates2)) {
    //             // Populate data
    //             foreach ($userRates1 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
    //                 // Populate rate data for each office
    //                 $rowIndex = 6; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }

    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
    //                     $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
    //                     $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data

    //                     // Apply number formatting to "60%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     // Apply number formatting to "40%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
    //                     $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    //                     $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
    //                     $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
    //                     $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

    //                     $rowIndex++;
    //                 }

    //             }

    //                             // Step 2: Sort the totalPercentage array by percentage values in descending order
    //             usort($totalPercentageArray, function($a, $b) {
    //                 return $b['percentage'] <=> $a['percentage'];
    //             });

    //             // Initialize rank
    //             $rank = 1;
    //             $prevPercentage = null; // To track if the current percentage is equal to the previous one

    //             foreach ($totalPercentageArray as $percentageData) {
    //                 $totalPercentage = $percentageData['percentage'];
    //                 $rowIndex = $percentageData['rowIndex'];

    //                 // Check if the current percentage is different from the previous one
    //                 if ($totalPercentage != $prevPercentage) {
    //                     // If different, update the rank and store the current percentage as previous
    //                     $prevPercentage = $totalPercentage;
    //                     $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank); // Place the rank beside the total percentage
    //                     $rank++; // Increment the rank only when the percentage changes
    //                 }
    //             }



    //             foreach ($columnNames as $columnName) {
    //                 // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                 if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                     continue;
    //                 }

    //                 $formattedColumnName = str_replace('_', ' ', $columnName);
    //                 $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                    
    //                 $ratingModel = new \App\Models\RatingModel();
    //                 $data = [
    //                     'month' => $month,          // Assuming $month is defined
    //                     'year' => $year,            // Assuming $year is defined
    //                     'offices' => $formattedColumnName,
    //                     'total' => $totalPercentage,
    //                     'level' => 'PPO',
    //                 ];
            
    //                 // Insert data into the rates table using the model
    //                 $inserted = $ratingModel->insert($data);
            
    //                 if (!$inserted) {
    //                     // Handle insertion failure if needed
    //                     return $this->failServerError('Failed to insert data into rates table.');
    //                 }
                   
    //             }

    //             foreach ($userRates2 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
    //                 // Populate rate data for each office
    //                 $rowIndex = 6; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }
    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
    //                     $rowIndex++;
    //                 }
    //             }

    //             // Set the header and filename for the download
    //             header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //             header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
    //             header('Cache-Control: max-age=0');

    //             // Save Excel 2007 file
    //             $writer = new Xlsx($spreadsheet);
    //             $writer->save('php://output');

    //             // Exit script to prevent any further output
    //             exit;
    //         } else {
    //             // If no user rates are found, return failure message
    //             return $this->failNotFound('User rates not found');
    //         }
    //     } else {
    //         // If no user offices are found, return failure message
    //         return $this->failNotFound('User offices not found');
    //     }

    // }

    public function generateOcciReport()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;

        // Load the database connection
        $db = \Config\Database::connect();

        // Fetch column names from the database table
        $table = 'occidental_cps';
        $query = $db->query("DESCRIBE $table");

        // Check if the query was successful
        if (!$query) {
            // Return error message if query fails
            return $this->failServerError('Failed to fetch column names.');
        }

        $columns = $query->getResultArray();
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

        // Check if columns were fetched successfully
        if (empty($columns)) {
            // Return error message if no columns are found
            return $this->failNotFound('No columns found in the database table.');
        }

        // Fetch results based on dynamic office names
        $queryDistinctOffices = "
            SELECT DISTINCT office
            FROM tbl_users
            LIMIT 4
        ";
        $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
        $officeNames = array_column($distinctOffices, 'office');
        $officeNamesString = "'" . implode("', '", $officeNames) . "'";

         // Fetch results based on dynamic office names
        $queryDistinctOffices2 = "
        SELECT DISTINCT office
        FROM tbl_users
        LIMIT 7 OFFSET 4
        ";
        $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
        $officeNames2 = array_column($distinctOffices2, 'office');
        $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


        // Build the SQL query with placeholders for month and year
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
        // Use the database connection to execute the query with prepared statements
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] +=  $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] +=  $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60;    // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40;    // Multiply by 60%
            $averageSums2[$avgCol2] = $result2;
        }

        // Extract column names
        $columnNames = array_column($columns, 'Field');

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set PRO MIMAROPA in the first added row and center it
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'UNIT PERFORMANCE EVALUATION RATING');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3','('.$month.' '.$year.')');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:P4');
        $sheet->setCellValue('A4', 'CITY/MUNICIPAL POLICE STATIONS');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      // Set the header for 'Office' and center align it
        $sheet->mergeCells('A7:A8');
        $sheet->setCellValue('A7', 'OFFICE/UNIT');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Adjust the width of column A
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed


        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B6', 'Operational (60%)');
        $style = $sheet->getStyle('B6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G6:M6');
        $sheet->setCellValue('G6', 'Administrative (40%)');
        $style = $sheet->getStyle('G6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('O7:O8');
        $sheet->setCellValue('O7', 'Total Percentage Ratings');
        $sheet->getStyle('O7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('O')->setWidth(20); 
        $sheet->getStyle('O7')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('P7:P8');
        $sheet->setCellValue('P7', 'Ranking');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('P')->setWidth(10); 

        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:P100'); // Adjust the range as needed

        // Set scaling to fit all columns on one page
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        // Insert page break after row 50
        $spreadsheet->getActiveSheet()->setBreak('A50', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        $additionalOperational = [167, 166, 167, 100];
        $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }
            
            $colIndexAd = 2;
            foreach ($additionalOperational as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexAd,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexAd,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexAd++;
            }
            
            // Set the header for "60%" column and center align it
            $sheet->mergeCells('F7:F8');
            $sheet->setCellValueByColumnAndRow($colIndex, 7, '60%');
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex2++;
            }
            
            $colIndexOp = 7;
            foreach ($additionalAdministrative as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexOp,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexOp,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexOp++;
            }
            

            // Add headers for "40%" columns
            
           // Set the header for '40%' and center align it
           $sheet->mergeCells('N7:N8');
            $sheet->setCellValueByColumnAndRow($colIndex2, 7, '40%');
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Set headers for other data dynamically based on column names
            $rowIndex = 9;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }

                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);

                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
                
                // Center align the header
                $sheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowIndex++;
            }

            

            // Execute the query with prepared statements to fetch user rates
            $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
            $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

            // If user rates are found, proceed to populate Excel report
            if (!empty($userRates1) && !empty($userRates2)) {
                // Populate data
                foreach ($userRates1 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // Apply number formatting to "60%" column
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        // Apply number formatting to "40%" column
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

                        $rowIndex++;
                    }

                }

                                // Step 2: Sort the totalPercentage array by percentage values in descending order
                usort($totalPercentageArray, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });

                // Initialize rank
                $rank = 1;
                $prevPercentage = null; // To track if the current percentage is equal to the previous one

                foreach ($totalPercentageArray as $percentageData) {
                    $totalPercentage = $percentageData['percentage'];
                    $rowIndex = $percentageData['rowIndex'];

                    // Check if the current percentage is different from the previous one
                    if ($totalPercentage != $prevPercentage) {
                        // If different, update the rank and store the current percentage as previous
                        $prevPercentage = $totalPercentage;
                        // Set the rank and center the text
                        $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank);
                        $sheet->getStyleByColumnAndRow(16, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $rank++; // Increment the rank only when the percentage changes
                    }
                }



                $counter = 1;
                foreach ($columnNames as $columnName) {
                    // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                    if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                        continue;
                    }
                
                    $formattedColumnName = str_replace('_', ' ', $columnName);
                    $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                
                    $ratingModel = new \App\Models\RatingModel();
                
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
                            ->set(['total' => $totalPercentage, 
                                   'percentage_60' => $averageSums[$columnName], 
                                   'percentage_40' => $averageSums2[$columnName],
                                   'foreignOfficeId' => $counter,])
                            ->update();
                
                        if (!$updated) {
                            // Handle update failure if needed
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
                            // Handle insertion failure if needed
                            return $this->failServerError('Failed to insert data into rates table.');
                        }
                    }
                    $counter++;
                }
                
                

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $rowIndex++;
                    }
                }

                // Set the header and filename for the download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
                header('Cache-Control: max-age=0');

                // Save Excel 2007 file
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');

                // Exit script to prevent any further output
                exit;
            } else {
                // If no user rates are found, return failure message
                return $this->failNotFound('User rates not found');
            }
        } else {
            // If no user offices are found, return failure message
            return $this->failNotFound('User offices not found');
        }

    }

    // public function generateOrminReport()
    // {
    //     // Get JSON data from the request
    //     $json = $this->request->getJSON();
    //     $month = $json->month;
    //     $year = $json->year;

    //     // Load the database connection
    //     $db = \Config\Database::connect();

    //     // Fetch column names from the database table
    //     $table = 'oriental_cps';
    //     $query = $db->query("DESCRIBE $table");

    //     // Check if the query was successful
    //     if (!$query) {
    //         // Return error message if query fails
    //         return $this->failServerError('Failed to fetch column names.');
    //     }

    //     $columns = $query->getResultArray();
    //     $mimaropaColumns = $query->getResultArray();

    //     $sums = [];
    //     $sums2 = [];
    //     foreach ($mimaropaColumns as $column) {
    //         $columnName = $column['Field'];

    //         if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
    //             continue;
    //         }
    //         $sums[$columnName] = 0;
    //         $sums2[$columnName] = 0;
            
    //     }

    //     // Check if columns were fetched successfully
    //     if (empty($columns)) {
    //         // Return error message if no columns are found
    //         return $this->failNotFound('No columns found in the database table.');
    //     }

    //     // Fetch results based on dynamic office names
    //     $queryDistinctOffices = "
    //         SELECT DISTINCT office
    //         FROM tbl_users
    //         LIMIT 4
    //     ";
    //     $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
    //     $officeNames = array_column($distinctOffices, 'office');
    //     $officeNamesString = "'" . implode("', '", $officeNames) . "'";

    //      // Fetch results based on dynamic office names
    //     $queryDistinctOffices2 = "
    //     SELECT DISTINCT office
    //     FROM tbl_users
    //     LIMIT 7 OFFSET 4
    //     ";
    //     $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
    //     $officeNames2 = array_column($distinctOffices2, 'office');
    //     $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


    //     // Build the SQL query with placeholders for month and year
    //     $queryOperation = "
    //     SELECT oriental_cps.*, tbl_users.office
    //     FROM oriental_cps
    //     INNER JOIN tbl_users ON oriental_cps.userid = tbl_users.user_id
    //     WHERE oriental_cps.month = ? AND oriental_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString)
    //     ";

       
    //     $queryAdministrative = "
    //     SELECT oriental_cps.*, tbl_users.office
    //     FROM oriental_cps
    //     INNER JOIN tbl_users ON oriental_cps.userid = tbl_users.user_id
    //     WHERE oriental_cps.month = ? AND oriental_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString2)
    //     ";
    //     // Use the database connection to execute the query with prepared statements
    //     $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //     $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //     // Compute sums for each column dynamically
    //     foreach ($results1 as $result1) {
    //         foreach ($sums as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result1)) {
    //                 $sums[$columnName] +=  $result1[$columnName];
    //             }
    //         }
    //     }

    //     foreach ($results2 as $result2) {
    //         foreach ($sums2 as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result2)) {
    //                 $sums2[$columnName] +=  $result2[$columnName];
    //             }
    //         }
    //     }

    //     // Calculate average of sums and multiply by 60%
    //     $averageSums = [];
    //     foreach ($sums as $avgCol => $sum) {
    //         $average = $sum / 600; // Calculate average
    //         $result1 = $average * 60;    // Multiply by 60%
    //         $averageSums[$avgCol] = $result1;
    //     }

    //     $averageSums2 = [];
    //     foreach ($sums2 as $avgCol2 => $sum) {
    //         $average2 = $sum / 400; // Calculate average
    //         $result2 = $average2 * 40;    // Multiply by 60%
    //         $averageSums2[$avgCol2] = $result2;
    //     }

    //     // Extract column names
    //     $columnNames = array_column($columns, 'Field');

    //     // Create new Spreadsheet object
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // Set PRO MIMAROPA in the first added row and center it
    //     $sheet->mergeCells('A1:O1');
    //     $sheet->setCellValue('A1', 'PRO MIMAROPA');
    //     $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Unit Performance Evaluation Rating in the second added row and center it
    //     $sheet->mergeCells('A2:O2');
    //     $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
    //     $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set the header for office and adjust column width
    //     $sheet->setCellValue('A4', 'Office');
    //     $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

    //     // Set Operational (60%) in the third row and center it
    //     $sheet->mergeCells('B3:E3');
    //     $sheet->setCellValue('B3', 'Operational (60%)');
    //     $style = $sheet->getStyle('B3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Administrative (40%) in the third row and center it
    //     $sheet->mergeCells('G3:M3');
    //     $sheet->setCellValue('G3', 'Administrative (40%)');
    //     $style = $sheet->getStyle('G3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     $sheet->setCellValue('O4', 'Total Percentage Ratings');
    //     $sheet->getColumnDimension('O')->setWidth(30); 

    //     // $sheet->setCellValue('P4', 'Ranking');
    //     // $sheet->getColumnDimension('P')->setWidth(20); 

    //     // Use the database connection to execute the query to fetch user offices
        
    //     $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
    //     $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
    //     $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
    //     $userOffices =$usersAllOffices->getResultArray();
    //     $userOffices1 =$operationalOffice->getResultArray();
    //     $userOffices2 =$administrativeOffice->getResultArray();

    //     // If user offices are found, proceed to populate Excel report
    //     if (!empty($userOffices1 && $userOffices2)) {
    //         // Populate office names as column headers
    //         $colIndex = 2;
    //         foreach ($userOffices1 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
    //             $colIndex++;
    //         }

    //         $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

    //         $colIndex2 = 7;
    //         foreach ($userOffices2 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
    //             $colIndex2++;
    //         }

    //         // Add headers for "40%" columns
            
    //         $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
    //        // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

    //         // Set headers for other data dynamically based on column names
    //         $rowIndex = 5;
    //         foreach ($columnNames as $columnName) {
    //             // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                 continue;
    //             }
            
    //             // Replace underscores with spaces in the column name
    //             $formattedColumnName = str_replace('_', ' ', $columnName);
            
    //             // Set the formatted column name in the spreadsheet
    //             $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
    //             $rowIndex++;
    //         }
            

    //         // Execute the query with prepared statements to fetch user rates
    //         $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //         $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //         // If user rates are found, proceed to populate Excel report
    //         if (!empty($userRates1) && !empty($userRates2)) {
    //             // Populate data
    //             foreach ($userRates1 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }

    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
    //                     $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
    //                     $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data

    //                     // Apply number formatting to "60%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     // Apply number formatting to "40%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
    //                     $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    //                     $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
    //                     $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $rowIndex++;
    //                 }

    //             }


    //             foreach ($columnNames as $columnName) {
    //                 // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                 if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                     continue;
    //                 }

    //                 $formattedColumnName = str_replace('_', ' ', $columnName);
    //                 $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                    
    //                 // $ratingModel = new \App\Models\RatingModel();
    //                 // $data = [
    //                 //     'month' => $month,          // Assuming $month is defined
    //                 //     'year' => $year,            // Assuming $year is defined
    //                 //     'offices' => $formattedColumnName,
    //                 //     'total' => $totalPercentage
    //                 // ];
            
    //                 // // Insert data into the rates table using the model
    //                 // $inserted = $ratingModel->insert($data);
            
    //                 // if (!$inserted) {
    //                 //     // Handle insertion failure if needed
    //                 //     return $this->failServerError('Failed to insert data into rates table.');
    //                 // }
                   
    //             }

    //             foreach ($userRates2 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }
    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
    //                     $rowIndex++;
    //                 }
    //             }

    //             // Set the header and filename for the download
    //             header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //             header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
    //             header('Cache-Control: max-age=0');

    //             // Save Excel 2007 file
    //             $writer = new Xlsx($spreadsheet);
    //             $writer->save('php://output');

    //             // Exit script to prevent any further output
    //             exit;
    //         } else {
    //             // If no user rates are found, return failure message
    //             return $this->failNotFound('User rates not found');
    //         }
    //     } else {
    //         // If no user offices are found, return failure message
    //         return $this->failNotFound('User offices not found');
    //     }

    // }

    public function generateOrminReport()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;

        // Load the database connection
        $db = \Config\Database::connect();

        // Fetch column names from the database table
        $table = 'oriental_cps';
        $query = $db->query("DESCRIBE $table");

        // Check if the query was successful
        if (!$query) {
            // Return error message if query fails
            return $this->failServerError('Failed to fetch column names.');
        }

        $columns = $query->getResultArray();
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

        // Check if columns were fetched successfully
        if (empty($columns)) {
            // Return error message if no columns are found
            return $this->failNotFound('No columns found in the database table.');
        }

        // Fetch results based on dynamic office names
        $queryDistinctOffices = "
            SELECT DISTINCT office
            FROM tbl_users
            LIMIT 4
        ";
        $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
        $officeNames = array_column($distinctOffices, 'office');
        $officeNamesString = "'" . implode("', '", $officeNames) . "'";

         // Fetch results based on dynamic office names
        $queryDistinctOffices2 = "
        SELECT DISTINCT office
        FROM tbl_users
        LIMIT 7 OFFSET 4
        ";
        $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
        $officeNames2 = array_column($distinctOffices2, 'office');
        $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


        // Build the SQL query with placeholders for month and year
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
        // Use the database connection to execute the query with prepared statements
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] +=  $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] +=  $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60;    // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40;    // Multiply by 60%
            $averageSums2[$avgCol2] = $result2;
        }

        // Extract column names
        $columnNames = array_column($columns, 'Field');

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set PRO MIMAROPA in the first added row and center it
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'UNIT PERFORMANCE EVALUATION RATING');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3','('.$month.' '.$year.')');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:P4');
        $sheet->setCellValue('A4', 'CITY/MUNICIPAL POLICE STATIONS');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      // Set the header for 'Office' and center align it
        $sheet->mergeCells('A7:A8');
        $sheet->setCellValue('A7', 'OFFICE/UNIT');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Adjust the width of column A
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed


        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B6', 'Operational (60%)');
        $style = $sheet->getStyle('B6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G6:M6');
        $sheet->setCellValue('G6', 'Administrative (40%)');
        $style = $sheet->getStyle('G6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('O7:O8');
        $sheet->setCellValue('O7', 'Total Percentage Ratings');
        $sheet->getStyle('O7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('O')->setWidth(20); 
        $sheet->getStyle('O7')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('P7:P8');
        $sheet->setCellValue('P7', 'Ranking');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('P')->setWidth(10); 

        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:P100'); // Adjust the range as needed

        // Set scaling to fit all columns on one page
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        // Insert page break after row 50
        $spreadsheet->getActiveSheet()->setBreak('A50', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        $additionalOperational = [167, 166, 167, 100];
        $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }
            
            $colIndexAd = 2;
            foreach ($additionalOperational as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexAd,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexAd,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexAd++;
            }
            
            // Set the header for "60%" column and center align it
            $sheet->mergeCells('F7:F8');
            $sheet->setCellValueByColumnAndRow($colIndex, 7, '60%');
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex2++;
            }
            
            $colIndexOp = 7;
            foreach ($additionalAdministrative as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexOp,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexOp,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexOp++;
            }
            

            // Add headers for "40%" columns
            
           // Set the header for '40%' and center align it
           $sheet->mergeCells('N7:N8');
            $sheet->setCellValueByColumnAndRow($colIndex2, 7, '40%');
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Set headers for other data dynamically based on column names
            $rowIndex = 9;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }

                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);

                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
                
                // Center align the header
                $sheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowIndex++;
            }

            

            // Execute the query with prepared statements to fetch user rates
            $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
            $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

            // If user rates are found, proceed to populate Excel report
            if (!empty($userRates1) && !empty($userRates2)) {
                // Populate data
                foreach ($userRates1 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // Apply number formatting to "60%" column
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        // Apply number formatting to "40%" column
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

                        $rowIndex++;
                    }

                }

                                // Step 2: Sort the totalPercentage array by percentage values in descending order
                usort($totalPercentageArray, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });

                // Initialize rank
                $rank = 1;
                $prevPercentage = null; // To track if the current percentage is equal to the previous one

                foreach ($totalPercentageArray as $percentageData) {
                    $totalPercentage = $percentageData['percentage'];
                    $rowIndex = $percentageData['rowIndex'];

                    // Check if the current percentage is different from the previous one
                    if ($totalPercentage != $prevPercentage) {
                        // If different, update the rank and store the current percentage as previous
                        $prevPercentage = $totalPercentage;
                        // Set the rank and center the text
                        $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank);
                        $sheet->getStyleByColumnAndRow(16, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $rank++; // Increment the rank only when the percentage changes
                    }
                }



                $counter = 1;
                foreach ($columnNames as $columnName) {
                    // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                    if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                        continue;
                    }
                
                    $formattedColumnName = str_replace('_', ' ', $columnName);
                    $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                
                    $ratingModel = new \App\Models\RatingModel();
                
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
                            ->set(['total' => $totalPercentage, 
                                   'percentage_60' => $averageSums[$columnName], 
                                   'percentage_40' => $averageSums2[$columnName],
                                   'foreignOfficeId' => $counter,])
                            ->update();
                
                        if (!$updated) {
                            // Handle update failure if needed
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
                            // Handle insertion failure if needed
                            return $this->failServerError('Failed to insert data into rates table.');
                        }
                    }
                    $counter++;
                }
                
                

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $rowIndex++;
                    }
                }

                // Set the header and filename for the download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
                header('Cache-Control: max-age=0');

                // Save Excel 2007 file
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');

                // Exit script to prevent any further output
                exit;
            } else {
                // If no user rates are found, return failure message
                return $this->failNotFound('User rates not found');
            }
        } else {
            // If no user offices are found, return failure message
            return $this->failNotFound('User offices not found');
        }

    }

    // public function generateMarinReport()
    // {
    //     // Get JSON data from the request
    //     $json = $this->request->getJSON();
    //     $month = $json->month;
    //     $year = $json->year;

    //     // Load the database connection
    //     $db = \Config\Database::connect();

    //     // Fetch column names from the database table
    //     $table = 'marinduque_cps';
    //     $query = $db->query("DESCRIBE $table");

    //     // Check if the query was successful
    //     if (!$query) {
    //         // Return error message if query fails
    //         return $this->failServerError('Failed to fetch column names.');
    //     }

    //     $columns = $query->getResultArray();
    //     $mimaropaColumns = $query->getResultArray();

    //     $sums = [];
    //     $sums2 = [];
    //     foreach ($mimaropaColumns as $column) {
    //         $columnName = $column['Field'];

    //         if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
    //             continue;
    //         }
    //         $sums[$columnName] = 0;
    //         $sums2[$columnName] = 0;
            
    //     }

    //     // Check if columns were fetched successfully
    //     if (empty($columns)) {
    //         // Return error message if no columns are found
    //         return $this->failNotFound('No columns found in the database table.');
    //     }

    //     // Fetch results based on dynamic office names
    //     $queryDistinctOffices = "
    //         SELECT DISTINCT office
    //         FROM tbl_users
    //         LIMIT 4
    //     ";
    //     $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
    //     $officeNames = array_column($distinctOffices, 'office');
    //     $officeNamesString = "'" . implode("', '", $officeNames) . "'";

    //      // Fetch results based on dynamic office names
    //     $queryDistinctOffices2 = "
    //     SELECT DISTINCT office
    //     FROM tbl_users
    //     LIMIT 7 OFFSET 4
    //     ";
    //     $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
    //     $officeNames2 = array_column($distinctOffices2, 'office');
    //     $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


    //     // Build the SQL query with placeholders for month and year
    //     $queryOperation = "
    //     SELECT marinduque_cps.*, tbl_users.office
    //     FROM marinduque_cps
    //     INNER JOIN tbl_users ON marinduque_cps.userid = tbl_users.user_id
    //     WHERE marinduque_cps.month = ? AND marinduque_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString)
    //     ";

       
    //     $queryAdministrative = "
    //     SELECT marinduque_cps.*, tbl_users.office
    //     FROM marinduque_cps
    //     INNER JOIN tbl_users ON marinduque_cps.userid = tbl_users.user_id
    //     WHERE marinduque_cps.month = ? AND marinduque_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString2)
    //     ";
    //     // Use the database connection to execute the query with prepared statements
    //     $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //     $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //     // Compute sums for each column dynamically
    //     foreach ($results1 as $result1) {
    //         foreach ($sums as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result1)) {
    //                 $sums[$columnName] +=  $result1[$columnName];
    //             }
    //         }
    //     }

    //     foreach ($results2 as $result2) {
    //         foreach ($sums2 as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result2)) {
    //                 $sums2[$columnName] +=  $result2[$columnName];
    //             }
    //         }
    //     }

    //     // Calculate average of sums and multiply by 60%
    //     $averageSums = [];
    //     foreach ($sums as $avgCol => $sum) {
    //         $average = $sum / 600; // Calculate average
    //         $result1 = $average * 60;    // Multiply by 60%
    //         $averageSums[$avgCol] = $result1;
    //     }

    //     $averageSums2 = [];
    //     foreach ($sums2 as $avgCol2 => $sum) {
    //         $average2 = $sum / 400; // Calculate average
    //         $result2 = $average2 * 40;    // Multiply by 60%
    //         $averageSums2[$avgCol2] = $result2;
    //     }

    //     // Extract column names
    //     $columnNames = array_column($columns, 'Field');

    //     // Create new Spreadsheet object
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // Set PRO MIMAROPA in the first added row and center it
    //     $sheet->mergeCells('A1:O1');
    //     $sheet->setCellValue('A1', 'PRO MIMAROPA');
    //     $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Unit Performance Evaluation Rating in the second added row and center it
    //     $sheet->mergeCells('A2:O2');
    //     $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
    //     $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set the header for office and adjust column width
    //     $sheet->setCellValue('A4', 'Office');
    //     $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

    //     // Set Operational (60%) in the third row and center it
    //     $sheet->mergeCells('B3:E3');
    //     $sheet->setCellValue('B3', 'Operational (60%)');
    //     $style = $sheet->getStyle('B3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Administrative (40%) in the third row and center it
    //     $sheet->mergeCells('G3:M3');
    //     $sheet->setCellValue('G3', 'Administrative (40%)');
    //     $style = $sheet->getStyle('G3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     $sheet->setCellValue('O4', 'Total Percentage Ratings');
    //     $sheet->getColumnDimension('O')->setWidth(30); 

    //     // $sheet->setCellValue('P4', 'Ranking');
    //     // $sheet->getColumnDimension('P')->setWidth(20); 

    //     // Use the database connection to execute the query to fetch user offices
        
    //     $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
    //     $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
    //     $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
    //     $userOffices =$usersAllOffices->getResultArray();
    //     $userOffices1 =$operationalOffice->getResultArray();
    //     $userOffices2 =$administrativeOffice->getResultArray();

    //     // If user offices are found, proceed to populate Excel report
    //     if (!empty($userOffices1 && $userOffices2)) {
    //         // Populate office names as column headers
    //         $colIndex = 2;
    //         foreach ($userOffices1 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
    //             $colIndex++;
    //         }

    //         $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

    //         $colIndex2 = 7;
    //         foreach ($userOffices2 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
    //             $colIndex2++;
    //         }

    //         // Add headers for "40%" columns
            
    //         $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
    //        // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

    //         // Set headers for other data dynamically based on column names
    //         $rowIndex = 5;
    //         foreach ($columnNames as $columnName) {
    //             // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                 continue;
    //             }
            
    //             // Replace underscores with spaces in the column name
    //             $formattedColumnName = str_replace('_', ' ', $columnName);
            
    //             // Set the formatted column name in the spreadsheet
    //             $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
    //             $rowIndex++;
    //         }
            

    //         // Execute the query with prepared statements to fetch user rates
    //         $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //         $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //         // If user rates are found, proceed to populate Excel report
    //         if (!empty($userRates1) && !empty($userRates2)) {
    //             // Populate data
    //             foreach ($userRates1 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }

    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
    //                     $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
    //                     $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data

    //                     // Apply number formatting to "60%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     // Apply number formatting to "40%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
    //                     $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    //                     $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
    //                     $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $rowIndex++;
    //                 }

    //             }


    //             foreach ($columnNames as $columnName) {
    //                 // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                 if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                     continue;
    //                 }

    //                 $formattedColumnName = str_replace('_', ' ', $columnName);
    //                 $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                    
    //                 // $ratingModel = new \App\Models\RatingModel();
    //                 // $data = [
    //                 //     'month' => $month,          // Assuming $month is defined
    //                 //     'year' => $year,            // Assuming $year is defined
    //                 //     'offices' => $formattedColumnName,
    //                 //     'total' => $totalPercentage
    //                 // ];
            
    //                 // // Insert data into the rates table using the model
    //                 // $inserted = $ratingModel->insert($data);
            
    //                 // if (!$inserted) {
    //                 //     // Handle insertion failure if needed
    //                 //     return $this->failServerError('Failed to insert data into rates table.');
    //                 // }
                   
    //             }

    //             foreach ($userRates2 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }
    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
    //                     $rowIndex++;
    //                 }
    //             }

    //             // Set the header and filename for the download
    //             header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //             header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
    //             header('Cache-Control: max-age=0');

    //             // Save Excel 2007 file
    //             $writer = new Xlsx($spreadsheet);
    //             $writer->save('php://output');

    //             // Exit script to prevent any further output
    //             exit;
    //         } else {
    //             // If no user rates are found, return failure message
    //             return $this->failNotFound('User rates not found');
    //         }
    //     } else {
    //         // If no user offices are found, return failure message
    //         return $this->failNotFound('User offices not found');
    //     }

    // }

    public function generateMarinReport()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;

        // Load the database connection
        $db = \Config\Database::connect();

        // Fetch column names from the database table
        $table = 'marinduque_cps';
        $query = $db->query("DESCRIBE $table");

        // Check if the query was successful
        if (!$query) {
            // Return error message if query fails
            return $this->failServerError('Failed to fetch column names.');
        }

        $columns = $query->getResultArray();
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

        // Check if columns were fetched successfully
        if (empty($columns)) {
            // Return error message if no columns are found
            return $this->failNotFound('No columns found in the database table.');
        }

        // Fetch results based on dynamic office names
        $queryDistinctOffices = "
            SELECT DISTINCT office
            FROM tbl_users
            LIMIT 4
        ";
        $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
        $officeNames = array_column($distinctOffices, 'office');
        $officeNamesString = "'" . implode("', '", $officeNames) . "'";

         // Fetch results based on dynamic office names
        $queryDistinctOffices2 = "
        SELECT DISTINCT office
        FROM tbl_users
        LIMIT 7 OFFSET 4
        ";
        $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
        $officeNames2 = array_column($distinctOffices2, 'office');
        $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


        // Build the SQL query with placeholders for month and year
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
        // Use the database connection to execute the query with prepared statements
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] +=  $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] +=  $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60;    // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40;    // Multiply by 60%
            $averageSums2[$avgCol2] = $result2;
        }

        // Extract column names
        $columnNames = array_column($columns, 'Field');

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set PRO MIMAROPA in the first added row and center it
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'UNIT PERFORMANCE EVALUATION RATING');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3','('.$month.' '.$year.')');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:P4');
        $sheet->setCellValue('A4', 'CITY/MUNICIPAL POLICE STATIONS');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      // Set the header for 'Office' and center align it
        $sheet->mergeCells('A7:A8');
        $sheet->setCellValue('A7', 'OFFICE/UNIT');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Adjust the width of column A
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed


        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B6', 'Operational (60%)');
        $style = $sheet->getStyle('B6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G6:M6');
        $sheet->setCellValue('G6', 'Administrative (40%)');
        $style = $sheet->getStyle('G6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('O7:O8');
        $sheet->setCellValue('O7', 'Total Percentage Ratings');
        $sheet->getStyle('O7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('O')->setWidth(20); 
        $sheet->getStyle('O7')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('P7:P8');
        $sheet->setCellValue('P7', 'Ranking');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('P')->setWidth(10); 

        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:P100'); // Adjust the range as needed

        // Set scaling to fit all columns on one page
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        // Insert page break after row 50
        $spreadsheet->getActiveSheet()->setBreak('A50', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        $additionalOperational = [167, 166, 167, 100];
        $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }
            
            $colIndexAd = 2;
            foreach ($additionalOperational as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexAd,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexAd,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexAd++;
            }
            
            // Set the header for "60%" column and center align it
            $sheet->mergeCells('F7:F8');
            $sheet->setCellValueByColumnAndRow($colIndex, 7, '60%');
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex2++;
            }
            
            $colIndexOp = 7;
            foreach ($additionalAdministrative as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexOp,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexOp,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexOp++;
            }
            

            // Add headers for "40%" columns
            
           // Set the header for '40%' and center align it
           $sheet->mergeCells('N7:N8');
            $sheet->setCellValueByColumnAndRow($colIndex2, 7, '40%');
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Set headers for other data dynamically based on column names
            $rowIndex = 9;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }

                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);

                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
                
                // Center align the header
                $sheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowIndex++;
            }

            

            // Execute the query with prepared statements to fetch user rates
            $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
            $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

            // If user rates are found, proceed to populate Excel report
            if (!empty($userRates1) && !empty($userRates2)) {
                // Populate data
                foreach ($userRates1 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // Apply number formatting to "60%" column
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        // Apply number formatting to "40%" column
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

                        $rowIndex++;
                    }

                }

                                // Step 2: Sort the totalPercentage array by percentage values in descending order
                usort($totalPercentageArray, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });

                // Initialize rank
                $rank = 1;
                $prevPercentage = null; // To track if the current percentage is equal to the previous one

                foreach ($totalPercentageArray as $percentageData) {
                    $totalPercentage = $percentageData['percentage'];
                    $rowIndex = $percentageData['rowIndex'];

                    // Check if the current percentage is different from the previous one
                    if ($totalPercentage != $prevPercentage) {
                        // If different, update the rank and store the current percentage as previous
                        $prevPercentage = $totalPercentage;
                        // Set the rank and center the text
                        $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank);
                        $sheet->getStyleByColumnAndRow(16, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $rank++; // Increment the rank only when the percentage changes
                    }
                }



                $counter = 1;
                foreach ($columnNames as $columnName) {
                    // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                    if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                        continue;
                    }
                
                    $formattedColumnName = str_replace('_', ' ', $columnName);
                    $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                
                    $ratingModel = new \App\Models\RatingModel();
                
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
                            ->set(['total' => $totalPercentage, 
                                   'percentage_60' => $averageSums[$columnName], 
                                   'percentage_40' => $averageSums2[$columnName],
                                   'foreignOfficeId' => $counter,])
                            ->update();
                
                        if (!$updated) {
                            // Handle update failure if needed
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
                            // Handle insertion failure if needed
                            return $this->failServerError('Failed to insert data into rates table.');
                        }
                    }
                    $counter++;
                }
                
                

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $rowIndex++;
                    }
                }

                // Set the header and filename for the download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
                header('Cache-Control: max-age=0');

                // Save Excel 2007 file
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');

                // Exit script to prevent any further output
                exit;
            } else {
                // If no user rates are found, return failure message
                return $this->failNotFound('User rates not found');
            }
        } else {
            // If no user offices are found, return failure message
            return $this->failNotFound('User offices not found');
        }

    }

    // public function generateRomReport()
    // {
    //     // Get JSON data from the request
    //     $json = $this->request->getJSON();
    //     $month = $json->month;
    //     $year = $json->year;

    //     // Load the database connection
    //     $db = \Config\Database::connect();

    //     // Fetch column names from the database table
    //     $table = 'romblon_cps';
    //     $query = $db->query("DESCRIBE $table");

    //     // Check if the query was successful
    //     if (!$query) {
    //         // Return error message if query fails
    //         return $this->failServerError('Failed to fetch column names.');
    //     }

    //     $columns = $query->getResultArray();
    //     $mimaropaColumns = $query->getResultArray();

    //     $sums = [];
    //     $sums2 = [];
    //     foreach ($mimaropaColumns as $column) {
    //         $columnName = $column['Field'];

    //         if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
    //             continue;
    //         }
    //         $sums[$columnName] = 0;
    //         $sums2[$columnName] = 0;
            
    //     }

    //     // Check if columns were fetched successfully
    //     if (empty($columns)) {
    //         // Return error message if no columns are found
    //         return $this->failNotFound('No columns found in the database table.');
    //     }

    //     // Fetch results based on dynamic office names
    //     $queryDistinctOffices = "
    //         SELECT DISTINCT office
    //         FROM tbl_users
    //         LIMIT 4
    //     ";
    //     $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
    //     $officeNames = array_column($distinctOffices, 'office');
    //     $officeNamesString = "'" . implode("', '", $officeNames) . "'";

    //      // Fetch results based on dynamic office names
    //     $queryDistinctOffices2 = "
    //     SELECT DISTINCT office
    //     FROM tbl_users
    //     LIMIT 7 OFFSET 4
    //     ";
    //     $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
    //     $officeNames2 = array_column($distinctOffices2, 'office');
    //     $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


    //     // Build the SQL query with placeholders for month and year
    //     $queryOperation = "
    //     SELECT romblon_cps.*, tbl_users.office
    //     FROM romblon_cps
    //     INNER JOIN tbl_users ON romblon_cps.userid = tbl_users.user_id
    //     WHERE romblon_cps.month = ? AND romblon_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString)
    //     ";

       
    //     $queryAdministrative = "
    //     SELECT romblon_cps.*, tbl_users.office
    //     FROM romblon_cps
    //     INNER JOIN tbl_users ON romblon_cps.userid = tbl_users.user_id
    //     WHERE romblon_cps.month = ? AND romblon_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString2)
    //     ";
    //     // Use the database connection to execute the query with prepared statements
    //     $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //     $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //     // Compute sums for each column dynamically
    //     foreach ($results1 as $result1) {
    //         foreach ($sums as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result1)) {
    //                 $sums[$columnName] +=  $result1[$columnName];
    //             }
    //         }
    //     }

    //     foreach ($results2 as $result2) {
    //         foreach ($sums2 as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result2)) {
    //                 $sums2[$columnName] +=  $result2[$columnName];
    //             }
    //         }
    //     }

    //     // Calculate average of sums and multiply by 60%
    //     $averageSums = [];
    //     foreach ($sums as $avgCol => $sum) {
    //         $average = $sum / 600; // Calculate average
    //         $result1 = $average * 60;    // Multiply by 60%
    //         $averageSums[$avgCol] = $result1;
    //     }

    //     $averageSums2 = [];
    //     foreach ($sums2 as $avgCol2 => $sum) {
    //         $average2 = $sum / 400; // Calculate average
    //         $result2 = $average2 * 40;    // Multiply by 60%
    //         $averageSums2[$avgCol2] = $result2;
    //     }

    //     // Extract column names
    //     $columnNames = array_column($columns, 'Field');

    //     // Create new Spreadsheet object
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // Set PRO MIMAROPA in the first added row and center it
    //     $sheet->mergeCells('A1:O1');
    //     $sheet->setCellValue('A1', 'PRO MIMAROPA');
    //     $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Unit Performance Evaluation Rating in the second added row and center it
    //     $sheet->mergeCells('A2:O2');
    //     $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
    //     $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set the header for office and adjust column width
    //     $sheet->setCellValue('A4', 'Office');
    //     $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

    //     // Set Operational (60%) in the third row and center it
    //     $sheet->mergeCells('B3:E3');
    //     $sheet->setCellValue('B3', 'Operational (60%)');
    //     $style = $sheet->getStyle('B3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Administrative (40%) in the third row and center it
    //     $sheet->mergeCells('G3:M3');
    //     $sheet->setCellValue('G3', 'Administrative (40%)');
    //     $style = $sheet->getStyle('G3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     $sheet->setCellValue('O4', 'Total Percentage Ratings');
    //     $sheet->getColumnDimension('O')->setWidth(30); 

    //     // $sheet->setCellValue('P4', 'Ranking');
    //     // $sheet->getColumnDimension('P')->setWidth(20); 

    //     // Use the database connection to execute the query to fetch user offices
        
    //     $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
    //     $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
    //     $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
    //     $userOffices =$usersAllOffices->getResultArray();
    //     $userOffices1 =$operationalOffice->getResultArray();
    //     $userOffices2 =$administrativeOffice->getResultArray();

    //     // If user offices are found, proceed to populate Excel report
    //     if (!empty($userOffices1 && $userOffices2)) {
    //         // Populate office names as column headers
    //         $colIndex = 2;
    //         foreach ($userOffices1 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
    //             $colIndex++;
    //         }

    //         $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

    //         $colIndex2 = 7;
    //         foreach ($userOffices2 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
    //             $colIndex2++;
    //         }

    //         // Add headers for "40%" columns
            
    //         $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
    //        // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

    //         // Set headers for other data dynamically based on column names
    //         $rowIndex = 5;
    //         foreach ($columnNames as $columnName) {
    //             // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                 continue;
    //             }
            
    //             // Replace underscores with spaces in the column name
    //             $formattedColumnName = str_replace('_', ' ', $columnName);
            
    //             // Set the formatted column name in the spreadsheet
    //             $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
    //             $rowIndex++;
    //         }
            

    //         // Execute the query with prepared statements to fetch user rates
    //         $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //         $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //         // If user rates are found, proceed to populate Excel report
    //         if (!empty($userRates1) && !empty($userRates2)) {
    //             // Populate data
    //             foreach ($userRates1 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }

    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
    //                     $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
    //                     $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data

    //                     // Apply number formatting to "60%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     // Apply number formatting to "40%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
    //                     $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    //                     $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
    //                     $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $rowIndex++;
    //                 }

    //             }


    //             foreach ($columnNames as $columnName) {
    //                 // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                 if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                     continue;
    //                 }

    //                 $formattedColumnName = str_replace('_', ' ', $columnName);
    //                 $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                    
    //                 // $ratingModel = new \App\Models\RatingModel();
    //                 // $data = [
    //                 //     'month' => $month,          // Assuming $month is defined
    //                 //     'year' => $year,            // Assuming $year is defined
    //                 //     'offices' => $formattedColumnName,
    //                 //     'total' => $totalPercentage
    //                 // ];
            
    //                 // // Insert data into the rates table using the model
    //                 // $inserted = $ratingModel->insert($data);
            
    //                 // if (!$inserted) {
    //                 //     // Handle insertion failure if needed
    //                 //     return $this->failServerError('Failed to insert data into rates table.');
    //                 // }
                   
    //             }

    //             foreach ($userRates2 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }
    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
    //                     $rowIndex++;
    //                 }
    //             }

    //             // Set the header and filename for the download
    //             header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //             header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
    //             header('Cache-Control: max-age=0');

    //             // Save Excel 2007 file
    //             $writer = new Xlsx($spreadsheet);
    //             $writer->save('php://output');

    //             // Exit script to prevent any further output
    //             exit;
    //         } else {
    //             // If no user rates are found, return failure message
    //             return $this->failNotFound('User rates not found');
    //         }
    //     } else {
    //         // If no user offices are found, return failure message
    //         return $this->failNotFound('User offices not found');
    //     }

    // }

    public function generateRomReport()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;

        // Load the database connection
        $db = \Config\Database::connect();

        // Fetch column names from the database table
        $table = 'romblon_cps';
        $query = $db->query("DESCRIBE $table");

        // Check if the query was successful
        if (!$query) {
            // Return error message if query fails
            return $this->failServerError('Failed to fetch column names.');
        }

        $columns = $query->getResultArray();
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

        // Check if columns were fetched successfully
        if (empty($columns)) {
            // Return error message if no columns are found
            return $this->failNotFound('No columns found in the database table.');
        }

        // Fetch results based on dynamic office names
        $queryDistinctOffices = "
            SELECT DISTINCT office
            FROM tbl_users
            LIMIT 4
        ";
        $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
        $officeNames = array_column($distinctOffices, 'office');
        $officeNamesString = "'" . implode("', '", $officeNames) . "'";

         // Fetch results based on dynamic office names
        $queryDistinctOffices2 = "
        SELECT DISTINCT office
        FROM tbl_users
        LIMIT 7 OFFSET 4
        ";
        $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
        $officeNames2 = array_column($distinctOffices2, 'office');
        $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


        // Build the SQL query with placeholders for month and year
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
        // Use the database connection to execute the query with prepared statements
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] +=  $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] +=  $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60;    // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40;    // Multiply by 60%
            $averageSums2[$avgCol2] = $result2;
        }

        // Extract column names
        $columnNames = array_column($columns, 'Field');

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set PRO MIMAROPA in the first added row and center it
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'UNIT PERFORMANCE EVALUATION RATING');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3','('.$month.' '.$year.')');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:P4');
        $sheet->setCellValue('A4', 'CITY/MUNICIPAL POLICE STATIONS');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      // Set the header for 'Office' and center align it
        $sheet->mergeCells('A7:A8');
        $sheet->setCellValue('A7', 'OFFICE/UNIT');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Adjust the width of column A
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed


        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B6', 'Operational (60%)');
        $style = $sheet->getStyle('B6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G6:M6');
        $sheet->setCellValue('G6', 'Administrative (40%)');
        $style = $sheet->getStyle('G6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('O7:O8');
        $sheet->setCellValue('O7', 'Total Percentage Ratings');
        $sheet->getStyle('O7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('O')->setWidth(20); 
        $sheet->getStyle('O7')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('P7:P8');
        $sheet->setCellValue('P7', 'Ranking');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('P')->setWidth(10); 

        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:P100'); // Adjust the range as needed

        // Set scaling to fit all columns on one page
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        // Insert page break after row 50
        $spreadsheet->getActiveSheet()->setBreak('A50', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        $additionalOperational = [167, 166, 167, 100];
        $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }
            
            $colIndexAd = 2;
            foreach ($additionalOperational as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexAd,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexAd,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexAd++;
            }
            
            // Set the header for "60%" column and center align it
            $sheet->mergeCells('F7:F8');
            $sheet->setCellValueByColumnAndRow($colIndex, 7, '60%');
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex2++;
            }
            
            $colIndexOp = 7;
            foreach ($additionalAdministrative as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexOp,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexOp,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexOp++;
            }
            

            // Add headers for "40%" columns
            
           // Set the header for '40%' and center align it
           $sheet->mergeCells('N7:N8');
            $sheet->setCellValueByColumnAndRow($colIndex2, 7, '40%');
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Set headers for other data dynamically based on column names
            $rowIndex = 9;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }

                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);

                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
                
                // Center align the header
                $sheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowIndex++;
            }

            

            // Execute the query with prepared statements to fetch user rates
            $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
            $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

            // If user rates are found, proceed to populate Excel report
            if (!empty($userRates1) && !empty($userRates2)) {
                // Populate data
                foreach ($userRates1 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // Apply number formatting to "60%" column
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        // Apply number formatting to "40%" column
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

                        $rowIndex++;
                    }

                }

                                // Step 2: Sort the totalPercentage array by percentage values in descending order
                usort($totalPercentageArray, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });

                // Initialize rank
                $rank = 1;
                $prevPercentage = null; // To track if the current percentage is equal to the previous one

                foreach ($totalPercentageArray as $percentageData) {
                    $totalPercentage = $percentageData['percentage'];
                    $rowIndex = $percentageData['rowIndex'];

                    // Check if the current percentage is different from the previous one
                    if ($totalPercentage != $prevPercentage) {
                        // If different, update the rank and store the current percentage as previous
                        $prevPercentage = $totalPercentage;
                        // Set the rank and center the text
                        $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank);
                        $sheet->getStyleByColumnAndRow(16, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $rank++; // Increment the rank only when the percentage changes
                    }
                }



                $counter = 1;
                foreach ($columnNames as $columnName) {
                    // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                    if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                        continue;
                    }
                
                    $formattedColumnName = str_replace('_', ' ', $columnName);
                    $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                
                    $ratingModel = new \App\Models\RatingModel();
                
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
                            ->set(['total' => $totalPercentage, 
                                   'percentage_60' => $averageSums[$columnName], 
                                   'percentage_40' => $averageSums2[$columnName],
                                   'foreignOfficeId' => $counter,])
                            ->update();
                
                        if (!$updated) {
                            // Handle update failure if needed
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
                            // Handle insertion failure if needed
                            return $this->failServerError('Failed to insert data into rates table.');
                        }
                    }
                    $counter++;
                }
                
                

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $rowIndex++;
                    }
                }

                // Set the header and filename for the download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
                header('Cache-Control: max-age=0');

                // Save Excel 2007 file
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');

                // Exit script to prevent any further output
                exit;
            } else {
                // If no user rates are found, return failure message
                return $this->failNotFound('User rates not found');
            }
        } else {
            // If no user offices are found, return failure message
            return $this->failNotFound('User offices not found');
        }

    }


    // public function generatePalReport()
    // {
    //     // Get JSON data from the request
    //     $json = $this->request->getJSON();
    //     $month = $json->month;
    //     $year = $json->year;

    //     // Load the database connection
    //     $db = \Config\Database::connect();

    //     // Fetch column names from the database table
    //     $table = 'palawan_cps';
    //     $query = $db->query("DESCRIBE $table");

    //     // Check if the query was successful
    //     if (!$query) {
    //         // Return error message if query fails
    //         return $this->failServerError('Failed to fetch column names.');
    //     }

    //     $columns = $query->getResultArray();
    //     $mimaropaColumns = $query->getResultArray();

    //     $sums = [];
    //     $sums2 = [];
    //     foreach ($mimaropaColumns as $column) {
    //         $columnName = $column['Field'];

    //         if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
    //             continue;
    //         }
    //         $sums[$columnName] = 0;
    //         $sums2[$columnName] = 0;
            
    //     }

    //     // Check if columns were fetched successfully
    //     if (empty($columns)) {
    //         // Return error message if no columns are found
    //         return $this->failNotFound('No columns found in the database table.');
    //     }

    //     // Fetch results based on dynamic office names
    //     $queryDistinctOffices = "
    //         SELECT DISTINCT office
    //         FROM tbl_users
    //         LIMIT 4
    //     ";
    //     $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
    //     $officeNames = array_column($distinctOffices, 'office');
    //     $officeNamesString = "'" . implode("', '", $officeNames) . "'";

    //      // Fetch results based on dynamic office names
    //     $queryDistinctOffices2 = "
    //     SELECT DISTINCT office
    //     FROM tbl_users
    //     LIMIT 7 OFFSET 4
    //     ";
    //     $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
    //     $officeNames2 = array_column($distinctOffices2, 'office');
    //     $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


    //     // Build the SQL query with placeholders for month and year
    //     $queryOperation = "
    //     SELECT palawan_cps.*, tbl_users.office
    //     FROM palawan_cps
    //     INNER JOIN tbl_users ON palawan_cps.userid = tbl_users.user_id
    //     WHERE palawan_cps.month = ? AND palawan_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString)
    //     ";

       
    //     $queryAdministrative = "
    //     SELECT palawan_cps.*, tbl_users.office
    //     FROM palawan_cps
    //     INNER JOIN tbl_users ON palawan_cps.userid = tbl_users.user_id
    //     WHERE palawan_cps.month = ? AND palawan_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString2)
    //     ";
    //     // Use the database connection to execute the query with prepared statements
    //     $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //     $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //     // Compute sums for each column dynamically
    //     foreach ($results1 as $result1) {
    //         foreach ($sums as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result1)) {
    //                 $sums[$columnName] +=  $result1[$columnName];
    //             }
    //         }
    //     }

    //     foreach ($results2 as $result2) {
    //         foreach ($sums2 as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result2)) {
    //                 $sums2[$columnName] +=  $result2[$columnName];
    //             }
    //         }
    //     }

    //     // Calculate average of sums and multiply by 60%
    //     $averageSums = [];
    //     foreach ($sums as $avgCol => $sum) {
    //         $average = $sum / 600; // Calculate average
    //         $result1 = $average * 60;    // Multiply by 60%
    //         $averageSums[$avgCol] = $result1;
    //     }

    //     $averageSums2 = [];
    //     foreach ($sums2 as $avgCol2 => $sum) {
    //         $average2 = $sum / 400; // Calculate average
    //         $result2 = $average2 * 40;    // Multiply by 60%
    //         $averageSums2[$avgCol2] = $result2;
    //     }

    //     // Extract column names
    //     $columnNames = array_column($columns, 'Field');

    //     // Create new Spreadsheet object
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // Set PRO MIMAROPA in the first added row and center it
    //     $sheet->mergeCells('A1:O1');
    //     $sheet->setCellValue('A1', 'PRO MIMAROPA');
    //     $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Unit Performance Evaluation Rating in the second added row and center it
    //     $sheet->mergeCells('A2:O2');
    //     $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
    //     $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set the header for office and adjust column width
    //     $sheet->setCellValue('A4', 'Office');
    //     $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

    //     // Set Operational (60%) in the third row and center it
    //     $sheet->mergeCells('B3:E3');
    //     $sheet->setCellValue('B3', 'Operational (60%)');
    //     $style = $sheet->getStyle('B3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Administrative (40%) in the third row and center it
    //     $sheet->mergeCells('G3:M3');
    //     $sheet->setCellValue('G3', 'Administrative (40%)');
    //     $style = $sheet->getStyle('G3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     $sheet->setCellValue('O4', 'Total Percentage Ratings');
    //     $sheet->getColumnDimension('O')->setWidth(30); 

    //     // $sheet->setCellValue('P4', 'Ranking');
    //     // $sheet->getColumnDimension('P')->setWidth(20); 

    //     // Use the database connection to execute the query to fetch user offices
        
    //     $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
    //     $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
    //     $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
    //     $userOffices =$usersAllOffices->getResultArray();
    //     $userOffices1 =$operationalOffice->getResultArray();
    //     $userOffices2 =$administrativeOffice->getResultArray();

    //     // If user offices are found, proceed to populate Excel report
    //     if (!empty($userOffices1 && $userOffices2)) {
    //         // Populate office names as column headers
    //         $colIndex = 2;
    //         foreach ($userOffices1 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
    //             $colIndex++;
    //         }

    //         $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

    //         $colIndex2 = 7;
    //         foreach ($userOffices2 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
    //             $colIndex2++;
    //         }

    //         // Add headers for "40%" columns
            
    //         $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
    //        // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

    //         // Set headers for other data dynamically based on column names
    //         $rowIndex = 5;
    //         foreach ($columnNames as $columnName) {
    //             // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                 continue;
    //             }
            
    //             // Replace underscores with spaces in the column name
    //             $formattedColumnName = str_replace('_', ' ', $columnName);
            
    //             // Set the formatted column name in the spreadsheet
    //             $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
    //             $rowIndex++;
    //         }
            

    //         // Execute the query with prepared statements to fetch user rates
    //         $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //         $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //         // If user rates are found, proceed to populate Excel report
    //         if (!empty($userRates1) && !empty($userRates2)) {
    //             // Populate data
    //             foreach ($userRates1 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }

    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
    //                     $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
    //                     $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data

    //                     // Apply number formatting to "60%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     // Apply number formatting to "40%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
    //                     $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    //                     $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
    //                     $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $rowIndex++;
    //                 }

    //             }


    //             foreach ($columnNames as $columnName) {
    //                 // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                 if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                     continue;
    //                 }

    //                 $formattedColumnName = str_replace('_', ' ', $columnName);
    //                 $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                    
    //                 // $ratingModel = new \App\Models\RatingModel();
    //                 // $data = [
    //                 //     'month' => $month,          // Assuming $month is defined
    //                 //     'year' => $year,            // Assuming $year is defined
    //                 //     'offices' => $formattedColumnName,
    //                 //     'total' => $totalPercentage
    //                 // ];
            
    //                 // // Insert data into the rates table using the model
    //                 // $inserted = $ratingModel->insert($data);
            
    //                 // if (!$inserted) {
    //                 //     // Handle insertion failure if needed
    //                 //     return $this->failServerError('Failed to insert data into rates table.');
    //                 // }
                   
    //             }

    //             foreach ($userRates2 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }
    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
    //                     $rowIndex++;
    //                 }
    //             }

    //             // Set the header and filename for the download
    //             header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //             header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
    //             header('Cache-Control: max-age=0');

    //             // Save Excel 2007 file
    //             $writer = new Xlsx($spreadsheet);
    //             $writer->save('php://output');

    //             // Exit script to prevent any further output
    //             exit;
    //         } else {
    //             // If no user rates are found, return failure message
    //             return $this->failNotFound('User rates not found');
    //         }
    //     } else {
    //         // If no user offices are found, return failure message
    //         return $this->failNotFound('User offices not found');
    //     }

    // }

    public function generatePalReport()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;

        // Load the database connection
        $db = \Config\Database::connect();

        // Fetch column names from the database table
        $table = 'palawan_cps';
        $query = $db->query("DESCRIBE $table");

        // Check if the query was successful
        if (!$query) {
            // Return error message if query fails
            return $this->failServerError('Failed to fetch column names.');
        }

        $columns = $query->getResultArray();
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

        // Check if columns were fetched successfully
        if (empty($columns)) {
            // Return error message if no columns are found
            return $this->failNotFound('No columns found in the database table.');
        }

        // Fetch results based on dynamic office names
        $queryDistinctOffices = "
            SELECT DISTINCT office
            FROM tbl_users
            LIMIT 4
        ";
        $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
        $officeNames = array_column($distinctOffices, 'office');
        $officeNamesString = "'" . implode("', '", $officeNames) . "'";

         // Fetch results based on dynamic office names
        $queryDistinctOffices2 = "
        SELECT DISTINCT office
        FROM tbl_users
        LIMIT 7 OFFSET 4
        ";
        $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
        $officeNames2 = array_column($distinctOffices2, 'office');
        $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


        // Build the SQL query with placeholders for month and year
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
        // Use the database connection to execute the query with prepared statements
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] +=  $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] +=  $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60;    // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40;    // Multiply by 60%
            $averageSums2[$avgCol2] = $result2;
        }

        // Extract column names
        $columnNames = array_column($columns, 'Field');

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set PRO MIMAROPA in the first added row and center it
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'UNIT PERFORMANCE EVALUATION RATING');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3','('.$month.' '.$year.')');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:P4');
        $sheet->setCellValue('A4', 'CITY/MUNICIPAL POLICE STATIONS');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      // Set the header for 'Office' and center align it
        $sheet->mergeCells('A7:A8');
        $sheet->setCellValue('A7', 'OFFICE/UNIT');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Adjust the width of column A
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed


        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B6', 'Operational (60%)');
        $style = $sheet->getStyle('B6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G6:M6');
        $sheet->setCellValue('G6', 'Administrative (40%)');
        $style = $sheet->getStyle('G6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('O7:O8');
        $sheet->setCellValue('O7', 'Total Percentage Ratings');
        $sheet->getStyle('O7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('O')->setWidth(20); 
        $sheet->getStyle('O7')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('P7:P8');
        $sheet->setCellValue('P7', 'Ranking');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('P')->setWidth(10); 

        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:P100'); // Adjust the range as needed

        // Set scaling to fit all columns on one page
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        // Insert page break after row 50
        $spreadsheet->getActiveSheet()->setBreak('A50', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        $additionalOperational = [167, 166, 167, 100];
        $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }
            
            $colIndexAd = 2;
            foreach ($additionalOperational as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexAd,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexAd,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexAd++;
            }
            
            // Set the header for "60%" column and center align it
            $sheet->mergeCells('F7:F8');
            $sheet->setCellValueByColumnAndRow($colIndex, 7, '60%');
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex2++;
            }
            
            $colIndexOp = 7;
            foreach ($additionalAdministrative as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexOp,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexOp,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexOp++;
            }
            

            // Add headers for "40%" columns
            
           // Set the header for '40%' and center align it
           $sheet->mergeCells('N7:N8');
            $sheet->setCellValueByColumnAndRow($colIndex2, 7, '40%');
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Set headers for other data dynamically based on column names
            $rowIndex = 9;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }

                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);

                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
                
                // Center align the header
                $sheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowIndex++;
            }

            

            // Execute the query with prepared statements to fetch user rates
            $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
            $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

            // If user rates are found, proceed to populate Excel report
            if (!empty($userRates1) && !empty($userRates2)) {
                // Populate data
                foreach ($userRates1 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // Apply number formatting to "60%" column
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        // Apply number formatting to "40%" column
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

                        $rowIndex++;
                    }

                }

                                // Step 2: Sort the totalPercentage array by percentage values in descending order
                usort($totalPercentageArray, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });

                // Initialize rank
                $rank = 1;
                $prevPercentage = null; // To track if the current percentage is equal to the previous one

                foreach ($totalPercentageArray as $percentageData) {
                    $totalPercentage = $percentageData['percentage'];
                    $rowIndex = $percentageData['rowIndex'];

                    // Check if the current percentage is different from the previous one
                    if ($totalPercentage != $prevPercentage) {
                        // If different, update the rank and store the current percentage as previous
                        $prevPercentage = $totalPercentage;
                        // Set the rank and center the text
                        $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank);
                        $sheet->getStyleByColumnAndRow(16, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $rank++; // Increment the rank only when the percentage changes
                    }
                }



                $counter = 1;
                foreach ($columnNames as $columnName) {
                    // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                    if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                        continue;
                    }
                
                    $formattedColumnName = str_replace('_', ' ', $columnName);
                    $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                
                    $ratingModel = new \App\Models\RatingModel();
                
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
                            ->set(['total' => $totalPercentage, 
                                   'percentage_60' => $averageSums[$columnName], 
                                   'percentage_40' => $averageSums2[$columnName],
                                   'foreignOfficeId' => $counter,])
                            ->update();
                
                        if (!$updated) {
                            // Handle update failure if needed
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
                            // Handle insertion failure if needed
                            return $this->failServerError('Failed to insert data into rates table.');
                        }
                    }
                    $counter++;
                }
                
                

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $rowIndex++;
                    }
                }

                // Set the header and filename for the download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
                header('Cache-Control: max-age=0');

                // Save Excel 2007 file
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');

                // Exit script to prevent any further output
                exit;
            } else {
                // If no user rates are found, return failure message
                return $this->failNotFound('User rates not found');
            }
        } else {
            // If no user offices are found, return failure message
            return $this->failNotFound('User offices not found');
        }

    }


    // public function generatePuerReport()
    // {
    //     // Get JSON data from the request
    //     $json = $this->request->getJSON();
    //     $month = $json->month;
    //     $year = $json->year;

    //     // Load the database connection
    //     $db = \Config\Database::connect();

    //     // Fetch column names from the database table
    //     $table = 'puertop_cps';
    //     $query = $db->query("DESCRIBE $table");

    //     // Check if the query was successful
    //     if (!$query) {
    //         // Return error message if query fails
    //         return $this->failServerError('Failed to fetch column names.');
    //     }

    //     $columns = $query->getResultArray();
    //     $mimaropaColumns = $query->getResultArray();

    //     $sums = [];
    //     $sums2 = [];
    //     foreach ($mimaropaColumns as $column) {
    //         $columnName = $column['Field'];

    //         if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
    //             continue;
    //         }
    //         $sums[$columnName] = 0;
    //         $sums2[$columnName] = 0;
            
    //     }

    //     // Check if columns were fetched successfully
    //     if (empty($columns)) {
    //         // Return error message if no columns are found
    //         return $this->failNotFound('No columns found in the database table.');
    //     }

    //     // Fetch results based on dynamic office names
    //     $queryDistinctOffices = "
    //         SELECT DISTINCT office
    //         FROM tbl_users
    //         LIMIT 4
    //     ";
    //     $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
    //     $officeNames = array_column($distinctOffices, 'office');
    //     $officeNamesString = "'" . implode("', '", $officeNames) . "'";

    //      // Fetch results based on dynamic office names
    //     $queryDistinctOffices2 = "
    //     SELECT DISTINCT office
    //     FROM tbl_users
    //     LIMIT 7 OFFSET 4
    //     ";
    //     $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
    //     $officeNames2 = array_column($distinctOffices2, 'office');
    //     $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


    //     // Build the SQL query with placeholders for month and year
    //     $queryOperation = "
    //     SELECT puertop_cps.*, tbl_users.office
    //     FROM puertop_cps
    //     INNER JOIN tbl_users ON puertop_cps.userid = tbl_users.user_id
    //     WHERE puertop_cps.month = ? AND puertop_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString)
    //     ";

       
    //     $queryAdministrative = "
    //     SELECT puertop_cps.*, tbl_users.office
    //     FROM puertop_cps
    //     INNER JOIN tbl_users ON puertop_cps.userid = tbl_users.user_id
    //     WHERE puertop_cps.month = ? AND puertop_cps.year = ?
    //     AND tbl_users.office IN ($officeNamesString2)
    //     ";
    //     // Use the database connection to execute the query with prepared statements
    //     $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //     $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //     // Compute sums for each column dynamically
    //     foreach ($results1 as $result1) {
    //         foreach ($sums as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result1)) {
    //                 $sums[$columnName] +=  $result1[$columnName];
    //             }
    //         }
    //     }

    //     foreach ($results2 as $result2) {
    //         foreach ($sums2 as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result2)) {
    //                 $sums2[$columnName] +=  $result2[$columnName];
    //             }
    //         }
    //     }

    //     // Calculate average of sums and multiply by 60%
    //     $averageSums = [];
    //     foreach ($sums as $avgCol => $sum) {
    //         $average = $sum / 600; // Calculate average
    //         $result1 = $average * 60;    // Multiply by 60%
    //         $averageSums[$avgCol] = $result1;
    //     }

    //     $averageSums2 = [];
    //     foreach ($sums2 as $avgCol2 => $sum) {
    //         $average2 = $sum / 400; // Calculate average
    //         $result2 = $average2 * 40;    // Multiply by 60%
    //         $averageSums2[$avgCol2] = $result2;
    //     }

    //     // Extract column names
    //     $columnNames = array_column($columns, 'Field');

    //     // Create new Spreadsheet object
    //     $spreadsheet = new Spreadsheet();
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // Set PRO MIMAROPA in the first added row and center it
    //     $sheet->mergeCells('A1:O1');
    //     $sheet->setCellValue('A1', 'PRO MIMAROPA');
    //     $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Unit Performance Evaluation Rating in the second added row and center it
    //     $sheet->mergeCells('A2:O2');
    //     $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
    //     $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set the header for office and adjust column width
    //     $sheet->setCellValue('A4', 'Office');
    //     $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

    //     // Set Operational (60%) in the third row and center it
    //     $sheet->mergeCells('B3:E3');
    //     $sheet->setCellValue('B3', 'Operational (60%)');
    //     $style = $sheet->getStyle('B3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     // Set Administrative (40%) in the third row and center it
    //     $sheet->mergeCells('G3:M3');
    //     $sheet->setCellValue('G3', 'Administrative (40%)');
    //     $style = $sheet->getStyle('G3');
    //     $alignment = $style->getAlignment();
    //     $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    //     $sheet->setCellValue('O4', 'Total Percentage Ratings');
    //     $sheet->getColumnDimension('O')->setWidth(30); 

    //     // $sheet->setCellValue('P4', 'Ranking');
    //     // $sheet->getColumnDimension('P')->setWidth(20); 

    //     // Use the database connection to execute the query to fetch user offices
        
    //     $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
    //     $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
    //     $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
    //     $userOffices =$usersAllOffices->getResultArray();
    //     $userOffices1 =$operationalOffice->getResultArray();
    //     $userOffices2 =$administrativeOffice->getResultArray();

    //     // If user offices are found, proceed to populate Excel report
    //     if (!empty($userOffices1 && $userOffices2)) {
    //         // Populate office names as column headers
    //         $colIndex = 2;
    //         foreach ($userOffices1 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
    //             $colIndex++;
    //         }

    //         $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

    //         $colIndex2 = 7;
    //         foreach ($userOffices2 as $office) {
    //             $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
    //             $colIndex2++;
    //         }

    //         // Add headers for "40%" columns
            
    //         $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
    //        // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

    //         // Set headers for other data dynamically based on column names
    //         $rowIndex = 5;
    //         foreach ($columnNames as $columnName) {
    //             // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //             if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                 continue;
    //             }
            
    //             // Replace underscores with spaces in the column name
    //             $formattedColumnName = str_replace('_', ' ', $columnName);
            
    //             // Set the formatted column name in the spreadsheet
    //             $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
    //             $rowIndex++;
    //         }
            

    //         // Execute the query with prepared statements to fetch user rates
    //         $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //         $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //         // If user rates are found, proceed to populate Excel report
    //         if (!empty($userRates1) && !empty($userRates2)) {
    //             // Populate data
    //             foreach ($userRates1 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }

    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
    //                     $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
    //                     $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data

    //                     // Apply number formatting to "60%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     // Apply number formatting to "40%" column
    //                     $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
    //                     $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    //                     $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
    //                     $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

    //                     $rowIndex++;
    //                 }

    //             }


    //             foreach ($columnNames as $columnName) {
    //                 // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                 if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                     continue;
    //                 }

    //                 $formattedColumnName = str_replace('_', ' ', $columnName);
    //                 $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                    
    //                 // $ratingModel = new \App\Models\RatingModel();
    //                 // $data = [
    //                 //     'month' => $month,          // Assuming $month is defined
    //                 //     'year' => $year,            // Assuming $year is defined
    //                 //     'offices' => $formattedColumnName,
    //                 //     'total' => $totalPercentage
    //                 // ];
            
    //                 // // Insert data into the rates table using the model
    //                 // $inserted = $ratingModel->insert($data);
            
    //                 // if (!$inserted) {
    //                 //     // Handle insertion failure if needed
    //                 //     return $this->failServerError('Failed to insert data into rates table.');
    //                 // }
                   
    //             }

    //             foreach ($userRates2 as $rate) {
    //                 // Find the index of the office in the userOffices array
    //                 $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
    //                 // Populate rate data for each office
    //                 $rowIndex = 5; // Start from the fifth row
    //                 foreach ($columnNames as $columnName) {
    //                     // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
    //                     if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
    //                         continue;
    //                     }
    //                     // Populate other column data
    //                     $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
    //                     $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
    //                     $rowIndex++;
    //                 }
    //             }

    //             // Set the header and filename for the download
    //             header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //             header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
    //             header('Cache-Control: max-age=0');

    //             // Save Excel 2007 file
    //             $writer = new Xlsx($spreadsheet);
    //             $writer->save('php://output');

    //             // Exit script to prevent any further output
    //             exit;
    //         } else {
    //             // If no user rates are found, return failure message
    //             return $this->failNotFound('User rates not found');
    //         }
    //     } else {
    //         // If no user offices are found, return failure message
    //         return $this->failNotFound('User offices not found');
    //     }

    // }

    public function generatePuerReport()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $month = $json->month;
        $year = $json->year;

        // Load the database connection
        $db = \Config\Database::connect();

        // Fetch column names from the database table
        $table = 'puertop_cps';
        $query = $db->query("DESCRIBE $table");

        // Check if the query was successful
        if (!$query) {
            // Return error message if query fails
            return $this->failServerError('Failed to fetch column names.');
        }

        $columns = $query->getResultArray();
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

        // Check if columns were fetched successfully
        if (empty($columns)) {
            // Return error message if no columns are found
            return $this->failNotFound('No columns found in the database table.');
        }

        // Fetch results based on dynamic office names
        $queryDistinctOffices = "
            SELECT DISTINCT office
            FROM tbl_users
            LIMIT 4
        ";
        $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
        $officeNames = array_column($distinctOffices, 'office');
        $officeNamesString = "'" . implode("', '", $officeNames) . "'";

         // Fetch results based on dynamic office names
        $queryDistinctOffices2 = "
        SELECT DISTINCT office
        FROM tbl_users
        LIMIT 7 OFFSET 4
        ";
        $distinctOffices2 = $db->query($queryDistinctOffices2)->getResultArray();
        $officeNames2 = array_column($distinctOffices2, 'office');
        $officeNamesString2 = "'" . implode("', '", $officeNames2) . "'";


        // Build the SQL query with placeholders for month and year
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
        // Use the database connection to execute the query with prepared statements
        $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
        $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results1 as $result1) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result1)) {
                    $sums[$columnName] +=  $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] +=  $result2[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $avgCol => $sum) {
            $average = $sum / 600; // Calculate average
            $result1 = $average * 60;    // Multiply by 60%
            $averageSums[$avgCol] = $result1;
        }

        $averageSums2 = [];
        foreach ($sums2 as $avgCol2 => $sum) {
            $average2 = $sum / 400; // Calculate average
            $result2 = $average2 * 40;    // Multiply by 60%
            $averageSums2[$avgCol2] = $result2;
        }

        // Extract column names
        $columnNames = array_column($columns, 'Field');

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set PRO MIMAROPA in the first added row and center it
        $sheet->mergeCells('A1:P1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:P2');
        $sheet->setCellValue('A2', 'UNIT PERFORMANCE EVALUATION RATING');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:P3');
        $sheet->setCellValue('A3','('.$month.' '.$year.')');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:P4');
        $sheet->setCellValue('A4', 'CITY/MUNICIPAL POLICE STATIONS');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

      // Set the header for 'Office' and center align it
        $sheet->mergeCells('A7:A8');
        $sheet->setCellValue('A7', 'OFFICE/UNIT');
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Adjust the width of column A
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed


        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B6:E6');
        $sheet->setCellValue('B6', 'Operational (60%)');
        $style = $sheet->getStyle('B6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G6:M6');
        $sheet->setCellValue('G6', 'Administrative (40%)');
        $style = $sheet->getStyle('G6');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('O7:O8');
        $sheet->setCellValue('O7', 'Total Percentage Ratings');
        $sheet->getStyle('O7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('O7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('O')->setWidth(20); 
        $sheet->getStyle('O7')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('P7:P8');
        $sheet->setCellValue('P7', 'Ranking');
        $sheet->getStyle('P7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('P7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getColumnDimension('P')->setWidth(10); 
        $spreadsheet->getActiveSheet()->getPageSetup()->setPrintArea('A1:P100'); // Adjust the range as needed

        // Set scaling to fit all columns on one page
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        // Insert page break after row 50
        $spreadsheet->getActiveSheet()->setBreak('A50', \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4 ");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        $additionalOperational = [167, 166, 167, 100];
        $additionalAdministrative = [80, 80, 80, 80, 35, 25, 20];

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex++;
            }
            
            $colIndexAd = 2;
            foreach ($additionalOperational as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexAd,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexAd,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexAd++;
            }
            
            // Set the header for "60%" column and center align it
            $sheet->mergeCells('F7:F8');
            $sheet->setCellValueByColumnAndRow($colIndex, 7, '60%');
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 7, $office['office']);
                $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndex2++;
            }
            
            $colIndexOp = 7;
            foreach ($additionalAdministrative as $office) {
                $sheet->setCellValueByColumnAndRow($colIndexOp,8, $office);
                $sheet->getStyleByColumnAndRow($colIndexOp,8)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIndexOp++;
            }
            

            // Add headers for "40%" columns
            
           // Set the header for '40%' and center align it
           $sheet->mergeCells('N7:N8');
            $sheet->setCellValueByColumnAndRow($colIndex2, 7, '40%');
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyleByColumnAndRow($colIndex2, 7)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // Set headers for other data dynamically based on column names
            $rowIndex = 9;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }

                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);

                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
                
                // Center align the header
                $sheet->getStyleByColumnAndRow(1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $rowIndex++;
            }

            

            // Execute the query with prepared statements to fetch user rates
            $userRates1 = $db->query($queryOperation, [$month, $year])->getResultArray();
            $userRates2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

            // If user rates are found, proceed to populate Excel report
            if (!empty($userRates1) && !empty($userRates2)) {
                // Populate data
                foreach ($userRates1 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex1 = array_search($rate['office'], array_column($userOffices1, 'office')) + 2; // +2 to account for header row
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        // Apply number formatting to "60%" column
                        $sheet->getStyleByColumnAndRow($colIndex, $rowIndex)->getNumberFormat()->setFormatCode('0.00');

                        // Apply number formatting to "40%" column
                        $sheet->getStyleByColumnAndRow($colIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($colIndex2 + 1, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $totalPercentageArray[] = ['percentage' => $totalPercentage, 'rowIndex' => $rowIndex];

                        $rowIndex++;
                    }

                }

                                // Step 2: Sort the totalPercentage array by percentage values in descending order
                usort($totalPercentageArray, function($a, $b) {
                    return $b['percentage'] <=> $a['percentage'];
                });

                // Initialize rank
                $rank = 1;
                $prevPercentage = null; // To track if the current percentage is equal to the previous one

                foreach ($totalPercentageArray as $percentageData) {
                    $totalPercentage = $percentageData['percentage'];
                    $rowIndex = $percentageData['rowIndex'];

                    // Check if the current percentage is different from the previous one
                    if ($totalPercentage != $prevPercentage) {
                        // If different, update the rank and store the current percentage as previous
                        $prevPercentage = $totalPercentage;
                        // Set the rank and center the text
                        $sheet->setCellValueByColumnAndRow(16, $rowIndex, $rank);
                        $sheet->getStyleByColumnAndRow(16, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $rank++; // Increment the rank only when the percentage changes
                    }
                }



                $counter = 1;
                foreach ($columnNames as $columnName) {
                    // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                    if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                        continue;
                    }
                
                    $formattedColumnName = str_replace('_', ' ', $columnName);
                    $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                
                    $ratingModel = new \App\Models\RatingModel();
                
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
                            ->set(['total' => $totalPercentage, 
                                   'percentage_60' => $averageSums[$columnName], 
                                   'percentage_40' => $averageSums2[$columnName],
                                   'foreignOfficeId' => $counter,])
                            ->update();
                
                        if (!$updated) {
                            // Handle update failure if needed
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
                            // Handle insertion failure if needed
                            return $this->failServerError('Failed to insert data into rates table.');
                        }
                    }
                    $counter++;
                }
                
                

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 9; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyleByColumnAndRow($officeIndex2, $rowIndex)->getNumberFormat()->setFormatCode('0.00');
                        $rowIndex++;
                    }
                }

                // Set the header and filename for the download
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="user_office_rates_report.xlsx"');
                header('Cache-Control: max-age=0');

                // Save Excel 2007 file
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');

                // Exit script to prevent any further output
                exit;
            } else {
                // If no user rates are found, return failure message
                return $this->failNotFound('User rates not found');
            }
        } else {
            // If no user offices are found, return failure message
            return $this->failNotFound('User offices not found');
        }

    }
}