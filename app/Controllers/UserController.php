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

class UserController extends ResourceController
{

    public function savePPORate(){
        $json = $this->request->getJSON();
        $model = new UserPPOModel();
        $data = [
            'userid' => $json->UserId,
            'month' => $json->Month,
            'year' => $json->Year,
            'occi' => $json->Occidental,
            'ormin' => $json->Oriental,
            'marin' => $json->Marinduque,
            'rom' => $json->Romblon,
            'pal' => $json->Palawan,
            'puertop' => $json->PuertoPrinsesa,
        ];
        $model->insert($data);
        return $this->respond(['message' => 'Rating Successfully Inserted']);

    }

    public function saveRMFBRate(){
        $json = $this->request->getJSON();
        $model = new UserRMFBModel();
        $data = [
            'userid' => $json->UserId,
            'month' => $json->Month,
            'year' => $json->Year,
            'regional' => $json->Regional,
            'occi' => $json->Occidental,
            'ormin' => $json->Oriental,
            'marin' => $json->Marinduque,
            'rom' => $json->Romblon,
            'pal' => $json->Palawan,
            'puertop' => $json->PuertoPrinsesa,
        ];
        $model->insert($data);
        return $this->respond(['message' => 'Rating Successfully Inserted']);

    }

    public function saveMunOcciRate()
    {

        $json = $this->request->getJSON();
        $model = new OccidentalModel();
        $data = [
            'userid' => $json->UserId,
            'month' => $json->Month,
            'year' => $json->Year,
            'abra' => $json->Abra,
            'calintaan' => $json->Calintaan,
            'looc' => $json->Looc,
            'lubang' => $json->Lubang,
            'magsaysay' => $json->Magsaysay,
            'mamburao' => $json->Mamburao,
            'paluan' => $json->Paluan,
            'rizal' => $json->Rizal,
            'sablayan' => $json->Sablayan,
            'san_jose' => $json->SanJose,
            'sta_cruz' => $json->SantaCruz,
        ];
        $model->insert($data);

        return $this->respond(['message' => 'Rating inserted successfully']);
    }

    public function saveMunOrientalRate()
    {

        $json = $this->request->getJSON();
        $model = new OrientalModel();
        $data = [
            'userid' => $json->UserId,
            'month' => $json->Month,
            'year' => $json->Year,
            'baco' => $json->Baco,
            'bansud' => $json->Bansud,
            'bongabong' => $json->Bongabong,
            'bulalacao' => $json->Bulalacao,
            'calapan' => $json->Calapan,
            'gloria' => $json->Gloria,
            'mansalay' => $json->Mansalay,
            'naujan' => $json->Naujan,
            'pinamalayan' => $json->Pinamalayan,
            'pola' => $json->Pola,
            'puerto_galera' => $json->PuertoGalera,
            'roxas' => $json->Roxas,
            'san_teodoro' => $json->SanTeodoro,
            'soccorro' => $json->Socorro,
            'victoria' => $json->Victoria,
        ];
        $model->insert($data);

        return $this->respond(['message' => 'Rating inserted successfully']);
    }

    public function saveMunPalawanRate()
    {

        $json = $this->request->getJSON();
        $model = new PalawanModel();
        $data = [
            'userid' => $json->UserId,
            'month' => $json->Month,
            'year' => $json->Year,
            'aborian' => $json->Aborian,
            'agutaya' => $json->Agutaya,
            'arceli' => $json->Arceli,
            'balabac' => $json->Balabac,
            'bataraza' => $json->Bataraza,
            'brookes_point' => $json->BrookesPoint,
            'busuanga' => $json->Busuanga,
            'cagayancilo' => $json->Cagayancilo,
            'coron' => $json->Coron,
            'culion' => $json->Culion,
            'cuyo' => $json->Cuyo,
            'dumaron' => $json->Dumaron,
            'el_nido' => $json->ElNido,
            'espanola' => $json->Espanola,
            'kalayaan' => $json->Kalayaan,
            'linapacan' => $json->Linapacan,
            'magsaysay' => $json->Magsaysay,
            'narra' => $json->Narra,
            'quezon' => $json->Quezon,
            'rizal' => $json->Rizal,
            'roxas' => $json->Roxas,
            'san_vicente' => $json->SanVicente,
            'taytay' => $json->Taytay,
            
        ];
        $model->insert($data);

        return $this->respond(['message' => 'Rating inserted successfully']);
    }

