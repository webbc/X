<?php
/**
 * 创建App对象
 */
namespace X;
class XBase{

    private static $app = null;//app实例对象
    public static $map = [];//类名与类文件的映射
    /**
     * 创建并返回app实例对象
     * @return obj app对象
     */
    public static function createApp(){
        if(self::$app === null){
            self::$app = new \X\Base\App();
        }
        return self::$app;
    }

    /**
     * 自动加载类文件
     * @param  str 类名
     * @return void
     */
    public static function myAutoLoader($className){
        $classFile = isset(self::$map[$className])?self::$map[$className]:'';
        if(is_file($classFile)){
            require ($classFile);
        }else if(stripos($className,'Model') !== false){
            require (APP_PATH.'/Model/'.$className.'.php');
        }
    }
}
?>