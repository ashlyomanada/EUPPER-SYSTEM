<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RestFul\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MainModel;
use App\Models\AdminModel;
use App\Models\PpoModel;
use App\Models\RatingModel;
use App\Models\MpsModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

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
        'confirmpassword' => $request->getPost('confirmpassword'),
        'office' => $request->getPost('office'),
        'phone_no' => $request->getPost('phone_no'),
        'email' => $request->getPost('email'),
        'image' => $filePath,
        'status' => 'Enable',
        'role' => 'user',
        'reset_token' => null,
        'token_expires_at' => null,
    ];

    // Assuming you have a model named MainModel, you can use it to insert data into the database
    $main = new MainModel();
    $main->insert($userData);

    // Redirect back to the form with a success message
    return redirect()->to('/')->with('success', 'Registration successful!');
}

public function sendEmail()
{
    try {
        // Get JSON data from the request
        $formData = $this->request->getJSON();

        // Load the email library
        $email = \Config\Services::email();

        // Set email parameters
        $email->setTo('ashlyomanada@gmail.com');
        $email->setFrom($formData->sender);
        $email->setSubject('Request Form from'.' '. $formData->username);
        $message = $formData->message;
        $email->setMessage($message);

        // Send email
        if ($email->send()) {
            return $this->response->setJSON(['message' => 'Email sent successfully.']);
        } else {
            log_message('error', 'Email failed to send. Error: ' . $email->printDebugger(['headers']));
            return $this->response->setJSON(['error' => 'Email failed to send.']);
        }
    } catch (\Exception $e) {
        // Log other exceptions
        log_message('error', 'Exception: ' . $e->getMessage());
        return $this->response->setJSON(['error' => 'Internal Server Error']);
    }
}

