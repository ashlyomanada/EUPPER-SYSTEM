<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RestFul\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MainModel;
use App\Models\AdminModel;
use App\Models\PpoModel;
use App\Models\PpocpoModel;
use App\Models\UsersModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;
use App\Models\UserRMFBModel;
use App\Models\OccidentalModel;
use App\Models\OrientalModel;
use App\Models\MarinduqueModel;
use App\Models\RomblonModel;
use App\Models\PalawanModel;
use App\Models\PuertoPrinsesaModel;
use App\Models\RmfbModel;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AdminController extends ResourceController
{
    public function getUsers(){
        $main = new MainModel();
        $data = $main->findall();
        return $this->respond($data,200);
    }

    public function getUsersInfo(){
        $main = new MainModel();
        $data = $main->findAll();
        return $this->respond($data, 200);
    }
    

    public function getAdmins(){
        $main = new AdminModel();
        $data = $main->findall();
        return $this->respond($data,200);
    }
    
    public function addColumnPPO(){
        $requestData = $this->request->getJSON();
        $columnName = $requestData->columnName;
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE ppo_cpo ADD COLUMN $columnName DECIMAL(10.0) NULL");
            return $this->respond(['message' => 'Column added successfully'], 200);
        } catch (\Exception $e) {
            return $this->fail(['error' => $e->getMessage()], 500);
        }
    }

    public function addColumnRMFB(){
        $requestData = $this->request->getJSON();
        $columnName = $requestData->columnName;
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE rmfb_tbl ADD COLUMN $columnName DECIMAL(10.0) NULL");
            return $this->respond(['message' => 'Column added successfully'], 200);
        } catch (\Exception $e) {
            return $this->fail(['error' => $e->getMessage()], 500);
        }
    }

    public function addColumnOcci(){
        $requestData = $this->request->getJSON();
        $columnName = $requestData->columnName;
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE occidental_cps ADD COLUMN $columnName DECIMAL(10.0) NULL");
            return $this->respond(['message' => 'Column added successfully'], 200);
        } catch (\Exception $e) {
            return $this->fail(['error' => $e->getMessage()], 500);
        }
    }

    public function addColumnOrmin(){
        $requestData = $this->request->getJSON();
        $columnName = $requestData->columnName;
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE oriental_cps ADD COLUMN $columnName DECIMAL(10.0) NULL");
            return $this->respond(['message' => 'Column added successfully'], 200);
        } catch (\Exception $e) {
            return $this->fail(['error' => $e->getMessage()], 500);
        }
    }

    public function addColumnRom(){
        $requestData = $this->request->getJSON();
        $columnName = $requestData->columnName;
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE romblon_cps ADD COLUMN $columnName DECIMAL(10.0) NULL");
            return $this->respond(['message' => 'Column added successfully'], 200);
        } catch (\Exception $e) {
            return $this->fail(['error' => $e->getMessage()], 500);
        }
    }

    public function addColumnMar(){
        $requestData = $this->request->getJSON();
        $columnName = $requestData->columnName;
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE marinduque_cps ADD COLUMN $columnName DECIMAL(10.0) NULL");
            return $this->respond(['message' => 'Column added successfully'], 200);
        } catch (\Exception $e) {
            return $this->fail(['error' => $e->getMessage()], 500);
        }
    }

    public function addColumnPal(){
        $requestData = $this->request->getJSON();
        $columnName = $requestData->columnName;
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE palawan_cps ADD COLUMN $columnName DECIMAL(10.0) NULL");
            return $this->respond(['message' => 'Column added successfully'], 200);
        } catch (\Exception $e) {
            return $this->fail(['error' => $e->getMessage()], 500);
        }
    }

    public function addColumnPuer(){
        $requestData = $this->request->getJSON();
        $columnName = $requestData->columnName;
        try {
            $db = \Config\Database::connect();
            $db->query("ALTER TABLE puertop_cps ADD COLUMN $columnName DECIMAL(10.0) NULL");
            return $this->respond(['message' => 'Column added successfully'], 200);
        } catch (\Exception $e) {
            return $this->fail(['error' => $e->getMessage()], 500);
        }
    }

    public function getUsersOffice(){
        $db = \Config\Database::connect(); // Load the database connection

        // Use the database connection to execute the query
        $query = $db->query("SELECT office FROM tbl_users");
        $userOffices = $query->getResultArray();

        if (!empty($userOffices)) {
            return $this->respond($userOffices, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }
    
    public function getUsersRatePPO(){
        $db = \Config\Database::connect(); 
    
        $query = $db->query("SELECT t1.user_id, t1.office, t2.*
                            FROM tbl_users t1
                            JOIN ppo_cpo t2 ON t1.user_id = t2.userid");
        $userRateAndOffice = $query->getResultArray();
    
        if (!empty($userRateAndOffice)) {
            return $this->respond($userRateAndOffice, 200);
        } else {
            return $this->failNotFound('User offices and rates not found');
        }
    }

    public function getUsersRateRMFB(){
        $db = \Config\Database::connect();

        $query = $db->query("SELECT t1.user_id, t1.office, t2.*
                            FROM tbl_users t1
                            JOIN rmfb_tbl t2 ON t1.user_id = t2.userid");
        $userRateAndOffice = $query->getResultArray();
    
        if (!empty($userRateAndOffice)) {
            return $this->respond($userRateAndOffice, 200);
        } else {
            return $this->failNotFound('User offices and rates not found');
        }
     
    }

    public function getUsersRateOcci(){
        $db = \Config\Database::connect();

        $query = $db->query("SELECT t1.user_id, t1.office, t2.*
                            FROM tbl_users t1
                            JOIN occidental_cps t2 ON t1.user_id = t2.userid");
        $userRateAndOffice = $query->getResultArray();
    
        if (!empty($userRateAndOffice)) {
            return $this->respond($userRateAndOffice, 200);
        } else {
            return $this->failNotFound('User offices and rates not found');
        }
    }

    public function getUsersRateOrmin(){
        $db = \Config\Database::connect();

        $query = $db->query("SELECT t1.user_id, t1.office, t2.*
                            FROM tbl_users t1
                            JOIN oriental_cps t2 ON t1.user_id = t2.userid");
        $userRateAndOffice = $query->getResultArray();
    
        if (!empty($userRateAndOffice)) {
            return $this->respond($userRateAndOffice, 200);
        } else {
            return $this->failNotFound('User offices and rates not found');
        }
    }

    public function getUsersRateMarin(){
        $db = \Config\Database::connect();

        $query = $db->query("SELECT t1.user_id, t1.office, t2.*
                            FROM tbl_users t1
                            JOIN marinduque_cps t2 ON t1.user_id = t2.userid");
        $userRateAndOffice = $query->getResultArray();
    
        if (!empty($userRateAndOffice)) {
            return $this->respond($userRateAndOffice, 200);
        } else {
            return $this->failNotFound('User offices and rates not found');
        }
    }

    public function getUsersRateRom(){
        $db = \Config\Database::connect();

        $query = $db->query("SELECT t1.user_id, t1.office, t2.*
                            FROM tbl_users t1
                            JOIN romblon_cps t2 ON t1.user_id = t2.userid");
        $userRateAndOffice = $query->getResultArray();
    
        if (!empty($userRateAndOffice)) {
            return $this->respond($userRateAndOffice, 200);
        } else {
            return $this->failNotFound('User offices and rates not found');
        }
    }

    public function getUsersRatePal(){
        $db = \Config\Database::connect();

        $query = $db->query("SELECT t1.user_id, t1.office, t2.*
                            FROM tbl_users t1
                            JOIN palawan_cps t2 ON t1.user_id = t2.userid");
        $userRateAndOffice = $query->getResultArray();
    
        if (!empty($userRateAndOffice)) {
            return $this->respond($userRateAndOffice, 200);
        } else {
            return $this->failNotFound('User offices and rates not found');
        }
    }

    public function getUsersRatePuer(){
        $db = \Config\Database::connect();

        $query = $db->query("SELECT t1.user_id, t1.office, t2.*
                            FROM tbl_users t1
                            JOIN puertop_cps t2 ON t1.user_id = t2.userid");
        $userRateAndOffice = $query->getResultArray();
    
        if (!empty($userRateAndOffice)) {
            return $this->respond($userRateAndOffice, 200);
        } else {
            return $this->failNotFound('User offices and rates not found');
        }
    }

    public function getUsersRateByMonth()
    {
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT ppo_cpo.*, tbl_users.office FROM ppo_cpo 
                             INNER JOIN tbl_users ON ppo_cpo.userid = tbl_users.user_id 
                             WHERE ppo_cpo.month = ? AND ppo_cpo.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }
    

    public function getUsersRateByOffice(){
        $json = $this->request->getJSON();
        $userId = $json->UserId;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT office FROM tbl_users WHERE user_id = ?", [$userId]);
        $officeResult = $query->getRowArray();
    
        if (!empty($officeResult)) {
            return $this->respond($officeResult, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
        
        
    }

    public function getAllUsersName(){
        $model = new MainModel();
        $data = $model->findAll();
        return $this->respond($data,200);
    }

    
    // public function generatePPOOffice()
    // {
    //     // Get JSON data from the request
    //     $json = $this->request->getJSON();
    //     $month = $json->month;
    //     $year = $json->year;

    //     // Load the database connection
    //     $db = \Config\Database::connect();

    //     // Fetch column names from the database table
    //     $table = 'ppo_cpo';
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
    //     SELECT ppo_cpo.*, tbl_users.office
    //     FROM ppo_cpo
    //     INNER JOIN tbl_users ON ppo_cpo.userid = tbl_users.user_id
    //     WHERE ppo_cpo.month = ? AND ppo_cpo.year = ?
    //     AND tbl_users.office IN ($officeNamesString)
    //     ";

       
    //     $queryAdministrative = "
    //     SELECT ppo_cpo.*, tbl_users.office
    //     FROM ppo_cpo
    //     INNER JOIN tbl_users ON ppo_cpo.userid = tbl_users.user_id
    //     WHERE ppo_cpo.month = ? AND ppo_cpo.year = ?
    //     AND tbl_users.office IN ($officeNamesString2)
    //     ";
    //     // Use the database connection to execute the query with prepared statements
    //     $results1 = $db->query($queryOperation, [$month, $year])->getResultArray();
    //     $results2 = $db->query($queryAdministrative, [$month, $year])->getResultArray();

    //     // Compute sums for each column dynamically
    //     foreach ($results1 as $result1) {
    //         foreach ($sums as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result1)) {
    //                 $sums[$columnName] += (int) $result1[$columnName];
    //             }
    //         }
    //     }

    //     foreach ($results2 as $result2) {
    //         foreach ($sums2 as $columnName => $sum) {
    //             if (array_key_exists($columnName, $result2)) {
    //                 $sums2[$columnName] += (int) $result2[$columnName];
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

    //                     $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
    //                     $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
    //                     $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        
    //                     $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
    //                     $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);

    //                     $rowIndex++;
    //                 }
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
                    $sums[$columnName] += (int) $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += (int) $result2[$columnName];
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
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set the header for office and adjust column width
        $sheet->setCellValue('A4', 'Office');
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B3', 'Operational (60%)');
        $style = $sheet->getStyle('B3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G3:M3');
        $sheet->setCellValue('G3', 'Administrative (40%)');
        $style = $sheet->getStyle('G3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('O4', 'Total Percentage Ratings');
        $sheet->getColumnDimension('O')->setWidth(30); 

        $sheet->setCellValue('P4', 'Ranking');
        $sheet->getColumnDimension('P')->setWidth(20); 

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
                $colIndex++;
            }

            $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
                $colIndex2++;
            }

            // Add headers for "40%" columns
            
            $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
           // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

            // Set headers for other data dynamically based on column names
            $rowIndex = 5;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }
            
                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);
            
                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
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
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);

                        $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);

                        $rowIndex++;
                       
                    }

                    
                }

                foreach ($columnNames as $columnName) {
                    // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                    if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                        continue;
                    }

                    $formattedColumnName = str_replace('_', ' ', $columnName);
                    $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                    
                    $ratingModel = new \App\Models\RatingModel();
                    $data = [
                        'month' => $month,          // Assuming $month is defined
                        'year' => $year,            // Assuming $year is defined
                        'offices' => $formattedColumnName,
                        'total' => $totalPercentage
                    ];
            
                    // Insert data into the rates table using the model
                    $inserted = $ratingModel->insert($data);
            
                    if (!$inserted) {
                        // Handle insertion failure if needed
                        return $this->failServerError('Failed to insert data into rates table.');
                    }
                   
                }

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
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
                    $sums[$columnName] += (int) $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += (int) $result2[$columnName];
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
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set the header for office and adjust column width
        $sheet->setCellValue('A4', 'Office');
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B3', 'Operational (60%)');
        $style = $sheet->getStyle('B3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G3:M3');
        $sheet->setCellValue('G3', 'Administrative (40%)');
        $style = $sheet->getStyle('G3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('O4', 'Total Percentage Ratings');
        $sheet->getColumnDimension('O')->setWidth(30); 

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
                $colIndex++;
            }

            $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
                $colIndex2++;
            }

            // Add headers for "40%" columns
            
            $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
           // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

            // Set headers for other data dynamically based on column names
            $rowIndex = 5;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }
            
                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);
            
                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
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
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);

                        $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $rowIndex++;
                    }
                }

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
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
                    $sums[$columnName] += (int) $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += (int) $result2[$columnName];
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
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set the header for office and adjust column width
        $sheet->setCellValue('A4', 'Office');
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B3', 'Operational (60%)');
        $style = $sheet->getStyle('B3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G3:M3');
        $sheet->setCellValue('G3', 'Administrative (40%)');
        $style = $sheet->getStyle('G3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('O4', 'Total Percentage Ratings');
        $sheet->getColumnDimension('O')->setWidth(30); 

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
                $colIndex++;
            }

            $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
                $colIndex2++;
            }

            // Add headers for "40%" columns
            
            $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
           // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

            // Set headers for other data dynamically based on column names
            $rowIndex = 5;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }
            
                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);
            
                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
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
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);

                        $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $rowIndex++;
                    }
                }

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
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
                    $sums[$columnName] += (int) $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += (int) $result2[$columnName];
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
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set the header for office and adjust column width
        $sheet->setCellValue('A4', 'Office');
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B3', 'Operational (60%)');
        $style = $sheet->getStyle('B3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G3:M3');
        $sheet->setCellValue('G3', 'Administrative (40%)');
        $style = $sheet->getStyle('G3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('O4', 'Total Percentage Ratings');
        $sheet->getColumnDimension('O')->setWidth(30); 

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
                $colIndex++;
            }

            $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
                $colIndex2++;
            }

            // Add headers for "40%" columns
            
            $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
           // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

            // Set headers for other data dynamically based on column names
            $rowIndex = 5;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }
            
                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);
            
                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
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
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);

                        $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $rowIndex++;
                    }
                }

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
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
                    $sums[$columnName] += (int) $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += (int) $result2[$columnName];
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
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set the header for office and adjust column width
        $sheet->setCellValue('A4', 'Office');
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B3', 'Operational (60%)');
        $style = $sheet->getStyle('B3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G3:M3');
        $sheet->setCellValue('G3', 'Administrative (40%)');
        $style = $sheet->getStyle('G3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('O4', 'Total Percentage Ratings');
        $sheet->getColumnDimension('O')->setWidth(30); 

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
                $colIndex++;
            }

            $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
                $colIndex2++;
            }

            // Add headers for "40%" columns
            
            $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
           // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

            // Set headers for other data dynamically based on column names
            $rowIndex = 5;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }
            
                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);
            
                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
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
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);

                        $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $rowIndex++;
                    }
                }

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
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
                    $sums[$columnName] += (int) $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += (int) $result2[$columnName];
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
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set the header for office and adjust column width
        $sheet->setCellValue('A4', 'Office');
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B3', 'Operational (60%)');
        $style = $sheet->getStyle('B3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G3:M3');
        $sheet->setCellValue('G3', 'Administrative (40%)');
        $style = $sheet->getStyle('G3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('O4', 'Total Percentage Ratings');
        $sheet->getColumnDimension('O')->setWidth(30); 

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
                $colIndex++;
            }

            $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
                $colIndex2++;
            }

            // Add headers for "40%" columns
            
            $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
           // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

            // Set headers for other data dynamically based on column names
            $rowIndex = 5;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }
            
                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);
            
                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
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
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);

                        $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $rowIndex++;
                    }
                }

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
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
                    $sums[$columnName] += (int) $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += (int) $result2[$columnName];
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
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set the header for office and adjust column width
        $sheet->setCellValue('A4', 'Office');
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B3', 'Operational (60%)');
        $style = $sheet->getStyle('B3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G3:M3');
        $sheet->setCellValue('G3', 'Administrative (40%)');
        $style = $sheet->getStyle('G3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('O4', 'Total Percentage Ratings');
        $sheet->getColumnDimension('O')->setWidth(30); 

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
                $colIndex++;
            }

            $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
                $colIndex2++;
            }

            // Add headers for "40%" columns
            
            $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
           // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

            // Set headers for other data dynamically based on column names
            $rowIndex = 5;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }
            
                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);
            
                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
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
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);

                        $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $rowIndex++;
                    }
                }

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
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
                    $sums[$columnName] += (int) $result1[$columnName];
                }
            }
        }

        foreach ($results2 as $result2) {
            foreach ($sums2 as $columnName => $sum) {
                if (array_key_exists($columnName, $result2)) {
                    $sums2[$columnName] += (int) $result2[$columnName];
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
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'PRO MIMAROPA');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Unit Performance Evaluation Rating in the second added row and center it
        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set the header for office and adjust column width
        $sheet->setCellValue('A4', 'Office');
        $sheet->getColumnDimension('A')->setWidth(30); // Adjust the width as needed

        // Set Operational (60%) in the third row and center it
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B3', 'Operational (60%)');
        $style = $sheet->getStyle('B3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set Administrative (40%) in the third row and center it
        $sheet->mergeCells('G3:M3');
        $sheet->setCellValue('G3', 'Administrative (40%)');
        $style = $sheet->getStyle('G3');
        $alignment = $style->getAlignment();
        $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('O4', 'Total Percentage Ratings');
        $sheet->getColumnDimension('O')->setWidth(30); 

        // Use the database connection to execute the query to fetch user offices
        
        $usersAllOffices = $db->query("SELECT DISTINCT office FROM tbl_users");
        $operationalOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 4");
        $administrativeOffice = $db->query("SELECT DISTINCT office FROM tbl_users LIMIT 7 OFFSET 4");
        
        $userOffices =$usersAllOffices->getResultArray();
        $userOffices1 =$operationalOffice->getResultArray();
        $userOffices2 =$administrativeOffice->getResultArray();

        // If user offices are found, proceed to populate Excel report
        if (!empty($userOffices1 && $userOffices2)) {
            // Populate office names as column headers
            $colIndex = 2;
            foreach ($userOffices1 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex, 4, $office['office']);
                $colIndex++;
            }

            $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%');

            $colIndex2 = 7;
            foreach ($userOffices2 as $office) {
                $sheet->setCellValueByColumnAndRow($colIndex2, 4, $office['office']);
                $colIndex2++;
            }

            // Add headers for "40%" columns
            
            $sheet->setCellValueByColumnAndRow($colIndex2, 4, '40%');
           // $sheet->setCellValueByColumnAndRow($colIndex + 2, 4, 'Total Percentage Ratings');

            // Set headers for other data dynamically based on column names
            $rowIndex = 5;
            foreach ($columnNames as $columnName) {
                // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                    continue;
                }
            
                // Replace underscores with spaces in the column name
                $formattedColumnName = str_replace('_', ' ', $columnName);
            
                // Set the formatted column name in the spreadsheet
                $sheet->setCellValueByColumnAndRow(1, $rowIndex, $formattedColumnName);
            
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
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }

                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex1, $rowIndex, $rate[$columnName]);

                        $sheet->setCellValueByColumnAndRow($colIndex, 4, '60%'); // Set header for "60%" column
                        $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, $averageSums[$columnName]); // Populate "60%" column data
                        $sheet->setCellValueByColumnAndRow($colIndex2, $rowIndex, $averageSums2[$columnName]);// Populate "40%" column data
                        
                        $totalPercentage = $averageSums[$columnName] + $averageSums2[$columnName];
                        $sheet->setCellValueByColumnAndRow($colIndex2 + 1, $rowIndex, $totalPercentage);
                        $rowIndex++;
                    }
                }

                foreach ($userRates2 as $rate) {
                    // Find the index of the office in the userOffices array
                    $officeIndex2 = array_search($rate['office'], array_column($userOffices2, 'office')) + 7;
                    // Populate rate data for each office
                    $rowIndex = 5; // Start from the fifth row
                    foreach ($columnNames as $columnName) {
                        // Skip the columns 'id', 'userid', 'month', 'year', and 'office'
                        if (in_array($columnName, ['id', 'userid', 'month', 'year', 'office'])) {
                            continue;
                        }
                        // Populate other column data
                        $sheet->setCellValueByColumnAndRow($officeIndex2, $rowIndex, $rate[$columnName]);
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


    public function createDynamicTable()
    {
        // Load the database connection via the Model or manually
        $db = db_connect();

        // Fetch column names dynamically from the oriental_cps table
        $table = 'ppo_cpo';
        $query = $db->query("DESCRIBE $table");

        // Check if the query was successful
        if (!$query) {
            // Return error message if query fails
            return $this->failServerError('Failed to fetch column names.');
        }

        $mimaropaColumns = $query->getResultArray();

        $sums = [];
        foreach ($mimaropaColumns as $column) {
            $columnName = $column['Field'];

            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
            $sums[$columnName] = 0;
        }

        // Fetch results based on dynamic office names
        $queryDistinctOffices = "
            SELECT DISTINCT office
            FROM tbl_users
            LIMIT 7 OFFSET 4
        ";
        $distinctOffices = $db->query($queryDistinctOffices)->getResultArray();
        $officeNames = array_column($distinctOffices, 'office');
        $officeNamesString = "'" . implode("', '", $officeNames) . "'";
        echo $officeNamesString;
        $query = "
            SELECT ppo_cpo.*, tbl_users.office
            FROM ppo_cpo
            INNER JOIN tbl_users ON ppo_cpo.userid = tbl_users.user_id
            WHERE ppo_cpo.month = 'January' AND ppo_cpo.year = 2024
            AND tbl_users.office IN ($officeNamesString)
        ";

        $results = $db->query($query)->getResultArray();

        // Compute sums for each column dynamically
        foreach ($results as $result) {
            foreach ($sums as $columnName => $sum) {
                if (array_key_exists($columnName, $result)) {
                    $sums[$columnName] += (int) $result[$columnName];
                }
            }
        }

        // Calculate average of sums and multiply by 60%
        $averageSums = [];
        foreach ($sums as $column => $sum) {
            $average = $sum / 600; // Calculate average
            $result = $average * 60;    // Multiply by 60%
            $averageSums[$column] = $result;
        }

        // Display the computed sums
    
        echo '<table border="1">';
        echo '<tr><th>Column</th><th>Result</th></tr>';
        foreach ($averageSums as $column => $result) {
            echo '<tr>';
            echo '<td>' . $column . '</td>';
            echo '<td>' . $result . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo $averageSums[$columnName];
    }


   
    public function generatePdf($selectedMonth, $selectedYear) {
        $model = new PpoModel();
    
        // Modify your query logic to filter data based on selectedMonth and selectedYear
        $data = $model
            ->where('month =', $selectedMonth)
            ->where('year =', $selectedYear)
            ->findAll();
    
        // Load mPDF library
        $mpdf = new Mpdf();
    
        // Set your header and footer HTML content
        $header = '<h2 style="text-align: center;">Unit Performance Evaluation Ratings</h2>';
        $footer = '<div style="text-align: center; font-style: italic;">PRO MIMAROPA EUPPER SYSTEM</div>';
    
        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);
    
        // Create PDF content
        $html = '<h4>Collation Reports</h4>';
    
        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5">';
    
        // Add table headers
        $html .= '<tr>';
        foreach (array_keys((array)$data[0]) as $header) {
            // Exclude 'id' and 'userid' columns
            if ($header !== 'id' && $header !== 'userid') {
                $html .= '<th>' . ucfirst($header) . '</th>';
            }
        }
        // Add new column headers
        $html .= '<th>DRD</th>';
        $html .= '<th>Operational</th>';
        $html .= '<th>Administrative</th>';
        $html .= '<th>Total</th>';
        $html .= '</tr>';
    
        // Add table rows
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ((array)$row as $key => $value) {
                // Exclude 'id' and 'userid' columns
                if ($key !== 'id' && $key !== 'userid') {
                    $html .= '<td>' . $value . '</td>';
                }
            }
            // Add new column values
            $html .= '<td>' . $row['drd'] . '</td>';
            $html .= '<td>' . $row['operational'] . '</td>';
            $html .= '<td>' . $row['administrative'] . '</td>';
            $html .= '<td>' . $row['total'] . '</td>';
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
    
    
    
    


    public function saveAdmin()
    {
        $data = $this->request->getJSON();
        $model = new MainModel();

        if ($data->user_id) {
            // Update existing admin
            $model->update($data->user_id, $data);
            $message = 'Admin updated successfully';
        } else {
            // Insert new admin
            $model->insert($data);
            $message = 'Admin saved successfully';
        }

        return $this->respond(['success' => true, 'message' => $message]);
    }


    public function changeAllUserStatus()
{
    $model = new MainModel();
    $users = $model->findAll();

    // Toggle the status for each user
    foreach ($users as $user) {
        $newStatus = ($user['status'] == 'Enable') ? 'Disable' : 'Enable';
        $user['status'] = $newStatus;

        // Update each user status individually
        try {
            $model->update($user['user_id'], $user);
        } catch (\Exception $e) {
            return $this->failServerError('Error updating user status');
        }
    }

    return $this->respond(['success' => true, 'message' => 'All user statuses updated successfully']);
}

    public function toggleUserStatus()
{
    $userId = $this->request->getVar('userId');
    $model = new MainModel();
    $user = $model->find($userId);

    if (empty($user)) {
        return $this->failNotFound('User not found');
    }

    // Toggle the status
    $newStatus = ($user['status'] == 'Enable') ? 'Disable' : 'Enable';
    $user['status'] = $newStatus;

    // Save the updated user status to the database using the $model instance
    try {
        $model->save($user);
        return $this->respond(['success' => true, 'message' => 'User status updated successfully']);
    } catch (\Exception $e) {
        return $this->failServerError('Error updating user status');
    }
}

public function filterUsers($searchTerm)
    {
        $model = new UsersModel();
        $users = $model->searchUsers($searchTerm);
        return json_encode($users);
    }



    public function saveUser()
    {
        $data = $this->request->getJSON();
        $model = new MainModel();
    
        try {
            // Debugging statements
            error_log('Received data: ' . print_r($data, true));
    
            if ($data->user_id) {
                // Update existing user
                $model->update($data->user_id, $data);
                $message = 'User updated successfully';
            } else {
                // Insert new user
                $model->insert($data);
                $message = 'User saved successfully';
            }
    
            return $this->respond(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            // Log the exception and return an error response
            error_log('Error in saveUser: ' . $e->getMessage());
            return $this->respond(['success' => false, 'message' => 'Internal Server Error']);
        }
    }
    
    
    

    public function findData($userId, $tableId)
    {
        $userModel = new PpoModel(); 
        $filteredData = $userModel->getFilteredData($userId, $tableId);
        return $this->respond($filteredData);
    }
   
    public function loginAdmin(){
        $json = $this->request->getJSON();

        if (isset($json->email) && isset($json->password)) {
            $email = $json->email;
            $password = $json->password;

        $userModel = new AdminModel();
           $data = $user = $userModel->where('email', $email)->first();

            if($data)
            {
                $pass = $data['password'];
                if($pass){
                    return $this->respond(['message' => 'Login successful', 'id' => $data['admin_id']], 200);                }
             else {
                return $this->respond(['message' => 'Invalid email or password'], 401);
            }
        } else {
            return $this->respond(['message' => 'Invalid JSON data'], 400);
        }
    }
    
    }

    public function saveUserRates()
    {
        $data = $this->request->getJSON();
        $model = new PpoModel();

        if ($data->id) {
            // Update existing admin
            $model->update($data->id, $data);
            $message = 'Admin updated successfully';
        } else {
            // Insert new admin
            $model->insert($data);
            $message = 'Admin saved successfully';
        }

        return $this->respond(['success' => true, 'message' => $message]);
    }

    public function countUserRatings() {
        try {
            $main = new PpoModel();
            $data = $main->findAll();
            $count = count($data);
    
            $response = [
                'data' => $data,
                'count' => $count
            ];
    
            return $this->respond($response, 200);
        } catch (\Exception $e) {
            return $this->respond(['error' => $e->getMessage()], 500);
        }
    }

    public function countUser() {
        try {
            $main = new MainModel();
            $data = $main->findAll();
            $count = count($data);
    
            $response = [
                'data' => $data,
                'count' => $count
            ];
    
            return $this->respond($response, 200);
        } catch (\Exception $e) {
            return $this->respond(['error' => $e->getMessage()], 500);
        }
    }

    public function filterData($year)
    {
        $model = new PpoModel();
        $users = $model->searchData($year);
        return json_encode($users);
    }

    public function calculateRatings($selectedYear)
    {
        try {
            $ratingModel = new PpoModel();
    
            // Pass the selected year to the getMonthlyRatings method
            $monthlyRatings = $ratingModel->getMonthlyRatings($selectedYear);
            $distinctMonths = $ratingModel->getDistinctMonths($selectedYear);
    
            // Extract the 'total' values from the database result
            $ratingsArray = array_column($monthlyRatings, 'total');
            $months = array_column($distinctMonths, 'month');
    
            // Calculate the total rating for the year
            $totalRatingForYear = array_sum($ratingsArray);
    
            // Calculate the percentage of each month's rating in the total
            $percentages = array_map(function ($rating) use ($totalRatingForYear) {
                return ($rating / $totalRatingForYear) * 100;
            }, $ratingsArray);
    
            // Format percentages with two decimal places
            $formattedPercentages = array_map(function ($percent) {
                return (int) number_format($percent, 2);
            }, $percentages);
    
            // Combine months and formatted percentages into an array of tuples
            $combinedData = array_map(null, $months, $formattedPercentages);
    
            // Sort the data based on months
            usort($combinedData, function ($a, $b) use ($distinctMonths) {
                $indexA = array_search($a[0], array_column($distinctMonths, 'month'));
                $indexB = array_search($b[0], array_column($distinctMonths, 'month'));

                return $indexA - $indexB;
            });

            
            
            
    
            // Unpack the sorted data
            list($sortedMonths, $sortedFormattedPercentages) = array_map(null, ...$combinedData);
    
            // Return JSON data
            return $this->response->setJSON([
                'totalRatingForYear' => $totalRatingForYear,
                'months' => $sortedMonths,
                'ratingsArray' => $ratingsArray,
                'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF5733', '#33FF57', '#5733FF', '#FF3366', '#33FFA5', '#A533FF'],
                'hoverBackgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#FF5733', '#33FF57', '#5733FF', '#FF3366', '#33FFA5', '#A533FF'],
                'formattedPercentages' => $sortedFormattedPercentages,
            ]);
        } catch (\Exception $e) {
            // Log the exception
            error_log($e->getMessage());
    
            // Return an error response if needed
            return $this->response->setJSON(['error' => 'Internal Server Error'])->setStatusCode(500);
        }
    }

    public function getRmfbRates(){
        $model = new UserRMFBModel();
        $data = $model->findAll();
        return $this->respond($data,200);
    }

    public function getOcciRates(){
        $model = new OccidentalModel();
        $data = $model->findAll();
        return $this->respond($data,200);
    }

    public function getOrienRates(){
        $model = new OrientalModel();
        $data = $model->findAll();
        return $this->respond($data,200);
    }
    

    public function getMarinRates(){
        $model = new MarinduqueModel();
        $data = $model->findAll();
        return $this->respond($data,200);
    }

    public function getRomRates(){
        $model = new RomblonModel();
        $data = $model->findAll();
        return $this->respond($data,200);
    }

    public function getPalRates(){
        $model = new PalawanModel();
        $data = $model->findAll();
        return $this->respond($data,200);
    }

    public function getPuertRates(){
        $model = new PuertoPrinsesaModel();
        $data = $model->findAll();
        return $this->respond($data,200);
    }
    
    
   

public function updateAdminInformation()
{
    $data = $this->request->getJSON();
    $model = new AdminModel();

    try {
        // Debugging statements
        error_log('Received data: ' . print_r($data, true));

        if ($data->admin_id) {
            // Update existing user
            $model->update($data->admin_id, $data);
            $message = 'User updated successfully';
        } else {
            // Insert new user
            $model->insert($data);
            $message = 'User saved successfully';
        }

        return $this->respond(['success' => true, 'message' => $message]);
    } catch (Exception $e) {
        // Log the exception and return an error response
        error_log('Error in saveUser: ' . $e->getMessage());
        return $this->respond(['success' => false, 'message' => 'Internal Server Error']);
    }
}

public function generateReport()
{
    $requestData = $this->request->getJSON();

    // Extract month and year from the request
    $month = $requestData->month;
    $year = $requestData->year;

    // Fetch data from the database based on the month and year
    $userRMFBModel = new UserRMFBModel();
    $data = $userRMFBModel
        ->where('month', $month)
        ->where('year', $year)
        ->findAll();

    // Generate Excel report
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set column headers
    $sheet->setCellValue('A1', 'Month');
    $sheet->setCellValue('B1', 'Year');
    $sheet->setCellValue('C1', 'Regional');
    $sheet->setCellValue('D1', 'Occidental');
    $sheet->setCellValue('E1', 'Oriental');
    $sheet->setCellValue('F1', 'Marinduque');
    $sheet->setCellValue('G1', 'Romblon');
    $sheet->setCellValue('H1', 'Palawan');
    $sheet->setCellValue('I1', 'PuertoPrincesa');

    // Populate data into the spreadsheet
    $row = 2;
    foreach ($data as $item) {
        $sheet->setCellValue('A' . $row, $item['month']);
        $sheet->setCellValue('B' . $row, $item['year']);
        $sheet->setCellValue('C' . $row, $item['regional']);
        $sheet->setCellValue('D' . $row, $item['occi']);
        $sheet->setCellValue('E' . $row, $item['ormin']);
        $sheet->setCellValue('F' . $row, $item['marin']);
        $sheet->setCellValue('G' . $row, $item['rom']);
        $sheet->setCellValue('H' . $row, $item['pal']);
        $sheet->setCellValue('I' . $row, $item['puertop']);
        $row++;
    }

    // Save the spreadsheet as an Excel file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $fileName = 'report.xlsx';

    // Set headers for file download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    // Output the file directly to the browser
    $writer->save('php://output');
    exit();
}

public function getFilterRmfbRates(){
    $json = $this->request->getJSON();

    $month = $json->Month;
    $year = $json->Year;
    $userRMFBModel = new UserRMFBModel();

    $data = $userRMFBModel
        ->where('month',$month)
        ->where('year',$year)
        ->find();
        
    return $this->respond($data,200);
}


public function generateRmfbReport()
{
    // Get JSON data from the request
    $requestData = $this->request->getJSON();

    // Extract month and year from the request
    $month = $requestData->month;
    $year = $requestData->year;

    // Fetch data from the database based on the month and year
    $rmfbModel = new RmfbModel();
    $data = $rmfbModel
        ->where('month', $month)
        ->where('year', $year)
        ->findAll();

    // Generate Excel report
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Add two rows before setting the titles for the first and second sections
    $sheet->insertNewRowBefore(1);
    $sheet->insertNewRowBefore(1);

    // Set PRO MIMAROPA in the first added row and center it
    $sheet->mergeCells('A1:O1');
    $sheet->setCellValue('A1', 'PRO MIMAROPA');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Set Unit Performance Evaluation Rating in the second added row and center it
    $sheet->mergeCells('A2:O2');
    $sheet->setCellValue('A2', 'Unit Performance Evaluation Rating');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Set title for the first section
    $sheet->mergeCells('B3:E3');
    $sheet->setCellValue('B3', 'Operational (60%)');

    // Get style for the merged cells and center the text horizontally
    $style = $sheet->getStyle('B3');
    $alignment = $style->getAlignment();
    $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Set title for the second section
    $sheet->mergeCells('G3:M3');
    $sheet->setCellValue('G3', 'Administrative (40%)');

    // Get style for the merged cells and center the text horizontally
    $style = $sheet->getStyle('G3');
    $alignment = $style->getAlignment();
    $alignment->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // Set column headers for the first section
    $sheet->setCellValue('A4', 'Office');
    $sheet->setCellValue('B4', 'ROD');
    $sheet->setCellValue('C4', 'RIDMD');
    $sheet->setCellValue('D4', 'RID');
    $sheet->setCellValue('E4', 'RCADD');
    $sheet->setCellValue('F4', '60%');
    $sheet->setCellValue('G4', 'RLRDD');
    $sheet->setCellValue('H4', 'RLDDD');
    $sheet->setCellValue('I4', 'RPRMD');
    $sheet->setCellValue('J4', 'RICTMD');
    $sheet->setCellValue('K4', 'RPSMD');
    $sheet->setCellValue('L4', 'RCD');
    $sheet->setCellValue('M4', 'RRD');
    $sheet->setCellValue('N4', '40%');
    $sheet->setCellValue('O4', 'Total Percentage Rating');
    $sheet->setCellValue('P4', 'Ranking'); // Add 'Ranking' column header

    // Merge and vertically center align cells A4 with A5, F4 with F5, N4 with N5, and O4 with O5
    $this->mergeAndCenterVertical($sheet, 'A4:A5');
    $this->mergeAndCenterVertical($sheet, 'F4:F5');
    $this->mergeAndCenterVertical($sheet, 'N4:N5');
    $this->mergeAndCenterVertical($sheet, 'O4:O5');
    $this->mergeAndCenterVertical($sheet, 'P4:P5'); // Merge 'Ranking' column header cells

    // Adjust the width of the 'Office' column
    $sheet->getColumnDimension('A')->setWidth(30); // You can adjust the width as per your requirement

    // Set width of the 'Total Percentage Rating' and 'Ranking' columns to match the 'Office' column
    $sheet->getColumnDimension('O')->setWidth($sheet->getColumnDimension('A')->getWidth());
    $sheet->getColumnDimension('P')->setWidth($sheet->getColumnDimension('A')->getWidth());

    // Set alignment to center for all column headers and values
    $headerStyle = $sheet->getStyle('A4:P5');
    $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $headerStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    // Set alignment to center for all values
    $valueStyle = $sheet->getStyle('A6:P' . (count($data) + 5));
    $valueStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $valueStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    // Sort the data based on the 'Total Percentage Rating' column
    usort($data, function($a, $b) {
        return $b['total'] - $a['total'];
    });

    // Populate data into the spreadsheet and add ranking
    $row = 5; // Starting from row 5 since row 1, 2, 3, and 4 are headers
    $rank = 1;
    foreach ($data as $item) {
        $row++;
        $sheet->setCellValue('A' . $row, $item['office']);
        $sheet->setCellValue('B' . $row, $item['rod']);
        $sheet->setCellValue('C' . $row, $item['ridmd']);
        $sheet->setCellValue('D' . $row, $item['rid']);
        $sheet->setCellValue('E' . $row, $item['rcadd']);
        $sheet->setCellValue('F' . $row, $item['60percent']);
        $sheet->setCellValue('G' . $row, $item['rlrdd']);
        $sheet->setCellValue('H' . $row, $item['rlddd']);
        $sheet->setCellValue('I' . $row, $item['rprmd']);
        $sheet->setCellValue('J' . $row, $item['rictmd']);
        $sheet->setCellValue('K' . $row, $item['rpsmd']);
        $sheet->setCellValue('L' . $row, $item['rcd']);
        $sheet->setCellValue('M' . $row, $item['rrd']);
        $sheet->setCellValue('N' . $row, $item['40percent']);
        $sheet->setCellValue('O' . $row, $item['total']);
        $sheet->setCellValue('P' . $row, $rank); // Add ranking
        $rank++;
    }

    // Save the spreadsheet as an Excel file
    $writer = new Xlsx($spreadsheet);
    $fileName = 'report.xlsx';

    // Set headers for file download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');

    // Output the file directly to the browser
    $writer->save('php://output');
    exit();
}

private function mergeAndCenterVertical($sheet, $cellRange)
{
    $sheet->mergeCells($cellRange);
    $style = $sheet->getStyle($cellRange);
    $style->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
}



    
}



/*
public function updateAdmin($id){
        $main = new AdminModel();
        $data = $main->find($id);

        if (!$data) {
            return $this->respond(['error' => 'Item not found.'], 404);
        }

        $postData = $this->request->getPost(); // Assuming form data is sent as POST

        $data = [
            'username' => $postData['username'],
            'password' => $postData['password'],
            'email' => $postData['email'],
            'phone_no' => $postData['phone_no'],
        ];

        $main->set($data)->where('admin_id', $id)->update();

        return $this->respond(['message' => 'Item updated successfully.']);
    }

     public function generatePdf(){
        $model = new PpoModel(); 
        $data = $model->findAll(); 

        // Load mPDF library
        $mpdf = new Mpdf();

        // Set your header and footer HTML content
        $header = '<h1>Your PDF Header</h1>';
        $footer = '<div style="text-align: center; font-style: italic;">Your PDF Footer</div>';

        // Set header and footer
        $mpdf->SetHTMLHeader($header);
        $mpdf->SetHTMLFooter($footer);

        // Create PDF content
        $html = '<h2>Data from Database</h2>';

        // Add a table
        $html .= '<table border="1" cellspacing="0" cellpadding="5">';
        
        // Add table headers
        $html .= '<tr>';
        foreach (array_keys((array)$data[0]) as $header) {
            $html .= '<th>' . $header . '</th>';
        }
        $html .= '</tr>';

        // Add table rows
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ((array)$row as $value) {
                $html .= '<td>' . $value . '</td>';
            }
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
*/