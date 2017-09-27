<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	/*
 * android 私聊 群聊 信鸽推送及时通信
 */
include 'api.php';
class talk extends Api {
	
	function __construct() {
		parent::__construct ();		
	
		$this->load->model ( 'xinge_model' );
	}
	
	// 私聊 列表 个人对个人
	public function p2p_list() {
		
		
		
		$data = $member = array ();
		$page = intval ( $_GET ['page'] ) - 1;
		$from_uid = intval ( $_GET ['from_uid'] );  // 自己
		$to_uid = intval ( $_GET ['to_uid'] );
		if (empty ( $from_uid ) || empty ( $to_uid )) {
			error ( 1, 'uid is null' );
		}
		
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM fly_talk_private WHERE status=1 AND (from_uid=$from_uid OR to_uid=$from_uid) AND (from_uid=$to_uid OR to_uid=$to_uid)" );
		$count_row = $query->row_array ();
		$count = $count_row ['num'];
		
		if ($count > $offset) {
			$sql = "SELECT id,from_uid,to_uid,title,thumb,audio,audio_time,addtime FROM fly_talk_private WHERE status=1 AND (from_uid=$from_uid OR to_uid=$from_uid) AND (from_uid=$to_uid OR to_uid=$to_uid) ORDER BY id DESC limit $offset,$this->pagesize";
			$query = $this->db->query ( $sql );
			$data = $query->result_array ();
			$data = array_reverse ( $data );
			foreach ( $data as &$row ) {
				$row ['addtime'] = timeFromNow ( $row ['addtime'] );				 
				if ($row ['thumb']) {
					$row ['thumb'] = base_url().new_thumbname ( $row ['thumb'], 100, 100 );
				}
				if ($row ['audio']) {
					$row ['audio'] = base_url().$row ['audio'];
				}
				$row['uid'] = $row ['from_uid'];
				$row =$this->member_model->append_one ( $row );
				unset($row['uid']);
			}
			//$data = $this->member_model->append_list ( $data );
		}
		
		// print_r($data);
		echo json_encode ( $data );
		
		// 把指定人发给我的 未读 设为已读
		//$this->db->query ( "update fly_talk_private set isread=1 where to_uid=$from_uid AND from_uid=$to_uid" );
	}
	
	// 私聊 保存
	function p2p_save() {
		
		
		$return = 'ok';
		
		$data = array (
				'from_uid' => intval ( $_POST ['from_uid'] ),
				'to_uid' => intval ( $_POST ['to_uid'] ),
				'title' => trim ( $_POST ['title'] ),
				'addtime' => time () 
		);
		
		if (empty ( $data ['from_uid'] ) || empty ( $data ['to_uid'] )) {
			error ( 1, "from_uid or to_uid is null" );
		}
		
		if ($_FILES ['thumb'] ['name']) { // 上传图片
			$data ['thumb'] = uploadFile ( 'thumb', 'talk' );
			if($data ['thumb']) thumb_resize ( $data ['thumb'], 100, 100 );
		}
		
		if ($_FILES ['audio'] ['name']) { // 上传语音
			$data ['audio'] = uploadFile ( 'audio', 'audio' );
			$data ['audio_time'] = intval ( $_POST ['audio_time'] );
		}
		
		$query = $this->db->insert ( 'fly_talk_private', $data );
		$data['id'] = $this->db->insert_id();		
			
		
		if(($data ['thumb'])) {
			$data ['thumb'] = base_url().new_thumbname ( $data ['thumb'], 100, 100 );
			$return = $data ['thumb'];
		}
		if(($data ['audio'])) {
			$data ['audio'] = base_url().$data ['audio'];
			$return = $data ['audio'];
		}
		// 推送给他
		$this->xinge_model->xinge_private ( $data );		
		
		success ( $return, $data['id'] );
	}
	
	// 私聊信息 详情
	public function p2p_detail() {
		
		
		$id = intval($_GET['id']);
		$query = $this->db->get_where('fly_talk_private', 'id = '.$id, 1);
		$data = $query->row_array();
		$data['uid'] = $data['from_uid'];
		if($data) {
			if(($data ['thumb'])) $data ['thumb'] = base_url().new_thumbname ( $data ['thumb'], 100, 100 );
			if(($data ['audio'])) $data ['audio'] = base_url().$data ['audio'];
			$data ['addtime'] = timeFromNow ( $data ['addtime'] );
		}
		
		$data = $this->member_model->append_one ( $data );
		echo json_encode($data);
	}
	
