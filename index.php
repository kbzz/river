<?php
header("Content-Type: text/html;charset=utf-8");
$DEVICE_NO = 'kdt2101661';
$key = '56e20';
$content = "";
$content .= "<CB>测试打印</CB><BR>";
$content .= "名称　　　　　 单价  数量 金额
";
$content .= "--------------------------------<BR>";
$content .= "饭　　　　　　 1.0    1   1.0<BR>";
$content .= "炒饭　　　　　 10.0   10  10.0<BR>";
$content .= "蛋炒饭　　　　 10.0   10  100.0<BR>";
$content .= "鸡蛋炒饭　　　 100.0  1   100.0<BR>";
$content .= "番茄蛋炒饭　　 1000.0 1   100.0<BR>";
$content .= "西红柿蛋炒饭　 1000.0 1   100.0<BR>";
$content .= "西红柿鸡蛋炒饭 100.0  10  100.0<BR>";
$content .= "备注：加辣<BR>";
$content .= "--------------------------------<BR>";
$content .= "合计：xx.0元<BR>";
$content .= "送货地点：北京市海淀区xx路xx号<BR>";
$content .= "联系电话：15999999988888<BR>";
$content .= "订餐时间：2015-09-09 09:08:08<BR>";
$content .= "<QR>http://open.printcenter.cn</QR><BR>";
$result = sendSelfFormatOrderInfo($DEVICE_NO, $key, 1,$content);
var_dump($result);
function sendSelfFormatOrderInfo($device_no,$key,$times,$orderInfo){ // $times打印次数
	$selfMessage = array(
		'deviceNo'=>$device_no,  
		'printContent'=>$orderInfo,
		'key'=>$key,
		'times'=>$times
	);				
	$url = "http://open.printcenter.cn:8080/addOrder";
	$options = array(
		'http' => array(
			'header' => "Content-type: application/x-www-form-urlencoded ",
			'method'  => 'POST',
			'content' => http_build_query($selfMessage),
		),
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	
	return $result;
}

?>