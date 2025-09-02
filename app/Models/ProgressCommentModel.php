<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgressCommentModel extends Model
{
    protected $table = 'progress_comments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'progress_id', 'comment_type', 'comment_text', 'commenter_role',
        'created_by', 'created_date'
    ];
    protected $useTimestamps = false;

    public function getCommentsByProgressId($progressId)
    {
        return $this->where('progress_id', $progressId)
                   ->orderBy('created_date', 'DESC')
                   ->findAll();
    }
}

