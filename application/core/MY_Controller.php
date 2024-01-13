<?php

class MY_Controller extends CI_Controller
{
    function __construct(){
        parent:: __construct();
    }
}

class RestApi_Controller extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    public function response($response , $code =401)
    {
        $this->output
        ->set_status_header($code)
        ->set_content_type('application/json' , 'utf-8')
        ->set_output(json_encode($response , JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
        ->_display();
        exit;
    }
}