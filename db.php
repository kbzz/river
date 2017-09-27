<?php

class Db{
	static private $_instance;
	static private $_connectSoure;
	private $_dbConfig=array(
			'host'=>'127.0.0.1',
			'user'=>'root',
			'password'=>'root',
			'database'=>'o2o',
		);
	private function __construct(){

	}

	static public function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self();	
		}	
		return self::$_instance;
	}

	public function connect(){
	  if(!self::$_connectSoure){	
			  self::$_connectSoure = mysql_connect($this->_dbConfig['host'],$this->_dbConfig['user'],$this->_dbConfig['password']); 
			  if(!self::$_connectSoure){
			  	throw new Exception('mysql connect error'.mysql_errno());
			  	//die('mysql connect error'.mysql_errno());
			  }
			  mysql_select_db($this->_dbConfig['database'],self::$_connectSoure);
			  mysql_query("set names UTF8",self::$_connectSoure);
	  }
	  return self::$_connectSoure;
	}
}

/*$connect=Db::getInstance()->connect();
$sql="select * from ims_bj_qmxk_goods";
$result=mysql_query($sql,$connect);
echo mysql_num_rows($result);
var_dump($result);
*/