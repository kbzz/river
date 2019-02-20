<?php 

echo 1231;
exit;
require_once('./list.php');
require_once('./db.php');
$page=isset($_GET['page'])?$_GET['page']:1;
$pageSize=isset($_GET['pageSize'])?$_GET['pageSize']:5;

if(!is_numeric($page) || !is_numeric($pageSize)){
	return Response::show('401','数据不合法');
}


$offset=($page-1)*$pageSize;
$sql="select * from ims_bj_qmxk_zitidian where weid=5 order by id asc limit ".$offset.",".$pageSize."";
try{
	$connect=Db::getInstance()->connect();
}catch(Exception $e){
	return Response::show('403','数据获连接异常');
}

$result=mysql_query($sql,$connect);

$videos=array();
while($video=mysql_fetch_assoc($result)){
	$videos[]=$video;
}
if($videos){
	return Response::show('200','数据获取成功',$videos);
}else{
	return Response::show('400','数据获取失败',$videos);
}