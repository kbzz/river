<?php

/**
* 
*/
class Response
{       
  
  /*按综合方式输出通信数据
    @param integer $code 状态码
    @param string $message 提示信息
    @param array $data 提示信息
    @param string $type 输出类型
    */
     const JSON = "json";

    public static function show($code,$message='',$data=array(),$type=self::JSON){

        if(!is_numeric($code)){
            echo '非法入侵';
            return '';
        }

        $type=isset($_GET['format'])?$_GET['format']:self::JSON;

        $result=array(

            'code'=>$code,
            'message'=>$message,
            'data'=>$data,        

            ); 
          
        if($type == 'json'){

            self::json($code,$message,$data);
            exit;

        }elseif($type == 'array'){

            var_dump($result);

        }elseif($type == 'xml') {
            self::xmlEncode($code, $message, $data);
            exit;
        } else {
            // TODO
        }
      
    }    


    /*按json方式输出通信数据
    @param integer $code 状态码
    @param string $message 提示信息
    @param array $data 提示信息*/

    public static function json($code,$message='',$data=array()){

        if(!is_numeric($code)){
            return '错误';
        }

        $data=array(

            'code'=>$code,
            'message'=>$message,
            'data'=>$data

            );
        echo json_encode($data);
        exit;
    }

    /*按xml方式输出通信数据
    @param integer $code 状态码
    @param string $message 提示信息
    @param array $data 提示信息*/

    public static function xmlEncode($code, $message, $data = array()) {
            if(!is_numeric($code)) {
                return '';
            }

            $result = array(
                'code' => $code,
                'message' => $message,
                'data' => $data,
            );

            header("Content-Type:text/xml");
            $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
            $xml .= "<root>\n";

            $xml .= self::xmlToEncode($result);

            $xml .= "</root>";
            echo $xml;
        }

        public static function xmlToEncode($data) {

            $xml = $attr = "";
            foreach($data as $key => $value) {
                if(is_numeric($key)) {
                    $attr = " id='{$key}'";
                    $key = "item";
                }
                $xml .= "<{$key}{$attr}>";
                $xml .= is_array($value) ? self::xmlToEncode($value) : $value;
                $xml .= "</{$key}>\n";
            }
            return $xml;
        }

}



?>