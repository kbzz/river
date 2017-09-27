<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 * 课程表
 */
include 'api.php';
class timetable extends Api {
	private $table = 'fly_timetable';
	
	function __construct() {
		parent::__construct ();
	}
	
	// 5天课程表
	function lists() {
		
		
		
		$schoolid =  intval($this->input->get('schoolid'));
		$classname = getNumber($this->input->get('classname'));
		$data = array();
		$m = 0;		
		for($i=0; $i<8;$i++) {
			for($n=0; $n<5;$n++) {
				$data[$m] = array("id"=>0);
				$m++;
			}
		}		
		
		// 查询 本班课程
		$query = $this->db->query ( "select id,title,week,section,tips,uid from $this->table where schoolid='$schoolid' and classname='$classname' order by section,week limit 40" );
		$list = $query->result_array ();
		$list = $this->member_model->append_list ( $list );
		foreach($list as $value) {
			$index = (($value['section']-1)*5)+$value['week']-1;	
			$data[$index] = $value;
		}
		
		echo json_encode ( $data );
	}
	
	// 只返回某位老师的课程表
	function list_my() {
		
		
		
		$schoolid =  intval($this->input->get('schoolid'));
		$uid = intval($this->input->get('uid'));
		
		$data = array();
		$m = 0;	
		for($i=0; $i<8;$i++) {
			for($n=0; $n<5;$n++) {
				$data[$m] = array("id"=>0);
				$m++;
			}
		}	
		
		
		// 查询 本班课程
		$query = $this->db->query ( "select id,title,week,section,tips,uid,classname from $this->table where uid='$uid' order by section,week limit 40" );
		$list = $query->result_array ();
		$list = $this->member_model->append_list ( $list );
		foreach($list as $value) {
			$index = (($value['section']-1)*5)+$value['week']-1;
			$value['classname'] = setClassname($value['classname']);
			$data[$index] = $value;
		}
		//print_r($data);exit;
		
		echo json_encode ( $data );
	}
	
	
	// 注意事项 列表页 获取最新的
	function list_tips() {
		
		
		
		$list = array();
		$schoolid =  intval($this->input->get('schoolid'));
		$classname = getNumber($this->input->get('classname'));
		$query = $this->db->query ( "select title,week,section,tips from $this->table where schoolid='$schoolid' and classname='$classname' and tips<>'' order by week desc,section desc limit 20" );
		$data = $query->result_array ();
		$week = config_item('week');
		$section = config_item('section');
		foreach($data as $value) {
			$row['title'] = $week[$value[week]].$section[$value[section]].$value[title].'课,'.$value[tips];
			$list[] = $row;
		}
		
		echo json_encode ( $list );
	}
	
	// 通知 详细页
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
	
	// 修改课程表  并推送
	function update() {
		
		
		$id = intval ( $this->input->post ( 'id' ) );
		
		if (empty ( $id )) {
			error ( 1, 'id is null' );
		}
		
		// 更新数据 记住
		$data = array (
				'tips' => $this->input->post ( 'tips' ) 
				);		
		$this->db->update ( $this->table, $data, 'id = '.$id );		
		
		// 推送给 该班的家长	
		if(!empty($data['tips'])) {
			
			$query = $this->db->query ( "select classname from $this->table where id='$id' limit 1");
			$value = $query->row_array();			
			$data1 = array(
					'title'=> $data['tips'],
					'addtime'=> times(time(),1),
					'classname'=> $value['classname']
			);
			
			$this->load->model ( 'xinge_model' );
			$ret = $this->xinge_model->push_timetable_tips($data1);			
		}
		
		success('ok', $id);
	}
	
	// 获取周数，日期
	function weekdays() {
		
		
		$data = $this->dayofweek();
		$this->load->driver ( 'cache', array (
				'adapter' => 'file'
		) );
		$website = $this->cache->get('classes');
		$data['week'] = $website['weekday'];
		
		echo json_encode($data);
	}
	
	// 获取本周 七天 对应的 日期
	function dayofweek() {		
		$data = array(		
				'1'=>'11',
				'2'=>13,
				'3'=>14,
				'4'=>15,
				'5'=>16,
				'6'=>16,
				'7'=>16,
		);		
		$today = date('N'); // 今天星期几
		$oneday = 24*60*60;
		foreach($data as $key=>&$value) {
			$temp = $oneday*($today - $key);			
			$value = date('n月j日',time()-$temp);			
		}		
	
		return $data;
	}
	
		
	
} // 类结束

