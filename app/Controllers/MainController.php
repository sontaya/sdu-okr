<?php

namespace App\Controllers;

use CodeIgniter\Controller;


class MainController extends TemplateController
{

    protected $allowed = ['index'];

    public function __construct()
    {

    }

    public function index()
    {
        $this->data['title'] = 'My Page Title';
        $this->contentTemplate = 'pages/index';
        return $this->render();

    }

    public function dashboard()
    {
        $this->data['title'] = 'My Dashboard ';
        $this->data['full_name'] = session('full_name');
        $this->data['uid'] = session('uid');
        $this->contentTemplate = 'pages/dashboard';
        return $this->render();
    }


}
