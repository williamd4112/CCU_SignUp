<?php

require_once('form_template.php');
require_once('pdo_database.php');

$account_database = 'account';

function filter($str)
{
	
}

function check($base , $username , $password)
{
	try{
		$prepare = $base->prepare('SELECT * FROM account WHERE `username`= :username AND `password`= :password');
		$prepare->execute(array(':username'=>$username , ':password'=>$password));
		$prepare->setFetchMode(PDO::FETCH_ASSOC);
		$rows = $prepare->fetchAll();
		return (empty($rows)) ? 0 : 1;
	}catch(PDOException $exception){
		return -1;
	}
}

$msg = "";

if(isset($_POST['username']) && isset($_POST['password'])){
  $username = $_POST['username'];
  $password = $_POST['password'];
  if($validation = check($db , $username , $password) == 0)
	$msg = new Message('登入失敗');
  else if($validation < 0)
	$msg = new Message('資料庫連線失敗');
  else{
	session_start();
	$_SESSION['username'] = $_POST['username'];
	Header("Location: admin.php");
  }
}

$head = new Head('selectDate' , 'index' , '學務處報名系統登入');
$frame = new Frame();
$body = new Body();
$frame->add($head);
$frame->add($body);
$content = $frame;
	
$block = new Block('登入' , 'login');
if(!empty($msg))
  $block->add($msg);
$block->add(new	QuesitionItem('使用者名稱' , new Text('username')));
$block->add(new	QuesitionItem('密碼' , new Password('password')));
$block->add(new Panel());

$content .= $block;

echo $content;

?>
