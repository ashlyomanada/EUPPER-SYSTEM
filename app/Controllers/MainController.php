<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MainModel;
use App\Models\AdminModel;
use App\Models\PpoModel;
use App\Models\RatingModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use App\Models\DueModel;

class MainController extends ResourceController
{
    public function index(){
        return view('upload');
    }
    
    public function login()
    {
        try {
            $json = $this->request->getJSON();
    
            if (isset($json->email) && isset($json->password)) {
                $email = $json->email;
                $password = $json->password;
    
                $userModel = new MainModel();
                $data = $userModel->where('email', $email)->first();
    
                if ($data) {
                    $pass = $data['password'];
                    $auth = password_verify($password, $pass);
    
                    if ($auth) {
                        // Check user role and send appropriate response
                        $role = $data['role'];
                        return $this->respond(['message' => 'Login successful', 'id' => $data['user_id'], 'role' => $role], 200);
                    } else {
                        return $this->respond(['message' => 'Invalid email or password'], 401);
                    }
                } else {
                    return $this->respond(['message' => 'Invalid email or password'], 401);
                }
            } else {
                return $this->respond(['message' => 'Invalid JSON data'], 400);
            }
        } catch (\Exception $e) {
            // Log the exception for debugging
            log_message('error', 'Exception in login: ' . $e->getMessage());
            return $this->respond(['message' => 'Internal Server Error'], 500);
        }
    }
    
    public function upload()
    {
        $request = $this->request;

        // Debugging to check what data is received
        log_message('debug', print_r($request->getPost(), true));
        log_message('debug', print_r($_FILES, true));
        // Get the file
        $file = $request->getFile('file');

        // Move the file to the writable/uploads directory
        $uploadsDirectory = FCPATH . 'uploads';  // Correct path using FCPATH

        // Check if the directory exists, if not, create it
        if (!is_dir($uploadsDirectory)) {
            mkdir($uploadsDirectory, 0777, true);
        }

        $file->move($uploadsDirectory);

        // Insert the user information into the database
        $filePath = 'uploads/' . $file->getName();
        
        $userData = [
            'username' => $request->getPost('username'),
            'password' => password_hash($request->getPost('password'), PASSWORD_DEFAULT),
            'office' => $request->getPost('office'),
            'phone_no' => $request->getPost('phone_no'),
            'email' => $request->getPost('email'),
            'image' => $filePath,
            'status' => 'Enable',
            'role' => 'user',
            'reset_token' => null,
            'token_expires_at' => null,
            'officeType' => $request->getPost('officeType'),
        ];

        // Assuming you have a model named MainModel, you can use it to insert data into the database
        $main = new MainModel();
        $main->insert($userData);

        // Redirect back to the form with a success message
        return redirect()->to('/')->with('success', 'Registration successful!');
    }

    public function uploadAdmin()
    {
        $request = $this->request;

        // Debugging to check what data is received
        log_message('debug', print_r($request->getPost(), true));
        log_message('debug', print_r($_FILES, true));
        // Get the file
        $file = $request->getFile('file');

        // Move the file to the writable/uploads directory
        $uploadsDirectory = FCPATH . 'uploads';  // Correct path using FCPATH

        // Check if the directory exists, if not, create it
        if (!is_dir($uploadsDirectory)) {
            mkdir($uploadsDirectory, 0777, true);
        }

        $file->move($uploadsDirectory);

        // Insert the user information into the database
        $filePath = 'uploads/' . $file->getName();
        
        $userData = [
            'username' => $request->getPost('username'),
            'password' => password_hash($request->getPost('password'), PASSWORD_DEFAULT),
            'office' => $request->getPost('office'),
            'phone_no' => $request->getPost('phone_no'),
            'email' => $request->getPost('email'),
            'image' => $filePath,
            'status' => 'Enable',
            'role' => 'admin',
            'reset_token' => null,
            'token_expires_at' => null,
            'officeType' => 'Admin Office',
        ];

        // Assuming you have a model named MainModel, you can use it to insert data into the database
        $main = new MainModel();
        $main->insert($userData);

        // Redirect back to the form with a success message
        return redirect()->to('/')->with('success', 'Registration successful!');
    }

