<?php

class Api_Controller extends RestApi_Controller
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

        if($this->api_auth->isNotAuthenticated())
        {
            $data = array(
                'status' => false,
                'message' =>'unauthorised',
                'data'=>[]
            );
            $this->response($data);
        }
    }

    function getPost()
    {
        $user_id = $this->api_auth->getUserId();
        $this->load->model('api_model');
        $allPost = $this->api_model->getAllpost();

        foreach($allPost as $post){
            if($post->user_id == $user_id){
                $post->is_edit = true;
            }else{
                $post->is_edit = false;
            }
        }
        $data = array(
            'status' => true,
            'message' =>'authorised',
            'data'=>$allPost
        );
        $this->response($data,200);
    }

    function createPost(){

        $user_id = $this->api_auth->getUserId();
        
        $post_description = $this->input->Post('post_description');

        $this->form_validation->set_rules('post_description','Name','required');

        if($this->form_validation->run() == true)
        {
            $data = array(
                'user_id' => $user_id,
                'post_description' => $post_description,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $createPost = $this->api_model->storePost($data);

            $responseData = array(
                'status' => true,
                'message' => 'Successfully created post',
                'data' => []
            );
            return $this->response($responseData,200);
        }
        else{
            $responseData = array(
                'status' => false,
                'message' => 'Fill all the required fields',
                'data' => []
            );
            return $this->response($responseData);
        }  
    }

    function createComment(){

        $post_id = $this->input->Post('post_id');
        $post_comment_user_id = $this->api_auth->getUserId();
        
        $post_comment = $this->input->Post('post_comment');

        $this->form_validation->set_rules('post_comment','Name','required');

        if($this->form_validation->run() == true)
        {
            $data = array(
                'post_comment_user_id' => $post_comment_user_id,
                'post_comment' => $post_comment,
                'post_id' => $post_id,
                'created_at' => date('Y-m-d H:i:s'),
            );
            $createPost = $this->api_model->storecomment($data);

            $responseData = array(
                'status' => true,
                'message' => 'Successfully commented post',
                'data' => []
            );
            return $this->response($responseData,200);
        }
        else{
            $responseData = array(
                'status' => false,
                'message' => 'Fill all the required fields',
                'data' => []
            );
            return $this->response($responseData);
        }   
    }

    function searchUser(){

        $cs_user_full_name = $this->input->Post('cs_user_full_name');
        $get_user_id = $this->api_model->getUserId($cs_user_full_name);
       
        if($get_user_id != null){
            $get_posts = $this->api_model->getPosts($get_user_id->cs_user_id);
            if($get_posts != null){
                $responseData = array(
                    'status' => true,
                    'message' => 'This user have posts',
                    'data' => $get_posts
                );
                return $this->response($responseData,200);
            }
            else{
                $responseData = array(
                    'status' => false,
                    'message' => "This user haven't posts",
                    'data' => []
                );
                return $this->response($responseData,200);
            }  
        }
        else{
            $responseData = array(
                'status' => false,
                'message' => "This user haven't Account",
                'data' => []
            );
            return $this->response($responseData);
        } 
    }

    function deletePost(){

        $post_id = $this->input->Post('post_id');
        $post_user_id = $this->api_auth->getUserId();
        $delete_post_ststus =$this->api_model->deletePost($post_id,$post_user_id);
        if($delete_post_ststus){
            $responseData = array(
                'status' => true,
                'message' => "This post have Successfully deleted",
                'data' => []
            );
            return $this->response($responseData,200);
        }else{
            $responseData = array(
                'status' => false,
                'message' => "This post have Unsuccessfully deleted",
                'data' => []
            );
            return $this->response($responseData);
        }

    }

    function getComments()
    {
        
        $post_id = $this->input->post('post_id');
        $allComments = $this->api_model->getAllComments($post_id);   // get all comments

        $data = array(
            'id' => $post_id,
            'status' => true,
            'message' =>'success',
            'data'=>$allComments
        );
        $this->response($data,200);
    }
}