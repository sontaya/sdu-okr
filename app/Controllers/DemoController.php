<?php
namespace App\Controllers;

class DemoController extends TemplateController
{
    protected $allowed = [];

    public function index()
    {

        $this->data['title'] = 'Demo Dual List';
        $this->data['cssSrc'] = [];
        $this->data['jsSrc'] = [
            'assets/js/demo.js'
        ];

        $this->contentTemplate = 'demo/duallist';
        return $this->render();
    }




}