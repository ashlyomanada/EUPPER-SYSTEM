<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RestFul\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MainModel;
use App\Models\AdminModel;
use App\Models\PpoModel;
use App\Models\UsersModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

class AdminController extends ResourceController
{
    public function getUsers(){
        $main = new MainModel();
        $data = $main->findall();
        return $this->respond($data,200);
    }

    public function getUsersInfo(){
        $main = new MainModel();
        $data = $main->findall();
        return $this->respond($data,200);
    }

    public function getAdmins(){
        $main = new AdminModel();
        $data = $main->findall();
        return $this->respond($data,200);
    }

    public function getPpo(){
        $main = new PpoModel();
        $data = $main->findall();
        return $this->respond($data,200);
    }
    public function generateExcel() {
        $model = new PpoModel();
        $data = $model->getData(); // Adjust this line based on your actual model method to fetch data
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Set your header
        $sheet->setCellValue('A1', 'Unit Performance Evaluation Ratings');
        $sheet->setCellValue('A2', 'Collation Reports');
        $sheet->mergeCells('A1:N1'); // Merge cells for the title
        $sheet->getStyle('A1:N2')->getFont()->setBold(true)->setSize(14);
    
        // Set new column headers
        $headers = array_keys((array)$data[0]);
        foreach ($headers as $colIndex => $header) {
            // Exclude 'id', 'userid', and the new columns
            if ($header !== 'id' && $header !== 'userid' && !in_array($header, ['operational', 'administrative', 'total'])) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, 3, ucfirst($header));
            }
        }
    
        // Add new column headers
        $sheet->setCellValue('M3', 'DRD');
        $sheet->setCellValue('N3', 'Operational');
        $sheet->setCellValue('O3', 'Administrative');
        $sheet->setCellValue('P3', 'Total');
    
        // Add data rows
        foreach ($data as $rowIndex => $rowData) {
            $colIndex = 0;
            foreach ($rowData as $key => $value) {
                // Exclude 'id', 'userid', and the new columns
                if ($key !== 'id' && $key !== 'userid' && !in_array($key, ['operational', 'administrative', 'total'])) {
                    $colIndex++;
                    $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex + 4, $value);
                }
            }
            // Add new column values
            $sheet->setCellValue('M' . ($rowIndex + 4), $rowData['drd']);
            $sheet->setCellValue('N' . ($rowIndex + 4), $rowData['operational']);
            $sheet->setCellValue('O' . ($rowIndex + 4), $rowData['administrative']);
            $sheet->setCellValue('P' . ($rowIndex + 4), $rowData['total']);
        }
    
        // Set column widths
        foreach (range('A', 'P') as $column) {
            $sheet->getColumnDimension($column)->setWidth(12);
        }
    
        // Set row height to auto
        $sheet->getDefaultRowDimension()->setRowHeight(-1);
    
        $filename = 'exported_data.xlsx';
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
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
        $model = new AdminModel();

        if ($data->admin_id) {
            // Update existing admin
            $model->update($data->admin_id, $data);
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