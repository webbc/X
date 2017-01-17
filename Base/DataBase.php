<?php
/**
 * 数据库类
 */
namespace X\Base;
class DataBase extends \PDO{

    public function __construct(){
        $cfg = require (APP_PATH.'/config.php');
        $dsn = 'mysql:host='.$cfg['host'].';dbname='.$cfg['dbname'];
        parent::__construct($dsn,$cfg['user'],$cfg['password']);
    }

    /**
     * 设置字符集
     * @param void $charset 字符集
     */
    public function setCharset($charset){
        $this->query('set names '.$charset);
    }

    /**
     * 切换数据库
     * @param  str $dbname 数据库名称
     * @return void         
     */
    public function selectDB($dbname){
        $this->query('use '.$dbname);
    }

    /**
     * 获取一行数据
     * @param  str $sql    sql语句模板
     * @param  arr $params 数组参数
     * @return arr         查询出来的数组
     */
    public function getRow($sql,$params=[]){
        $st = $this->prepare($sql);
        if($st->execute($params)){
            return $st->fetch(\PDO::FETCH_ASSOC);
        }else{
            list(,$errorCode,$errorMsg) = $st->errorInfo();
            throw new \Exception($errorMsg,$errorCode);
        }
    }

    /**
     * 获取多行数据
     * @param  str $sql    sql语句模板
     * @param  array  $params 参数数组
     * @return array         多行数据
     */
    public function getAll($sql,$params=[]){
        $st = $this->prepare($sql);
        if($st->execute($params)){
            return $st->fetchAll(\PDO::FETCH_ASSOC);
        }else{
            list(,$errorCode,$errorMsg) = $st->errorInfo();
            throw new \Exception($errorMsg,$errorCode);
        }
    }

    /**
     * 删除数据
     * @param  str $sql    sql语句对象
     * @param  arr $params 数组参数
     * @return int         受影响的行数
     */
    public function delete($sql,$params=[]){
        $st = $this->prepare($sql);
        if($st->execute($params)){
            return $st->rowCount();
        }else{
            list(,$errorCode,$errorMsg) = $st->errorInfo();
            throw new \Exception($errorMsg,$errorCode);
        }
    }

    /**
     * 插入数据
     * @param  str $sql  sql语句
     * @param  arr $vals 要输入的数据数组
     * @return int       返回新增加的主键值
     */
    public function insert($sql,$vals){
       $st = $this->prepare($sql);
       if($st->execute($vals)){
            return $this->lastInsertId();
       }else{
            list(,$errorCode,$errorMsg) = $st->errorInfo();
            throw new \Exception($errorMsg,$errorCode);
       }
    }

    /**
     * 更新数据
     * @param  str $sql  sql语句
     * @param  arr $vals 要插入的数据数组
     * @return int       受影响的行数
     */
    public function update($sql,$data){
       $st = $this->prepare($sql);
       if($st->execute($data)){
            return $st->rowCount();
       }else{
            list(,$errorCode,$errorMsg) = $st->errorInfo();
            throw new \Exception($errorMsg,$errorCode);
       }
    }


}
?>