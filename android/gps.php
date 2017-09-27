<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 * GPS 相关信息
 */
include 'api.php';
class gps extends Api {
	private $table = 'fly_gps';
	
	function __construct() {
		parent::__construct ();
		
		$this->load->model('gps_model');
	}
	
	// 某个孩子 某一天的运动轨迹
	function track() {
		
		
		
		$studentid = intval($_GET['studentid']);
		$date = mysql_real_escape_string (trim($_GET['date']));
		
		// 测试的，固定一天
		$studentid = 2595;		
		$list = $this->gps_model->track($studentid,$date);
		echo json_encode ( $list );
	}
	
	// 孩子位置
	function location() {	
		
		
		$studentid = intval($_GET['studentid']);	
		$studentid = 2595;
		$value = $this->gps_model->location($studentid);
		
		echo json_encode($value);
	}
	
} // 类结束

