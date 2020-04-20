<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends CI_Controller {
	
	public function __construct () {
		parent::__construct();
		$this->app = app();
		$this->smtp = smtp();
	}

	public function index() {
		$data = array(
			'title' => "Login"
		);
		$this->load->view('welcome_message', $data);
	}

	// Compress image
	public function compressImage($source, $destination, $quality) {
	  	$info = getimagesize($source);
		if ($info['mime'] == 'image/jpeg') {
			$image = imagecreatefromjpeg($source);
		} elseif ($info['mime'] == 'image/gif') {
	    	$image = imagecreatefromgif($source);
	  	} elseif ($info['mime'] == 'image/png') {
	    	$image = imagecreatefrompng($source);
	  	}

	  	return imagejpeg($image, $destination, $quality);
	}

	public function foto() {
		if(isset($_FILES['foto'])){

			$Quality = 40;

		  	// Getting file name
		  	$filename = md5($_FILES['foto']['name'].rand().date('Y-m-d H:i:s')).'.png';	 
		  	// Valid extension
		  	$valid_ext = array('png','jpeg','jpg','gif');
		  	// Location
		  	$target_file = "assets/foto/".$filename;
		  	// file extension
		  	$file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
		  	$file_extension = strtolower($file_extension);

		  	// Check extension
		  	if(in_array($file_extension, $valid_ext)){
			    // Compress Image
			    $this->compressImage($_FILES['foto']['tmp_name'], $target_file, $Quality);

			    $data[$this->input->post('kolom')] = $filename;
				$this->db->where('email', $this->input->post('email'));
				$this->db->update('user', $data);
			    
				$this->db->where('email', $this->input->post('email'));
				$res = $this->db->get('user')->row();

				$resx['status'] = 1;
				$resx['message'] = "Upload foto baru berhasil.";
				$resx['data'] = $res;
				$resx['code'] = 0;
				$resx['redirect'] = '/tab4';
				echo json_encode($resx);
			}else{				
				$resx['status'] = 0;
				$resx['message'] = "Kesalahan tipe file foto!";
				$resx['data'] = null;
				$resx['code'] = 0;
				$resx['redirect'] = '';
			}
		}
	}

}