<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RestFul\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MainModel;
use App\Models\AdminModel;
use App\Models\PpoModel;
use App\Models\RatingModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Mpdf\Mpdf;

class MainController extends ResourceController
{
    public function index(){
        return view('upload');
    }
    
    public function login(){
        $json = $this->request->getJSON();

        if (isset($json->email) && isset($json->password)) {
            $email = $json->email;
            $password = $json->password;

        $userModel = new MainModel();
           $data = $user = $userModel->where('email', $email)->first();

            if($data)
            {
                $pass = $data['password'];
                $auth = password_verify($password, $pass);
                if($auth){
                    return $this->respond(['message' => 'Login successful', 'id' => $data['user_id']], 200);                }
             else {
                return $this->respond(['message' => 'Invalid email or password'], 401);
            }
        } else {
            return $this->respond(['message' => 'Invalid JSON data'], 400);
        }
    }
    
    }
    
    public function login2()
{
    $json = $this->request->getJSON();

    if (isset($json->email) && isset($json->password)) {
        $email = $json->email;
        $password = $json->password;

        $userModel = new MainModel();
        $data = $user = $userModel->where('email', $email)->first();

        if ($data) {
            $pass = $data['password'];
            $auth = password_verify($password, $pass);

            if ($auth) {
                // Generate a token (you might want to use a proper library for this)
                $token = bin2hex(random_bytes(32));

                // Update the user's token in the database (assuming you have a column for it)
                $userModel->where('user_id', $data['user_id'])->set('token', $token)->update();

                return $this->respond([
                    'message' => 'Login successful',
                    'id' => $data['user_id'],
                    'token' => $token,
                ], 200);
            } else {
                return $this->respond(['message' => 'Invalid email or password'], 401);
            }
        } else {
            return $this->respond(['message' => 'Invalid email or password'], 401);
        }
    } else {
        return $this->respond(['message' => 'Invalid JSON data'], 400);
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


