<?php
// app/Models/ProgressHistoryModel.php
namespace App\Models;

use CodeIgniter\Model;

class ProgressHistoryModel extends Model
{
    protected $table = 'progress_history';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'progress_id', 'action', 'old_value', 'new_value', 'notes',
        'created_by', 'created_date'
    ];
    protected $useTimestamps = false;

    public function getHistoryByProgressId($progressId)
    {
        return $this->where('progress_id', $progressId)
                   ->orderBy('created_date', 'DESC')
                   ->findAll();
    }
}