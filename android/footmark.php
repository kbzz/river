<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 *  成长足迹
 */
include 'api.php';

class footmark extends Api
{	
	private $table = 'fly_footmark';
	
    function __construct () {
    	    	 
        parent::__construct();
    } 	
    
    // 学期 列表
    function lists() {
    	
    	
    	
    	
    	$semester = config_item('semester');

    	
    	
    	
    	echo json_encode ( $semester );
    }
    
    // 成长足迹详情 
    function detail() {
    	
    	
    	
    	
    	
    	$data = array ();
    	$studentid = intval($_GET['studentid']);
    	if(empty($studentid)) {
    		error(1,'studentid is null');
    	}
    	
    	
    	
    	
    	$semester = intval ( $_GET ['semester'] );
    	
    	$query = $this->db->query ( "SELECT * FROM $this->table WHERE studentid=$studentid and semester='$semester' limit 1" );
    	$data = $query->row_array ();
    	$data = $this->student_model->append_one ( $data );    	
    	$data['pubdate'] = times($data['pubdate']);
    	$semester = config_item('semester');
    	$data['semester'] = $semester[$data['semester']];    	
    	echo json_encode ( $data );
    	exit;
    	
//     	if ($studentid && $semester) { 
//     		$query = $this->db->query ( "SELECT * FROM $this->table WHERE studentid='$studentid' and semester='$semester'limit 1" );
//     		$data = $query->row_array ();
//     		$data = $this->student_model->append_one ( $data );
//     		$data = $this->member_model->append_one ( $data );
//     	}
    	
//     	echo json_encode ( $data );
    }
    
} // 类结束

