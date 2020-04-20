<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends CI_Controller {
	
	public function __construct () {
		parent::__construct();
		$this->app = app();
		$this->smtp = smtp();
	}

	public function index() {
		$data = array(
			'title' => "Profile"
		);
		$this->load->view('welcome_message', $data);
	}

	public function ubahSandi() {
		$post = $this->input->post();


		$this->db->where('email', $post['email']);
		$this->db->where('password', sha1($post['password']));
		$issetPass = $this->db->get('user')->num_rows();
		if ($this->db->affected_rows() < 1) {
			$resx['status'] = 0;
			$resx['message'] = "Sandi Saat Ini yang kamu masukkan salah!";
			$resx['data'] = null;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);
			die;
		}

	    $data['password'] = sha1($post['password1']);
		$this->db->where('password', sha1($post['password']));
		$this->db->where('email', $post['email']);
		$this->db->update('user', $data);
		$resx['status'] = 1;
		$resx['message'] = "Sandi kamu berhasil diubah.";
		$resx['data'] = null;
		$resx['code'] = 0;
		$resx['redirect'] = '/tab4';
		echo json_encode($resx);
	}

	public function ubahProfil() {
		$post = $this->input->post();

	    $data['name'] = $post['nama'];
	    $data['dob'] = $post['dob'];
	    $data['phone'] = $post['phone'];
	    $data['hobi'] = $post['hobi'];
	    $data['tentang'] = $post['tentang'];

		$this->db->where('email', $post['email']);
		$this->db->update('user', $data);

		$this->db->where('email', $post['email']);
		$data = $this->db->get('user')->row();

		$resx['status'] = 1;
		$resx['message'] = "Profil kamu berhasil diubah.";
		$resx['data'] = $data;
		$resx['code'] = 0;
		$resx['redirect'] = '/tab4';
		echo json_encode($resx);
	}

	public function ubahBank() {
		$post = $this->input->post();

	    $where['id_user'] = $post['id_user'];

	    $data['id_user'] = $post['id_user'];
	    $data['nama'] = $post['nama'];
	    $data['rekening'] = $post['rekening'];
	    $data['bank'] = $post['bank'];
	    $data['utama'] = 1;
	    $data['status'] = 1;
	    $data['updated_date'] = date('Y-m-d H:i:s');

		$this->db->where('id_user', $where['id_user']);
		$this->db->update('user_bank', $data);

		$this->db->where('id_user', $where['id_user']);
		$isset = $this->db->get('user_bank')->num_rows();

		if ($isset === 0) {
			$this->db->insert('user_bank', $data);
		} else {
			$this->db->where('id_user', $where['id_user']);
			$this->db->update('user_bank', $data);
		}

		$this->db->where('id_user', $where['id_user']);
		$data = $this->db->get('user_bank')->row();

		$resx['status'] = 1;
		$resx['message'] = "Data Bank kamu berhasil diubah";
		$resx['data'] = $data;
		$resx['code'] = 0;
		$resx['redirect'] = '/tab4';
		echo json_encode($resx);
	}

	public function ubahCard() {
		$post = $this->input->post();

	    $where['id_user'] = $post['id_user'];

	    $data['id_user'] = $post['id_user'];
	    $data['nama_user'] = $post['nama'];
	    $data['cc_number'] = $post['cc_number'];
	    $data['cc_exp_month'] = $post['cc_exp_month'];
	    $data['cc_exp_year'] = $post['cc_exp_year'];
	    $data['cc_cvv'] = $post['cc_cvv'];
	    $data['utama'] = 1;
	    $data['status'] = 1;
	    $data['updated_date'] = date('Y-m-d H:i:s');

		$this->db->where('id_user', $where['id_user']);
		$this->db->update('user_credit_cards', $data);

		$this->db->where('id_user', $where['id_user']);
		$isset = $this->db->get('user_credit_cards')->num_rows();

		if ($isset === 0) {
			$this->db->insert('user_credit_cards', $data);
		} else {
			$this->db->where('id_user', $where['id_user']);
			$this->db->update('user_credit_cards', $data);
		}

		$this->db->where('id_user', $where['id_user']);
		$data = $this->db->get('user_credit_cards')->row();

		$resx['status'] = 1;
		$resx['message'] = "Kartu Kredit / Debit berhasil diubah";
		$resx['data'] = $data;
		$resx['code'] = 0;
		$resx['redirect'] = '/tab4';
		echo json_encode($resx);
	}

	public function daftarVIP() {
		$post = $this->input->post();

	    $where['id_user'] = $post['id_user'];

	    $dateNow = date('Y-m-d');
	    $dateExp = date('Y-m-d', strtotime("+1 months", strtotime($dateNow)));
	    $_dateNow = date('d F Y');
	    $_dateExp = date('d F Y', strtotime("+1 months", strtotime($dateNow)));

	    $data['id_user'] = $post['id_user'];
	    $data['saldo'] = 50000;
	    $data['tipe'] = 'Debit';
	    $data['keterangan'] = 'Pendaftaran VIP '.$_dateNow. ' s/d '.$_dateExp;
	    $data['waktu'] = date('Y-m-d H:i:s');
		$this->db->insert('transaction_saldo', $data);

	    $update['vip'] = 1;
	    $update['vip_expired'] = $dateExp;
	    $update['saldo'] = (int)$post['saldo'] - 50000;
		$this->db->where($where);
		$this->db->update('user', $update);

		$this->db->where($where);
		$data = $this->db->get('user')->row();

		$resx['status'] = 1;
		$resx['message'] = "Kamu berhasil jadi VIP";
		$resx['data'] = $data;
		$resx['code'] = 0;
		$resx['redirect'] = '/tab4';
		echo json_encode($resx);
	}

	public function normalizeVIP() {
		$post = $this->input->post();

	    $where['id_user'] = $post['id_user'];

	    $update['vip'] = 0;
	    $update['vip_expired'] = NULL;
		$this->db->where($where);
		$this->db->update('user', $update);

		$this->db->where($where);
		$data = $this->db->get('user')->row();

		$resx['data'] = $data;
		echo json_encode($resx);
	}

}