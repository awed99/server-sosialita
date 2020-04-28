<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Callback extends CI_Controller {
	
	public function __construct () {
		parent::__construct();
		$this->app = app();
		$this->smtp = smtp();
	}

	public function index() {
		$data = array(
			'title' => "Callback"
		);
		$this->load->view('welcome_message', $data);
	}

	public function fva() {
		// https://sosialita.indobeneficiadigital.com/callback/fva
		$data = json_decode(file_get_contents('php://input'), true);
		$data['id_user'] = explode('#', $data['external_id'])[0];
		$this->db->insert('callback_fva', $data);

   		$this->db->where('id_user', $data['id_user']);
     	$dataUser = $this->db->get('user')->row_array();

     	$update['saldo'] = (int)$dataUser['saldo'] + (int)$data['amount'];
   		$this->db->where('id_user', $data['id_user']);
     	$this->db->update('user', $update);

     	$update2['status'] = 'PAID';
     	$update2['updated'] = date('Y-m-d H:i:s');
   		$this->db->where('external_id', $data['external_id']);
     	$this->db->update('transaction_isi_saldo', $update2);
	}

	public function disbursement() {
		// https://sosialita.indobeneficiadigital.com/callback/disbursement
		$data = json_decode(file_get_contents('php://input'), true);
		$data['id_user'] = explode('#', $data['external_id'])[0];
		$data['failure_code'] = str_replace('_', ' ', $data['failure_code']);
		$this->db->insert('callback_disbursement', $data);

   		$this->db->where('id_user', $data['id_user']);
     	$dataUser = $this->db->get('user')->row_array();

     	$update['saldo'] = (int)$dataUser['saldo'] - (int)$data['amount'];
   		$this->db->where('id_user', $data['id_user']);
     	$this->db->update('user', $update);

     	$update2['status'] = $data['status'];
     	$update2['failure_code'] = $data['failure_code'];
     	$update2['updated_date'] = date('Y-m-d H:i:s');
   		$this->db->where('external_id', $data['external_id']);
     	$this->db->update('transaction_tarik_saldo', $update2);
	}

}