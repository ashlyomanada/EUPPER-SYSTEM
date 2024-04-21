<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RestFul\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OccidentalModel;
use App\Models\OrientalModel;
use App\Models\PalawanModel;
use App\Models\RomblonModel;
use App\Models\MarinduqueModel;
use App\Models\UserPPOModel;
use App\Models\UserRMFBModel;
use App\Models\PuertoPrinsesaModel;
use App\Models\PpocpoModel;
use App\Models\MainModel;
use CodeIgniter\I18n\Time;
use Config\Services;

class UserController extends ResourceController
{

    public function getColumnNamePPO()
    {
        $db = db_connect();
        $table = 'ppo_cpo'; 
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        $columnNames = array_column($columns, 'Field');
        return $this->response->setJSON($columnNames);
    }

    public function getColumnNameRMFB()
    {
        $db = db_connect();
        $table = 'rmfb_tbl'; 
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        $columnNames = array_column($columns, 'Field');
        return $this->response->setJSON($columnNames);
    }

    public function getColumnNameOcci()
    {
        $db = db_connect();
        $table = 'occidental_cps'; 
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        $columnNames = array_column($columns, 'Field');
        return $this->response->setJSON($columnNames);
    }

    public function getColumnNameOrmin()
    {
        $db = db_connect();
        $table = 'oriental_cps'; 
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        $columnNames = array_column($columns, 'Field');
        return $this->response->setJSON($columnNames);
    }

    public function getColumnNameRom()
    {
        $db = db_connect();
        $table = 'romblon_cps'; 
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        $columnNames = array_column($columns, 'Field');
        return $this->response->setJSON($columnNames);
    }

    public function getColumnNameMar()
    {
        $db = db_connect();
        $table = 'marinduque_cps'; 
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        $columnNames = array_column($columns, 'Field');
        return $this->response->setJSON($columnNames);
    }

    public function getColumnNamePal()
    {
        $db = db_connect();
        $table = 'palawan_cps'; 
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        $columnNames = array_column($columns, 'Field');
        return $this->response->setJSON($columnNames);
    }

