<?php

class Auth_Controller extends RestApi_Controller
{
    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: false");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }
        parent::__construct();
        $this->load->library('api_auth');
        $this->load->model('api_model');
    }  

    function register()
    {
        $fullName = $this->input->Post('cs_user_full_name');
        $userName = $this->input->Post('cs_user_name');
        $email = $this->input->post('cs_user_email');
        $password = $this->input->post('cs_user_password');
        $Conform_password = $this->input->post('cs_passconf');


        $this->form_validation->set_rules('cs_user_full_name','Name','required');
        $this->form_validation->set_rules('cs_user_name','Name','required|is_unique[users.cs_user_name]');
        $this->form_validation->set_rules('cs_user_email', 'Email', 'required|valid_email|is_unique[users.cs_user_email]');
        $this->form_validation->set_rules('cs_user_password','Password','required');
        $this->form_validation->set_rules('cs_passconf', 'Password Confirmation', 'required|matches[cs_user_password]');

        if($this->form_validation->run() == true)
        {
            $data = array(
                'cs_user_full_name' => $fullName,
                'cs_user_name' => $userName,
                'cs_user_email' => $email,
                'cs_user_password' => sha1($password),
                'created_at' => date('Y-m-d H:i:s'),
            );
            $this->api_model->registerUser($data);

            $responseData = array(
                'status' => true,
                'message' => 'Successfully Registerd',
                'data' => []
            );
            return $this->response($responseData,200);
        }
        else
        {
            $responseData = array(
                'status' => false,
                'message' => 'Fill all the required fields',
                'data' => []
            );
            return $this->response($responseData);
        }
    }

    function login(){
    
        $userName = $this->input->post('cs_user_name');
        $password = $this->input->post('cs_user_password');

        $this->form_validation->set_rules('cs_user_name','Name','required');
        $this->form_validation->set_rules('cs_user_password','Password','required');

        if($this->form_validation->run() == true)
        {

            $data = array(
                'cs_user_name' => $userName,
                'cs_user_password' => sha1($password),
            );
            $loginStatus = $this->api_model->checkLogin($data);

            if($loginStatus)
            {
                $userId = $loginStatus->cs_user_id;
                $user_full_name = $this->api_model->userDetails($userName)->cs_user_full_name;
                $bearToken = $this->api_auth->generateToken($userId);
                $responseData = array(
                    'status' => true,
                    'message' => 'Successfully Logged In',
                    'token' => $bearToken,
                    'name' => $user_full_name,
                );
                return $this->response($responseData,200);
            }
            else
            {
                $responseData = array(
                    'status' => false,
                    'message' => 'Invalid Crendetials',
                    'data' => []
                );
                return $this->response($responseData);
            }
        }
        else
        {
            $responseData = array(
                'status' => false,
                'message' => 'Fill all the required fields',
                'data' => []
            );
            return $this->response($responseData);
        }
        
    } 

    
    function forgetPassword(){

        $userName = $this->input->Post('cs_user_name');
        $email = $this->input->post('cs_user_email');
        $password = $this->input->post('cs_password');
        $Conform_password = $this->input->post('cs_conform_password');

        $this->form_validation->set_rules('cs_password','Password','required');
        $this->form_validation->set_rules('cs_conform_password', 'Password Confirmation', 'required|matches[cs_password]');

        if($this->form_validation->run() == true)
        {
                $data = array(
                    'cs_user_password' => sha1($password),
                    'updated_at' => date('Y-m-d H:i:s'),
                );
                $this->api_model->updateUser($userName,$data);
    
                $responseData = array(
                    'status' => true,
                    'message' => 'Successfully Changed password',
                    'data' => []
                );
                return $this->response($responseData,200);  
        }
        else
        {
            $responseData = array(
                'status' => false,
                'message' => 'Fill all the required fields',
                'data' => []
            );
            return $this->response($responseData);
        }
    }

    function checkUser(){

        $userName = $this->input->Post('cs_user_name');
        $email = $this->input->post('cs_user_email');
        
        $this->form_validation->set_rules('cs_user_name','Name','required');
        $this->form_validation->set_rules('cs_user_email', 'Email', 'required');
        
        $userStatus = $this->api_model->checkUser($userName,$email);
            if($userStatus == true){

                $data = array(
                    'userName' => $userName,
                    'email' =>$email
                );
                
                $responseData = array(
                    'status' => true,
                    'message' => 'User is registered this system',
                    'data' => $data
                );
                return $this->response($responseData,200);
            }
            else{
                $responseData = array(
                    'status' => false,
                    'message' => 'This user name is not registered',
                    'data' => []
                );
                return $this->response($responseData,200);
            }
        }
    }