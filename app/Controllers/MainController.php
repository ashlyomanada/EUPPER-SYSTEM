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
        return view('welcome_message');
    }
    public function getUsers(){

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

    public function save(){
        $json = $this->request->getJSON();
        $data = [
            'username' => $json->username,
            'password' => $json->password,
            'confirmpassword' => $json->confirmpassword,
            'office' => $json->office,
            'phone_no' => $json->phone_no,
            'email' => $json->email,
        ];
        $main = new MainModel();
        $r = $main->save($data);
        return $this->respond($r,200);
    }
    
    
   

    public function login(){
    $email = $this->request->getPost('email');
    $password = $this->request->getPost('password');

    $main = new MainModel();
    $user = $main->where('email', $email)->first();


    // If login is successful
    return $this->respond([
        'success' => true,
        'message' => 'Login successful',
        'user' => $user,
    ], 200);
}

public function generateExcel()
{
    $model = new PpoModel();
    $data = $model->getData(); // Assuming you have a method to fetch data from the database in your model

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Add headers
    $headers = array_keys($data[0]);
    foreach ($headers as $colIndex => $header) {
        $sheet->setCellValueByColumnAndRow($colIndex + 1, 1, $header);
    }

    // Add data
    foreach ($data as $rowIndex => $rowData) {
        $colIndex = 0;
        foreach ($rowData as $value) {
            $colIndex++;
            $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex + 2, $value);
        }
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'exported_data.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    
}



}