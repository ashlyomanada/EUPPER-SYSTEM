<?php

namespace App\Models;

use CodeIgniter\Model;

class PalawanModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'palawan_cps';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['userid','month','year','aborian','agutaya','arceli','balabac','bataraza','brookes_point','busuanga','cagayancilo','coron','culion','cuyo','dumaron','el_nido','espanola','kalayaan','linapacan','magsaysay','narra','quezon','rizal','roxas','san_vicente','taytay',];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}