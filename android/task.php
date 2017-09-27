<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 * 作业 课外实践
 */
include 'api.php';
class task extends Api {
	private $table = 'fly_task';
	
	function __construct() {
		parent::__construct ();
	}
	
	// 某个班的作业列表
	function lists() {
		
		
		
		$data = $member = array ();
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		$where = '1';
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
		
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM $this->table where $where" );
		$count_row = $query->row_array();
		$count = $count_row ['num'];
		
		if ($count > $offset) {
			$sql = "SELECT id,typename,title,pubdate,addtime,uid,thumb,audio,audio_time FROM $this->table where $where ORDER BY id DESC limit $offset,$this->pagesize";
			$query = $this->db->query ( $sql );
			$data = $query->result_array ();
			foreach ( $data as &$value ) {
				$value ['addtime'] = times ( $value ['addtime'] );
				$value ['desc'] =  $value ['typename'] ;  // ios 需要		
				if ($value ['thumb']) {
					$value ['thumb'] = base_url().new_thumbname ( $value ['thumb'], 100, 100 );
				}
				if ($value ['audio']) {
					$value ['audio'] = base_url().$value ['audio'];
				}	
			}
			$data = $this->member_model->append_list ( $data );
		}
		
		echo json_encode ( $data );
	}
	
	// 我发布的作业列表 教师端 
	function list_teacher() {
		
		
		$data = $member = array ();
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		$uid = $this->input->get('uid');
		
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
		
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM $this->table where uid='$uid' $where");
		$count_row = $query->row_array();
		$count = $count_row ['num'];
		
		if ($count > $offset) {
			$sql = "SELECT id,typename,title,pubdate,addtime,uid,thumb,audio,audio_time FROM $this->table where uid='$uid' $where ORDER BY id DESC limit $offset,$this->pagesize";
			$query = $this->db->query ( $sql );
			$data = $query->result_array ();
			foreach ( $data as &$value ) {
				$value ['addtime'] = times ( $value ['addtime'] );
				$value ['desc'] =  $value ['typename'] ; // ios 需要
				if ($value ['thumb']) {
					$value ['thumb'] = base_url().new_thumbname ( $value ['thumb'], 100, 100 );
				}
				if ($value ['audio']) {
					$value ['audio'] = base_url().$value ['audio'];
				}
			}
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
			$value = $query->row_array ();
			if ($value ['thumb']) {
				$value ['thumb'] = base_url().new_thumbname ( $value ['thumb'], 100, 100 );
			}
			if ($value ['audio']) {
				$value ['audio'] = base_url().$value ['audio'];
			}
			$data = $this->member_model->append_one ( $value );
		}
		
		echo json_encode ( $data );
	}
	
	// 保存
	function save() {
		
		
		$data = array(
				'schoolid' => $this->input->post('schoolid'),
				'typename' => $this->input->post ( 'typename' ),
				'classname' => getNumber($this->input->post ( 'classname' )),
				'title' => $this->input->post ( 'title' ),
				'uid' => $this->input->post ( 'uid' ),
				'pubdate' => $this->input->post ( 'pubdate' ),
				);
				
		if (empty ( $data ['title'] )) {
			error ( 1, 'title is null' );
		}
		if (empty ( $data ['uid'] )) {
			error ( 2, 'uid is null' );
		}
		
		if ($_FILES ['thumb'] ['name']) { // 上传图片
			$data ['thumb'] = uploadFile ( 'thumb', 'task' );
			if($data ['thumb']) {
				thumb_resize ( $data ['thumb'], 100, 100 );
			}
		}
		
		if ($_FILES ['audio'] ['name']) { // 上传语音
			$data ['audio'] = uploadFile ( 'audio', 'audio' );
			$data ['audio_time'] = intval ( $_POST ['audio_time'] );
		}
		
		$data ['addtime'] = time ();
		$query = $this->db->insert ( $this->table, $data );
		// 统计发布数
		$this->stat_model->school($data['schoolid'], 'task');
		
		success('ok',$this->db->insert_id());
	}
	
} // 类结束

