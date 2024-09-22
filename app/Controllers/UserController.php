<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
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

    private function insertData($table, $data)
    {
        // Extract the values for UserId, Month, and Year
        $userId = $data['UserId'];
        $month = $data['Month'];
        $year = $data['Year'];
        
        // Check if the record with the same UserId, Month, and Year already exists
        $db = \Config\Database::connect();
        $builder = $db->table($table);
        $existingRecord = $builder->where('userId', $userId)
                                  ->where('month', $month)
                                  ->where('year', $year)
                                  ->get()
                                  ->getRow();
        
        if ($existingRecord) {
            // If record exists, return an error response
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Record already exists']);
        }
    
        // Construct the insert query if the record does not exist
        $sql = "INSERT INTO $table (";
        $values = "VALUES (";
        foreach ($data as $key => $value) {
            $sql .= "`$key`, ";
            $values .= "'$value', ";
        }
        $sql = rtrim($sql, ", ") . ") ";
        $values = rtrim($values, ", ") . ")";
        $query = $sql . $values;
    
        // Execute the query
        $result = $db->query($query);
        
        if ($result) {
            return $this->response->setStatusCode(200)->setJSON(['success' => 'Data inserted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to insert data']);
        }
    }

    public function insertDataPPO()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        return $this->insertData('ppo_cpo', $data);
    }

    
    public function insertDataRMFB()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        return $this->insertData('rmfb_tbl', $data);
    }

    public function insertDataOcci()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        return $this->insertData('occidental_cps', $data);
    }

    public function insertDataOrmin()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        return $this->insertData('oriental_cps', $data);
    }

    public function insertDataRom()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        return $this->insertData('romblon_cps', $data);
    }

    public function insertDataMar()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        return $this->insertData('marinduque_cps', $data);
    }

    public function insertDataPal()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        return $this->insertData('palawan_cps', $data);
    }

    public function insertDataPuer()
    {
        $request = service('request');
        $data = $request->getJSON(true);
        return $this->insertData('puertop_cps', $data);
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
            return $this->respond('Email not found',404);
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

    // protected function sendResetEmail($email, $token)
    // {
    //     $emailConfig = config('Email');
    //     $emailService = Services::email();
    //     $emailService->initialize($emailConfig);
    
    //     $emailService->setFrom('your_email@example.com', 'Your Name');
    //     $emailService->setTo($email);
    //     $emailService->setSubject('Reset Your Password');
    
    //     // Get the base URL from the configuration and construct the reset link
    //     $resetLink = 'https://e-upper.online/resetPassword/' . $token;
    
    //     $emailService->setMessage("Click this link to reset your password: $resetLink");
    
    //     try {
    //         $emailService->send();
    //     } catch (\Exception $e) {
    //         // Handle email sending failure
    //         log_message('error', 'Email sending failed: ' . $emailService->printDebugger(['headers']));
    //         // You can also throw an exception to handle it in the frontend
    //         throw new \Exception('Failed to send password reset email');
    //     }
    // }

    protected function sendResetEmail($email, $token)
    {
        $emailConfig = config('Email');
        $emailService = Services::email();
        $emailService->initialize($emailConfig);

        $emailService->setFrom('euperadministrator@gmail.com', 'EUPER SYSTEM');
        $emailService->setTo($email);
        $emailService->setSubject('Reset Your Password');

        // Construct the reset link
        $resetLink = 'http://localhost:8081/resetPassword/' . $token;
        // $resetLink = 'https://e-upper.online/resetPassword/' . $token;

        // Create the email body using the template
        // Create the email body using HTML format
        $message = "
        <p>Dear User,</p>
        <p>We received a request to reset the password for your account. If you didn’t make this request, you can ignore this email.</p>
        <p>To reset your password, please click the link below:</p>
        <p><a href=\"$resetLink\">Set a new password</a></p>
        <p>For security reasons, this link will expire in 24 hours. If the link expires, you can request a new password reset.</p>
        <p>If you encounter any issues, feel free to contact our support team.</p>
        <p>Thank you,<br>Euper System</p>
            ";

        // Set the email message and mail type
        $emailService->setMessage($message);
        $emailService->setMailType('html');  // This ensures HTML formatting

        try {
            $emailService->send();
        } catch (\Exception $e) {
            // Handle email sending failure
            log_message('error', 'Email sending failed: ' . $emailService->printDebugger(['headers']));
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

    public function getMaxRateByUser(){
        $json = $this->request->getJSON();
        $userId = $json->UserId;

        $model = new MainModel();
        $result = $model->where("user_id", $userId)->first();

        if($result){
            return $this->respond($result, 200);
        }else{
            return $this->respond(401);
        }
    }

    // public function sendPasswordResetEmail()
    // {
    //     $json = $this->request->getJSON();
    //     $email = $json->email;

    //     // Check if email exists
    //     $userModel = new MainModel();
    //     $user = $userModel->where('email', $email)->first();

    //     if (!$user) {
    //         return $this->respond(['status' => false, 'message' => 'Email not found'], 404);
    //     }

    //     // Generate OTP (6 digits)
    //     $otp = random_int(100000, 999999);

    //     $expiration = Time::now()->addMinutes(30); // Token expiration time (30 minutes from now)

    //     // Save the token and expiration time in the database
    //     $userModel->update($user['user_id'], [
    //         'reset_token' => $otp,
    //         'token_expires_at' => $expiration->toDateTimeString()
    //     ]);

    //     // Send the password reset email with token
    //     $this->sendResetEmail($email, $otp);

    //     return $this->respond(['message' => 'Password reset email sent'], 200);
    // }

    // protected function sendResetEmail($email, $otp)
    // {
    //     $emailConfig = config('Email');
    //     $emailService = Services::email();
    //     $emailService->initialize($emailConfig);
    
    //     $emailService->setFrom('your_email@example.com', 'Your Name');
    //     $emailService->setTo($email);
    //     $emailService->setSubject('Reset Your Password');
    
    //     $emailService->setMessage("This is the OTP: $otp you requested");
    
    //     try {
    //         $emailService->send();
    //     } catch (\Exception $e) {
    //         // Handle email sending failure
    //         log_message('error', 'Email sending failed: ' . $emailService->printDebugger(['headers']));
    //         // You can also throw an exception to handle it in the frontend
    //         throw new \Exception('Failed to send password reset email');
    //     }
    // }
    

    // public function verifyOtp()
    // {
    //     $json = $this->request->getJSON();
    //     $otp = $json->otp;

    //     // Validate the OTP
    //     $userModel = new MainModel();
    //     $user = $userModel->where('reset_token', $otp)->first();

    //     if (!$user) {
    //         return $this->respond(['message' => 'Invalid OTP'], 400);
    //     }

    //     // Check if OTP is expired
    //     if (strtotime($user['token_expires_at']) < time()) {
    //         return $this->respond(['message' => 'OTP expired'], 400);
    //     }

    //     // OTP is valid, allow user to proceed to reset password
    //     return $this->respond(['message' => 'OTP verified, proceed to reset your password'], 200);
    // }

    // public function resetPassword()
    // {
    //     $json = $this->request->getJSON();
    //     $token = $json->token;
    //     $password = $json->password;

    //     // Validate token and update password
    //     $userModel = new MainModel();
    //     $user = $userModel->where('reset_token', $token)->first();

    //     if (!$user) {
    //         return $this->failNotFound('Invalid token');
    //     }

    //     // Update user password
    //     $userModel->update($user['user_id'], [
    //         'password' => password_hash($password, PASSWORD_DEFAULT), // Hash the new password
    //         'reset_token' => null, // Clear the reset token after password reset
    //         'token_expires_at' => null,
    //     ]);

    //     return $this->respond(['message' => 'Password reset successfully'], 200);
    // }


}