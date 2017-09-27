<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 * android客户端调用接口文件 通用文件接口 
 * code by tangjian 20130726
 */
class Api extends CI_Controller {
	public $pagesize = 20; // 分页每页条数····	
	
	function __construct() {
		
		parent::__construct ();
		
		$this->load->model ( 'stat_model' );//记录访问次数 日志
		$this->stat_model->visit_app();
          
		
	}
	
	// 意见反馈保存
	function feedback_save() {		
		$data = array(				
				'uid'=> $this->input->post('uid'),
				'title'=> $this->input->post('title'),
				'addtime'=> time()
				);
		if(empty($data['title'])) {
			error(1,'title is null');
		}
		$this->db->insert('fly_feedback', $data);
		
		success('ok', $this->db->insert_id());
	}
	
	// 客户端异常错误 保存
	function error_save() {		
		$data = array(				
				'uid'=> $this->input->post('uid'),
				'title'=> $this->input->post('title'),
				'catid'=> intval($this->input->post('catid')),
				'addtime'=> time()
		);
		if(empty($data['title'])) {
			error(1,'title is null');
		}
		if(empty($data['catid'])) {
			$data['catid'] = 1;
		}
		$this->db->insert('fly_error', $data);
		
		success('ok', $this->db->insert_id());
	}
	
	
	
	//访问统计
	function visit_url($title){
		//echo $_GET["d"];exit;
	      
		
		
	    $data['title']=$title;
		
		
		$data['url']='c='.$_GET['c']. '/'.'m='.$_GET['m'];
		$data['c']=$_GET['c'];
		$data['m']=$_GET['m'];
		$data['addtime']=date('Y-m-d h:i:s',time());
		$data['time']=time();
		$data['ip']=$_SERVER["REMOTE_ADDR"];
		
		//print_r($data);exit;
		$this->db->insert ( 'fly_visit', $data );
	   }

	   
	   
	   
	   
	
	
} // 类结束


