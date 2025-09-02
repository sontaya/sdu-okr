<?php
namespace App\Models;

use CodeIgniter\Model;

class ProgressEntryModel extends Model
{
    protected $table = 'progress_entries';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'progress_id', 'entry_id', 'created_date'
    ];
    protected $useTimestamps = false;

    public function getEntriesByProgressId($progressId)
    {
        return $this->db->table($this->table . ' pe')
            ->select('
                pe.id as progress_entry_id,
                pe.entry_id,
                kre.entry_name,
                kre.entry_description,
                kre.entry_status,
                kre.created_date as entry_created_date
            ')
            ->join('key_result_entries kre', 'pe.entry_id = kre.id')
            ->where('pe.progress_id', $progressId)
            ->orderBy('kre.entry_name')
            ->get()
            ->getResultArray();
    }

    public function saveProgressEntries($progressId, $entryIds)
    {
        // ลบรายการเก่าก่อน
        $this->where('progress_id', $progressId)->delete();

        // เพิ่มรายการใหม่
        if (!empty($entryIds)) {
            $data = [];
            foreach ($entryIds as $entryId) {
                $data[] = [
                    'progress_id' => $progressId,
                    'entry_id' => $entryId,
                    'created_date' => date('Y-m-d H:i:s')
                ];
            }
            $this->insertBatch($data);
        }

        return true;
    }
}