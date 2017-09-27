<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
	/*
 * 学生 相关的接口
 */
include 'api.php';
class student extends Api {
	
	function __construct() {
		parent::__construct ();		
	}
	
	// 获取学生列表，根据班级名称
	public function lists() {
		
		
		
		
		
		$list = $member = array ();
		$wheresql = 'status=1';
		$schoolid =  intval($this->input->get('schoolid'));
		if($schoolid) {
			$wheresql .= " and schoolid='$schoolid' ";
		}
		$classname = getNumber ( $this->input->get ( 'classname' ) );
		if ($classname) {
			$wheresql .= " and classname='$classname'";
		}
		
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM fly_student WHERE  $wheresql" );
		$count_row = $query->row_array ();
		$count = $count_row ['num'];
		
		if ($count > $offset) {
			$sql = "SELECT id,name,thumb FROM fly_student WHERE  $wheresql ORDER BY id DESC limit 500";
			$query = $this->db->query ( $sql );
			$list = $query->result_array ();
			foreach ( $list as &$row ) {
				if ($row ['thumb']) {
					$row ['thumb'] = base_url () . new_thumbname ( $row ['thumb'], 100, 100 );
				}
			}
		}
		
		echo json_encode ( $list );
	}
	
	// 学生详情接口
	function detail() {
		
		
		
		$studentid = intval ( $_GET ['studentid'] );
		$student = $this->student_model->get_one ($studentid);
		
		$student ['classname'] = setClassname ($student ['classname']);
		$student['age'] = '';
		// print_r($student);
		echo json_encode ( $student );
	}
	
	// 学生档案保存
	function record_save() {
		
		
		$data = array (
				'schoolid' => intval ( $this->input->post('schoolid')),
				'studentid' => intval ( $this->input->post ( 'studentid' ) ),
				'classname' => getNumber ( $this->input->post ( 'classname' ) ),
				'title' => trim ( $this->input->post ( 'title' ) ),
				'addtime' => time () 
		);
		
		if (empty ( $data ['studentid'] )) {
			error ( 1, 'studentid is null' );
		}
		
		if ($_FILES ['thumb'] ['name']) { // 上传图片 同时生成两张缩略图
			$data ['thumb'] = uploadFile ( 'thumb', 'record' );
			if ($data ['thumb'])
				thumb_resize ( $data ['thumb'] );
		}
		
		$query = $this->db->insert ( 'fly_record', $data );
		
		success ( 'ok', $this->db->insert_id () );
	}
	
	// 学生档案列表
	function record_list() {
		
		
		$studentid = intval ( $_GET ['studentid'] );
		
		$data = $member = array ();
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		$query = $this->db->query ( "SELECT COUNT(*) AS num FROM fly_record where studentid=$studentid" );
		$count_row = $query->row_array ();
		$count = $count_row ['num'];
		// 文字路况列表
		if ($count > $offset) {
			$sql = "SELECT id,title,thumb,addtime FROM fly_record where studentid=$studentid ORDER BY id DESC limit $offset,$this->pagesize";
			$query = $this->db->query ( $sql );
			$data = $query->result_array ();
			foreach ( $data as &$row ) {
				$row ['addtime'] = times ( $row ['addtime'] );
				if ($row ['thumb']) {
					$row ['thumb'] = base_url () . new_thumbname ( $row ['thumb'], 100, 100 );
				}
			}
		}
		
		echo json_encode ( $data );
	}
	
	
	
	// 评价评语 列表 在 家长端显示
	function evaluate_list() {
		
		
		$studentid = intval ( $this->input->get ( 'studentid' ) );
		if (empty ( $studentid )) {
			error ( 1, 'studentid is null' );
		}
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		$query = $this->db->query ( "select addtime,star,title,uid from fly_evaluate where studentid='$studentid' order by id desc limit $offset,$this->pagesize" );
		$list = $query->result_array ();
		foreach ( $list as &$row ) {
			$row ['addtime'] = times ( $row ['addtime'] );
		}
		$list = $this->member_model->append_list ( $list );
		
		echo json_encode ( $list );
	}
	
	// 评价评语 列表 在教师端显示
	function evaluate_list_teacher() {
		
		
		$uid = intval ( $this->input->get ( 'uid' ) );		
		$wheresql = '';
		$schoolid =  intval($this->input->get('schoolid'));
		if($schoolid) {
			$where .= " and schoolid='$schoolid' ";
		}
		$classname = getNumber ( $this->input->get ( 'classname' ) );
		if ($classname) {
			$wheresql .= " and classname='$classname'";
		}
		
		if (empty ( $uid ) || empty ( $classname )) {
			error ( 1, 'uid or classname is null' );
		}
		
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		$query = $this->db->query ( "select addtime,star,title,studentid,uid from fly_evaluate where uid='$uid' $wheresql order by id desc limit $offset,$this->pagesize" );
		$list = $query->result_array ();
		foreach ( $list as &$row ) {
			$row ['addtime'] = times ( $row ['addtime'] );
		}
		$list = $this->student_model->append_list ( $list );
		
		echo json_encode ( $list );
	}
	
	// 学生认星增章
	function star_detail() {
		
		
		$studentid = intval ( $_GET ['studentid'] );
		$data = array (
				'xuexi' => 0,
				'xuexi_xing' => 0,
				'laodong' => 0,
				'laodong_xing' => 0,
				'wenming' => 0,
				'wenming_xing' => 0,
				'tiyu' => 0,
				'tiyu_xing' => 0,
				'yishu' => 0,
				'yishu_xing' => 0,
				'chengxin' => 0,
				'chengxin_xing' => 0 
		);
		// 查询多行
		$query = $this->db->query ( "select * from fly_star where studentid='$studentid' limit 1" );
		$value = $query->row_array ();
		if (! empty ( $value )) {
			$data = array (
					'xuexi' => intval ( $value ['xuexi'] / 5 ),
					'xuexi_xing' => intval ( $value ['xuexi'] % 5 ),
					'laodong' => intval ( $value ['laodong'] / 5 ),
					'laodong_xing' => intval ( $value ['laodong'] % 5 ),
					'wenming' => intval ( $value ['wenming'] / 5 ),
					'wenming_xing' => intval ( $value ['wenming'] % 5 ),
					'tiyu' => intval ( $value ['tiyu'] / 5 ),
					'tiyu_xing' => intval ( $value ['tiyu'] % 5 ),
					'yishu' => intval ( $value ['yishu'] / 5 ),
					'yishu_xing' => intval ( $value ['yishu'] % 5 ),
					'chengxin' => intval ( $value ['chengxin'] / 5 ),
					'chengxin_xing' => intval ( $value ['chengxin'] % 5 ) 
			);
		}
		
		// print_r($student);
		echo json_encode ( $data );
	}

	// 修改 保存头像
	function thumb_save() {
		
		
		
		$return = "";
		$studentid = intval ( $_GET ['studentid'] );
		if (empty ( $studentid )) {
			error ( 1, "studentid is null" );
		}
		
		if ($_FILES ['thumb'] ['name']) {
			$data ['thumb'] = uploadFile ( 'thumb', 'student' );
			if ($data ['thumb']) {
				thumb ( $data ['thumb'], 200, 200 );
				$query = $this->db->update ( 'fly_student', $data, 'id = ' . $studentid );
				$return = base_url () . new_thumbname ( $data ['thumb'], 200, 200 );
				success ( $return );
			}
		}
	
		error ( 2, "thumb update failed" );
	}
	
} // 类结束

