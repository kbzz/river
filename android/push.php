<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 *  推送模块相关
 */

include 'api.php';
class Push extends Api {	
	
	function __construct() {
		parent::__construct ();
		
		$this->load->model('xinge_model');
	}
	
	// 注销单个token的所有tag 信鸽
	function XingeDeleteTokenTags() {
		$uid = trim($_GET['uid']);
		$token = trim($_GET['token']);
		if(empty($uid) || empty($token)) {
			error(1,'uid or token is null');
		}
		
		$ret = $this->xinge_model->deleteTokenTags($token);
		
		if($ret['ret_code'] != 0) {
			error(1,'发生错误了，请稍后再试');
		}
		
		success ( 'ok' );
	}
	
} // 类结束

