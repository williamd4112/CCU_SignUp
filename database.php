<?php
	
	//Host settings
	$db_host = "localhost";
	$db_username = "root";
	$db_password = "w868755714";

	//Connection
	$db_link = @mysql_connect($db_link , $db_username , $db_password);
	if(!$db_link)
		die("Fail to connect to the database.");

	//Charaset
	mysql_query("SET NAMES 'utf8'");
	
	$db = @mysql_select_db("student");
	if(!$db)
	  die("Database selection failed");

	function createTable($name , $fields)
	{
		$sql_query = 'CREATE TABLE '.$name.'(';
		foreach($fields as $index=>$field){
			$sql_query .= $field['name'].' varchar(255)';
			if(((int)$index + 1) == count($fields))
			  $sql_query .= ');';
			else
			  $sql_query .= ',';
		}
		$resource = mysql_query($sql_query);
		if(!$resource)
		  return 'query failed';
		else{
		  return $sql_query;
		}
	}


?>
