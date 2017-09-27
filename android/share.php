<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 * 分享信息
 */
include 'api.php';
class share extends Api {
	public $table = 'fly_share';
	
	function __construct() {
		parent::__construct ();
		
		$this->load->model('share_model');
	}
	
	// 分享列表
	function lists() {
		
		
		$data = $member = array ();
		$where = '1';
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		$schoolid =  intval($this->input->get('schoolid'));
		if($schoolid) {
			$where .= " and schoolid='$schoolid' ";
		}
		$classname = getNumber($this->input->get('classname'));
		if($classname) {
			$where .= " and classname='$classname' ";
		}
		
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM $this->table where $where" );
		$count_row = $query->row_array();
		$count = $count_row ['num'];
		
		// 文字路况列表
		if ($count > $offset) {
			
			
			$sql = "SELECT id,addtime,title,thumb,uid,comments FROM $this->table where $where ORDER BY id DESC limit $offset,$this->pagesize";
			$query = $this->db->query ( $sql );
			$data = $query->result_array ();
			foreach ( $data as &$value ) {
				$value ['addtime'] = timeFromNow ( $value ['addtime'] );
				if ($value ['thumb']) {
					$value ['thumb'] = base_url().new_thumbname ( $value ['thumb'], 100, 100 );
				}
			}
			$data = $this->member_model->append_list ( $data );
		}
		
		echo json_encode ( $data );
	}
	
	// 分享列表
	function list_test() {
		
		
		$data = $member = array ();
		$where = '';
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		$classname = getNumber($this->input->get('classname'));
	
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM $this->table where classname='$classname'" );
		$count_row = $query->row_array();
		$count = $count_row ['num'];
	
	
	
		// 文字路况列表
		if ($count > $offset) {
			$sql = "SELECT id,addtime,title,thumb,uid FROM $this->table where classname='$classname' ORDER BY id DESC limit $offset,$this->pagesize";
			$query = $this->db->query ( $sql );
			$data = $query->result_array ();
			foreach ( $data as $key=>&$value ) {
				$photos = array();
				$value ['addtime'] = timeFromNow ( $value ['addtime'] );
				$value['photos'] = $photos;
				if($key%3==0) {
					$photos[] = array('id'=>1,'url'=>'http://school.wojia99.com/school/uploads/share/20141008/20141008023627_80466_100_100.jpg');
					$photos[] = array('id'=>2,'url'=>'http://school.wojia99.com/school/uploads/news/20141019/20141019230000_78845_100_100.jpg');
					$photos[] = array('id'=>3,'url'=>'http://school.wojia99.com/school/uploads/share/20141008/20141008023627_80466_100_100.jpg');
					$photos[] = array('id'=>4,'url'=>'http://school.wojia99.com/school/uploads/share/20141008/20141008023627_80466_100_100.jpg');
					$photos[] = array('id'=>5,'url'=>'http://school.wojia99.com/school/uploads/news/20141019/20141019230000_78845_100_100.jpg');
					$photos[] = array('id'=>6,'url'=>'http://school.wojia99.com/school/uploads/share/20141008/20141008023627_80466_100_100.jpg');
					$value['photos'] = $photos;
				} else {
					$photos[] = array('id'=>456,'url'=>'http://school.wojia99.com/school/uploads/share/20141008/20141008023627_80466_100_100.jpg');
					$photos[] = array('id'=>457,'url'=>'http://school.wojia99.com/school/uploads/news/20141019/20141019230000_78845_100_100.jpg');
					$photos[] = array('id'=>458,'url'=>'http://school.wojia99.com/school/uploads/share/20141008/20141008023627_80466_100_100.jpg');
					$photos[] = array('id'=>456,'url'=>'http://school.wojia99.com/school/uploads/share/20141008/20141008023627_80466_100_100.jpg');
					$value['photos'] = $photos;
				}
			}
			$data = $this->member_model->append_list ( $data );
		}
	
		echo json_encode ( $data );
	}
	
	// 保存 分享
	function save() {
		
		
		$data = array (
				'uid' => intval ( $_POST ['uid'] ),
				'title' => trim ( $_POST ['title'] ),
				'schoolid' => intval($this->input->post('schoolid')),
				'classname' => getNumber ( $_POST ['classname'] ),
				'addtime' => time ()
		);
		
		if (empty ( $data ['uid'] )) {
			error ( 1, "uid is null" );
		}
		
		if ($_FILES ['thumb'] ['name']) { // 上传图片
			$data ['thumb'] = uploadFile ( 'thumb', 'share' );
			if($data ['thumb']) {
				thumb_resize ( $data ['thumb'], 100, 100 );				
			}
		}
		
		if ($_FILES ['audio'] ['name']) { // 上传语音
			$data ['audio'] = uploadFile ( 'audio', 'audio' );
			$data ['audio_time'] = intval ( $_POST ['audio_time'] );
		}
		
		$query = $this->db->insert ( 'fly_share', $data );
		// 统计发布数
		$this->stat_model->school($data['schoolid'], 'share');
		
		success('ok',$this->db->insert_id());
	}
	
	// 删除  分享
	function delete() {
		
		
		
		$id = intval($_GET['id']);
		$uid = intval($_GET['uid']);
		if ( empty ( $id) || empty ( $uid) ) {
			error ( 1, "id or uid is null" );
		}
		
		// 删除
		$this->share_model->delete($id, $uid);
		
		success('ok',$id);
	}
	
	// 分享详情
	function detail() {	
		
		
		
		$id = intval($_GET['id']);	
		if ( empty ( $id)  ) {
			error ( 1, "id is null" );
		}
		$value = $this->share_model->detail($id);
		$value['addtime'] = timeFromNow($value['addtime']);
		if ($value ['thumb']) {
			$value ['thumb'] = base_url().new_thumbname ( $value ['thumb'], 100, 100 );
		}
		unset($value['status']);
		echo json_encode($value);
	}
	
	// 评论列表
	function comment_list() {
		
		
			
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
				
		$shareid = intval($this->input->get('shareid'));
		if(empty($shareid)) {
			error ( 1, "shareid is null" );
		}
		
		$list = $this->share_model->comment_list($shareid,$offset,20);	
		foreach ($list as &$value) {
			$value ['addtime'] = timeFromNow ( $value ['addtime'] );
		}
		
		echo json_encode ( $list );
	}
	
	// 评论保存
	function comment_save() {	
		
		
		$data = array (
				'schoolid' => intval ( $this->input->post('schoolid')),
				'shareid' => intval ( $this->input->post('shareid')),
				'uid' => intval ( $this->input->post('uid') ),
				'title' => trim ( $this->input->post('title') ),			
				'addtime' => time ()
				);
				
		if (empty ( $data ['uid'] ) || empty ( $data ['shareid'] )) {
			error ( 1, "uid or shareid is null" );
		}
		
		$insert_id = $this->share_model->comment_save($data);	
		success('ok', $insert_id);
	}
	
	// 删除一条评论
	function comment_delete() {
		
		
		$id = intval($_GET['id']);
		$shareid = intval($_GET['shareid']);
		
		if ( empty ( $id)  || empty ( $shareid)) {
			error ( 1, "id or shareid is null" );
		}
		
		$this->share_model->comment_delete_id($id, $shareid);		
		
		success('ok',$id);
	}
	
} // 类结束

