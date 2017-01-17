<?php
/**
 * 模型基类
 */
namespace X\Base;
class Model{

    private $table = '';//模型相连的数据库表
    private $db = null;//数据库的连接对象
    private $fields = [];//数据库表的字段
    private $pk = '';//数据库中的主键
    private $data = [];//存放ORM映射
    private $options = [];//存放查询sql语句的相关配置

    public function __construct(){
        $this->getTable();//分析表名
        $this->getConn();//连接数据库
        $this->parseTable();//分析表结构
        $this->reset();//重设查询sql的条件内容
    }

    /**
     * 魔术方法，读取ORM调用中的属性值
     * @param  str $name 数据库的字段
     * @return $name 该字段的值
     */
    public function __get($name){
        return isset($this->data[$name])?$this->data[$name]:null;
    }

    /**
     * 魔术方法，设置ORM调用中的属性值
     * @param str $name  数据库的字段
     * @param str $value 字段的值
     */
    public function __set($name,$value){
        $this->data[$name] = $value;
    }
    
    /**
     * 获取与模型相连的数据库表
     * @return void
     */
    public function getTable(){
        $class = get_called_class();
        $table = strtolower(substr($class, 0,-5));
        $this->table = $table;
    }

    /**
     * 连接数据库
     * @return void
     */
    public function getConn(){
        $this->db = new \X\Base\DataBase();
        $this->db->setCharset('utf8');
    }

    /**
     * 分析表字段和主键
     * @return void 
     */
    public function parseTable(){
        $info = $this->db->getAll('desc '.$this->table);//获取数据库表的结构信息
        foreach($info as $v){
            $this->fields[] = $v['Field'];
            if($v['Key'] === 'PRI'){
                $this->pk = $v['Field'];
            }
        }
    }

    /**
     * 根据主键查找一行数据
     * @param  void $id 主键
     * @return arr     一行数据数组
     */
    public function find($id){
        $sql = 'select * from '.$this->table.' where '.$this->pk.'= ?';
        return $this->data = $this->db->getRow($sql,[$id]);
    }

    /**
     * 根据主键删除一行数据
     * @param  int $id 主键值
     * @return int $num 返回受影响的行数    
     */
    public function delete($id){
        $sql = 'delete from '.$this->table.' where '.$this->pk.'=?';
        return $this->db->delete($sql,[$id]);
    }

    /**
     * 添加一条数据
     * @param array $data 要添加的数据数组
     * @return int $num 返回添加的主键
     */
    public function add($data=[]){
        //如果调用add方法时传入空数组，则从ORM调用方式中读取数据
        if(empty($data)){
            $data = $this->data;
            $this->data = [];//还原ORM调用方式中的数据
        }

        $data = $this->facade($data);//过滤非法字段

        //如果插入的数据为空，为报错
        if(empty($data)){
            throw new \Exception('data object is empty');
        }
        $keys = array_keys($data);//获取数组中的所有键
        $vals = array_values($data);//获取数组中的所有值
        $sql = 'insert into '.$this->table.' (';
        $sql .= implode($keys, ',');
        $sql .= ') values ('.str_repeat('?,', count($keys));
        $sql = rtrim($sql,',') .')';
        return $this->db->insert($sql,$vals);
    }

    /**
     * 修改一条数据
     * @param  arr $data 要修改的数组数据
     * @return int       返回修改的影响的行数
     */
    public function save($data=[]){
        if(empty($data)){
            $data = $this->data;
        }
        $this->facade($data);//过滤非法字段
        if(!array_key_exists($this->pk,$data)){
            throw new \Exception('need the primary key on save',500);
        }
        $sql = 'update '.$this->table.' set ';
        $pkValue = $data[$this->pk];
        unset($data[$this->pk]);
        foreach ($data as $key => $value) {
            $sql.=$key.' = ?,';
        }
        $sql = rtrim($sql,',');
        $sql .= ' where '.$this->pk.' = ?';
        array_push($data,$pkValue);
        return $this->db->update($sql,array_values($data));
    }

    /**
     * 查询方法
     * @return array 查询返回的内容
     */
    public function select(){
        $sql = $this->parseSQL();
        echo $sql,'<br/>';
        $data = $this->db->getAll($sql);
        $this->reset();
        return $data;
    }

    /**
     * 指定查询字段
     * @param  string $str 查询字段字符
     * @return obj 当前model对象
     */
    public function fields($fields){
        $this->options['fields'] = $fields;
        return $this;
    }

    /**
     * 指定查询条件
     * @param  array  $condition 查询的条件
     * @return [type]          [description]
     */
    public function where($condition = []){
        //如果传入的是数组，则拆分数组转换为条件字符串
        if(is_array($condition)){
            $temp = '';
            foreach ($condition as $k => $v) {
                $temp .= ' '.$k .' = \'' .$v.'\''.' and';
            }
            $temp = rtrim($temp,' and');
            $this->options['where'] = 'where '.$temp;
        }else if(is_string($condition)){//如果已经是条件字符串，则直接使用
            $this->options['where'] .= 'where '.$condition;
        }
        return $this;
    }

    /**
     * 指定分组条件
     * @param  string $group 分组条件
     * @return obj        当前模型对象
     */
    public function group($group = ''){
        if(!empty($group)){
            $this->options['group'] .= 'group by '.$group;
        }
        return $this;
    } 

    /**
     * 指定分组筛选条件
     * @param  string $having 分组筛选的条件
     * @return obj         当前模型对象
     */
    public function having($having = ''){
        if(!empty($having)){
            $this->options['having'] .=  'having '.$having;
        }
        return $this;
    }

    /**
     * 指定排序条件
     * @param  string $order 要排序的条件
     * @return obj        当前模型对象
     */
    public function order($order = ''){
        if(!empty($order)){
            $this->options['order'] .= 'order by '.$order;
        }
        return $this;
    }

    /**
     * 指定查询的条目数
     * @param  int $offset 起始值
     * @param  int $n      总共的条数
     * @return obj         当前模型对象
     */
    public function limit($offset,$n=null){
        if($n === null){
            $n = $offset;
            $offset = 0;
        }
        $this->options['limit'] .= 'limit '.$offset.','.$n;
        return $this;
    }

    /**
     * 拼接查询的SQL语句
     * @return string SQL语句
     */
    public function parseSQL(){
        $format = 'select %s from %s %s %s %s %s %s';
        $sql = sprintf($format,$this->options['fields'],$this->table,$this->options['where'],$this->options['group'],$this->options['having'],$this->options['order'],$this->options['limit']);
        return $sql;
    }

    /**
     * 重新设置查询SQL配置
     * @return [type] [description]
     */
    public function reset(){
        $this->options = [
            'fields'=>'*',
            'where'=>'',
            'group'=>'',
            'having'=>'',
            'order'=>'',
            'limit'=>''
        ];
    }

    /**
     * 清除不合法字段
     * @param  array $data 要查询的数组      
     * @return array       过滤后的数组
     */
    public function facade($data){
        foreach ($data as $k => $v) {
            if(!in_array($k,$this->fields)){
                unset($data[$k]);
            }
        }
        return $data;
    }


}
?>

