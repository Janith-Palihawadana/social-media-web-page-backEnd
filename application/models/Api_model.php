<?php

class Api_model extends CI_Model
{
    function registerUser($data)
    {
        $this->db->insert('users', $data);
    }

    function checkLogin($data)
    { 
        $this->db->where('cs_user_name',$data['cs_user_name']);
        $this->db->where('cs_user_password',$data['cs_user_password']);
        $query = $this->db->get('users');

       
        if($query->row() != null){
            return $query->row();
        }
        else{
            return false;
        }
    }

    function getAllpost(){
        $this->db->select('users.cs_user_full_name,posts.*')->where('is_active',1);
        $query = $this->db->from('posts','users');
        $this->db->join('users','users.cs_user_id = posts.user_id');
        $this->db->order_by('posts.created_at','DESC');
        $query = $this->db->get();
        return $query->result();
    }

    function storePost($data){
        $this->db->insert('posts', $data);
    }

    function storecomment($data){
        $this->db->insert('post_comments', $data);
    }

    function getPostInfo($post_ref){
        $this->db->select('post_id')->where('post_ref',$post_ref);
        $query = $this->db->get('posts');
        return $query->row();
    }

    function getUserId($user_full_name){
        $this->db->select('cs_user_id') ->where('cs_user_full_name',$user_full_name);
        $query = $this->db->get('users');

        if($query->row() != null){
            return $query->row();
        }
        else{
            return null;
        }
    }

    function deletePost($post_id,$user_id){
        
        $this->db->select('*') ->where('post_id',$post_id)->where('user_id',$user_id);
        $query =$this->db->get('posts');
        if($query->row() != null){
            $data = array(
                'is_active' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            );
            $this->db->where('post_id',$post_id)->where('user_id',$user_id);
            $this->db->update('posts', $data);
            return true;
        }
        else{
            return false;
        }
    }

    function checkUser($user_name,$email){
        $this->db->where('cs_user_name',$user_name)->where('cs_user_email',$email);
        $query =$this->db->get('users');

        if($query->row() != null){
            return true;
        }
        else{
            return false;
        }
    }

    function updateUser($user_name,$data){
        $this->db->where('cs_user_name',$user_name);
        $this->db->update('users', $data);    
    }

    function allCommentPost(){
        $this->db->select('users.cs_user_name,post_comments.*');
        $this->db->from('post_comments', 'users');
        $this->db->join('users','users.cs_user_id = post_comments.post_comment_user_id');
        $query = $this->db->get();
        return $query->result();
    }

    function getPosts($user_id){
        $this->db->select('posts.*,users.cs_user_full_name') ->where('posts.user_id',$user_id)->where('posts.is_active',1);
        $this->db->from('posts', 'users');
        $this->db->join('users','users.cs_user_id = posts.user_id');
        $this->db->order_by('posts.created_at','DESC');
        $query =$this->db->get();

        if($query->row() != null){
            return $query->result();
        }
        else{
            return null;
        }
    }

    function userDetails($user_name){
        $this->db->select('users.cs_user_full_name')->where('cs_user_name',$user_name);
        $query =$this->db->get('users');
        return $query->row();
    }

    function getAllComments($post_id){
        $query = $this->db->select('post_comments.*, users.cs_user_full_name as name')
                            ->from('post_comments ')
                            ->join('posts', 'posts.post_id = post_comments.post_id', 'LEFT')
                            ->join('users', 'post_comments.post_comment_user_id = users.cs_user_id', 'LEFT')
                            ->order_by('post_comments.created_at','DESC')
                            ->where('posts.post_id',$post_id)
                            ->get();
        return $query->result();
    }
    
}