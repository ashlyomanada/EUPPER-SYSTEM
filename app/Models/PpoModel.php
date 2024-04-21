<?php

namespace App\Models;

use CodeIgniter\Model;

class PpoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'ppo_tbl';
    protected $primaryKey       = 'officeId';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ppoOffices',];

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

    public function getData()
    {
        return $this->findAll();
    }

    public function getForeignId($userId)
    {
        try {
            return $this->where('userid', $userId)->get();
        } catch (\Exception $e) {
            log_message('error', 'Database error: ' . $e->getMessage());
            return null; // Or handle the error in a way that makes sense for your application
        }
    }

    public function getFilteredData($userId, $tableId)
{
    $tableName = $this->getTableNameFromId($tableId);
    $this->table = $tableName;
    return $this->where(['userid' => $userId])->findAll();
}

private function getTableNameFromId($tableId)
{
    $tableMappings = [
        '1' => 'ppo_tbl',
        '2' => 'table_name_2',
        '3' => 'table_name_3',
    ];
    return $tableMappings[$tableId] ?? 'ppo_tbl';
}

public function searchData($year)
{
    return $this->db->table('login_info')
    ->like('year', $year)
    ->get()
    ->getResultArray();
}

// Modify the getMonthlyRatings method to accept a year parameter
public function getMonthlyRatings($year)
{
    return $this->where('year', $year)->findAll();
}

public function getDistinctMonths($selectedYear)
    {
        return $this->distinct()->select('month')->where('year', $selectedYear)->findAll();
    }
}