<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'uid', 'citizen_id', 'first_name', 'last_name', 'full_name', 'department_id', 'role',
        'created_by', 'created_date', 'updated_by', 'updated_date', 'lasted_login', 'lasted_ip'
    ];
}
