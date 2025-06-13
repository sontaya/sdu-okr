<?php
namespace App\Models;

use CodeIgniter\Model;

class KeyResultEntryModel extends Model
{
    protected $table = 'key_result_entries';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'key_result_id',
        'entry_name',
        'entry_description',
        'entry_status',
        'created_by',
        'created_date'
    ];
}
