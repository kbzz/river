<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );
	
/*
 * android 会员接口
 */
include 'api.php';
class member extends Api {
	private $table = 'fly_member';
	
	function __construct() {
		parent::__construct ();
	}
	
	// 本班家长列表  聊天那用的
	function lists() {
		
		
		
		$list = array ();
		$uid = intval ( $_GET ['uid'] );
		$schoolid =  intval($this->input->get('schoolid'));
		$classname = getNumber ( $this->input->get ( 'classname' ) );
		
		$query = $this->db->query ( "SELECT id,truename,gender,thumb,studentid,relation FROM $this->table where schoolid='$schoolid' and classname='$classname' and catid=1 ORDER BY id DESC limit 500" );
		$list = $query->result_array ();
		foreach ( $list as &$row ) {
			if ($row ['thumb']) {
				$row ['thumb'] = base_url () . new_thumbname ( $row ['thumb'], 100, 100 );
			}
		}
		$list = $this->student_model->append_list2 ( $list );
		
		echo json_encode ( $list );
	}
	
	// 本班教师列表 在聊天模块
	function teacher_list() {
		
		
		
		$list = array ();
		$schoolid =  intval($this->input->get('schoolid'));
		$classname = getNumber ( $this->input->get ( 'classname' ) );
		
		$query = $this->db->query ( "SELECT m.id,m.truename,m.gender,m.thumb FROM $this->table m,fly_teacher t where schoolid='$schoolid' and m.id=t.id and m.catid=2 and (t.manage_class like '%{$classname}%' or t.teach_class like '%{$classname}%') ORDER BY m.id DESC limit 200" );
		$list = $query->result_array ();
		foreach ( $list as &$row ) {
			if ($row ['thumb']) {
				$row ['thumb'] = base_url () . new_thumbname ( $row ['thumb'], 100, 100 );
			}
		}
		$list = $this->student_model->append_list2 ( $list );
		
		echo json_encode ( $list );
	}
	
	// 会员 登录验证
	public function check_login() {
		
		
		$catid = intval ( $this->input->get_post ('catid') );		
		$username = trim ( $this->input->post ( 'username' ) );
		$password = trim ( $this->input->post ( 'password' ) );
		
		if (empty ( $username ) || empty ( $password )) {
			error ( 5, '用户名和密码不能为空' );
		}
		
		// 手机号码
		if(strlen($username)>=11) {
			$wheredata = array (
					'catid' => $catid,
					'tel' => $username
			);
		} else {  // 账号
			$wheredata = array (
					'catid' => $catid,
					'username' => $username
			);
		}
		$query = $this->db->get_where ( 'fly_member', $wheredata, 1 );
		$user = $query->row_array ();
		if (empty ( $user )) {
			error ( '1', '账号不存在' );
		}
		$password = get_password ( $password );
		if ($user ['password'] != $password) {
			error ( '2', '密码错误' );
		}
		if ($user ['status'] == 0) {
			error ( '3', '账号已被锁定，请联系管理员' );
		}
		
		$this->detail ( $user['id'] );
	}
	
	// 检测会员 是否可用，是否更新资料了，0 没有更新，1 更新无需重新登录 2 需要重新登录；	
	public function check_status() {
		$status = 0;
		$uid = intval ( $_GET ['uid'] );
		if($uid) {
			$status = $this->member_model->check_status($uid);
		}
		
		echo json_encode ( array('status'=>$status) );
	}
	
	// 注册
	public function regist() {
		
		
		$postdate = array (
				'username' => trim ( $_POST ['username'] ),
				'password' => trim ( $_POST ['password'] ),
				'email' => trim ( $_POST ['email'] ),
				'nickname' => trim ( $_POST ['nickname'] ),
				'tel' => trim ( $_POST ['tel'] ),
				'regtime' => time (),
				'status' => 1,
				'lastlogintime' => time () 
		);
		
		if ($postdate ['username'] == "" || $postdate ['password'] == "") {
			error ( 1, '用户名或者密码不能为空' );
		}
		$query = $this->db->query ( "select id from fly_member where username='{$postdate[username]}' limit 1" );
		if ($query->num_rows () > 0) {
			error ( 2, '用户名已经存在，请换一个' );
		}
		$query = $this->db->query ( "select id from fly_member where email='{$postdate[email]}' limit 1" );
		if ($query->num_rows () > 0) {
			error ( 3, '邮箱已经被使用，请换一个' );
		}
		
		$postdate ['password'] = get_password ( $postdate ['password'] );
		$query = $this->db->insert ( 'fly_member', $postdate );
		if ($this->db->insert_id () > 0) {
			$postdate ['id'] = $this->db->insert_id ();
			$postdate ['groupid'] = 1;
			exit ( json_encode ( $postdate ) );
			
			// 统计加1
			$this->load->model ( 'stat_model' );
			$this->stat_model->day_save ( 'members' );
		}
	}
	
