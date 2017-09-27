<?php   
class BBB{  
      
    private $name;  
    function __construct($name){  
     $this->name = $name;  
    }  
    function hello() {  
        echo $this->name;  
    }  
}  


class MyClass 
{ 
   var $s = "Hello World";
 public  function getProperty() { 
        return $this->s; 
    } 
  /*   function getProperty($name) { 
        echo $name;
    } 
*/	
  public static function river($aaa) { 
        echo $aaa; 
    } 

}
$aaa='kiss me';
MyClass::river($aaa);

$a= new MyClass;

$a->s ='kiss you';

echo $a->getProperty();

?>  
