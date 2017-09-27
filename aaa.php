<?php   
  
require_once './bbb.php';  
  
if(class_exists('BBB')){  
  
    $bbb = new BBB('zzz');  
    $bbb->hello();  
  
    echo "<br>";  
  
/*    $class = 'BBB';  
    $bbb = new $class('李四');  
    $bbb->hello();  
  
    echo "<br>";  
  
    $class = 'BBB';  
    $bbb = new $class('王五');  
    $bbb->hello();  */
}  
?> 