	// 修改
	public function update() {
		
		
		
		$uid = intval ( $_POST ['uid'] );
		$postdate = array (
				'email' => trim ( $_POST ['email'] ),
				'nickname' => trim ( $_POST ['nickname'] ),
				'tel' => trim ( $_POST ['tel'] ),
				'sign' => trim ( $_POST ['sign'] ) 
		);
		
		if (empty ( $uid )) {
			error ( 1, 'uid is null' );
		}
		
		$query = $this->db->query ( "select id from fly_member where email='$postdate[email]' limit 1" );
		$row = $query->row_array ();
		if (! empty ( $row )) {
			if ($row ['id'] != $uid) {
				error ( 2, 'email used' );
			}
		}
		
		$query = $this->db->update ( 'fly_member', $postdate, 'id = ' . $uid );
		success ( 'ok' );
	}	
	
	
	// 获取一条会员全部信息
	function detail($uid=0) {
	
		
		if($_GET['uid']) {
			$uid = intval($_GET['uid']);
		}
		if(empty($uid)) {
			error ( 1, 'uid is null' );
		}
		
		$row = $this->member_model->get_one($uid);
		if(empty($row)) {
			error ( 2, 'user is null' );
		}
		unset ( $row ['password'] );
		$row ['userid'] = $row ['id']; // ios需要
		if ($row ['thumb']) {
			$row ['thumb'] = base_url () . new_thumbname ( $row ['thumb'], 100, 100 );
		}
		if ($row['catid']==1) {
			$row = $this->student_model->append_one ( $row );
		}
		if ($row['catid']==2) {
			$row ['manage_class_name'] = setClassname ( $row ['manage_class'] );
			$row ['teach_class_name'] = setClassname ( $row ['teach_class'] );
		}
		
		// 客户端资料更新成功后，把状态码 设为正常 0
		$this->member_model->set_status2_ok($uid);
		
		echo json_encode ( $row );
	}
	
	// 会员 找回密码
	function find_password() {
		
		
		
		$result = '';
		$email = trim ( $_POST ['email'] );
		
		if (! isemail ( $email )) {
			error ( 1, "邮箱格式错误" );
		}
		
		$query = $this->db->get_where ( 'fly_member', array (
				'email' => $email 
		), 1 );
		$row = $query->row_array ();
		if (empty ( $row )) {
			error ( 2, "没有找到该邮箱" );
		}
		
		// 修改密码
		$this->load->helper ( 'string' );
		$radom = strtolower ( random_string ( 'alpha', 6 ) );
		$new_password = get_password ( $radom );
		$this->db->update ( 'fly_member', array (
				'password' => $new_password 
		), array (
				'email' => $email 
		) );
		
		// 发送新密码到该邮箱
		$this->load->library ( 'email' );
		$config ['protocol'] = 'smtp';
		$config ['smtp_host'] = 'smtp.qq.com';
		$config ['smtp_user'] = '1574147371@qq.com';
		$config ['smtp_pass'] = 'ruantejishu000';
		$config ['smtp_port'] = '25';
		$config ['newline'] = "\r\n";
		$this->email->initialize ( $config );
		$this->email->from ( '1574147371@qq.com', '微路' );
		$this->email->to ( $email );
		$this->email->subject ( '微路客户端 - 找回密码' );
		$this->email->message ( "微路客服提示您：您的新密码已重置为  $radom " );
		$this->email->send ();
		
		success ( 'ok' );
		
		// 第一步 发送邮箱验证码到他邮箱
		// 第二步 验证 验证码是否正确
		// 第三步 重新设置新密码
	}
	
	// 更新 此会员登录次数 登录时间
	function update_logincount() {
		$uid = intval ( $this->input->get ( 'uid' ) );
		
		if (! empty ( $uid )) {
			$query = $this->db->query ( 'update fly_member set logincount=logincount+1,lastlogintime=' . time () . ' where id=' . $uid );
		}
	}
	
	// 修改 保存头像
	function thumb_save() {
		$return = "";
		$uid = intval ( $_GET ['uid'] );
		if (empty ( $uid )) {
			error ( 1, "uid is null" );
		}
		
		if ($_FILES ['thumb'] ['name']) {
			$data ['thumb'] = uploadFile ( 'thumb', 'member' );
			if ($data ['thumb']) {
				thumb ( $data ['thumb'], 100, 100 );
				$query = $this->db->update ( 'fly_member', $data, 'id = ' . $uid );
				$return = base_url () . new_thumbname ( $data ['thumb'], 100, 100 );
				success ( $return );
			}
		}
		
		error ( 2, "thumb update failed" );
	}
	
	// 获取头像
	function thumb() {
		
		
		
		$uid = intval ( $_GET ['uid'] );
		if (empty ( $uid )) {
			error ( 1, "uid is null" );
		}
		
		$data = $this->member_model->get_one ( $uid );
		
		$ret = array (
				'small' => '',
				'big' => ''
		);
		
		if($data ['thumb']) {
			$ret = array (
					'small' => base_url () . new_thumbname ( $data ['thumb'] ),
					'big' => base_url () . $data ['thumb']
			);
		}
				
		echo json_encode ( $ret );
	}
	
