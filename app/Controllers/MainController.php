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
    
    // public function upload()
    // {
    //     $request = $this->request;

    //     // Debugging to check what data is received
    //     log_message('debug', print_r($request->getPost(), true));
    //     log_message('debug', print_r($_FILES, true));
    //     // Get the file
    //     $file = $request->getFile('file');

    //     // Move the file to the writable/uploads directory
    //     $uploadsDirectory = FCPATH . 'uploads';  // Correct path using FCPATH

    //     // Check if the directory exists, if not, create it
    //     if (!is_dir($uploadsDirectory)) {
    //         mkdir($uploadsDirectory, 0777, true);
    //     }

    //     $file->move($uploadsDirectory);

    //     // Insert the user information into the database
    //     $filePath = 'uploads/' . $file->getName();
        
    //     $userData = [
    //         'username' => $request->getPost('username'),
    //         'password' => password_hash($request->getPost('password'), PASSWORD_DEFAULT),
    //         'office' => $request->getPost('office'),
    //         'phone_no' => $request->getPost('phone_no'),
    //         'email' => $request->getPost('email'),
    //         'image' => $filePath,
    //         'status' => 'Enable',
    //         'role' => 'user',
    //         'reset_token' => null,
    //         'token_expires_at' => null,
    //         'officeType' => $request->getPost('officeType'),
    //     ];

    //     // Assuming you have a model named MainModel, you can use it to insert data into the database
    //     $main = new MainModel();
    //     $main->insert($userData);

    //     // Redirect back to the form with a success message
    //     return redirect()->to('/')->with('success', 'Registration successful!');
    // }

    public function upload()
    {
        $request = $this->request;
        $main = new MainModel();

        // Sanitize user inputs
        $email = filter_var($request->getPost('email'), FILTER_SANITIZE_EMAIL);
        $username = filter_var($request->getPost('username'), FILTER_SANITIZE_STRING);
        $office = filter_var($request->getPost('office'), FILTER_SANITIZE_STRING);
        $phone_no = filter_var($request->getPost('phone_no'), FILTER_SANITIZE_STRING);
        $officeType = filter_var($request->getPost('officeType'), FILTER_SANITIZE_STRING);

        // Check if the email already exists
        $existingUser = $main->where('email', $email)->first();
        if ($existingUser) {
            // Log the event and return a JSON response with an error
            log_message('error', 'Email already exists for registration: ' . $email);
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Email already exists.']);
        }

        // Validate the file input
        $file = $request->getFile('file');
        if (!$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid file uploaded.']);
        }

        // Validate file type and size
        $validated = $this->validate([
            'file' => [
                'uploaded[file]',
                'mime_in[file,image/jpg,image/jpeg,image/png]', // Allow only image files
            
            ]
        ]);
        if (!$validated) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'File validation failed.']);
        }

        // Move the file to the writable/uploads directory
        $uploadsDirectory = FCPATH . 'uploads';
        if (!is_dir($uploadsDirectory)) {
            mkdir($uploadsDirectory, 0755, true); // Use 0755 permissions for better security
        }

        // Generate a unique filename to avoid overwriting existing files
        $newFileName = $file->getRandomName();
        $file->move($uploadsDirectory, $newFileName);
        $filePath = 'uploads/' . $newFileName;

        // Hash the password securely
        $passwordHash = password_hash($request->getPost('password'), PASSWORD_DEFAULT);

        // Prepare the user data for insertion
        $userData = [
            'username' => $username,
            'password' => $passwordHash,
            'office' => $office,
            'phone_no' => $phone_no,
            'email' => $email,
            'image' => $filePath,
            'status' => 'Enable',
            'role' => 'user',
            'reset_token' => null,
            'token_expires_at' => null,
            'officeType' => $officeType,
        ];

        // Insert user data into the database
        try {
            $main->insert($userData);
            // Log successful registration
            log_message('info', 'New user registered: ' . $email);
        } catch (\Exception $e) {
            // Log the error
            log_message('error', 'Database error during registration: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error. Please try again later.']);
        }

        // Return success response
        return $this->response->setJSON(['success' => 'Registration successful!']);
    }


    // public function uploadAdmin()
    // {
    //     $request = $this->request;

    //     // Debugging to check what data is received
    //     log_message('debug', print_r($request->getPost(), true));
    //     log_message('debug', print_r($_FILES, true));
    //     // Get the file
    //     $file = $request->getFile('file');

    //     // Move the file to the writable/uploads directory
    //     $uploadsDirectory = FCPATH . 'uploads';  // Correct path using FCPATH

    //     // Check if the directory exists, if not, create it
    //     if (!is_dir($uploadsDirectory)) {
    //         mkdir($uploadsDirectory, 0777, true);
    //     }

    //     $file->move($uploadsDirectory);

    //     // Insert the user information into the database
    //     $filePath = 'uploads/' . $file->getName();
        
    //     $userData = [
    //         'username' => $request->getPost('username'),
    //         'password' => password_hash($request->getPost('password'), PASSWORD_DEFAULT),
    //         'office' => $request->getPost('office'),
    //         'phone_no' => $request->getPost('phone_no'),
    //         'email' => $request->getPost('email'),
    //         'image' => $filePath,
    //         'status' => 'Enable',
    //         'role' => 'admin',
    //         'reset_token' => null,
    //         'token_expires_at' => null,
    //         'officeType' => 'Admin Office',
    //     ];

    //     // Assuming you have a model named MainModel, you can use it to insert data into the database
    //     $main = new MainModel();
    //     $main->insert($userData);

    //     // Redirect back to the form with a success message
    //     return redirect()->to('/')->with('success', 'Registration successful!');
    // }

    public function uploadAdmin()
    {
        $request = $this->request;
        $main = new MainModel();
    
        // Sanitize user inputs
        $email = filter_var($request->getPost('email'), FILTER_SANITIZE_EMAIL);
        $username = filter_var($request->getPost('username'), FILTER_SANITIZE_STRING);
        $office = filter_var($request->getPost('office'), FILTER_SANITIZE_STRING);
        $phone_no = filter_var($request->getPost('phone_no'), FILTER_SANITIZE_STRING);
    
        // Validate required fields
        if (empty($email) || empty($username) || empty($office) || empty($phone_no)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'All fields are required.']);
        }
    
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid email format.']);
        }
    
        // Check if the email already exists
        $existingUser = $main->where('email', $email)->first();
        if ($existingUser) {
            // Log the event and return an error response
            log_message('error', 'Email already exists for admin registration: ' . $email);
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Email already exists.']);
        }
    
        // Validate file input
        $file = $request->getFile('file');
        if (!$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid file uploaded.']);
        }
    
        // Validate file type and size
        $validated = $this->validate([
            'file' => [
                'uploaded[file]',
                'mime_in[file,image/jpg,image/jpeg,image/png]', // Allow only image files
               
            ]
        ]);
        if (!$validated) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'File validation failed.']);
        }
    
        // Move the file to the writable/uploads directory
        $uploadsDirectory = FCPATH . 'uploads';
        if (!is_dir($uploadsDirectory)) {
            mkdir($uploadsDirectory, 0755, true);  // Use 0755 for better security
        }
    
        // Generate a unique filename to avoid overwriting existing files
        $newFileName = $file->getRandomName();
        $file->move($uploadsDirectory, $newFileName);
        $filePath = 'uploads/' . $newFileName;
    
        // Secure password hashing
        $passwordHash = password_hash($request->getPost('password'), PASSWORD_DEFAULT);
    
        // Prepare the admin data for insertion
        $userData = [
            'username' => $username,
            'password' => $passwordHash,
            'office' => $office,
            'phone_no' => $phone_no,
            'email' => $email,
            'image' => $filePath,
            'status' => 'Enable',
            'role' => 'admin',
            'reset_token' => null,
            'token_expires_at' => null,
            'officeType' => 'Admin Office',
        ];
    
        // Insert the admin data into the database
        try {
            $main->insert($userData);
            log_message('info', 'New admin registered: ' . $email);
        } catch (\Exception $e) {
            log_message('error', 'Database error during admin registration: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Server error. Please try again later.']);
        }
    
        // Return success response
        return $this->response->setJSON(['success' => 'Admin registration successful!']);
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
    

    // public function sendEmail()
    // {
    //     try {
    //         // Get JSON data from the request
    //         $formData = $this->request->getJSON();

    //         // Define validation rules
    //         $validationRules = [
    //             'sender' => 'required|valid_email',
    //             'message' => 'required|string|min_length[5]'
    //         ];

    //         // Validate the request data
    //         if (!$this->validate($validationRules)) {
    //             // If validation fails, return errors
    //             return $this->response->setJSON([
    //                 'error' => 'Validation failed',
    //                 'messages' => $this->validator->getErrors()
    //             ]);
    //         }

    //         // Load the email library
    //         $email = \Config\Services::email();
    //         $model = new MainModel();
    //         $adminEmails = $model->where('role', 'admin')->findAll();

    //         // Check if there are admin emails
    //         if (empty($adminEmails)) {
    //             return $this->response->setJSON(['error' => 'No admin email addresses found.']);
    //         }

    //         // Add each admin email address to the recipients
    //         foreach ($adminEmails as $admin) {
    //             $email->setTo($admin['email']);
    //             $email->setFrom($formData->sender);
    //             $email->setSubject('Request Form from ' . $formData->username);
    //             $message = $formData->message;
    //             $email->setMessage($message);

    //             // Send email to each admin
    //             if (!$email->send()) {
    //                 log_message('error', 'Email failed to send to ' . $admin['email'] . '. Error: ' . $email->printDebugger(['headers']));
    //                 return $this->response->setJSON(['error' => 'Email failed to send to some recipients.']);
    //             }
    //         }

    //         return $this->response->setJSON(['message' => 'Emails sent successfully.']);
    //     } catch (\Exception $e) {
    //         // Log other exceptions
    //         log_message('error', 'Exception: ' . $e->getMessage());
    //         return $this->response->setJSON(['error' => 'Internal Server Error']);
    //     }
    // }

    public function checkUserStatus($id){
        $model = new MainModel();
        if($id){
            $result = $model->where('user_id', $id)->first();
            if($result){
                return $this->respond($result,200);
            }else{
                return $this->respond(404);
            }
        }else{
            return $this->respond(400);
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
                'message' => 'required|string|min_length[5]',
                'username' => 'required|string'
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

            // Prepare email content
            $subject = 'New Request Form Submission from ' . $formData->username;
            $message = "
                <p>Dear Admin,</p>
                <p>You have received a new request form submission from <strong>{$formData->username}</strong>.</p>
                <p><strong>Message Details:</strong></p>
                <p>{$formData->message}</p>
                <p>Sender's Email: {$formData->sender}</p>
                <p>Thank you,</p>
                <p>Euper System</p>
            ";

            // Add each admin email address to the recipients and send the email
            foreach ($adminEmails as $admin) {
                $email->clear(); // Clear previous email data to send to multiple admins
                $email->setTo($admin['email']);
                $email->setFrom($formData->sender, $formData->username);
                $email->setSubject($subject);
                $email->setMessage($message);
                $email->setMailType('html'); // Set email type to HTML for better formatting

                // Send email to each admin and log errors if any
                if (!$email->send()) {
                    log_message('error', 'Email failed to send to ' . $admin['email'] . '. Error: ' . $email->printDebugger(['headers']));
                }
            }

            return $this->response->setJSON(['message' => 'Emails sent successfully.']);
        } catch (\Exception $e) {
            // Log any unexpected exceptions
            log_message('error', 'Exception: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Internal Server Error']);
        }
    }



    public function getAllRatesPPO()
    {
        $ratingModel = new \App\Models\RatingModel();
        
        // Fetch all ratings from the database
        $data = $ratingModel->where('level','ppo_cpo')->findAll();
        
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


    // public function updateUserRating()
    // {
    //     $requestData = $this->request->getJSON();

    //     if (empty($requestData->id)) {
    //         return $this->fail('Rating ID is required', 400);
    //     }

    //     if (empty($requestData->TableName)) {
    //         return $this->fail('Table Name is required', 400);
    //     }

    //     $tableName = $requestData->TableName;
    //     $db = \Config\Database::connect(); // Load the database connection

    //     // Prepare the update query
    //     $query = "UPDATE $tableName SET ";
    //     $params = [];
    //     $firstParam = true;

    //     foreach ($requestData as $key => $value) {
    //         if ($key !== 'id' && $key !== 'TableName') { // Exclude 'id' and 'TableName' from update fields
    //             if (!$firstParam) {
    //                 $query .= ", ";
    //             }
    //             $query .= "$key = ?";
    //             $params[] = $value;
    //             $firstParam = false;
    //         }
    //     }

    //     $query .= " WHERE id = ?";
    //     $params[] = $requestData->id;

    //     // Execute the update query
    //     try {
    //         $result = $db->query($query, $params);
    //         if ($db->affectedRows() > 0) {
    //             return $this->respond(['success' => true], 200);
    //         } else {
    //             return $this->failNotFound('Rating not found or no changes made');
    //         }
    //     } catch (\Exception $e) {
    //         return $this->failServerError('Error updating rating: ' . $e->getMessage());
    //     }
    // }

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

        // Validate if the record already exists with the same UserId, Month, and Year
        $userId = $requestData->userid ?? null;
        $month = $requestData->month ?? null;
        $year = $requestData->year ?? null;

        if ($userId && $month && $year) {
            $builder = $db->table($tableName);
            $existingRecord = $builder->where('userid', $userId)
                                    ->where('month', $month)
                                    ->where('year', $year)
                                    ->where('id !=', $requestData->id) // Exclude the current record by id
                                    ->get()
                                    ->getRow();

            if ($existingRecord) {
                return $this->fail('Record with the same UserId, Month, and Year already exists', 400);
            }
        }

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
    
    // public function sendSMS()
    // {
    //     // Retrieve JSON payload sent from Vue.js frontend
    //     $json = $this->request->getJSON();

    //     // Extract required data from JSON payload
    //     $senderName = "Euper";
    //     $phoneNumber = $json->recipient;
    //     $message = $json->message;

    //     $apiKey = 'fdc0fc4b3cf35557937435f41a072b43-ab235ced-7004-4b69-b1b1-166957845f9b';
    //     $url = 'https://w1eley.api.infobip.com/sms/2/text/advanced';

    //     $client = new Client([
    //         'verify' => false, // Disable SSL certificate verification (use with caution)
    //     ]);

    //     try {
    //         $response = $client->post($url, [
    //             'headers' => [
    //                 'Authorization' => 'App ' . $apiKey,
    //                 'Content-Type' => 'application/json',
    //                 'Accept' => 'application/json'
    //             ],
    //             'json' => [
    //                 'messages' => [
    //                     [
    //                         'from' => $senderName,
    //                         'destinations' => [
    //                             ['to' => $phoneNumber]
    //                         ],
    //                         'text' => $message
    //                     ]
    //                 ]
    //             ]
    //         ]);

    //         $statusCode = $response->getStatusCode();
    //         $body = (string) $response->getBody();

    //         if ($statusCode == 200) {
    //             return $this->respond([
    //                 'success' => true,
    //                 'message' => 'SMS sent successfully',
    //                 'response' => $body
    //             ], 200);
    //         } else {
    //             return $this->failServerError('Unexpected HTTP status: ' . $statusCode);
    //         }
    //     } catch (RequestException $e) {
    //         return $this->failServerError('Error: ' . $e->getMessage());
    //     }
    // }

    public function sendSMSToUser()
{
    // Get JSON input
    $json = $this->request->getJSON();
    $phoneNumber = $json->recipient;
    $message = $json->message;

    // Define Semaphore API key
    $apiKey = 'ef0a9cf7d5bf8f4b43bbdac91a2f1276'; // Replace with your actual Semaphore API key

    // Set parameters for the Semaphore API request
    $parameters = [
        'apikey' => $apiKey,
        'number' => $phoneNumber, // Recipient's phone number
        'message' => $message,     // Message to send
        'sendername' => 'EuperAdmin' // Custom sender name
    ];

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options for Semaphore API
    curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (not recommended for production)

    // Execute the cURL request
    $output = curl_exec($ch);

    // Close the cURL resource
    curl_close($ch);

    // Parse the response to check for success or failure
    if ($output !== false) {
        $response = [
            'success' => true,
            'message' => 'SMS sent successfully.',
            'output' => $output // Add API response output if needed for further verification
        ];

        // Return 200 OK status with JSON response
        return $this->response->setStatusCode(200)->setJSON($response);
    } else {
        // If sending failed
        $response = [
            'success' => false,
            'message' => 'Failed to send SMS.',
            'output' => $output // Add API response output for debugging
        ];

        // Return 500 Internal Server Error status with JSON response
        return $this->response->setStatusCode(500)->setJSON($response);
    }
}

// public function sendSMSToUser()
// {
//     // Get JSON input
//     $json = $this->request->getJSON();
//     $phoneNumber = $json->recipient;
//     $message = $json->message;

//     // Format phone number (assuming it's a Philippine number starting with 0)
//     $phoneNumber = '+63' . ltrim($phoneNumber, '0'); // Remove leading 0 and add country code

//     // Semaphore API key
//     $apiKey = 'ef0a9cf7d5bf8f4b43bbdac91a2f1276'; // Replace with your actual Semaphore API key

//     // Parameters for Semaphore API
//     $parameters = [
//         'apikey' => $apiKey,
//         'number' => $phoneNumber,
//         'message' => $message,
//         'sendername' => 'EuperAdmin'
//     ];

//     // Initialize cURL
//     $ch = curl_init();

//     // Set cURL options for Semaphore API
//     curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For dev environment only, enable this for production

//     // Execute the cURL request
//     $output = curl_exec($ch);

//     // Check for cURL errors
//     if (curl_errno($ch)) {
//         $error_msg = curl_error($ch);
//         $response = [
//             'success' => false,
//             'message' => 'cURL Error: ' . $error_msg,
//             'output' => ''
//         ];

//         curl_close($ch);
//         return $this->response->setStatusCode(500)->setJSON($response);
//     }

//     // Close cURL resource
//     curl_close($ch);

//     // Decode Semaphore API response
//     $apiResponse = json_decode($output, true);

//     // Check if the API response is empty
//     if (empty($apiResponse)) {
//         $response = [
//             'success' => false,
//             'message' => 'Empty response from Semaphore API. Check your API key or request parameters.',
//             'output' => $output
//         ];

//         return $this->response->setStatusCode(500)->setJSON($response);
//     }

//     // Check if there's a status in the API response
//     if (isset($apiResponse['status']) && $apiResponse['status'] == 'success') {
//         // SMS sent successfully, but still pending delivery
//         $response = [
//             'success' => true,
//             'message' => 'SMS sent to Semaphore successfully and is pending delivery.',
//             'output' => $output // Include the API response for reference
//         ];

//         return $this->response->setStatusCode(200)->setJSON($response);
//     } else if (isset($apiResponse[0]['status']) && $apiResponse[0]['status'] == 'Pending') {
//         // Handle Pending status from the response details
//         $response = [
//             'success' => true,
//             'message' => 'SMS is pending delivery.',
//             'output' => $output
//         ];

//         return $this->response->setStatusCode(200)->setJSON($response);
//     } else {
//         // If there's no valid status, return 'Unknown' status
//         $response = [
//             'success' => false,
//             'message' => 'Failed to send SMS. Status: Unknown',
//             'output' => $output // Include the API response for debugging
//         ];

//         return $this->response->setStatusCode(500)->setJSON($response);
//     }
// }



    
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
                'sendername' => 'EuperAdmin'
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

    public function getAllAverageRatesPerTbl($table,$year)
    {
        $json = $this->request->getJSON();
            $ratingModel = new \App\Models\RatingModel();
        
        $db = \Config\Database::connect();

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
                                       ->where('level', $table)
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

}
    

    