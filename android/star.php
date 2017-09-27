<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
	/*
 * 勋章 评价表现
 */
include 'api.php';
class star extends Api {

	function __construct() {
		parent::__construct ();
		$this->load->model ( 'star_model' );
	}
	
	// 评价评语 保存 同时
	function evaluate_save() {
		
		
		
		$data = array (
				'schoolid' => $this->input->post('schoolid'),
				'classname' => getNumber ( $this->input->post ( 'classname' ) ),
				'studentid' => intval ( $this->input->post ( 'studentid' ) ),				
				'star' => trim ( $this->input->post ( 'star' ) ),
				'title' => trim ( $this->input->post ( 'title' ) ),
				'uid' => trim ( $this->input->post ( 'uid' ) ),
				'addtime' => time (),
				'xuexi' => intval ( $this->input->post ( 'xuexi' ) ),
				'laodong' => intval ( $this->input->post ( 'laodong' ) ),
				'wenming' => intval ( $this->input->post ( 'wenming' ) ),
				'tiyu' => intval ( $this->input->post ( 'tiyu' ) ),
				'yishu' => intval ( $this->input->post ( 'yishu' ) ),
				'chengxin' => intval ( $this->input->post ( 'chengxin' ) ) 
		);
		if (empty ( $data ['studentid'] )) {
			error ( 1, 'studentid is null' );
		}
		$query = $this->db->insert ( 'fly_evaluate', $data );
		
		// 勋章星星 加
		$star_data = array (
				'schoolid' => $data['schoolid'],
				'classname' => $data ['classname'] ,
				'xuexi' => intval ( $data ['xuexi'] ),
				'laodong' => intval ( $data ['laodong'] ),
				'wenming' => intval ( $data ['wenming'] ),
				'tiyu' => intval ( $data ['tiyu'] ),
				'yishu' => intval ( $data ['yishu'] ),
				'chengxin' => intval ( $data ['chengxin'] )
		);
		$this->star_model->update ( $data ['studentid'], $star_data );
		
		success ( 'ok', $this->db->insert_id () );
	}	
	
} // 类结束