    public function saveMunRomblonRate()
    {

        $json = $this->request->getJSON();
        $model = new RomblonModel();
        $data = [
            'userid' => $json->UserId,
            'month' => $json->Month,
            'year' => $json->Year,
            'alcantara' => $json->Alcantara,
            'banton' => $json->Banton,
            'cajidiocan' => $json->Cajidiocan,
            'calatrava' => $json->Calatrava,
            'concepcion' => $json->Concepcion,
            'concuera' => $json->Concuera,
            'ferrol' => $json->Ferrol,
            'looc' => $json->Looc,
            'magdiwang' => $json->Magdiwang,
            'odiongan' => $json->Odiongan,
            'romblon' => $json->Romblon,
            'san_agustin' => $json->SanAgustin,
            'san_andres' => $json->SanAndres,
            'san_fernando' => $json->SanFernando,
            'san_jose' => $json->SanJose,
            'sta_fe' => $json->StaFe,
            'sta_maria' => $json->StaMaria,        
        ];
        $model->insert($data);

        return $this->respond(['message' => 'Rating inserted successfully']);
    }

    public function saveMunMarinduqueRate()
    {

        $json = $this->request->getJSON();
        $model = new MarinduqueModel();
        $data = [
            'userid' => $json->UserId,
            'month' => $json->Month,
            'year' => $json->Year,
            'boac' => $json->Boac,
            'buenavista' => $json->Buenavista,
            'gasan' => $json->Gasan,
            'mogpog' => $json->Mogpog,
            'sta_cruz' => $json->StaCruz,
            'torrijos' => $json->Torrijos,
        ];
        $model->insert($data);

        return $this->respond(['message' => 'Rating inserted successfully']);
    }

    public function saveMunPuertoRate()
    {

        $json = $this->request->getJSON();
        $model = new PuertoPrinsesaModel();
        $data = [
            'userid' => $json->UserId,
            'month' => $json->Month,
            'year' => $json->Year,
            'ps1' => $json->PS1,
            'ps2' => $json->PS2,
        ];
        $model->insert($data);

        return $this->respond(['message' => 'Rating inserted successfully']);
    }

    public function viewUserPPORates($userId)
    {
        if (!empty($userId)) {
            $userRatingsModel = new UserPPOModel();
            $userRatings = $userRatingsModel->where('userid', $userId)->findAll();
            if (!empty($userRatings)) {
                return $this->respond($userRatings,200);
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
            $userRatingsModel = new UserRMFBModel();
            $userRatings = $userRatingsModel->where('userid', $userId)->findAll();
            if (!empty($userRatings)) {
                return $this->respond($userRatings,200);
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
            $userRatingsModel = new OccidentalModel();
            $userRatings = $userRatingsModel->where('userid', $userId)->findAll();
            if (!empty($userRatings)) {
                return $this->respond($userRatings,200);
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
            $userRatingsModel = new OrientalModel();
            $userRatings = $userRatingsModel->where('userid', $userId)->findAll();
            if (!empty($userRatings)) {
                return $this->respond($userRatings,200);
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
            $userRatingsModel = new MarinduqueModel();
            $userRatings = $userRatingsModel->where('userid', $userId)->findAll();
            if (!empty($userRatings)) {
                return $this->respond($userRatings,200);
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
            $userRatingsModel = new RomblonModel();
            $userRatings = $userRatingsModel->where('userid', $userId)->findAll();
            if (!empty($userRatings)) {
                return $this->respond($userRatings,200);
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
            $userRatingsModel = new PalawanModel();
            $userRatings = $userRatingsModel->where('userid', $userId)->findAll();
            if (!empty($userRatings)) {
                return $this->respond($userRatings,200);
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
            $userRatingsModel = new PuertoPrinsesaModel();
            $userRatings = $userRatingsModel->where('userid', $userId)->findAll();
            if (!empty($userRatings)) {
                return $this->respond($userRatings,200);
            } else {
                return $this->failNotFound('User ratings not found');
            }
        } else {
            return $this->fail('User id is required', 400);
        }
    }


}