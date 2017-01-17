<?php
/**
 * 控制器基类
 */
namespace X\Base;
class Controller {
    private $data = [];//模板中包括的变量
    /**
     * 模板赋值方法
     * @return void 
     */         
    public function assign($key,$value){
        $this->data[$key] = $value;
    }

    /**
     * 控制器的模板输出方法
     * @return void
     */
    public function display(){  
        $class = get_class($this);//获取对象的类名
        $class = str_replace('Controller', '', $class);//剔除Controller子串
        $backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);//获取调用栈
        $lastUseFunction = $backTrace[1]['function'];//获取调用此方法的方法名
        extract($this->data);//将数组转换成变量
        include (APP_PATH.'/View/'.$class.'/'.$lastUseFunction.'.html');//模板内容
    }

}
?>