    public function getColumnNamePuer()
    {
        $db = db_connect();
        $table = 'puertop_cps'; 
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        $columnNames = array_column($columns, 'Field');
        return $this->response->setJSON($columnNames);
    }

  
    public function insertDataPPO()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        $sql = "INSERT INTO ppo_cpo (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values .= "'$value', ";
        }
        $sql = rtrim($sql, ", ") . ") ";
        $values = rtrim($values, ", ") . ")";
        $query = $sql . $values;
        $db = \Config\Database::connect();
        $result = $db->query($query);
        if ($result) {
            return $this->response->setStatusCode(200)->setJSON(['success' => 'Data inserted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert data']);
        }
    }

    public function insertDataRMFB()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        $sql = "INSERT INTO rmfb_tbl (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values .= "'$value', ";
        }
        $sql = rtrim($sql, ", ") . ") ";
        $values = rtrim($values, ", ") . ")";
        $query = $sql . $values;
        $db = \Config\Database::connect();
        $result = $db->query($query);
        if ($result) {
            return $this->response->setStatusCode(200)->setJSON(['success' => 'Data inserted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert data']);
        }
    }
    
    public function insertDataOcci(){
        $request = service('request');
        $data = $request->getJSON(true);
        $sql = "INSERT INTO occidental_cps (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values .= "'$value', ";
        }
        $sql = rtrim($sql, ", ") . ") ";
        $values = rtrim($values, ", ") . ")";
        $query = $sql . $values;
        $db = \Config\Database::connect();
        $result = $db->query($query);
        if ($result) {
            return $this->response->setStatusCode(200)->setJSON(['success' => 'Data inserted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert data']);
        }
    }

    public function insertDataOrmin(){
        $request = service('request');
        $data = $request->getJSON(true);
        $sql = "INSERT INTO oriental_cps (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values .= "'$value', ";
        }
        $sql = rtrim($sql, ", ") . ") ";
        $values = rtrim($values, ", ") . ")";
        $query = $sql . $values;
        $db = \Config\Database::connect();
        $result = $db->query($query);
        if ($result) {
            return $this->response->setStatusCode(200)->setJSON(['success' => 'Data inserted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert data']);
        }
    }

    public function insertDataRom(){
        $request = service('request');
        $data = $request->getJSON(true);
        $sql = "INSERT INTO romblon_cps (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values .= "'$value', ";
        }
        $sql = rtrim($sql, ", ") . ") ";
        $values = rtrim($values, ", ") . ")";
        $query = $sql . $values;
        $db = \Config\Database::connect();
        $result = $db->query($query);
        if ($result) {
            return $this->response->setStatusCode(200)->setJSON(['success' => 'Data inserted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert data']);
        }
    }

    public function insertDataMar(){
        $request = service('request');
        $data = $request->getJSON(true);
        $sql = "INSERT INTO marinduque_cps (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values .= "'$value', ";
        }
        $sql = rtrim($sql, ", ") . ") ";
        $values = rtrim($values, ", ") . ")";
        $query = $sql . $values;
        $db = \Config\Database::connect();
        $result = $db->query($query);
        if ($result) {
            return $this->response->setStatusCode(200)->setJSON(['success' => 'Data inserted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert data']);
        }
    }

    public function insertDataPal(){
        $request = service('request');
        $data = $request->getJSON(true);
        $sql = "INSERT INTO palawan_cps (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values .= "'$value', ";
        }
        $sql = rtrim($sql, ", ") . ") ";
        $values = rtrim($values, ", ") . ")";
        $query = $sql . $values;
        $db = \Config\Database::connect();
        $result = $db->query($query);
        if ($result) {
            return $this->response->setStatusCode(200)->setJSON(['success' => 'Data inserted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert data']);
        }
    }

    public function insertDataPuer(){
        $request = service('request');
        $data = $request->getJSON(true);
        $sql = "INSERT INTO puertop_cps (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values .= "'$value', ";
        }
        $sql = rtrim($sql, ", ") . ") ";
        $values = rtrim($values, ", ") . ")";
        $query = $sql . $values;
        $db = \Config\Database::connect();
        $result = $db->query($query);
        if ($result) {
            return $this->response->setStatusCode(200)->setJSON(['success' => 'Data inserted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert data']);
        }
    }

    public function viewUserPPORates($userId)
    {
        if (!empty($userId)) {
            $db = \Config\Database::connect(); // Load the database connection

            // Use the database connection to execute the query
            $query = $db->query("SELECT * FROM ppo_cpo WHERE userid = ?", [$userId]);
            $userRatings = $query->getResultArray();

            if (!empty($userRatings)) {
                return $this->respond($userRatings, 200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id is required', 400);
        }
    }


    public function viewUserRMFBRates($userId)
    {
        if (!empty($userId)) {
            $db = \Config\Database::connect(); // Load the database connection

            // Use the database connection to execute the query
            $query = $db->query("SELECT * FROM rmfb_tbl WHERE userid = ?", [$userId]);
            $userRatings = $query->getResultArray();

            if (!empty($userRatings)) {
                return $this->respond($userRatings, 200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id is required', 400);
        }
    }

    public function viewUserOcciRates($userId)
    {
        if (!empty($userId)) {
            $db = \Config\Database::connect(); // Load the database connection

            // Use the database connection to execute the query
            $query = $db->query("SELECT * FROM occidental_cps WHERE userid = ?", [$userId]);
            $userRatings = $query->getResultArray();

            if (!empty($userRatings)) {
                return $this->respond($userRatings, 200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id is required', 400);
        }
    }

    public function viewUserOrienRates($userId)
    {
        if (!empty($userId)) {
            $db = \Config\Database::connect(); // Load the database connection

            // Use the database connection to execute the query
            $query = $db->query("SELECT * FROM oriental_cps WHERE userid = ?", [$userId]);
            $userRatings = $query->getResultArray();

            if (!empty($userRatings)) {
                return $this->respond($userRatings, 200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id is required', 400);
        }
    }

    public function viewUserMarinRates($userId)
    {
        if (!empty($userId)) {
            $db = \Config\Database::connect(); // Load the database connection

            // Use the database connection to execute the query
            $query = $db->query("SELECT * FROM marinduque_cps WHERE userid = ?", [$userId]);
            $userRatings = $query->getResultArray();

            if (!empty($userRatings)) {
                return $this->respond($userRatings, 200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id is required', 400);
        }
    }

    public function viewUserRombRates($userId)
    {
        if (!empty($userId)) {
            $db = \Config\Database::connect(); // Load the database connection

            // Use the database connection to execute the query
            $query = $db->query("SELECT * FROM romblon_cps WHERE userid = ?", [$userId]);
            $userRatings = $query->getResultArray();

            if (!empty($userRatings)) {
                return $this->respond($userRatings, 200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id is required', 400);
        }
    }

    public function viewUserPalRates($userId)
    {
        if (!empty($userId)) {
            $db = \Config\Database::connect(); // Load the database connection

            // Use the database connection to execute the query
            $query = $db->query("SELECT * FROM palawan_cps WHERE userid = ?", [$userId]);
            $userRatings = $query->getResultArray();

            if (!empty($userRatings)) {
                return $this->respond($userRatings, 200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id is required', 400);
        }
    }

    public function viewUserPuertoRates($userId)
    {
        if (!empty($userId)) {
            $db = \Config\Database::connect(); // Load the database connection

            // Use the database connection to execute the query
            $query = $db->query("SELECT * FROM puertop_cps WHERE userid = ?", [$userId]);
            $userRatings = $query->getResultArray();

            if (!empty($userRatings)) {
                return $this->respond($userRatings, 200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id is required', 400);
        }
    }

    public function sendPasswordResetEmail()
    {
        $json = $this->request->getJSON();
        $email = $json->email;

        // Check if the email exists in your database
        $userModel = new MainModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->respond('Email not found',400);
        }else{
             // Generate a unique token and save it with expiration time
        $token = bin2hex(random_bytes(32));
        $expiration = Time::now()->addMinutes(30); // Token expiration time (30 minutes from now)

        // Save the token and expiration time in the database
        $userModel->update($user['user_id'], [
            'reset_token' => $token,
            'token_expires_at' => $expiration->toDateTimeString()
        ]);

        // Send the password reset email with token
        $this->sendResetEmail($email, $token);

        return $this->respond(['message' => 'Password reset email sent'], 200);
        }

       
    }


    protected function sendResetEmail($email, $token)
{
    $emailConfig = config('Email');
    $emailService = Services::email();
    $emailService->initialize($emailConfig);

    $emailService->setFrom('your_email@example.com', 'Your Name');
    $emailService->setTo($email);
    $emailService->setSubject('Reset Your Password');

    // Replace 'http://your_frontend_url/reset-password' with your actual frontend reset password URL
    $resetLink = 'http://localhost:8081/resetPassword/' . $token;

    $emailService->setMessage("Click this link to reset your password: $resetLink");

    try {
        $emailService->send();
    } catch (\Exception $e) {
        // Handle email sending failure
        log_message('error', 'Email sending failed: ' . $emailService->printDebugger(['headers']));
        // You can also throw an exception to handle it in the frontend
        throw new \Exception('Failed to send password reset email');
    }
}

public function resetPassword()
    {
        $json = $this->request->getJSON();
        $token = $json->token;
        $password = $json->password;

        // Validate token and update password
        $userModel = new MainModel();
        $user = $userModel->where('reset_token', $token)->first();

        if (!$user) {
            return $this->failNotFound('Invalid token');
        }

        // Update user password
        $userModel->update($user['user_id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT), // Hash the new password
            'reset_token' => null, // Clear the reset token after password reset
            'token_expires_at' => null,
        ]);

        return $this->respond(['message' => 'Password reset successfully'], 200);
    }


}