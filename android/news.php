<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
	/*
 * 新闻动态 通知公告 接口
 */
include 'api.php';
class news extends Api {
	private $table = 'fly_news';
    
	function __construct() {
		parent::__construct ();
		
	}
	
	// 资讯列表页
	function lists() {
	
	 
		
		$result = array ();
		$catid = intval ( $_GET ['catid'] );
		$schoolid = intval ( $this->input->get ( 'schoolid' ) );
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		$wheresql = "where catid='$catid' and status=1 and (schoolid='$schoolid' or schoolid=0)";
		
		$query = $this->db->query ( "select id,title,thumb,addtime from $this->table  $wheresql order by id desc limit $offset,$this->pagesize" );
		$list = $query->result_array ();
		
		// 每四条合成一组新闻
		$i = 1;
		$group = array ();
		foreach ( $list as $key => $value ) {
			if (! isset ( $group ['time'] )) {
				$group ['time'] = timeFromNow ( $value ['addtime'] );
			}
			
			$group ['pubdate'] = $group ['time']; // ios 需要
			$group ['id' . $i] = $value ['id'];
			$group ['title' . $i] = $value ['title'];
			$group ['thumb' . $i] = '';
			if ($value ['thumb']) {
				if ($i != 1) {
					$value ['thumb'] = new_thumbname ( $value ['thumb'], 100, 100 );
				}
				$group ['thumb' . $i] = base_url () . $value ['thumb'];
			}
			
			if (count ( $list ) == $key + 1) {
				$result [] = $group;
				break;
			}
			if ($i == 4) {
				$result [] = $group;
				$i = 1;
				$group = array ();
			} else {
				$i ++;
			}
		}
		
		// print_r($result);
		echo json_encode ( $result );
	}
	
	// 通知公告列表页
	function list2() {
		
		
		$result = array ();
		$catid = intval ( $_GET ['catid'] );
		$schoolid = intval ( $this->input->get ( 'schoolid' ) );
		$this->pagesize = 20;
		$page = intval ( $_GET ['page'] ) - 1;
		$offset = $page > 0 ? $page * $this->pagesize : 0;
		
		// 全区、学校、班级 三级的通知都必需显示
		$wheresql = "where catid='$catid' and status=1 and (schoolid=0 or (schoolid='$schoolid' and classname='0'))";
		$classname = getNumber ( $this->input->get ( 'classname' ) );
		if ($classname) {
			$wheresql = "where catid='$catid' and status=1 and (schoolid=0 or (schoolid='$schoolid' and classname='0') or (schoolid='$schoolid' and classname='$classname'))";
		}
		$query = $this->db->query ( "select id,schoolid,classname,title,thumb,addtime from $this->table $wheresql order by id desc limit $offset,$this->pagesize" );
		$result = $query->result_array ();
		foreach ( $result as &$value ) {
			$value ['addtime'] = timeFromNow ( $value ['addtime'] );
			if ($value ['thumb']) {
				$value ['thumb'] = base_url () . new_thumbname ( $value ['thumb'], 100, 100 );
			}
		}
		
		echo json_encode ( $result );
	}
	
	// 新闻 详细页
	function detail() {
		
		
		
		$data = array ();
		$id = intval ( $_GET ['id'] );
		
		if (! empty ( $id )) {
			$sql = "SELECT * FROM $this->table WHERE id='$id' limit 1";
			$query = $this->db->query ( $sql );
			$value = $query->row_array ();
			$value ['addtime'] = times ( $value ['addtime'] );
			if ($value ['thumb']) {
				$value ['thumb'] = base_url () . $value ['thumb'];
			}
		}
		
		$data ['value'] = $value;
		
		$this->load->view ( 'android/news_show', $data );
	}
	
	// 通知公告，班主任 发给本班家长
	function save() {
		
		
		
		
		$data = array (
				'uid' => intval ( $_POST ['uid'] ),				
				'schoolid' => intval ( $_POST ['schoolid'] ),
				'classname' => getNumber ( $_POST ['classname'] ),
				'title' => trim ( $_POST ['title'] ),
				'content' => trim ( $_POST ['content'] ),
				'catid' => 2,
				'addtime' => time () 
		);
		
		if (empty ( $data ['uid'] ) || empty ( $data ['classname'] ) || empty ( $data ['title'] )) {
			error ( 1, "uid,classname,title is null" );
		}
		
		$this->db->insert ( 'fly_news', $data );
		$data['id'] = $this->db->insert_id();
		
		// 推送
		$data['tag'] = $data['schoolid'] .'_'.$data['classname'];
		$data['window'] = 1;
		$data['addtime'] = times($data['addtime'],1);
		$this->load->model('push_model');		
		$this->push_model->pushNewsAll($data);
		
		// 统计发布数
		$this->stat_model->school ( $data ['schoolid'], 'news' );
		
		success ( 'ok', $this->db->insert_id () );
	}
} // 类结束

