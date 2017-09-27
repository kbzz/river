<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 *  版本信息
 */
include 'api.php';
class version extends Api {	
	
	function __construct() {
		parent::__construct ();
		
		$this->load->driver('cache',array('adapter'=>'file'));
	}
	
	//==============家长端===========================
	function index() {
		
		
		$value = $this->cache->get('version');		
		$version = array (
				'version' => $value['parents_version'],
				'message' => $value['parents_message'],
				'isneed' => intval($value['parents_isneed'])
				);
		
		echo json_encode ( $version, JSON_UNESCAPED_UNICODE );		
	}	
	
	function get_apk() {
		header ( 'Location: parents.apk' );
		exit ();
	}
	
	//==============教师端===========================
	function teacher_version() {
		
		
		
		$value = $this->cache->get('version');		
		$version = array (
				'version' => $value['teacher_version'],
				'message' => $value['teacher_message'],
				'isneed' => intval($value['teacher_isneed'])
				);
				
		echo json_encode ( $version );
	}
	
	function teacher_apk() {
		header ( 'Location: teacher.apk' );
		exit ();
	}
	
} // 类结束