	// 删除一条私聊
	public function p2p_delete() {
		
		
		
		$id = intval($_GET['id']);
		if(empty($id)){
			error ( 1, "id is null" );
		}
		
		// 删除
		$this->db->delete('fly_talk_private', 'id = '.$id);
		
		success ( 'ok', $id);
	}
	
	// 群聊信息
	public function group_list() {
	
		
		
		$data = $member = array ();
		$page = intval ( $_GET ['page'] ) - 1;
		$where = '';		
		
		$schoolid =  intval($this->input->get('schoolid'));
		if($schoolid) {
			$where .= " and schoolid='$schoolid' ";
		}	
		$classname = getNumber($this->input->get('classname'));
		if($classname) {
			$where .= " and classname='$classname' ";
		}
		if (empty ( $classname )) {
			error ( 1, 'classname is null' );
		}
		
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM fly_talk_group WHERE status=1 $where " );
		$count_row = $query->row_array ();
		$count = $count_row ['num'];
		
		if ($count > $offset) {
			$sql = "SELECT id,addtime,uid,title,thumb,audio,audio_time FROM fly_talk_group WHERE status=1 $where ORDER BY id DESC limit $offset,$this->pagesize";
			$query = $this->db->query ( $sql );
			$data = $query->result_array ();
			$data = array_reverse ( $data );
			foreach ( $data as &$row ) {
				$row ['addtime'] = timeFromNow ( $row ['addtime'] );				
				if ($row ['thumb']) {
					$row ['thumb'] = base_url().new_thumbname ( $row ['thumb'], 100, 100 );
				}
				if ($row ['audio']) {
					$row ['audio'] = base_url().$row ['audio'];
				}				
			}
			
			$data = $this->member_model->append_list ( $data );
		}	
		
		// print_r($data);
		echo json_encode ( $data );
	}
	
	// 群聊保存
	function group_save() {
		
		
		
		$return = 'ok';
		$data = array (
				'uid' => intval ( $_POST ['uid'] ),
				'schoolid' => $this->input->post('schoolid'),
				'classname' => getNumber ( $_POST ['classname'] ),
				'title' => trim ( $_POST ['title'] ),
				'addtime' => time ());	
		
		if (empty ( $data ['uid'] ) || empty ( $data ['classname'] )) {
			error ( 1, "uid or classname is null" );
		}
		
		if ($_FILES ['thumb'] ['name']) { // 上传图片		
			$data ['thumb'] = uploadFile ( 'thumb', 'talk' );
			if($data ['thumb']) thumb_resize ( $data ['thumb'], 100, 100 );
		}
		
		if ($_FILES ['audio'] ['name']) { // 上传语音
			$data ['audio'] = uploadFile ( 'audio', 'audio' );
			$data ['audio_time'] = intval ( $_POST ['audio_time'] );
		}
		
		$query = $this->db->insert ( 'fly_talk_group', $data );
		$data['id'] = $this->db->insert_id();
		
		// 推送给群组
		$data = $this->member_model->append_one ( $data );		 
		if(($data ['thumb'])) {
			$data ['thumb'] = base_url().new_thumbname ( $data ['thumb'], 100, 100 );
			$return = $data ['thumb'];
		}
		if(($data ['audio'])) {
			$data ['audio'] = base_url().$data ['audio'];
			$return = $data ['audio'];
		}
		
		$data['classid'] = getNumber($data['classname']);		
		$this->xinge_model->xinge_group ( $data );
		
		success ( $return, $data['id'] );
	}
	
	// 群聊信息 详情
	public function group_detail() {
		
		
		$id = intval($_GET['id']);
		$query = $this->db->get_where('fly_talk_group', 'id = '.$id, 1);
		$data = $query->row_array();
		if($data) {
			if(($data ['thumb'])) $data ['thumb'] = base_url().new_thumbname ( $data ['thumb'], 100, 100 );
			if(($data ['audio'])) $data ['audio'] = base_url().$data ['audio'];			
			$data ['addtime'] = timeFromNow ( $data ['addtime'] );
		}
		
		$data = $this->member_model->append_one ( $data );
		echo json_encode($data);
	}

	// 删除一条群聊
	public function group_delete() {
		
		
		$id = intval($_GET['id']);
		if(empty($id)){
			error ( 1, "id is null" );
		}
	
		// 删除
		$this->db->delete('fly_talk_group', 'id = '.$id);
	
		success ( 'ok', $id);
	}
	
} // 类结束
