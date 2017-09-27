<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
/*
 * 统计接口
 */
include 'api.php';
class stat extends Api {
	
	function __construct() {
		parent::__construct ();
		
		
	}
	
	// 访问日志，记录会员访问次数和访问时间
	function base_save() {
		$data = array (
				'schoolid' => $this->input->post('schoolid'),
				'uid' => intval ( $_POST ['uid'] ),
// 				'city' => trim ( $_POST ['city'] ),
// 				'district' => trim ( $_POST ['district'] ),
// 				'lnglat' => trim ( $_POST ['lnglat'] ),
				'version' => trim ( $_POST ['version'] ),
				'os_version' => trim ( $_POST ['OSVersion'] ),
				'phone_model' => trim ( $_POST ['PhoneModel'] ),
				'phone_brand' => trim ( $_POST ['PhoneBrand'] ),
				'phone_os' => trim ( $_POST ['PhoneOS'] ),
				'client' => trim ( $_POST ['client'] ),
				'ip' => ip (),
				'addtime' => time ());
		
		if (empty ( $data ['version'] )) {
			error ( 1, 'data version is null' );
		}
		
		$query = $this->db->insert ( 'fly_stat', $data ); // 写入基础统计表
		
		$this->db->query("update fly_member set logincount=logincount+1,lastlogintime='$data[addtime]' where id='$data[uid]' limit 1");
		
		// 写入统计概况
		if($data['client'] == 1) {
			$this->stat_model->school($data['schoolid'], 'app_parents');
		}
		if($data['client'] == 2) {
			$this->stat_model->school($data['schoolid'], 'app_teacher');
		}
		
		success('ok');
	}
	
}  // 类结束