	// 会员昵称 修改 保存
	function nickname_save() {
		
		$return = "";
		$uid = intval ( $_REQUEST ['uid'] );
		$data ['nickname'] = trim ( $_REQUEST ['nickname'] );
		
		if (! empty ( $uid ) && ! empty ( $data ['nickname'] )) {
			$query = $this->db->update ( 'fly_member', $data, 'id = ' . $uid );
		}
		
		success ( 'ok' );
	}
	
	// 会员个性签名 修改 保存
	function sign_save() {
		
		
		$return = "";
		$uid = intval ( $_REQUEST ['uid'] );
		$data ['sign'] = trim ( $_POST ['sign'] );
		
		if (! empty ( $uid )) {
			$query = $this->db->update ( 'fly_member', $data, 'id = ' . $uid );
		}
		
		success ( 'ok' );
	}
	
	// 电话号码 修改 保存
	function tel_save() {	
		
		
		
		$uid = intval ( $_POST ['uid'] );
		$data ['tel'] = trim ( $_POST ['tel'] );
		if(empty ( $uid ) ) {
			error(1,'用户id为空');
		}
		if(strlen ( $data ['tel'] ) < 11) {
			error(2,'手机号码不能少于11位');
		}
		
		// 检查手机号码 是否存在		
		if($this->member_model->is_tel_exist($data['tel'], $uid)){
			error (3, '手机号码已经存在，请更换' );
		}
		
		// 更新 保存
		$data['status2'] = 1;
		$query = $this->db->update ( 'fly_member', $data, 'id = ' . $uid );		
		
		success ( 'ok' );
	}
	
	// 会员 密码 修改 保存post字段 uid, old_password, new_password
	function password_save() {
		
		
		$uid = intval ( $this->input->post ('uid') );
		$old_password =  trim ( $this->input->post ('old_password') );
		$new_password =  trim ( $this->input->post ('new_password') );
		
		if (empty ( $uid ) || empty ( $old_password ) || empty ( $new_password )) {
			error ( 1, '用户id, 原密码和新密码不能为空' );
		}	
		if ($old_password == $new_password) {
			error ( 5, '原密码和新密码不能相同' );
		}	
		
		$query = $this->db->get_where ( 'fly_member', 'id = '.$uid, 1 );
		$row = $query->row_array ();
		if (empty ( $row )) {
			error ( 2, '该用户不存在' );
		}
		
		$old_password = get_password($old_password);
		$new_password = get_password($new_password);				
		if( $row['password'] != $old_password) {
			error ( 3, '原密码不正确' );
		}
		
		$this->db->update ( 'fly_member', array (
				'password' => $new_password 
		), 'id = ' . $uid );
		$affected = $this->db->affected_rows ();
		if ($affected == 0) {
			error ( 4, '对不起，出错了，请稍后再试' );
		}
		
		success ( 'ok' );
	}
	
	// 查找会员
	function list_search() {
		
		
		$schoolid =  intval($this->input->get('schoolid'));
		$keywords = trim ( $this->input->post ( 'keywords' ) );
		if (empty ( $keywords )) {
			error ( 1, 'keywords is null' );
		}
		$list = $this->member_model->list_search ( $keywords, $schoolid );
		
		echo json_encode ( $list );
	}
	
	// 好友列表
	function friend_list() {
		
		
		
		$uid = intval ( $_GET ['uid'] );
		if (empty ( $uid )) {
			error ( 1, 'uid is null' );
		}
		echo json_encode ( ($this->member_model->friend_list ( $uid )) );
	}
	
	// 添加好友
	function friend_add() {
		
		
		$uid = intval ( $_GET ['uid'] );
		$fid = intval ( $_GET ['fid'] );
		if (empty ( $uid ) || empty ( $fid )) {
			error ( 1, 'uid or fid is null' );
		}
		$this->member_model->friend_add ( $uid, $fid );
		success ( 'ok' );
	}
	
	// 删除好友好友
	function friend_delete() {
		
		
		$uid = intval ( $_GET ['uid'] );
		$fid = intval ( $_GET ['fid'] );
		if (empty ( $uid ) || empty ( $fid )) {
			error ( 1, 'uid or fid is null' );
		}
		$this->member_model->friend_delete ( $uid, $fid );
		success ( 'ok' );
	}
	
	
	// 本班家长列表  教师端的 家长使用情况
	function parents_list() {
		
		
		$list = array ();
		$uid = intval ( $_GET ['uid'] );
		$schoolid =  intval($this->input->get('schoolid'));
		$classname = getNumber ( $this->input->get ( 'classname' ) );
		
		$query = $this->db->query ( "SELECT id,username,truename,studentid,relation,lastlogintime,logincount,tel FROM $this->table where schoolid='$schoolid' and classname='$classname' and catid=1 ORDER BY id DESC limit 500" );
		$list = $query->result_array ();
		foreach ( $list as &$value ) {
			$value['lastlogintime'] = times( $value['lastlogintime'] , 1 );
		}
		$list = $this->student_model->append_list2 ( $list );
	
		echo json_encode ( $list );
	}
	
} // 类结束
