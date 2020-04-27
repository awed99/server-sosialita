<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {
	
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

	public function auth() {
		$email = strtolower($this->input->post('Email'));
		$pass = sha1($this->input->post('Password'));

		$where['email'] = $email;
		$where['password'] = $pass;
		$where['active'] = 1;
		$this->db->where($where);
		$isset = $this->db->get('user')->num_rows();
		if ($isset > 0) {
			$this->db->where($where);
			$res = $this->db->get('user')->row();
			$this->db->where('id_user', $res->id_user);
			$addr = $this->db->get('alamat')->row();
			$resx['status'] = 1;
			$resx['message'] = "Kamu berhasil login.";
			$resx['data']['user'] = json_encode($res);
			$resx['data']['address'] = json_encode($addr);
			$resx['code'] = 0;
			$resx['redirect'] = '/tab1';
			echo json_encode($resx);
		} else {
			$resx['status'] = 0;
			$resx['message'] = "Email / Sandi Salah!";
			$resx['data'] = null;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);		
		}
	}

	public function password_forgot() {
		$code = rand(100000, 999999);
		$email = strtolower($this->input->post('Email'));
		$message = '<p><strong>Selamat datang di '.$this->app['name'].'!</strong><br/><br/> Kamu telah meminta <b>Perubahan SANDI</b> di aplikasi '.$this->app['name'].'. Tetapi Kamu harus mengkonfirmasi kode yang dikirimkan melalui email ini untuk <b>Mengganti Sandi</b>. silakan masukan kode di bawah ini untuk <b>Mengganti Sandi</b> Akun Kamu & membuktikan bahwa itu adalah Kamu.</p><br/><b><h2>'.$code.'</h2></b><br/><br/>Copyright &copy; '.$this->app['name'].' '.date('Y');

		include "classes/class.phpmailer.php";
		$mail = new PHPMailer;
		$mail->IsSMTP();
		$mail->SMTPSecure = $this->smtp['Ssl'];
		$mail->Host = $this->smtp['Host']; //hostname masing-masing provider email
		$mail->SMTPDebug = 0;
		$mail->Port = $this->smtp['Port'];
		$mail->SMTPAuth = true;
		$mail->Username = $this->smtp['Username']; //user email
		$mail->Password = $this->smtp['Password']; //password email
		$mail->SetFrom($this->smtp['MailFrom'], $this->smtp['NameFrom']); //set email pengirim
		$mail->Subject = "Permintaan ubah sandi ".$this->app['name']; //subyek email
		$mail->AddAddress($email,$this->app['name']." User"); //tujuan email
		$mail->MsgHTML($message);
		if ($mail->Send()) {
			$resx['status'] = 1;
			$resx['message'] = "Periksa email kamu.";
			$resx['code'] = $code;
		} else {
			$resx['status'] = 0;
			$resx['message'] = "Kegagalan Server!";
			$resx['code'] = 0;
		}
		
		$resx['data'] = null;
		$resx['redirect'] = null;
		echo json_encode($resx);
	}

	public function password_new() {
		if ($this->input->post('Password')) {
			$data['password'] = sha1($this->input->post('Password'));
			$where['email'] = strtolower($this->input->post('Email'));
			$this->db->where($where);
			$this->db->update('user' ,$data);

			$resx['status'] = 1;
			$resx['message'] = "Kamu berhasil merubah sandi.";
			$resx['code'] = 0;
			$resx['data'] = null;
			$resx['redirect'] = '/auth';
			echo json_encode($resx);
		}
	}

	public function cek_email() {
		$email = strtolower($this->input->post('email'));
		echo $this->db->where('email', $email)->get('user')->num_rows();
	}

	public function activate() {
		$where['email'] = strtolower($this->input->post('email'));
		$set['active'] = 1;
		$this->db->where($where);
		$this->db->update('user', $set);

		$where['email'] = $where['email'];
		$where['active'] = 1;
		$this->db->where($where);
		$res = $this->db->get('user')->row();
		if ($res) {
			$resx['status'] = 1;
			$resx['message'] = "Selamat Akun Sosialita kamu sudah aktif.";
			$resx['data'] = null;
			$resx['code'] = 0;
			$resx['redirect'] = 'auth/auth';
			echo json_encode($resx);
		} else {
			$resx['status'] = 0;
			$resx['message'] = 'Kegagalan Server!';
			$resx['data'] = null;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);		
		}
		
	}

	public function register_post() {
		$code = rand(100000, 999999);
		$post = $this->input->post();
		$email = strtolower($post['Email']);
		$password = sha1($post['Password']);
		$name = ucwords($post['Nama']);
		$dob = $post['Dob'];
		$phone = $post['Phone'];
		$active = 0;
		$created_time = date("Y-m-d H:i:s");

		$data['email'] = $email;
		$data['password'] = $password;
		$data['name'] = $name;
		$data['dob'] = $dob;
		$data['phone'] = $phone;
		$data['active'] = $active;
		$data['created_time'] = $created_time;
		$data['code'] = $code;
		
		$this->db->where('email', $email);
		$this->db->or_where('phone', $phone);
		$isset = $this->db->get('user');
		if ($isset) {
			$active = $isset->row()->active;
			if ($active === "1") {
				$resx['status'] = 2;
				$resx['message'] = "Email / No HP Kamu sudah pernah didaftarkan.";
				$resx['data'] = null;
				$resx['redirect'] = "";
				$resx['code'] = 0;
				echo json_encode($resx);
				die;
			} elseif ($active === "2") {
				$resx['status'] = 0;
				$resx['message'] = "Akun kamu diblokir oleh sistem!";
				$resx['data'] = null;
				$resx['redirect'] = "";
				$resx['code'] = 0;
				echo json_encode($resx);
				die;
			} elseif ($active === "0") {
				$resx['status'] = 2;
				$resx['message'] = "Aktivasi akun dengan kode di email kamu!";
				$resx['data'] = null;
				$resx['redirect'] = "/auth/activate";
				$resx['code'] = $code;
				// echo json_encode($resx);
				// die;
			}
		} else {
			$res = $this->db->insert('user', $data);
		}

		// $status = 3;
		// $redirect = '';
		// $message = "Aktivasi akun dengan kode di SMS kamu!";
		
		// if (1 === 1) {
		// 	$link_activation = site_url('login/activate');
		// 	$message = '<p><strong>Selamat datang di '.$this->app['name'].'!</strong><br/><br/> Akun Kamu telah terdaftar di aplikasi '.$this->app['name'].'. Tetapi Kamu tidak dapat Login sebelum mengaktifkan akun Kamu. silakan masukan kode di bawah ini untuk mengaktifkan akun Kamu & membuktikan bahwa itu adalah Kamu.</p><br/><b><h2>'.$code.'</h2></b><br/><br/>Copyright &copy; '.$this->app['name'].' '.date('Y');

		// 	include "classes/class.phpmailer.php";
		// 	$mail = new PHPMailer;
		// 	$mail->IsSMTP();
		// 	$mail->SMTPSecure = $this->smtp['Ssl'];
		// 	$mail->Host = $this->smtp['Host']; //hostname masing-masing provider email
		// 	$mail->SMTPDebug = 0;
		// 	$mail->Port = $this->smtp['Port'];
		// 	$mail->SMTPAuth = true;
		// 	$mail->Username = $this->smtp['Username']; //user email
		// 	$mail->Password = $this->smtp['Password']; //password email
		// 	$mail->SetFrom($this->smtp['MailFrom'], $this->smtp['NameFrom']); //set email pengirim
		// 	$mail->Subject = "Aktivasi Akun ".$this->app['name']; //subyek email
		// 	$mail->AddAddress($email,$this->app['name']." User"); //tujuan email
		// 	$mail->MsgHTML($message);
		// 	if ($mail->Send()) {
		// 		// echo "Message has been sent";
		// 		$status = 1;
		// 		$redirect = "/auth/activate";
		// 		$message = "Aktivasi akun dengan kode di email kamu!";
		// 	} else {
		// 		// echo "<script>alert('Failed to sending message!')</script>";
		// 		$status = 2;
		// 		$redirect = '';
		// 		$message = "Kegagalan Server!";
		// 	}
		// }
		$status = 1;
		$redirect = "/auth/activate";
		$message = "Aktivasi akun dengan kode di SMS kamu!";

		$resx['status'] = $status;
		$resx['message'] = $message;
		$resx['data'] = null;
		$resx['code'] = $code;
		$resx['redirect'] = $redirect;
		echo json_encode($resx);
	}

	public function address() {
		$post = $this->input->post();
		$data['id_user'] = $post['id_user'];
		$data['latitude'] = $post['latitude'];
		$data['longitude'] = $post['longitude'];
		$data['alamat'] = $post['alamat'];
		$data['nomor'] = $post['nomor'];
		$data['jalan'] = $post['jalan'];
		$data['negara'] = $post['negara'];
		$data['kode_negara'] = $post['kode_negara'];
		$data['kode_pos'] = $post['kode_pos'];
		$data['provider'] = $post['provider'];
		$data['daerah'] = $post['daerah'];
		$data['kelurahan'] = $post['kelurahan'];
		$data['kecamatan'] = $post['kecamatan'];
		$data['kota'] = $post['kota'];
		$data['provinsi'] = $post['provinsi'];
		$insert = false;
		
		$id_user = $post['id_user'];
		$isset = $this->db->where('id_user', $id_user)->get('alamat')->num_rows();

		if ($isset === 0) {
			$insert = $this->db->insert('alamat', $data);
		} else {
			$where['id_user'] = $post['id_user'];
			$this->db->where($where);
			$update = $this->db->update('alamat', $data);
		}

		$this->db->where('id_user', $id_user);
		$addr = $this->db->get('alamat')->row();

		if ($update || $insert) {
			$resx['status'] = 1;
			$resx['message'] = "Sukses memperbaharui alamat.";
			$resx['data'] = ($addr);
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);
		} else {
			$resx['status'] = 0;
			$resx['message'] = 'Kegagalan Server!';
			$resx['data'] = null;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);		
		}
	}

	public function logout() {
		unset($_SESSION);
		session_destroy();
		redirect('dashboard');
	}

}