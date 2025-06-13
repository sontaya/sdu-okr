<?php
namespace App\Models;

use CodeIgniter\Model;

class KeyResultFileModel extends Model
{
    protected $table = 'key_result_files';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'entry_id',
        'original_name',
        'file_name',
        'file_path',
        'uploaded_date'
    ];
}
