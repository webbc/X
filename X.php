<?php
/**
 * 自动加载类
 */

define('XPATH',__DIR__);//定义框架的根目录
require (XPATH.'/XBase.php');//包含XBase文件

//继承XBase类
class X extends \X\XBase{

}

//定义自动加载
X::$map = [ 
    'X\Base\App'=>XPATH.'/Base/App.php',
    'X\Base\Controller'=>XPATH.'/Base/Controller.php',
    'X\Base\Model'=>XPATH.'/Base/Model.php',
    'X\Base\DataBase'=>XPATH.'/Base/DataBase.php'
];

spl_autoload_register(['X','myAutoLoader']);//设置自动加载

?>