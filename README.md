# X
这是一个比较简单的PHP框架，很多功能都是模仿TP里面实现的，比如说模型中ORM映射、链式操作、字段过滤等，数据库操作是采用PDO的预处理来操作的。

----------
#使用X框架
##1、创建应用程序目录

![应用程序目录结构图](http://i.imgur.com/2z9EJsg.jpg)

- index.php 文件：项目入口文件
- config.php 文件：数据库配置文件
- Controller 目录:存放用户自定义控制器
- Model 目录:存放用户自定义模型
- View 目录：存放用户自定义输出模板
	- 控制器目录,比如：User
		- 模板文件，存放该控制器下的模型文件


##2、创建index.php单入口文件

    <?php
	/**
	 * 应用程序入口
	 */
	define('APP_PATH',__DIR__);//定义项目根目录
	require ('../X/X.php');//引入框架的核心文件
	$app = X::createApp();//创建应用程序对象
	$app->runController();//运行框架的控制器
	?>

##2、定义数据库配置
	<?php
	/**
	 * 数据库配置文件
	 */
	return [
	    'host'=>'localhost',
	    'user'=>'root',
	    'password'=>'',
	    'dbname'=>'test'
	];
	?>
##3、创建一个控制器
这里创建一个UserController来进行说明。

	<?php
	/**
	 * 用户控制器
	 */
	use \X\Base\Controller;
	class UserController extends Controller{
		
		//用户注册操作
	    public function reg(){
	        $userModel = new \UserModel();//创建模型对象

	        $userModel->find(27);//根据主键查找数据

	        $userModel->delete(22);//根据主键删除数据
	        
			//添加数据
	        $data = ['username'=>'九纹龙史进','password'=>'111','gender'=>'女'];
	        $userModel->add($data);
	        
			//修改数据
	        $data = ['uid'=>23,'username'=>'admin','password'=>'222333','email'=>'222@qq.com'];
	        $userModel->save($data);
	        
			//ORM调用方式添加数据
	        $userModel->username = '宋江';
	        $userModel->password = '123';
	        $userModel->add();

			//ORM调用方式修改数据	
	        $userModel->find(44);
	        $userModel->username = '宋公明';
	        $userModel->password = '密码：'.$userModel->password;
	        $userModel->save();
	        
			//链式方法查询数据
	        $data = $userModel->fields('username,password,email,count(uid) as people')->where(['username'=>'admin','status'=>0])->group('username')->having('people >= 1')->order('email desc')->limit(0,5)->select();
	        $data = $userModel->fields('uid')->select();
			
			//显示模板内容
	        $title = '今天天气不错';
	        $content = '今天敲了很少的代码';
	        $this->assign('title',$title);
	        $this->assign('content',$content);
	        $this->display();
	    }
	
	}
	?>

##4、创建一个模型
这里采用UserModel来进行说明

	<?php
	/**
	 * 用户模型层
	 */
	use \X\Base\Model;
	class UserModel extends Model{
	    //...用户自定义操作数据方法
	}
	?>
##5、创建模板文件
首先应该在View目录下创建和控制器名称相同的目录，在新创建的目录下才能创建模板文件，模板文件和操作名相同。

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
	    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	    <title>Document</title>
	</head>
	<body>
	    <h1><?php echo $title;?></h1>
	    <p><?php echo $content;?></p>
	</body>
	</html>