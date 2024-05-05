<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RestFul\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MainModel;
use App\Models\AdminModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

    public function getUsersRateRMFB(){
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT rmfb_tbl.*, tbl_users.office FROM rmfb_tbl 
                             INNER JOIN tbl_users ON rmfb_tbl.userid = tbl_users.user_id 
                             WHERE rmfb_tbl.month = ? AND rmfb_tbl.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
     
    }

    public function getUsersRateOcci(){
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT occidental_cps.*, tbl_users.office FROM occidental_cps 
                             INNER JOIN tbl_users ON occidental_cps.userid = tbl_users.user_id 
                             WHERE occidental_cps.month = ? AND occidental_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
     
    }

    public function getUsersRateOrmin(){
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT oriental_cps.*, tbl_users.office FROM oriental_cps 
                             INNER JOIN tbl_users ON oriental_cps.userid = tbl_users.user_id 
                             WHERE oriental_cps.month = ? AND oriental_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRateMarin(){
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT marinduque_cps.*, tbl_users.office FROM marinduque_cps 
                             INNER JOIN tbl_users ON marinduque_cps.userid = tbl_users.user_id 
                             WHERE marinduque_cps.month = ? AND marinduque_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRateRom(){
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT romblon_cps.*, tbl_users.office FROM romblon_cps 
                             INNER JOIN tbl_users ON romblon_cps.userid = tbl_users.user_id 
                             WHERE romblon_cps.month = ? AND romblon_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRatePal(){
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT palawan_cps.*, tbl_users.office FROM palawan_cps 
                             INNER JOIN tbl_users ON palawan_cps.userid = tbl_users.user_id 
                             WHERE palawan_cps.month = ? AND palawan_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRatePuer(){
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT puertop_cps.*, tbl_users.office FROM puertop_cps 
                             INNER JOIN tbl_users ON puertop_cps.userid = tbl_users.user_id 
                             WHERE puertop_cps.month = ? AND puertop_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRatePPOByMonth()
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

    public function getUsersRateRMFBByMonth()
    {
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT rmfb_tbl.*, tbl_users.office FROM rmfb_tbl 
                             INNER JOIN tbl_users ON rmfb_tbl.userid = tbl_users.user_id 
                             WHERE rmfb_tbl.month = ? AND rmfb_tbl.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRateOcciByMonth()
    {
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT occidental_cps.*, tbl_users.office FROM occidental_cps 
                             INNER JOIN tbl_users ON occidental_cps.userid = tbl_users.user_id 
                             WHERE occidental_cps.month = ? AND occidental_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRateOrminByMonth()
    {
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT oriental_cps.*, tbl_users.office FROM oriental_cps 
                             INNER JOIN tbl_users ON oriental_cps.userid = tbl_users.user_id 
                             WHERE oriental_cps.month = ? AND oriental_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRateMarinByMonth()
    {
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT marinduque_cps.*, tbl_users.office FROM marinduque_cps 
                             INNER JOIN tbl_users ON marinduque_cps.userid = tbl_users.user_id 
                             WHERE marinduque_cps.month = ? AND marinduque_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRateRomByMonth()
    {
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT romblon_cps.*, tbl_users.office FROM romblon_cps 
                             INNER JOIN tbl_users ON romblon_cps.userid = tbl_users.user_id 
                             WHERE romblon_cps.month = ? AND romblon_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRatePalByMonth()
    {
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT palawan_cps.*, tbl_users.office FROM palawan_cps 
                             INNER JOIN tbl_users ON palawan_cps.userid = tbl_users.user_id 
                             WHERE palawan_cps.month = ? AND palawan_cps.year = ?", [$month, $year]);
        $userRates = $query->getResultArray();
    
        if (!empty($userRates)) {
            return $this->respond($userRates, 200);
        } else {
            return $this->failNotFound('User offices not found');
        }
    }

    public function getUsersRatePuerByMonth()
    {
        $json = $this->request->getJSON();
        $month = $json->Month;
        $year = $json->Year;
        $db = \Config\Database::connect(); // Load the database connection
    
        // Use the database connection to execute the query
        $query = $db->query("SELECT puertop_cps.*, tbl_users.office FROM puertop_cps 
                             INNER JOIN tbl_users ON puertop_cps.userid = tbl_users.user_id 
                             WHERE puertop_cps.month = ? AND puertop_cps.year = ?", [$month, $year]);
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
    
   
    public function loginAdmin()
    {
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
    
}