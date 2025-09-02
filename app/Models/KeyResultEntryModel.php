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

    /**
     * ดึงข้อมูล entry พร้อมชื่อผู้สร้าง
     */
    public function getEntryWithCreator($entryId)
    {
        $db = \Config\Database::connect();

        return $db->table('key_result_entries kre')
            ->select('
                kre.id,
                kre.key_result_id,
                kre.entry_name,
                kre.entry_description,
                kre.entry_status,
                kre.created_date,
                kre.created_by,
                u.full_name as creator_name
            ')
            ->join('users u', 'kre.created_by = u.id', 'left')
            ->where('kre.id', $entryId)
            ->get()
            ->getRowArray();
    }

    /**
     * ดึงรายการ entries พร้อมชื่อผู้สร้าง (สำหรับใช้ที่อื่น)
     */
    public function getEntriesWithCreator($keyResultId = null)
    {
        $db = \Config\Database::connect();

        $builder = $db->table('key_result_entries kre')
            ->select('
                kre.id,
                kre.key_result_id,
                kre.entry_name,
                kre.entry_description,
                kre.entry_status,
                kre.created_date,
                kre.created_by,
                u.full_name as creator_name
            ')
            ->join('users u', 'kre.created_by = u.id', 'left')
            ->orderBy('kre.created_date', 'DESC');

        if ($keyResultId) {
            $builder->where('kre.key_result_id', $keyResultId);
        }

        return $builder->get()->getResultArray();
    }

}
