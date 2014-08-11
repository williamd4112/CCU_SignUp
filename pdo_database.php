<?php

$dbtype_sql = 'mysql';
$host_sql = 'localhost';
$dbname_sql = 'student';
$username_sql = 'root';
$password_sql = 'w868755714';

$db = "";

try { 
	$db = new PDO($dbtype_sql.':host='.$host_sql.';dbname='.$dbname_sql , $username_sql , $password_sql);
	//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
	$db->query('SET NAMES UTF8');
	//$db->exec('set names utf8');
}catch(PDOException $e){
	 die('Error!: ' . $e->getMessage() . '<br />');
}   

?>
