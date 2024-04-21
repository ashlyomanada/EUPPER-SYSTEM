<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RestFul\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\RmfbModel;
use App\Models\MpsOcciModel;

class InsertController extends ResourceController
{
    public function insertRmfb()
{
    $json = $this->request->getJSON();
    $sixtypercent = ($json->Rod + $json->Ridmd + $json->Rid + $json->Rcadd) / 600 * 60;
    $fortypercent = ($json->Rlrdd + $json->Rlddd + $json->Rprmd + $json->Rictmd + $json->Rpsmd + $json->Rcd + $json->Rrd) / 400 * 40;

    $ratingModel = new RmfbModel();
    $data = [
        'userid'   => $json->storedUserId,
        'month'    => $json->Month,
        'year'     => $json->Year,
        'office'   => $json->Office,
        'rod'       => $json->Rod,
        'ridmd'     => $json->Ridmd,
        'rid'       => $json->Rid,
        'rcadd'     => $json->Rcadd,
        'rlrdd'    => $json->Rlrdd,
        'rlddd'     => $json->Rlddd,
        'rprmd'    => $json->Rprmd,
        'rictmd'      => $json->Rictmd,
        'rpsmd'       => $json->Rpsmd,
        'rcd'      =>$json->Rcd,
        'rrd'   => $json->Rrd,
        '60percent'       => $sixtypercent,
        '40percent'  => $fortypercent,
        'total'    => $sixtypercent + $fortypercent,
    ];

    $ratingModel->insert($data);

    return $this->respond(['message' => 'Rating inserted successfully']);
}

public function viewRmfbRates()
    {
    $model = new RmfbModel();
    $data = $model->findAll();
    return $this->respond($data,200);
    }

    public function userRmfbRates($userID)
    {
    $model = new RmfbModel();
    $data = $model->where('userid',$userID)->findAll();
    return $this->respond($data,200);
    }

    public function saveRmfbUserRates()
    {
        $data = $this->request->getJSON();
        $model = new RmfbModel();
    
        if ($data->id) {
            // Update existing record
            $model->update($data->id, $data);
    
            // Recalculate percentages and total
            $sixtypercent = ($data->rod + $data->ridmd + $data->rid + $data->rcadd) / 600 * 60;
            $fortypercent = ($data->rlrdd + $data->rlddd + $data->rprmd + $data->rictmd + $data->rpsmd + $data->rcd + $data->rrd) / 400 * 40;
    
            // Update the record with the new percentages and total
            $model->update($data->id, [
                '60percent' => $sixtypercent,
                '40percent' => $fortypercent,
                'total' => $sixtypercent + $fortypercent,
            ]);
    
            $message = 'Rating updated successfully';
        } else {
            // Insert new record
            $model->insert($data);
            $message = 'Rating saved successfully';
        }
    
        return $this->respond(['success' => true, 'message' => $message]);
    }
    
    public function insertMpsOcci()
{
    $json = $this->request->getJSON();
    $sixtypercent = ($json->Rod + $json->Ridmd + $json->Rid + $json->Rcadd) / 600 * 60;
    $fortypercent = ($json->Rlrdd + $json->Rlddd + $json->Rprmd + $json->Rictmd + $json->Rpsmd + $json->Rcd + $json->Rrd) / 400 * 40;

    $ratingModel = new MpsOcciModel();
    $data = [
        'userid'   => $json->storedUserId,
        'month'    => $json->Month,
        'year'     => $json->Year,
        'office'   => $json->Office,
        'rod'       => $json->Rod,
        'ridmd'     => $json->Ridmd,
        'rid'       => $json->Rid,
        'rcadd'     => $json->Rcadd,
        'rlrdd'    => $json->Rlrdd,
        'rlddd'     => $json->Rlddd,
        'rprmd'    => $json->Rprmd,
        'rictmd'      => $json->Rictmd,
        'rpsmd'       => $json->Rpsmd,
        'rcd'      =>$json->Rcd,
        'rrd'   => $json->Rrd,
        '60percent'       => $sixtypercent,
        '40percent'  => $fortypercent,
        'total'    => $sixtypercent + $fortypercent,
    ];

    $ratingModel->insert($data);

    return $this->respond(['message' => 'Rating inserted successfully']);
}

public function viewMpsOcciRates()
    {
    $model = new MpsOcciModel();
    $data = $model->findAll();
    return $this->respond($data,200);
    }

    public function userMpsOcciRates($userID)
    {
    $model = new MpsOcciModel();
    $data = $model->where('userid',$userID)->findAll();
    return $this->respond($data,200);
    }
}