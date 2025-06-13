<?php
namespace App\Models;

use CodeIgniter\Model;

class KeyResultTagModel extends Model
{
    protected $table = 'key_result_tags';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'entry_id',
        'tag_name',
        'tag_date'
    ];
}
