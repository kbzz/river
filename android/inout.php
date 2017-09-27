<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 * 进出校门记录 孩子动态
 */
include 'api.php';
class inout extends Api {
	private $table = 'fly_inout';
	
	function __construct() {
		parent::__construct ();
		$this->load->model ( 'inout_model' );
	}
	
	// 进出校门记录 列表页
	function lists() {
		
		
		$studentid = intval ( $_GET ['studentid'] );
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		$student = $this->student_model->get_one($studentid);
			
		// 刷卡的数据库
		$list = $this->inout_model->getListByRFID($student['rfid'],$offset,$this->pagesize);
		$list2 = array();
		foreach ( $list as $r ) {
			$value ['type'] = $r['RE_RFID_Status']=='100'?1:2;
			$value ['occurdate'] = date('m-d',strtotime($r['RE_RecordTime']));
			$value ['title'] = '您的孩子已于' . date ( 'H时i分', strtotime($r['RE_RecordTime']) );
			$value ['title'] .= $r['RE_RFID_Status']=='100'?'进入学校':'离开学校';
			$value ['RE_RecordTime'] = strtotime($r['RE_RecordTime']);
			
			$list2[] = $value;
		}
		// print_r($list);
		echo json_encode ( $list2 );
	}
	
	// 我的班级  某个班 进出校门统计
	function myclass() {
		
		
		
		$schoolid =  intval($this->input->get('schoolid'));
		$classname =  getNumber($this->input->get('classname'));		
		$page = intval ( $_GET ['platestage'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		// 刷卡的数据库
		$query = $this->db->query ( "SELECT datetime,status,total,yidao,weidao,weidaoren FROM fly_inout_stat where schoolid='$schoolid' and classname='$classname' order by datetime desc limit $offset,$this->pagesize");
		$list = $query->result_array ();
		
		echo json_encode ( $list );
	}
	
	// 根据RFID返回进出校门信息, 调用一次则发送位置0
	// 请求方法：get
	// 参数：id（学生卡的id号）
	// 返回：
	// RE_RFID_Status：进出校门标识（100进,200出）
	// RE_RecordTime：最近的时间戳
	// 形如  {"RE_RFID_Status":"100","RE_RecordTime":"14562587"}
	function last_one() {
		
		
		$studentid = intval( $_GET ['studentid'] );
		if(empty($studentid)) {
			error(1, 'studentid is null');
		}
		$row = $this->inout_model->last_inout($studentid);
		if(empty($row)) {
			error(2, '暂无信息');
		}
		$row['RE_RecordTime'] = strtotime($row['RE_RecordTime']);
		echo json_encode ( $row );
	}
	
	
} // 类结束


