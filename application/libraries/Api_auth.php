<?php
class Api_Auth
{
    /* Simple token based API authentication library
    Author: sanjeev sharma
    Email:  codextimeindia@gmail.com
    YT Channel:  https://youtube.com/codextime
    */
    private $encryptKey = 'gdgdsnfg54n5t594g54gn4g04gj09j5gh043gh3403hj'; //random generated secret key
    private $secretIV = '5g8g5jg59jjhgh569538545t90yjyh04yj4y606ygfmhkgf'; //random generated initialization vector

    
    private $isTokenExpire=true; //set true for token expiry, false for never expire
    private $tokenExpriryInHours=24; //token expiry time in Hours


    private $encryptionMethod = "AES-256-CBC";
    private $output=null;
  

    public function encrypt($string)
    {
        $key = hash('sha256', $this->encryptKey);
        $iv = substr(hash('sha256', $this->secretIV), 0, 16);
        $encryptedText = openssl_encrypt($string, $this->encryptionMethod, $key, 0, $iv);
        return $encryptedText;
    }

    public function decrypt($string)
    {
        $key = hash('sha256', $this->encryptKey);
        $iv = substr(hash('sha256', $this->secretIV), 0, 16);
        $decryptedText = openssl_decrypt($string, $this->encryptionMethod, $key, 0, $iv);
        return $decryptedText;
    }

    public function generateToken($userId)
    {
        $tokenString = base64_encode(random_bytes(64));
        $token = strtr($tokenString, '+/', '-_');
        $mainToken = hash('sha256', $token);
        $this->storeTokenInAuthTokens($userId,$mainToken);
        $bearerToken =$mainToken.'.'.$this->encrypt($userId);
        return $bearerToken;
    }

    /* check user authentication */
    public function isNotAuthenticated()
    {
        $mainToken = $this->getMainToken();
        //var_dump($mainToken);exit;
        if($mainToken!=false)
        {
            $userId = $this->getUserId();
            $authStatus = $this->checkTokenFromUserTable($userId,$mainToken);
            if($authStatus==true)
            {
                return false;
            }
            else 
            {
                return true;
            }
        }
        else 
        {
            return false;
        }
       
    }


    /* get User Id */

    public function getUserId()
    {
        if($this->getTokenParts()!=false) 
        {
            $tokenPart = $this->getTokenParts();
            //print_r($tokenPart);exit;
            $userIdToken = $tokenPart[1];
            $userId = $this->decrypt($userIdToken);
            return $userId;
        }   
        else 
        {
            echo 'Token parts Error!';exit;
        }
    }


    /* get Token parts */
    public function getTokenParts()
    {
        
        $bearerToken = $this->getBearerToken();
        
        if($bearerToken==null) 
        {
            $err = array(
                'status' =>false,
                'message' => 'Token not found',
            );
            return json_encode($err);exit;
        }
        else 
        {
            $tokenChunks = explode(".", $bearerToken);
            return $tokenChunks;
           
        }
    }


    /* Get Main Token */
    public function getMainToken()
    {
        if($this->getTokenParts()!=false) 
        {
            $tokenPart = $this->getTokenParts();
            $mainToken = $tokenPart[0];
            
            return $mainToken;
        }   
        else 
        {
            return false;
        }
    }


    /** 
     * Get header Authorization
     * */
    function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } 
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) 
        { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } 
        elseif (function_exists('apache_request_headers')) 
        {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }

        return $headers;
    }

    /**
     * get access token from header
     * */
    function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    

    function checkTokenFromUserTable($userId,$mainToken)
    {
        $CI= &get_instance();  //Store instance in a variable.
        $CI->load->database();
        $CI->db->select('*');
        $CI->db->from('auth_tokens');
        $CI->db->where(['user_id' => $userId,'token'=>$mainToken]);
        if($this->isTokenExpire)
        {
            $expiryDate = date('Y-m-d H:i:s');
            $CI->db->where('expiry_date >=',$expiryDate);
        }
        $query = $CI->db->get();
        
        //echo $query->num_rows();exit;
        if($query->num_rows() == 0) 
        {
            return FALSE;
        }
        else 
        {
            return TRUE;
        }
    }

    function storeTokenInAuthTokens($userId,$token)
    {
        $expirtyDate = $this->getTimeAfterHours($this->tokenExpriryInHours);
        $CI= &get_instance();  //Store instance in a variable.
        $CI->load->database();
        if($CI->db->query("SELECT * FROM auth_tokens WHERE user_id='$userId'")->num_rows() > 0)
        {
            $CI->db->where('user_id',$userId);
            $CI->db->update('auth_tokens',['expiry_date' =>$expirtyDate,'user_id'=>$userId,'token'=>$token,'created_at'=>date('Y-m-d H:i')]);
        }
        else 
        {
            $CI->db->insert('auth_tokens',['expiry_date' =>$expirtyDate,'user_id'=>$userId,'token'=>$token,'created_at'=>date('Y-m-d H:i')]);
        }  
    }

    function getTimeAfterHours($hours)
    {
        if($hours!=null || $hours !=0)
        {
            return  date('Y-m-d H:i:s', strtotime($hours.' hour'));
        }
        else 
        {
            $hours=24; //setting default time
            return  date('Y-m-d H:i:s', strtotime($hours.' hour'));
        } 
    }


    
}
