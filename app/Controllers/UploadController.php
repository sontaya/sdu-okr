<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class UploadController extends Controller
{
    protected $uploadPath;

    public function __construct()
    {
        $this->uploadPath = WRITEPATH . 'uploads/tmp/';

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0775, true);
        }
    }

    public function uploadTemp()
    {
        $file = $this->request->getFile('file');

        log_message('debug', 'DROPZONE DEBUG FILE: ' . json_encode($this->request->getFiles()));

        if (!$file || !$file->isValid()) {
            log_message('debug', '❌ getFile failed: ' . json_encode($this->request->getFiles()));
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบไฟล์ หรือไฟล์ไม่ถูกต้อง'
            ])->setStatusCode(400);
        }

        // ✅ เก็บชื่อไฟล์เดิม
        $originalName = $file->getClientName();
        $newName = $file->getRandomName();

        $file->move($this->uploadPath, $newName);

        return $this->response->setJSON([
            'success' => true,
            'filename' => $newName,
            'original_name' => $originalName // ✅ ส่งชื่อเดิมกลับไปด้วย
        ]);
    }

    public function removeTemp()
    {
        $json = $this->request->getJSON();
        $filename = $json->filename ?? null;

        if (!$filename) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบชื่อไฟล์'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $filePath = $this->uploadPath . $filename;

        if (is_file($filePath)) {
            unlink($filePath);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'ลบไฟล์สำเร็จ'
        ]);
    }
}