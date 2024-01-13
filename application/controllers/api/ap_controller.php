<?php

class ap_controller extends RestApi_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('api_auth');
        $this->load->model('api_model');
    }  

    function register1()
    {
        $fullName = $this->input->post('cs_user_full_name');
        $userName = $this->input->Post('cs_user_name');
        var_dump('hit1',$userName);
    }
}