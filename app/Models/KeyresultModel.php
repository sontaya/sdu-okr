<?php
namespace App\Models;

use CodeIgniter\Model;

class KeyresultModel extends Model
{
    protected $table = 'key_results'; // ใช้ table หลักไว้ก่อน
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // ไม่ต้องกำหนด allowedFields ถ้าใช้เฉพาะ get/query
    // protected $allowedFields = [...];

    public function getKeyResults($params = [])
    {
        // ✅ 1. สร้าง cache key ที่ unique ตาม parameters
        $cacheKey = $this->generateCacheKey($params);

        // ✅ 2. ลองดึงจาก cache ก่อน
        $cache = \Config\Services::cache();
        if ($cachedData = $cache->get($cacheKey)) {
            log_message('info', "Cache HIT for key: {$cacheKey}");
            return $cachedData;
        }

        log_message('info', "Cache MISS for key: {$cacheKey}");

        // ✅ 3. ถ้าไม่มีใน cache ให้ query database
        $results = $this->executeQuery($params);

        // ✅ 4. เก็บผลลัพธ์ใน cache (TTL แตกต่างกันตามประเภทข้อมูล)
        $ttl = $this->getCacheTTL($params);
        $cache->save($cacheKey, $results, $ttl);

        log_message('info', "Cached data for key: {$cacheKey} (TTL: {$ttl}s)");

        return $results;
    }

    public function getKeyResultsDebug($params = [])
    {
        $cacheKey = $this->generateCacheKey($params);
        $cache = \Config\Services::cache();

        echo "<div style='background:yellow;padding:10px;'>";
        echo "🔑 Cache Key: {$cacheKey}<br>";

        if ($cachedData = $cache->get($cacheKey)) {
            echo "✅ Cache HIT - ดึงจาก cache<br>";
            echo "⏱️ เวลาที่ใช้: ~5ms<br>";
            echo "</div>";
            return $cachedData;
        }

        echo "❌ Cache MISS - Query database<br>";
        $start = microtime(true);
        $results = $this->executeQuery($params);
        $end = microtime(true);

        $queryTime = ($end - $start) * 1000;
        echo "⏱️ Query เวลา: {$queryTime}ms<br>";

        $cache->save($cacheKey, $results, $this->getCacheTTL($params));
        echo "💾 บันทึกใน cache แล้ว<br>";
        echo "</div>";

        return $results;
    }

    /**
     * ✅ สร้าง cache key ที่ unique
     */
    private function generateCacheKey($params)
    {
        // เรียงลำดับ params เพื่อให้ได้ key เดียวกัน
        ksort($params);

        // ใช้ MD5 เพื่อให้ key สั้นและ clean
        $keyString = 'keyresults_' . md5(serialize($params));

        // เพิ่ม version เพื่อ invalidate cache เมื่อมีการเปลี่ยนแปลง structure
        return $keyString . '_v1';
    }

    /**
     * ✅ กำหนด TTL ตามประเภทการใช้งาน
     */
    private function getCacheTTL($params)
    {
        // ถ้าเป็นการ count อย่างเดียว - cache นาน (30 นาที)
        if (!empty($params['count_only'])) {
            return 1800;
        }

        // ถ้ามี pagination - cache สั้น (5 นาที)
        if (!empty($params['limit'])) {
            return 300;
        }

        // ถ้าเป็นการดึงข้อมูลเฉพาะ ID - cache ปานกลาง (15 นาที)
        if (!empty($params['conditions']['key_result_id'])) {
            return 900;
        }

        // default - cache 10 นาที
        return 600;
    }

    /**
     * ✅ แยก query logic ออกมา
     */
    private function executeQuery($params)
    {
        $builder = $this->db->table('objective_groups og')
            ->select('
                og.id AS og_id, og.name AS og_name
                ,obj.id AS objective_id, obj.sequence_no AS objective_sequence, concat(obj.sequence_no,". ", obj.name) AS objective_name
                ,kt.id AS key_result_template_id, kt.sequence_no AS key_result_template_sequence
                , concat(obj.sequence_no,".",kt.sequence_no," ", kt.name) AS key_result_template_name
                ,kr.id AS key_result_id, kr.key_result_year, kr.sequence_no AS key_result_sequence
                , concat(kr.sequence_no,". ", kr.name) AS key_result_name
                , kr.target_value, kr.target_unit
                , kd.role as key_result_dep_role
            ')
            ->join('objectives obj', 'og.id = obj.objective_group_id')
            ->join('key_result_templates kt', 'kt.objective_id = obj.id')
            ->join('key_results kr', 'kr.key_result_template_id = kt.id')
            ->join('key_result_departments kd', 'kr.id = kd.key_result_id');

        // เงื่อนไข dynamic
        if (!empty($params['conditions']['key_result_id'])) {
            $builder->where('kr.id', $params['conditions']['key_result_id']);
        }

        if (!empty($params['conditions']['department_id'])) {
            $builder->where('kd.department_id', $params['conditions']['department_id']);
        }

        if (!empty($params['conditions']['year'])) {
            $builder->where('kr.key_result_year', $params['conditions']['year']);
        }

        // ค้นหาด้วย keyword
        if (!empty($params['keyword'])) {
            $keyword = trim($params['keyword']);
            $builder->groupStart()
                ->like('kr.name', $keyword)
                ->orLike('kt.name', $keyword)
                ->orLike('obj.name', $keyword)
                ->groupEnd();
        }

        // นับจำนวนรายการอย่างเดียว
        if (!empty($params['count_only'])) {
            return $builder->countAllResults();
        }

        // Pagination
        if (!empty($params['limit'])) {
            $builder->limit($params['limit'], $params['offset'] ?? 0);
        }

        // เรียงลำดับ
        $builder->orderBy('og.id')
                ->orderBy('obj.sequence_no')
                ->orderBy('kt.sequence_no')
                ->orderBy('kr.sequence_no');

        return $builder->get()->getResultArray();
    }

    /**
     * ✅ ฟังก์ชัน clear cache เมื่อมีการเปลี่ยนแปลงข้อมูล
     */
    public function clearKeyResultsCache($keyResultId = null)
    {
        $cache = \Config\Services::cache();

        if ($keyResultId) {
            // Clear cache เฉพาะ key result นี้
            $pattern = 'keyresults_*key_result_id*' . $keyResultId . '*';
            $cache->deleteMatching($pattern);
        } else {
            // Clear cache ทั้งหมดที่เกี่ยวข้องกับ key results
            $cache->deleteMatching('keyresults_*');
        }

        log_message('info', 'Cleared key results cache' . ($keyResultId ? " for ID: {$keyResultId}" : ' (all)'));
    }

    public function getDepartmentsByKeyResult($key_result_id)
    {
        return $this->db->table('key_result_departments kd')
            ->select('kd.role, d.short_name, d.name AS full_name')
            ->join('departments d', 'kd.department_id = d.id')
            ->where('kd.key_result_id', $key_result_id)
            ->get()
            ->getResultArray();
    }


}