public function getAllRates()
{
    $ratingModel = new \App\Models\RatingModel();
    
    // Fetch all ratings from the database
    $data = $ratingModel->findAll();
    
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


public function update($id = null)
{
    if ($id === null) {
        return $this->fail('Invalid ID', 400);
    }
    $data = $this->request->getJSON();
    $result = $this->model->update($id, $data);

    if ($result) {
        return $this->respondUpdated(['status' => 'Admin updated successfully']);
    } else {
        return $this->fail('Admin update failed', 500);
    }
}

// public function updateUserRating()
//     {
//         $requestData = $this->request->getJSON();

//         if (empty($requestData->id)) {
//             return $this->fail('Rating ID is required', 400);
//         }

//         $db = \Config\Database::connect(); // Load the database connection

//         // Prepare the update query
//         $query = "UPDATE ppo_cpo SET ";
//         $params = [];
//         $firstParam = true;

//         foreach ($requestData as $key => $value) {
//             if ($key !== 'id') { // Exclude 'id' from update fields
//                 if (!$firstParam) {
//                     $query .= ", ";
//                 }
//                 $query .= "$key = ?";
//                 $params[] = $value;
//                 $firstParam = false;
//             }
//         }

//         $query .= " WHERE id = ?";
//         $params[] = $requestData->id;

//         // Execute the update query
//         try {
//             $result = $db->query($query, $params);
//             if ($db->affectedRows() > 0) {
//                 return $this->respond(['success' => true], 200);
//             } else {
//                 return $this->failNotFound('Rating not found or no changes made');
//             }
//         } catch (\Exception $e) {
//             return $this->failServerError('Error updating rating: ' . $e->getMessage());
//         }
//     }

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

// public function sendSMS()
// {
//     helper('Infobip\SmsHelper'); // Load the custom SMS helper

//     $json = $this->request->getJSON();

//     $recipient = $json->recipient; // Destination phone number
//     $message = $json->message;

//     $result = sendSms($recipient, $message);

//     if ($result) {
//         return $this->respond(['success' => true, 'message' => "SMS sent successfully. Message ID: {$result}"], 200);
//     } else {
//         return $this->fail('Failed to send SMS.', 400);
//     }
// }

// public function sendSMS()
//     {
//         $apiKey = 'fdc0fc4b3cf35557937435f41a072b43-ab235ced-7004-4b69-b1b1-166957845f9b';
//         $url = 'https://w1eley.api.infobip.com/sms/2/text/advanced';

//         $client = new Client([
//             'verify' => false, // Disable SSL certificate verification (use with caution)
//         ]);

//         try {
//             $response = $client->post($url, [
//                 'headers' => [
//                     'Authorization' => 'App ' . $apiKey,
//                     'Content-Type' => 'application/json',
//                     'Accept' => 'application/json'
//                 ],
//                 'json' => [
//                     'messages' => [
//                         [
//                             'destinations' => [
//                                 ['to' => '639507335078']
//                             ],
//                             'from' => 'ServiceSMS',
//                             'text' => 'Congratulations on sending your first message. Go ahead and check the delivery report in the next step.'
//                         ]
//                     ]
//                 ]
//             ]);

//             $statusCode = $response->getStatusCode();
//             $body = (string) $response->getBody();

//             if ($statusCode == 200) {
//                 echo $body;
//             } else {
//                 echo 'Unexpected HTTP status: ' . $statusCode;
//             }
//         } catch (RequestException $e) {
//             echo 'Error: ' . $e->getMessage();
//         }
//     }
    
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

public function insertRating()
{
    $json = $this->request->getJSON();
    $sixtypercent = ($json->Do + $json->Didm + $json->Di + $json->Dpcr) / 600 * 60;
    $fortypercent = ($json->Dl + $json->Dhrdd + $json->Dprm + $json->Dictm + $json->Dpl + $json->Dc + $json->Drd) / 400 * 40;

    $ratingModel = new PpoModel();
    $data = [
        'userid'   => $json->storedUserId,
        'month'    => $json->Month,
        'year'     => $json->Year,
        'office'   => $json->Office,
        'do'       => $json->Do,
        'didm'     => $json->Didm,
        'di'       => $json->Di,
        'dpcr'     => $json->Dpcr,
        'dl'       => $json->Dl,
        'dhrdd'    => $json->Dhrdd,
        'dprm'     => $json->Dprm,
        'dictm'    => $json->Dictm,
        'dpl'      => $json->Dpl,
        'dc'       => $json->Dc,
        'drd'      =>$json->Drd,
        'operational'   => $sixtypercent,
        'administrative'  => $fortypercent,
        'total'    => $sixtypercent + $fortypercent,
    ];

    $ratingModel->insert($data);

    return $this->respond(['message' => 'Rating inserted successfully']);
}

public function insertMps(){
    $json = $this->request->getJSON();
    $sixtypercent = ($json->ROD + $json->RIDMD + $json->RID + $json->RCADD) / 600 * 60;
    $fortypercent = ($json->RLRDD + $json->RLDDD + $json->RPRMD + $json->RICTMD + $json->RPSMD + $json->RCD + $json->RRD) / 400 * 40;
    $ratingModel = new MpsModel();
    $data = [
        'userid'   => $json->UserId,
        'month'    => $json->Month,
        'year'     => $json->Year,
        'office'   => $json->Municipality,
        'ROD'       => $json->ROD,
        'RIDMD'     => $json->RIDMD,
        'RID'       => $json->RID,
        'RCADD'     => $json->RCADD,
        'RLRDD'       => $json->RLRDD,
        'RLDDD'    => $json->RLDDD,
        'RPRMD'     => $json->RPRMD,
        'RICTMD'    => $json->RICTMD,
        'RPSMD'      => $json->RPSMD,
        'RCD'       => $json->RCD,
        'RRD'      => $json->RRD,
        'Total'   => $sixtypercent + $fortypercent,
        'Ranking'  => 0,
    ];
    $ratingModel->insert($data);

    return $this->respond(['message' => 'Rating inserted successfully']);
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

public function getUserRatings(){
    $main = new PpoModel();
    $data = $main->findall();
    return $this->respond($data,200);
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


public function viewUserPPO($userId)
{
    if (!empty($userId)) {
        $userRatingsModel = new UserRatingsModel();
        $userRatings = $userRatingsModel->where('user_id', $userId)->findAll();

        if (!empty($userRatings)) {
            // If user ratings are found, return a JSON response with success message and data
            return $this->respond([
                'success' => true,
                'message' => 'User ratings fetched successfully',
                'data' => $userRatings
            ]);
        } else {
            // If user ratings are not found, return a JSON response with a message
            return $this->failNotFound('User ratings not found');
        }
    } else {
        // If user id is not provided, return a JSON response with an error message
        return $this->fail('User id is required', 400);
    }
}

}
    

    