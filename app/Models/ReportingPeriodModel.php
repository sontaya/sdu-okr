<?php
// app/Models/ReportingPeriodModel.php
namespace App\Models;

use CodeIgniter\Model;

class ReportingPeriodModel extends Model
{
    protected $table = 'reporting_periods';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'year', 'quarter', 'quarter_name', 'start_date', 'end_date',
        'is_active', 'created_date', 'updated_date'
    ];
    protected $useTimestamps = false;

    public function getActiveReportingPeriods()
    {
        return $this->where('is_active', 1)
                   ->orderBy('year', 'DESC')
                   ->orderBy('quarter', 'ASC')
                   ->findAll();
    }

    public function getCurrentPeriod()
    {
        $today = date('Y-m-d');
        return $this->where('start_date <=', $today)
                   ->where('end_date >=', $today)
                   ->where('is_active', 1)
                   ->first();
    }

    public function getPeriodsByYear($year)
    {
        return $this->where('year', $year)
                   ->where('is_active', 1)
                   ->orderBy('quarter', 'ASC')
                   ->findAll();
    }
}