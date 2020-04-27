<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dompet extends CI_Controller {
	
	public function __construct () {
		parent::__construct();
		$this->app = app();
		$this->smtp = smtp();
	}

	public function index() {
		$data = array(
			'title' => "Dompet"
		);
		$this->load->view('welcome_message', $data);
	}

	public function get_balance() {
		echo xendit_curl('https://api.xendit.co/balance?account_type=CASH', [], $this->app['secretKey']);

	}

	public function create_virtual_account() {
		$post = $this->input->post();
		// $post['suggested_saldo'] = ($post['nominal']*0.05) + 10000 + $post['nominal'];
		$post['expected_amount'] = ($post['nominal']*0.05) + 10000 + $post['nominal'];
		$post['is_closed'] = true;
		$post['is_single_use'] = true;

		$res = xendit_curl('https://api.xendit.co/callback_virtual_accounts', [], $this->app['secretKey'], $post, true);
		$data = json_decode($res, true);
		$data['id_user'] = $post['id_user'];
		$data['nominal'] = $post['nominal'];
		$data['biaya'] = ($post['nominal']*0.05) + 10000;
		$data['total'] = $post['nominal'] + $data['biaya'];
		$data['created_date'] = explode('#', $post['external_id'])[1];
		$this->db->insert('transaction_isi_saldo', $data);

		$resx['status'] = 1;
		$resx['message'] = "Silakan Transfer ke Nomor Rekening Berikut";
		$resx['data'] = $data;
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function create_card_transaction() {
		$post = $this->input->post();

		$data['id_user'] = $post['id_user'];
		$data['external_id'] = $post['external_id'];
		$data['bank_code'] = $post['bank_code'];
		$data['status'] = $post['status'];
		$data['nominal'] = $post['nominal'];
		$data['biaya'] = ($post['nominal']*0.05) + 10000;
		$data['total'] = $post['nominal'] + $data['biaya'];
		$data['currency'] = 'IDR';
		$data['owner_id'] = '5d7cdfcf8a41a63c5252431c';
		$data['merchant_code'] = '-';
		$data['name'] = 'DGPAY PACARAN';
		$data['account_number'] = '-';
		$data['is_single_use'] = true;
		$data['expiration_date'] = date('Y-m-d H:i');
		$data['created_date'] = explode('#', $post['external_id'])[1];
		$data['id'] = '';
		$this->db->insert('transaction_isi_saldo', $data);

   		$this->db->where('external_id', $post['external_id']);
   		$this->db->where('id_user', $post['id_user']);
     	$datax = $this->db->get('transaction_isi_saldo')->row();

   		$this->db->where('id_user', $post['id_user']);
     	$dataUser = $this->db->get('user')->row_array();

     	if ($post['status'] === 'PAID') {
	     	$update['saldo'] = (int)$dataUser['saldo'] + (int)$post['nominal'];
	   		$this->db->where('id_user', $post['id_user']);
	     	$this->db->update('user', $update);
	     	$dataUser['saldo'] = $update['saldo'];
	    }

		$resx['status'] = ($post['status'] === 'PAID') ? 1 : 0;
		$resx['message'] = ($post['status'] === 'PAID') ? "Transaksi berhasil" : "Transaksi Gagal";
		$resx['data']['transaksi'] = $datax;
		$resx['data']['user'] = (object)$dataUser;
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function create_credit_card() {
		$post = $this->input->post();
		$data0['id_user'] = $post['id_user'];
		$data0['nama_user'] = $post['nama_user'];
		$data0['cc_number'] = $post['cc_num'];
		$data0['cc_exp_month'] = $post['cc_exp_month'];
		$data0['cc_exp_year'] = $post['cc_exp_year'];
		$data0['cc_cvv'] = $post['cc_cvv'];
		$this->db->insert('credit_cards', $data0);
	}

	public function create_disbursement() {
		$post = $this->input->post();
		$post['amount'] = (int)$post['amount'] - 10000;

   		$this->db->where('id_user', $post['id_user']);
     	$x = $this->db->get('user')->row_array();

     	if ((int)$x['saldo'] < (int)$post['amount']) {
			$resx['status'] = 0;
			$resx['message'] = "Saldo kamu tidak mencukupi";
			$resx['data']['user'] = null;
			$resx['data']['disbursement'] = null;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);
			die;
     	}

		$res0 = xendit_disbursement_curl('https://api.xendit.co/disbursements', $post, $this->app['secretKey'], true);
		$data0 = json_decode($res0, true);
		$id = $data0['id'];

		sleep(7);

		$res = xendit_disbursement_curl('https://api.xendit.co/disbursements/'.$id, $post, $this->app['secretKey']);
		$data = json_decode($res, true);
		$data['id'] = $id;
		$data['email_to'] = $data['email_to'][0];
		$data['account_number'] = $post['account_number'];
		$data['id_user'] = $post['id_user'];
		$data['amount'] = $post['amount'] + 10000;
		$data['biaya'] = 10000;
		$data['total'] = $post['amount'];
		$this->db->insert('transaction_tarik_saldo', $data);

   		$this->db->where('id_user', $post['id_user']);
     	$dataUser = $this->db->get('user')->row_array();

     	if ($data['status'] === 'COMPLETED') {
	     	$update['saldo'] = (int)$dataUser['saldo'] - (int)$post['nominal'];
	   		$this->db->where('id_user', $post['id_user']);
	     	$this->db->update('user', $update);
	     	$dataUser['saldo'] = $update['saldo'];
	    }

		$resx['status'] = ($data['status'] === 'COMPLETED') ? 1 : 0;
		$resx['message'] = "Status Penarikan Saldo : ".$data['status'];
		$resx['data']['user'] = $dataUser;
		$resx['data']['disbursement'] = $data;
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function get_user_by_email() {
		$post = $this->input->post();

   		$this->db->where('email', $post['email']);
     	$dataUser = $this->db->get('user');
     	if ($dataUser->num_rows() > 0) {
     		$data = $dataUser->row_array();

			$resx['status'] = 1;
			$resx['message'] = "Akun tujuan terdaftar";
			$resx['data'] = $data;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);
     	} else {     		
     		$data['id_user'] = '';
     		$data['name'] = '';

			$resx['status'] = 0;
			$resx['message'] = "Akun tujuan tidak terdaftar";
			$resx['data'] = $data;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);
     	}
	}

	public function get_user_by_id() {
		$post = $this->input->post();

   		$this->db->where('email', $post['email']);
     	$dataUser = $this->db->get('user');
     	if ($dataUser->num_rows() > 0) {
     		$data = $dataUser->row_array();

			$resx['status'] = 1;
			$resx['message'] = "Akun tujuan terdaftar";
			$resx['data'] = $data;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);
     	} else {     		
     		$data['id_user'] = '';
     		$data['name'] = '';

			$resx['status'] = 0;
			$resx['message'] = "Akun tujuan tidak terdaftar";
			$resx['data'] = $data;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);
     	}
	}

	public function transfer_saldo() {
		$post = $this->input->post();
		$post['waktu'] = date('Y-m-d H:i:s');

   		$this->db->where('id_user', $post['id_user_pengirim']);
     	$dataUser = $this->db->get('user')->row_array();

   		$this->db->where('id_user', $post['id_user_penerima']);
     	$dataUser2 = $this->db->get('user')->row_array();

     	if ((int)$dataUser['saldo'] < (int)$post['saldo']) {
			$resx['status'] = 0;
			$resx['message'] = "Saldo kamu tidak mencukupi";
			$resx['data']['user'] = null;
			$resx['data']['transfer'] = null;
			$resx['code'] = 0;
			$resx['redirect'] = '';
			echo json_encode($resx);
			die;
     	}

     	unset($post['email']);
     	$this->db->insert('transaction_transfer_saldo', $post);

     	$update['saldo'] = (int)$dataUser['saldo'] - (int)$post['saldo'];
   		$this->db->where('id_user', $post['id_user_pengirim']);
     	$this->db->update('user', $update);     	
     	$dataUser['saldo'] = $update['saldo'];

     	$update['saldo'] = (int)$dataUser2['saldo'] + (int)$post['saldo'];
   		$this->db->where('id_user', $post['id_user_penerima']);
     	$this->db->update('user', $update);

		$resx['status'] = 1;
		$resx['message'] = "Transfer Saldo Berhasil";
		$resx['data']['user'] = $dataUser;
		$resx['data']['transfer'] = $post;
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function get_data() {
		$post = $this->input->post();

   		$this->db->where('id_user', $post['id_user']);
   		$this->db->where($post['id_field'], $post['id_value']);
     	$data = $this->db->get($post['tabel'])->row();

		$resx['status'] = 1;
		$resx['message'] = "";
		$resx['data'] = $data;
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function mutasi_saldo() {
		$post = $this->input->post();

   		$this->db->where('id_user', $post['id_user']);
   		$this->db->limit($post['limit_row'], $post['limit_from']);
     	$data = $this->db->get('v_mutasi_saldo')->result();

   		$this->db->where('id_user', $post['id_user']);
     	$total = $this->db->get('v_mutasi_saldo')->num_rows();

		$resx['status'] = 1;
		$resx['message'] = "";
		$resx['data'] = $data;
		$resx['total'] = $total;
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function mutasi_transaksi() {
		$post = $this->input->post();

   		$this->db->where('id_user', $post['id_user']);
   		$this->db->limit($post['limit_row'], $post['limit_from']);
     	$data = $this->db->get('v_mutasi_transaksi')->result();

   		$this->db->where('id_user', $post['id_user']);
     	$total = $this->db->get('v_mutasi_transaksi')->num_rows();

		$resx['status'] = 1;
		$resx['message'] = "";
		$resx['data'] = $data;
		$resx['total'] = $total;
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}
}