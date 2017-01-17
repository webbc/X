<?php
/**
 * 应用初始化类
 */
namespace X\Base;
class App{

    /**
     * 构造方法
     */
    public function __construct(){
        $this->initSystem();
    }

    /**
     * @return void
     * 初始化系统
     */
    private function initSystem(){
        set_error_handler([$this,'myErrorHandler']);
        set_exception_handler([$this,'myExceptionHandler']);
    }

    /**
     * 用户自定义的错误控制处理中心
     * @param  错误编号
     * @param  错误消息
     * @param  错误文件名
     * @param  错误的行号
     * @return void
     */
    public function myErrorHandler($errno,$errstr,$errfile,$errline){
        $errorException = new \ErrorException($errstr,$errno,1,$errfile,$errline);
        throw $errorException;//抛出错误异常
    }

    /**
     * 用户自定义异常控制处理中心
     * @param  异常对象
     * @return void
     */
    public function myExceptionHandler($exception){
        $this->handler($exception);
    }

    /**
     * 异常处理
     * @param  异常对象
     * @return void
     */
    private function handler($exception){
        $msg = $exception->getMessage();
        $line = $exception->getLine();
        $file = $exception->getFile();
        echo 'line:',$line,'msg:',$msg ,'<br/><pre>';
        $traces = $exception->getTrace();
        //如果接受的是错误导致的异常，则删除调用栈的第一个数组元素
        if($exception instanceof \ErrorException){
            array_shift($traces);
        }
        print_r($traces);//打印异常调用栈
    }

    /**
     * 路由分析，包括控制器和操作的分析，地址参数的分析
     * @return arr $ca 控制器和操作名的数组
     */
    private function resolve(){
        //1、控制器和操作的分析
        $path = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';//获取路径信息
        $path = trim($path,'/');//将开头和结尾的/截取掉
        if($path === ''){ //路径为空
            $path = []; //创建一个空数组
        }else{//路径不为空
            $path = explode('/', $path);//将路径分割成数组
        }
        //如果路径的控制器名或操作名没有指定，强制指定为Index控制器下的index方法
        $ca = $path + ['Index','index'];

        //2、地址栏参数的分析
        $params = array_slice($path,2);
        for($i=0,$len=count($params);$i<$len-1;$i+=2){
            $_GET[$params[$i]] = $params[$i+1];
        }
        return $ca;
    }

    /**
     * 运行控制器，根据地址栏的变化来运行不同的控制器
     * @return [type] [description]
     */
    public function runController(){
        list($controller,$action) = $this->resolve();//获取当前url的控制器和操作名
        $controller = ucfirst($controller).'Controller';//拼接形成类名
        $controllerObj = $this->createController($controller);//创建控制器对象
        $controllerObj->$action();//调用控制器的操作
    }

    /**
     * 创建控制器对象并返回
     * @return obj $class 控制器对象
     */
    private function createController($controller){
        $className = $controller;//接受控制器名
        $classFile = APP_PATH.'/Controller/'.$className.'.php';//获取控制器类的路径
        //判断控制器类是否被加载并且控制器类文件存在
        if(!class_exists($className,false) && is_file($classFile)){
            \X::$map[$className] = $classFile;//自动加载
        }
        return new $className();
    }
}
?>