    public function uploadProfile()
    {
        $request = $this->request;
    
        // Debugging to check what data is received
        log_message('debug', print_r($request->getPost(), true));
        log_message('debug', print_r($_FILES, true));
    
        // Get the file
        $file = $request->getFile('file');
    
        // Move the file to the writable/uploads directory
        $uploadsDirectory = FCPATH . 'uploads';  // Correct path using FCPATH
    
        // Check if the directory exists, if not, create it
        if (!is_dir($uploadsDirectory)) {
            mkdir($uploadsDirectory, 0777, true);
        }
    
        // Move the file to the specified directory
        if ($file->isValid() && !$file->hasMoved()) {
            $file->move($uploadsDirectory);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File upload failed.']);
        }
    
        // Get the file path
        $filePath = 'uploads/' . $file->getName();
    
        // Get the user ID from the request
        $userId = $request->getPost('userId');
        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User ID is required.']);
        }
    
        // Update the user information in the database
        $userData = [
            'image' => $filePath,
        ];
    
        // Assuming you have a model named MainModel, you can use it to update the user data
        $main = new MainModel();
    
        // Update the user record
        if ($main->update($userId, $userData)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Profile picture updated successfully!', 'profilePicPath' => $filePath]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update profile picture.']);
        }
    }
    

    public function sendEmail()
    {
        try {
            // Get JSON data from the request
            $formData = $this->request->getJSON();

            // Define validation rules
            $validationRules = [
                'sender' => 'required|valid_email',
                'message' => 'required|string|min_length[5]'
            ];

            // Validate the request data
            if (!$this->validate($validationRules)) {
                // If validation fails, return errors
                return $this->response->setJSON([
                    'error' => 'Validation failed',
                    'messages' => $this->validator->getErrors()
                ]);
            }

            // Load the email library
            $email = \Config\Services::email();
            $model = new MainModel();
            $adminEmails = $model->where('role', 'admin')->findAll();

            // Check if there are admin emails
            if (empty($adminEmails)) {
                return $this->response->setJSON(['error' => 'No admin email addresses found.']);
            }

            // Add each admin email address to the recipients
            foreach ($adminEmails as $admin) {
                $email->setTo($admin['email']);
                $email->setFrom($formData->sender);
                $email->setSubject('Request Form from ' . $formData->username);
                $message = $formData->message;
                $email->setMessage($message);

                // Send email to each admin
                if (!$email->send()) {
                    log_message('error', 'Email failed to send to ' . $admin['email'] . '. Error: ' . $email->printDebugger(['headers']));
                    return $this->response->setJSON(['error' => 'Email failed to send to some recipients.']);
                }
            }

            return $this->response->setJSON(['message' => 'Emails sent successfully.']);
        } catch (\Exception $e) {
            // Log other exceptions
            log_message('error', 'Exception: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Internal Server Error']);
        }
    }

    public function getAllAverageRatesPPO($year)
    {
        $json = $this->request->getJSON();
            $ratingModel = new \App\Models\RatingModel();
        
        $db = \Config\Database::connect();

        $table = 'ppo_cpo';
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        
        $iterate = 1;
        $totalsByOffice = []; // Array to store total sum for each office
        
        foreach($columns as $column) {
            $columnName = $column['Field'];
        
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
        
            $officeTotal = $ratingModel->selectSum('total')
                                       ->where('year', $year)
                                       ->where('level', 'PPO')
                                       ->where('foreignOfficeId', $iterate)
                                       ->first(); // Use first() to retrieve a single row
        
            // Store the total sum for the current office
            $totalsByOffice[$columnName] = number_format($officeTotal['total'] / 12, 2);
            $iterate++;
        }
        
        $responseData = [
            'totalsByOffice' => $totalsByOffice,
        ];
        
        return $this->respond($responseData, 200);
        
    }

    public function getAllAverageRatesRMFB($year)
    {
        $json = $this->request->getJSON();
            $ratingModel = new \App\Models\RatingModel();
        
        $db = \Config\Database::connect();

        $table = 'rmfb_tbl';
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        
        $iterate = 1;
        $totalsByOffice = []; // Array to store total sum for each office
        
        foreach($columns as $column) {
            $columnName = $column['Field'];
        
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
        
            $officeTotal = $ratingModel->selectSum('total')
                                       ->where('year', $year)
                                       ->where('level', 'RMFB')
                                       ->where('foreignOfficeId', $iterate)
                                       ->first(); // Use first() to retrieve a single row
        
            // Store the total sum for the current office
            $totalsByOffice[$columnName] = number_format($officeTotal['total'] / 12, 2);
            $iterate++;
        }
        
        $responseData = [
            'totalsByOffice' => $totalsByOffice,
        ];
        
        return $this->respond($responseData, 200);
        
    }

    public function getAllAverageRatesOccidental($year)
    {
        $json = $this->request->getJSON();
            $ratingModel = new \App\Models\RatingModel();
        
        $db = \Config\Database::connect();

        $table = 'occidental_cps';
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        
        $iterate = 1;
        $totalsByOffice = []; // Array to store total sum for each office
        
        foreach($columns as $column) {
            $columnName = $column['Field'];
        
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
        
            $officeTotal = $ratingModel->selectSum('total')
                                       ->where('year', $year)
                                       ->where('level', 'Occidental')
                                       ->where('foreignOfficeId', $iterate)
                                       ->first(); // Use first() to retrieve a single row
        
            // Store the total sum for the current office
            $totalsByOffice[$columnName] = number_format($officeTotal['total'] / 12, 2);
            $iterate++;
        }
        
        $responseData = [
            'totalsByOffice' => $totalsByOffice,
        ];
        
        return $this->respond($responseData, 200);
        
    }

    public function getAllAverageRatesOriental($year)
    {
        $json = $this->request->getJSON();
            $ratingModel = new \App\Models\RatingModel();
        
        $db = \Config\Database::connect();

        $table = 'oriental_cps';
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        
        $iterate = 1;
        $totalsByOffice = []; // Array to store total sum for each office
        
        foreach($columns as $column) {
            $columnName = $column['Field'];
        
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
        
            $officeTotal = $ratingModel->selectSum('total')
                                       ->where('year', $year)
                                       ->where('level', 'Oriental')
                                       ->where('foreignOfficeId', $iterate)
                                       ->first(); // Use first() to retrieve a single row
        
            // Store the total sum for the current office
            $totalsByOffice[$columnName] = number_format($officeTotal['total'] / 12, 2);
            $iterate++;
        }
        
        $responseData = [
            'totalsByOffice' => $totalsByOffice,
        ];
        
        return $this->respond($responseData, 200);
        
    }

    public function getAllAverageRatesMarinduque($year)
    {
        $json = $this->request->getJSON();
            $ratingModel = new \App\Models\RatingModel();
        
        $db = \Config\Database::connect();

        $table = 'marinduque_cps';
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        
        $iterate = 1;
        $totalsByOffice = []; // Array to store total sum for each office
        
        foreach($columns as $column) {
            $columnName = $column['Field'];
        
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
        
            $officeTotal = $ratingModel->selectSum('total')
                                       ->where('year', $year)
                                       ->where('level', 'Marindque')
                                       ->where('foreignOfficeId', $iterate)
                                       ->first(); // Use first() to retrieve a single row
        
            // Store the total sum for the current office
            $totalsByOffice[$columnName] = number_format($officeTotal['total'] / 12, 2);
            $iterate++;
        }
        
        $responseData = [
            'totalsByOffice' => $totalsByOffice,
        ];
        
        return $this->respond($responseData, 200);
        
    }

    public function getAllAverageRatesRomblon($year)
    {
        $json = $this->request->getJSON();
            $ratingModel = new \App\Models\RatingModel();
        
        $db = \Config\Database::connect();

        $table = 'romblon_cps';
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        
        $iterate = 1;
        $totalsByOffice = []; // Array to store total sum for each office
        
        foreach($columns as $column) {
            $columnName = $column['Field'];
        
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
        
            $officeTotal = $ratingModel->selectSum('total')
                                       ->where('year', $year)
                                       ->where('level', 'Romblon')
                                       ->where('foreignOfficeId', $iterate)
                                       ->first(); // Use first() to retrieve a single row
        
            // Store the total sum for the current office
            $totalsByOffice[$columnName] = number_format($officeTotal['total'] / 12, 2);
            $iterate++;
        }
        
        $responseData = [
            'totalsByOffice' => $totalsByOffice,
        ];
        
        return $this->respond($responseData, 200);
        
    }

    public function getAllAverageRatesPalawan($year)
    {
        $json = $this->request->getJSON();
            $ratingModel = new \App\Models\RatingModel();
        
        $db = \Config\Database::connect();

        $table = 'palawan_cps';
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        
        $iterate = 1;
        $totalsByOffice = []; // Array to store total sum for each office
        
        foreach($columns as $column) {
            $columnName = $column['Field'];
        
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
        
            $officeTotal = $ratingModel->selectSum('total')
                                       ->where('year', $year)
                                       ->where('level', 'Palawan')
                                       ->where('foreignOfficeId', $iterate)
                                       ->first(); // Use first() to retrieve a single row
        
            // Store the total sum for the current office
            $totalsByOffice[$columnName] = number_format($officeTotal['total'] / 12, 2);
            $iterate++;
        }
        
        $responseData = [
            'totalsByOffice' => $totalsByOffice,
        ];
        
        return $this->respond($responseData, 200);
        
    }

    public function getAllAverageRatesPuerto($year)
    {
        $json = $this->request->getJSON();
            $ratingModel = new \App\Models\RatingModel();
        
        $db = \Config\Database::connect();

        $table = 'puertop_cps';
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        
        $iterate = 1;
        $totalsByOffice = []; // Array to store total sum for each office
        
        foreach($columns as $column) {
            $columnName = $column['Field'];
        
            if (in_array($columnName, ['id', 'userid', 'month', 'year'])) {
                continue;
            }
        
            $officeTotal = $ratingModel->selectSum('total')
                                       ->where('year', $year)
                                       ->where('level', 'Puerto')
                                       ->where('foreignOfficeId', $iterate)
                                       ->first(); // Use first() to retrieve a single row
        
            // Store the total sum for the current office
            $totalsByOffice[$columnName] = number_format($officeTotal['total'] / 12, 2);
            $iterate++;
        }
        
        $responseData = [
            'totalsByOffice' => $totalsByOffice,
        ];
        
        return $this->respond($responseData, 200);
        
    }


    public function getAllRatesPPO()
    {
        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all ratings from the database
        $data = $ratingModel->where('level','PPO')->findAll();
        
        // Sort the ratings array by the 'total' field in descending order (highest to lowest)
        usort($data, function($a, $b) {
            // Convert 'total' values to float for numeric comparison
            $totalA = floatval($a['total']);
            $totalB = floatval($b['total']);
            
            // Compare 'total' values to determine order (descending)
            if ($totalA < $totalB) {
                return 1; // $a should come after $b (descending order)
            } elseif ($totalA > $totalB) {
                return -1; // $a should come before $b (descending order)
            } else {
                return 0; // $a and $b are equal
            }
        });

        // Return sorted data in the response
        return $this->respond($data, 200);
    }

    public function getAllRatesRMFB()
    {
        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all ratings from the database
        $data = $ratingModel->where('level','RMFB')->findAll();
        
        // Sort the ratings array by the 'total' field in descending order (highest to lowest)
        usort($data, function($a, $b) {
            // Convert 'total' values to float for numeric comparison
            $totalA = floatval($a['total']);
            $totalB = floatval($b['total']);
            
            // Compare 'total' values to determine order (descending)
            if ($totalA < $totalB) {
                return 1; // $a should come after $b (descending order)
            } elseif ($totalA > $totalB) {
                return -1; // $a should come before $b (descending order)
            } else {
                return 0; // $a and $b are equal
            }
        });

        // Return sorted data in the response
        return $this->respond($data, 200);
    }

    public function getAllRatesOccidental()
    {
        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all ratings from the database
        $data = $ratingModel->where('level','Occidental')->findAll();
        
        // Sort the ratings array by the 'total' field in descending order (highest to lowest)
        usort($data, function($a, $b) {
            // Convert 'total' values to float for numeric comparison
            $totalA = floatval($a['total']);
            $totalB = floatval($b['total']);
            
            // Compare 'total' values to determine order (descending)
            if ($totalA < $totalB) {
                return 1; // $a should come after $b (descending order)
            } elseif ($totalA > $totalB) {
                return -1; // $a should come before $b (descending order)
            } else {
                return 0; // $a and $b are equal
            }
        });

        // Return sorted data in the response
        return $this->respond($data, 200);
    }

    public function getAllRatesOriental()
    {
        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all ratings from the database
        $data = $ratingModel->where('level','Oriental')->findAll();
        
        // Sort the ratings array by the 'total' field in descending order (highest to lowest)
        usort($data, function($a, $b) {
            // Convert 'total' values to float for numeric comparison
            $totalA = floatval($a['total']);
            $totalB = floatval($b['total']);
            
            // Compare 'total' values to determine order (descending)
            if ($totalA < $totalB) {
                return 1; // $a should come after $b (descending order)
            } elseif ($totalA > $totalB) {
                return -1; // $a should come before $b (descending order)
            } else {
                return 0; // $a and $b are equal
            }
        });

        // Return sorted data in the response
        return $this->respond($data, 200);
    }

    public function getAllRatesMarinduque()
    {
        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all ratings from the database
        $data = $ratingModel->where('level','Marinduque')->findAll();
        
        // Sort the ratings array by the 'total' field in descending order (highest to lowest)
        usort($data, function($a, $b) {
            // Convert 'total' values to float for numeric comparison
            $totalA = floatval($a['total']);
            $totalB = floatval($b['total']);
            
            // Compare 'total' values to determine order (descending)
            if ($totalA < $totalB) {
                return 1; // $a should come after $b (descending order)
            } elseif ($totalA > $totalB) {
                return -1; // $a should come before $b (descending order)
            } else {
                return 0; // $a and $b are equal
            }
        });

        // Return sorted data in the response
        return $this->respond($data, 200);
    }

    public function getAllRatesRomblon()
    {
        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all ratings from the database
        $data = $ratingModel->where('level','Romblon')->findAll();
        
        // Sort the ratings array by the 'total' field in descending order (highest to lowest)
        usort($data, function($a, $b) {
            // Convert 'total' values to float for numeric comparison
            $totalA = floatval($a['total']);
            $totalB = floatval($b['total']);
            
            // Compare 'total' values to determine order (descending)
            if ($totalA < $totalB) {
                return 1; // $a should come after $b (descending order)
            } elseif ($totalA > $totalB) {
                return -1; // $a should come before $b (descending order)
            } else {
                return 0; // $a and $b are equal
            }
        });

        // Return sorted data in the response
        return $this->respond($data, 200);
    }

    public function getAllRatesPalawan()
    {
        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all ratings from the database
        $data = $ratingModel->where('level','Palawan')->findAll();
        
        // Sort the ratings array by the 'total' field in descending order (highest to lowest)
        usort($data, function($a, $b) {
            // Convert 'total' values to float for numeric comparison
            $totalA = floatval($a['total']);
            $totalB = floatval($b['total']);
            
            // Compare 'total' values to determine order (descending)
            if ($totalA < $totalB) {
                return 1; // $a should come after $b (descending order)
            } elseif ($totalA > $totalB) {
                return -1; // $a should come before $b (descending order)
            } else {
                return 0; // $a and $b are equal
            }
        });

        // Return sorted data in the response
        return $this->respond($data, 200);
    }

    public function getAllRatesPuerto()
    {
        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all ratings from the database
        $data = $ratingModel->where('level','Puerto')->findAll();
        
        // Sort the ratings array by the 'total' field in descending order (highest to lowest)
        usort($data, function($a, $b) {
            // Convert 'total' values to float for numeric comparison
            $totalA = floatval($a['total']);
            $totalB = floatval($b['total']);
            
            // Compare 'total' values to determine order (descending)
            if ($totalA < $totalB) {
                return 1; // $a should come after $b (descending order)
            } elseif ($totalA > $totalB) {
                return -1; // $a should come before $b (descending order)
            } else {
                return 0; // $a and $b are equal
            }
        });

        // Return sorted data in the response
        return $this->respond($data, 200);
    }


    public function updateUserRating()
    {
        $requestData = $this->request->getJSON();

        if (empty($requestData->id)) {
            return $this->fail('Rating ID is required', 400);
        }

        if (empty($requestData->TableName)) {
            return $this->fail('Table Name is required', 400);
        }

        $tableName = $requestData->TableName;
        $db = \Config\Database::connect(); // Load the database connection

        // Prepare the update query
        $query = "UPDATE $tableName SET ";
        $params = [];
        $firstParam = true;

        foreach ($requestData as $key => $value) {
            if ($key !== 'id' && $key !== 'TableName') { // Exclude 'id' and 'TableName' from update fields
                if (!$firstParam) {
                    $query .= ", ";
                }
                $query .= "$key = ?";
                $params[] = $value;
                $firstParam = false;
            }
        }

        $query .= " WHERE id = ?";
        $params[] = $requestData->id;

        // Execute the update query
        try {
            $result = $db->query($query, $params);
            if ($db->affectedRows() > 0) {
                return $this->respond(['success' => true], 200);
            } else {
                return $this->failNotFound('Rating not found or no changes made');
            }
        } catch (\Exception $e) {
            return $this->failServerError('Error updating rating: ' . $e->getMessage());
        }
    }

    public function getColumnNamePerTbl()
    {
        $json = $this->request->getJSON();
        $tableName = $json->TableName;
        $db = db_connect();
        $table = $tableName; 
        $query = $db->query("DESCRIBE $table");
        $columns = $query->getResultArray();
        $columnNames = array_column($columns, 'Field');
        return $this->response->setJSON($columnNames);
    }

    public function viewUserByTblRates()
    {
        $json = $this->request->getJSON();
        $userId = $json->User;
        $tableName = $json->TableName;

        if (!empty($userId) && !empty($tableName)) {
            $db = \Config\Database::connect(); // Load the database connection

            // Use the database connection to execute the query
            $query = $db->query("SELECT * FROM $tableName WHERE userid = ?", [$userId]);
            $userRatings = $query->getResultArray();

            if (!empty($userRatings)) {
                return $this->respond($userRatings, 200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id and TableName are required', 400);
        }
    }
    
    public function sendSMS()
    {
        // Retrieve JSON payload sent from Vue.js frontend
        $json = $this->request->getJSON();

        // Extract required data from JSON payload
        $senderName = "Euper";
        $phoneNumber = $json->recipient;
        $message = $json->message;

        $apiKey = 'fdc0fc4b3cf35557937435f41a072b43-ab235ced-7004-4b69-b1b1-166957845f9b';
        $url = 'https://w1eley.api.infobip.com/sms/2/text/advanced';

        $client = new Client([
            'verify' => false, // Disable SSL certificate verification (use with caution)
        ]);

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'App ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'messages' => [
                        [
                            'from' => $senderName,
                            'destinations' => [
                                ['to' => $phoneNumber]
                            ],
                            'text' => $message
                        ]
                    ]
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($statusCode == 200) {
                return $this->respond([
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'response' => $body
                ], 200);
            } else {
                return $this->failServerError('Unexpected HTTP status: ' . $statusCode);
            }
        } catch (RequestException $e) {
            return $this->failServerError('Error: ' . $e->getMessage());
        }
    }

    public function sendSMSToUser()
    {
        $json = $this->request->getJSON();
        $phoneNumber = $json->recipient;
        $message = $json->message;

        $apiKey = 'ef0a9cf7d5bf8f4b43bbdac91a2f1276'; // Replace 'YOUR_API_KEY' with your Semaphore API key

        $parameters = [
            'apikey' => $apiKey,
            'number' =>  $phoneNumber, // Replace with recipient's phone number
            'message' => $message,
            'sendername' => 'SEMAPHORE'
        ];

        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disable SSL certificate verification (not recommended for production)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Execute cURL request
        $output = curl_exec($ch);

        // Close cURL resource
        curl_close($ch);

        // Check if the SMS was sent successfully
        if ($output !== false) {
            // SMS sent successfully
            $response = [
                'success' => true,
                'message' => 'SMS sent successfully.'
            ];

            // Set response status code to 200
            return $this->response->setStatusCode(200)->setJSON($response);
        } else {
            // Failed to send SMS
            $response = [
                'success' => false,
                'message' => 'Failed to send SMS.'
            ];

            // Set response status code to 500 (Internal Server Error)
            return $this->response->setStatusCode(500)->setJSON($response);
        }
    }

    public function sendSMSToAllUser()
    {
        // Load the UserModel
        $userModel = new MainModel();
        $json = $this->request->getJSON();
        // Fetch phone numbers of all users from the database
        $phoneNumbers = $userModel->getAllPhoneNumbers();
        $apiKey = 'ef0a9cf7d5bf8f4b43bbdac91a2f1276'; // Replace 'YOUR_API_KEY' with your Semaphore API key
        // Initialize array to store responses
        $responses = [];
        // Iterate over each phone number and send SMS
        foreach ($phoneNumbers as $user) {
            $phoneNumber = $user['phone_no'];
            $parameters = [
                'apikey' => $apiKey,
                'number' =>  $phoneNumber,
                'message' => $json->message,
                'sendername' => 'SEMAPHORE'
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disable SSL certificate verification (not recommended for production)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Execute cURL request
            $output = curl_exec($ch);

            // Close cURL resource
            curl_close($ch);

            // Check if the SMS was sent successfully
            if ($output !== false) {
                // SMS sent successfully
                $responses[] = [
                    'success' => true,
                    'message' => "SMS sent successfully to {$phoneNumber}."
                ];
            } else {
                // Failed to send SMS
                $responses[] = [
                    'success' => false,
                    'message' => "Failed to send SMS to {$phoneNumber}."
                ];
            }
        }

        return $this->respond($responses, 200);
    }

    public function insertDue()
    {
        $json = $this->request->getJSON();
        $dueDate = $json->dueDate;

        $model = new DueModel();
        $data = [
            'date' => $dueDate,
        ];

        $model->insert($data);
        return $this->respond(200);
    }

    public function selectDue(){
        $model = new DueModel();
        // Get the last inserted record
        $lastInserted = $model->orderBy('id', 'DESC')->first();
        // You can also directly return the record if found
        if ($lastInserted) {
            return $this->respond($lastInserted, 200);
        } else {
            // Handle the case where no records are found
            return $this->failNotFound('No records found');
        }
    }
    

    public function getUserData($userId)
    {
        $main = new MainModel();
        $userData = $main->getUserById($userId);
        if ($userData) {
            return $this->response->setJSON($userData);
        } else {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }
    }


    public function getUserAdmin($userId)
    {
        $main = new AdminModel();
        $userData = $main->where('admin_id', $userId)->find();
        if ($userData) {
            return $this->response->setJSON($userData);
        } else {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }
    }

    public function saveProfile()
    {
        $data = $this->request->getJSON();
        $model = new MainModel();

        if ($data->userId) {
            // Update existing admin
            $model->update($data->userId, $data);
            $message = 'User updated successfully';
        } else {
            // Insert new admin
            $model->insert($data);
            $message = 'User saved successfully';
        }

        return $this->respond(['success' => true, 'message' => $message]);
    }

    public function viewUserRatings($userId)
    {
        $main = new PpoModel();
        $userData = $main->getForeignId($userId);
        if (count($userData->getResult()) > 0) {
            return $this->response->setJSON($userData->getResult());
        } else {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }
    }

}
    

    