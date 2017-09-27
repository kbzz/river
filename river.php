<?php



require_once('./file.php');
require_once('./list.php');


$data=array(
    'id'=>1,
    'name'=>'kiss is',
    'pass'=>'me',
    'iss'=>array(
    	'ke'=>7,
    	'ke1'=>8,
    	'ke2'=>array(7,8,9)
    	)


 );
/*   	$file= new file();

	if($file->cacheData('river',null,'1111')){
		echo 'success';
	}else{
		echo 'error';
	}
*/

 Response::show('1010','操作成功',$data);








?>












