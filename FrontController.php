<?php
defined('BASEPATH') or exit('No direct script access allowed');
class FrontController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['FrontModel']);
    }

    public function uniqueId()
    {
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNIPQRSTUVWXYZ';
        $nstr = str_shuffle($str);
        $unique_id = substr($nstr, 0, 10);
        return $unique_id;
    }

    //----------------------------- Upload single file-----------------------------
    public function doUploadImage($path, $file_name)
    {
        $config = array(
            'upload_path' => $path,
            'allowed_types' => "jpeg|jpg|png|pdf",
            'file_name' => rand(11111, 99999),
            'max_size' => "5120",
        );
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if ($this->upload->do_upload($file_name)) {
            $data = $this->upload->data();
            return $data['file_name'];
        } else {
            return $this->upload->display_errors();
        }
    }

    //----------------------------- Upload multiple files-------------------------------------------
    public function upload_files($path, $file_name)
    {
        $this->output->set_content_type('application/json');
        $files = $_FILES[$file_name];
        $config = array(
            'upload_path' => $path,
            'allowed_types' => 'jpeg|jpg|gif|png|pdf',
            'overwrite' => 1,
        );
        $this->load->library('upload', $config);
        $images = array();
        $i = 0;
        foreach ($files['name'] as $key => $image) {
            $_FILES['images[]']['name'] = $files['name'][$key];
            $_FILES['images[]']['type'] = $files['type'][$key];
            $_FILES['images[]']['tmp_name'] = $files['tmp_name'][$key];
            $_FILES['images[]']['error'] = $files['error'][$key];
            $_FILES['images[]']['size'] = $files['size'][$key];

            $title = rand('1111', '9999');
            $image = explode('.', $image);
            $count = count($image);
            $extension = $image[$count - 1];
            $fileName = $title . '.' . $extension;
            $images[$i] = $fileName;
            $config['file_name'] = $fileName;
            $this->upload->initialize($config);

            if ($this->upload->do_upload('images[]')) {
                $this->upload->data();
            } else {
                return $this->upload->display_errors();
            }
            $i++;
        }
        return $images;
    }

    public function genrateToken()
    {
        $token = openssl_random_pseudo_bytes(16);
        $token = bin2hex($token);
        return $token;
    }

    public function sendMail($data)
    {
        $this->load->library('email');
        $domain_name='webapp';
        $email=$this->FrontModel->getEmailByDomain($domain_name)['domain_email'];
        $to = $email;
        $subject = $data['subject'];
        $message = $data['message'];
        $header = "from:ambuj.deisgnoweb@gmail.com \r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html\r\n";
        $retval = mail($to, $subject, $message, $header);
        return true;
    }

    public function index(){
        $data['title']="Webapp";
        $this->load->view('front/index',$data);
    }

    public function doInsertLead(){
        $this->output->set_content_type('application/json');
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('email_id', 'E-mail', 'required');
        $this->form_validation->set_rules('country', 'Country', 'required');
        $this->form_validation->set_rules('phone', 'Phone', 'required');
        $this->form_validation->set_rules('requirements', 'Requirement', 'required');
        if ($this->form_validation->run() === FALSE) {
            $this->output->set_output(json_encode(['result' => 0, 'errors' => $this->form_validation->error_array()]));
            return FALSE;
        }

        $insertdata=array(
            'name'   => $this->input->post('name'),
            'email'   => $this->input->post('email_id'),
            'country'   => $this->input->post('country'),
            'phone'   => $this->input->post('phone'),
            'requirements'   => $this->input->post('requirements'),
        );
        $result=$this->FrontModel->insertLead($insertdata);
        
        if ($result) {
            $this->sendMail($data=null);
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Form Submitted','url' => base_url('')]));
            return FALSE;
        } else {
            $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something Went Wrong']));
            return FALSE;
        }
        
    }
    public function doInsertLead1(){
        $this->output->set_content_type('application/json');
        $this->form_validation->set_rules('name1', 'Name', 'required');
        $this->form_validation->set_rules('lastname1', 'Last Name', 'required');
        $this->form_validation->set_rules('email1', 'E-mail', 'required');
        $this->form_validation->set_rules('phone1', 'Phone', 'required');
        $this->form_validation->set_rules('company_name', 'Company Name', 'required');
        $this->form_validation->set_rules('requirements1', 'Requirement', 'required');
        if ($this->form_validation->run() === FALSE) {
            $this->output->set_output(json_encode(['result' => 0, 'errors' => $this->form_validation->error_array()]));
            return FALSE;
        }

        $insertdata=array(
            'name'   => $this->input->post('name1'),
            'email'   => $this->input->post('email1'),
            'country'   => $this->input->post('country1'),
            'phone'   => $this->input->post('phone1'),
            'company_name'   => $this->input->post('company_name'),
            'last_name'      => $this->input->post('last_name1'),
            'requirements'   => $this->input->post('requirements1'),
        );
        $result=$this->FrontModel->insertLead($insertdata);
        
        if ($result) {
            $this->output->set_output(json_encode(['result' => 1, 'msg' => 'Form Submitted','url' => base_url('')]));
            return FALSE;
        } else {
            $this->output->set_output(json_encode(['result' => -1, 'msg' => 'Something Went Wrong']));
            return FALSE;
        }
        
    }

    function validate()
	{
		$captcha_response = trim($this->input->post('g-recaptcha-response'));
		if($captcha_response != '')
		{
			$keySecret = '6LdMlfYiAAAAAMigdMDx_Z0r1gkiHljHVfA87_lx';

			$check = array(
				'secret'		=>	$keySecret,
				'response'		=>	$this->input->post('g-recaptcha-response')
			);

			$startProcess = curl_init();

			curl_setopt($startProcess, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");

			curl_setopt($startProcess, CURLOPT_POST, true);

			curl_setopt($startProcess, CURLOPT_POSTFIELDS, http_build_query($check));

			curl_setopt($startProcess, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($startProcess, CURLOPT_RETURNTRANSFER, true);

			$receiveData = curl_exec($startProcess);

			$finalResponse = json_decode($receiveData, true);

			if($finalResponse['success'])
			{
				$storeData = array(
					'first_name'	=>	$this->input->post('first_name'),
					'last_name'		=>	$this->input->post('last_name'),
					'age'			=>	$this->input->post('age'),
					'gender'		=>	$this->input->post('gender')
				);

				$this->captcha_model->insert($storeData);

				$this->session->set_flashdata('success_message', 'Data Stored Successfully');

				redirect('captcha');
			}
			else
			{
				$this->session->set_flashdata('message', 'Validation Fail Try Again');
				redirect('captcha');
			}
		}
		else
		{
			$this->session->set_flashdata('message', 'Validation Fail Try Again');

			redirect('captcha');
		}
	}

}

