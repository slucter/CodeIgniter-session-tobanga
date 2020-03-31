<?php   

class Auth_model extends CI_Model{

    public function registUser($data)
    {
        $this->db->insert('user', $data);
    }
}