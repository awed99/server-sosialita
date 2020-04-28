<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller {
	
	public function __construct () {
		parent::__construct();
		$this->app = app();
		$this->smtp = smtp();
	}

	public function index() {
		$data = array(
			'title' => "Chat"
		);
		$this->load->view('welcome_message', $data);
	}

	public function sendMessage() {
		$post = $this->input->post();
		$data = $post;
		$data['waktu'] = date('Y-m-d H:i:s');

     	$this->db->insert('chatting', $data);

		$resx['status'] = 1;
		$resx['message'] = "Pesan terkirim";
		$resx['data'] = [];
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function chatsRealTime() {
		$post = $this->input->post();

   		$this->db->where('id_user_pengirim', $post['id_user']);
   		$this->db->or_where('id_user_penerima', $post['id_user']);
   		$this->db->where('delete_pesan', 0);
   		$this->db->where('delete_chat', 0);
     	$data = $this->db->get('v_chats')->result();

   		$this->db->where('id_user_pengirim', $post['id_user']);
   		$this->db->or_where('id_user_penerima', $post['id_user']);
   		$this->db->where('delete_pesan', 0);
   		$this->db->where('delete_chat', 0);
     	$total = $this->db->get('v_chats')->num_rows();

		$resx['status'] = 1;
		$resx['message'] = "";
		$resx['data'] = $data;
		$resx['total'] = $total;
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function chattingRealTime() {
		$post = $this->input->post();

   		$this->db->where('id_user_pengirim', $post['id_user']);
   		$this->db->or_where('id_user_penerima', $post['id_user']);
   		$this->db->or_where('id_user_pengirim', $post['id_user_chatting']);
   		$this->db->or_where('id_user_penerima', $post['id_user_chatting']);
   		$this->db->where('delete_pesan', 0);
   		$this->db->where('delete_chat', 0);
   		$this->db->where('id_chatting >=', $post['last_id_chatting']);
   		$this->db->order_by('id_chatting', 'desc');
     	$data = $this->db->get('v_chatting')->result();

   		$this->db->where('id_user_pengirim', $post['id_user']);
   		$this->db->or_where('id_user_penerima', $post['id_user']);
   		$this->db->or_where('id_user_pengirim', $post['id_user_chatting']);
   		$this->db->or_where('id_user_penerima', $post['id_user_chatting']);
   		$this->db->where('delete_pesan', 0);
   		$this->db->where('delete_chat', 0);
     	$total = $this->db->get('v_chatting')->num_rows();

		$resx['status'] = 1;
		$resx['message'] = "";
		$resx['data'] = $data;
		$resx['total'] = $total;
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function readChatting() {
		$post = $this->input->post();
		$update['dibaca'] = 1;

   		$this->db->where('id_user_pengirim', $post['id_user_pengirim']);
   		$this->db->where('id_user_penerima', $post['id_user_penerima']);
     	$this->db->update('chatting', $update);

		$resx['status'] = 1;
		$resx['message'] = "Chat dibaca";
		$resx['data'] = [];
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function delete_chats() {
		$post = $this->input->post();
		$update['delete_chat'] = 1;

   		$this->db->where('id_user_pengirim', $post['id1']);
   		$this->db->or_where('id_user_penerima', $post['id2']);
   		$this->db->where('id_user_pengirim', $post['id1']);
   		$this->db->or_where('id_user_penerima', $post['id2']);
     	$this->db->update('chatting', $update);

		$resx['status'] = 1;
		$resx['message'] = "Chat terhapus";
		$resx['data'] = [];
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}

	public function delete_chatting() {
		$post = $this->input->post();
		$update['delete_pesan'] = 1;

   		$this->db->where('id_chatting', $post['id_chatting']);
     	$this->db->update('chatting', $update);

		$resx['status'] = 1;
		$resx['message'] = "Pesan terhapus";
		$resx['data'] = [];
		$resx['code'] = 0;
		$resx['redirect'] = '';
		echo json_encode($resx);
	}
}