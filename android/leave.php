<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

/*
 * 家长 电子请假条
 */

include 'api.php';
class leave extends Api {
	private $table = 'fly_leave';
	
	function __construct() {
		parent::__construct ();		
	}
	
	// 某个学生的请假列表
	function list_my() {
		
		
		
		
		$data = $member = array ();
		$studentid = intval($_GET['studentid']);
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM $this->table WHERE studentid='$studentid'" );
		$count_row = $query->row_array ();
		$count = $count_row ['num'];
		
		if ($count > $offset) {
			$query = $this->db->query ( "SELECT id,addtime,classname,studentid,title,content,typename,pubdate,isread,reply,uid FROM $this->table WHERE studentid='$studentid' ORDER BY id DESC limit $offset,$this->pagesize" );
			$data = $query->result_array();
			foreach($data as &$value) {
				$value ['addtime'] = times ( $value ['addtime'],1 );
				$value ['type'] = $value ['typename'];  // ios 需要
			}
			$data = $this->member_model->append_list ( $data );
		}
		
		echo json_encode ( $data );
	}
	
	// 某个班的 所有请假信息，教师端可看
	function list_class() {
				
		$data = $member = array ();
		$schoolid =  intval($this->input->get('schoolid'));
		$classname = getNumber($this->input->get('classname'));
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM $this->table WHERE schoolid='$schoolid' and classname='$classname'" );
		$count_row = $query->row_array ();
		$count = $count_row ['num'];
		
		if ($count > $offset) {
			$query = $this->db->query ( "SELECT id,addtime,studentid,uid,title,content,typename,pubdate,isread,reply FROM $this->table WHERE schoolid='$schoolid' and classname='$classname' ORDER BY id DESC limit $offset,$this->pagesize" );
			$data = $query->result_array();
			foreach($data as &$value) {
				$value ['addtime'] = times ( $value ['addtime'],1 );
				$value ['type'] = $value ['typename'];  // ios 需要
			}
			$data = $this->student_model->append_list ( $data );
			$data = $this->member_model->append_list ( $data );
		}
		
		echo json_encode ( $data );
	}
	
	// 详细页
	function detail() {
		
		
		
		$data = array ();
		$id = intval ( $_GET ['id'] );
		
		if (! empty ( $id )) {
			$sql = "SELECT * FROM $this->table WHERE id='$id' limit 1";
			$query = $this->db->query ( $sql );
			$data = $query->row_array ();
			$data = $this->member_model->append_one ( $data );
		}
		
		echo json_encode ( $data );
	}
	
	// 请假 提交保存
	function save() {	
		
		
		
		$data = array (
				'schoolid' => $this->input->post('schoolid'),
				'classname' =>  getNumber( $this->input->post ('classname') ),
				'studentid' => intval ( $this->input->post ('studentid') ),
				'typename' =>  ( $this->input->post ('typename') ),
				'pubdate' =>  ( $this->input->post ('pubdate') ),
				'uid' =>  intval( $this->input->post ('uid') ),
				'title' => trim ( $this->input->post ('title') ),
				'content' => trim ( $this->input->post ('content') ),
				'addtime' => time()
				);
				
		if (empty ( $data ['studentid'] )) {
			error ( 1, 'studentid is null' );
		}
		$query = $this->db->insert ( $this->table, $data );
		
		// 推送给班主任
		$data1 = array(
				'uid'=> $this->member_model->get_manage_id($data['schoolid'],$data['classname']),
				'title'=> '收到一条新的请假信息，请查看。',
				'addtime'=> times(time(),1),
				);
		$this->load->model ( 'push_model' );
		$this->push_model->pushLeaveTeacher($data1);
		
		success('ok');
	}
	
	// 班主任 回复家长
	function reply_save() {
		
		
		
		
		$id = intval($_POST['id']);
		$data = array (
				//'uid' => trim ( $this->input->post ('pid') ),
				'isread' => trim ( $this->input->post ('isread') ),
				'reply' => trim ( $this->input->post ('reply') ),				
		);	
		if (empty ( $id)) {
			error ( 1, 'id is null' );
		}		
		$query = $this->db->update ( $this->table, $data, 'id = '.$id);
		
		// 回复家长
		$data1 = array(
				'uid'=> $data['uid'],
				'title'=> '班主任已经查阅您的请假信息了。',
				'addtime'=> times(time(),1),
		);
		$this->load->model ( 'push_model' );
		$this->push_model->pushLeaveParents($data1);
		
		success('ok', $id);
	}
	
} // 类结束

