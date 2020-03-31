<?php   

class Auth extends CI_Controller{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }

    public function index()
    {
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        if($this->form_validation->run() == FALSE){

            $data['judul'] = 'Halaman Login';
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        }else{
            $this->_validasiLogin();
        }

    }
    private function _validasiLogin()
    {
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $user = $this->db->get_where('user',['email' => $email])->row_array();
        if($user){

            if($user['is_active'] == 1){

                if(password_verify($password, $user['password'])){
                    $ses = [
                        'email' => $user['email'],
                        'role_id' => $user['role_id'],
                    ];

                    $this->session->set_userdata($ses);
                    if($user['role_id'] == 1){
                        redirect('admin');
                    }else{
                        redirect('user');
                    }
                }else{
                    $this->session->set_flashdata('loginPassword','<div class="alert alert-danger" role="alert">Password Salah!</div>');
                    redirect('auth');
                }
            }else{
                $this->session->set_flashdata('loginActive','<div class="alert alert-danger" role="alert">Email belum di aktivasi</div>');
                redirect('auth');
            }
        }else{
            $this->session->set_flashdata('loginEmail','<div class="alert alert-danger" role="alert">Email Belum Terdaftar</div>');
            redirect('auth');
        }
    }
    public function registration()
    {
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim|regex_match[/[0-9]*/]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]',[
            'is_unique' => 'Email sudah terdaftar'
        ]);
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]',[
            'matches' => 'Password tidak sama!',
            'min_length' => 'Kurang Panjang!'
        ]);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');
        if($this->form_validation->run() == FALSE){
            $data['judul'] = 'Halaman Registrasi';
            $this->load->view('templates/auth_header',$data);
            $this->load->view('auth/register');
            $this->load->view('templates/auth_footer');
        }else{
            $data = [
                'nama' => htmlspecialchars($this->input->post('nama',true)),
                'email' => htmlspecialchars($this->input->post('email',true)),
                'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
                'gambar' => 'default.jpg',
                'is_active' => 1,
                'role_id' => 2,
                'date_create' => time()
            ];

            // $this->db->insert('user',$data);
            $this->auth_model->registUser($data);
            $this->session->set_flashdata('pesanRegis','<div class="alert alert-success" role="alert">Berhasil Mendaftar, silahkan login</div>');
            redirect('auth');
        }

    }

    public function logout()
    {
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');
        $this->session->set_flashdata('pesanLogout','<div class="alert alert-primary" role="alert">Berhasil Log out</div>');
        redirect('auth');
    }

}