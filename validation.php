<?php

require_once('define.php');
//require_once('pdo_database.php');

//front-end input format: name="$name[0]"
//									  1
//									  2
//									  3
//									  4
//									  ...
//text,textarea , select use 0
//checkbox use 0 ~ ...
//count of input array must be larger than require	
function validate($fields)
{
  //count of match
  $match = 0;
  //expected count of matching
  $expect = 0;
  foreach($fields as $field){
	if($field->require > 0)
	  $expect++;
	else
	  continue;
	if(isset($field) && !empty($field)){
	  $type = $field->input[0];
	  if($type == 'checkbox'){
		if(isset($_POST[$field->name]) && !empty($_POST[$field->name]))
			if(count($_POST[$field->name]) >= $field->require)
				 $match++;
	  }
	  else{
		if(isset($_POST[$field->name][0]) && !empty($_POST[$field->name][0]))
		  $match++;	
	  }
	}
  }
	
	//totally incomplete
	if($match == 0)
		 return -1;
	
	//totally complete:w
	if($match == $expect)
		return 1;
	//partially
	else
		return 0;
}

function isRepeatCookieSeries($series , $ref)
{
  for($i = 0 ; $i < 25 ; $i++){
	if(isset($_COOKIE[$series.$i]))
	  if($_COOKIE[$series.$i] == $ref)
		return true;
  }

  return false;
}

function outdate($info)
{
	return (strtotime($info->deadline) < strtotime(date("Y-m-d"))) ? true : false;
}

function validatePOST($fields)
{
	foreach($fields as $field){
	  if(!isset($_POST[$field]) || empty($_POST[$field]))
		return false;
	}
	return true;
}

function run($db , $info , $fields , $selection , $division , $instruction="")
{
	if(outdate($info)){
		$message = new Message("報名已經截止" , "outdate");
		$instruction = '<div class="instruction"><span>項目說明<br></span><div class="text">'.nl2br($instruction).'</div></div>';
	  	$panel = new Panel(array("返回"=>"button") , "history.back()");
		$panel->markID('outdate');
		$view = generateView($info , $instruction.$message.$panel , "outdate");
	  	return $view;   
	  	exit;
	}

	$validation = validate($fields);
	  
	if($validation > 0){
		if($validation_only = validateOnly($info , $fields) < 0){
		  switch($validation_only){
		  case -1:
			return fail($info , '資料已存在' , $selection , $division);
		  case -2:
			return fail($info , '資料庫連接失敗' , $selection , $division);
		  default:
			break;
		  }
		  exit;
		}
		
		//insert into data base
		//$sql_query = genQuery($info , $fields);
		//$resource = mysql_query($sql_query);
		$resource = assembleSQL($db , $info , $fields);
		if($resource < 0)
	  		return fail($info , '資料庫連接失敗 , 請聯絡相關單位' , $selection , $division);		
		else 
			return success($info , $selection , $division);

	}
	else if($validation < 0)
		return sign($info , $fields , $selection , $division , $instruction);
	 else
  		return fail($info , '資料表不完整' , $selection , $division);
}

function assembleSQL($base , $info , $fields)
{
	try{
		$query = 'INSERT INTO '.$info->name.' (';
		foreach($fields as $index=>$field){
			$query .= $field->name;
			if($index + 1 == count($fields))
			  $query .= ') ';
			else
			  $query .= ',';
		}
		$query .= 'VALUES (';
		foreach($fields as $index=>$field){
			$query .= '?';
			if($index + 1 == count($fields))
			  $query .= ');';
			else
			  $query .= ',';
		}

		$prepare = $base->prepare($query);

		$values = array();
		foreach($fields as $field){
		  if($field->input[0] == 'checkbox'){
				$val = "";
				foreach($_POST[$field->name] as $col)
				  $val .= $col.' ';
				array_push($values , $val);
				//$prepare->bindValue(':'.$field->name , $val);
		  }
		  else
			array_push($values , $_POST[$field->name][0]);
			//$prepare->bindValue(':'.$field->name , $_POST[$field->name][0]);
		}
		//foreach($values as $key=>$value)
		  //$prepare->bindValue($key , $value);
		//echo $prepare->queryString();
		//$prepare->debugDumpParams();
		//echo '<pre>'.print_r($values , true).'</pre>';
		//echo '<pre>'.print_r($base->errorInfo()).'</pre>';
		$result = $prepare->execute($values);

		return ($result) ? 1 : -1;

	}catch(PDOException $e){
		return -1;
	}
}

function validateOnly($info , $fields)
{

	foreach($fields as $field){
	  if($field->only == 'true'){
		  if(isset($_POST[$field->name])){
			  if(!empty($_POST[$field->name][0])){
				$sql_query = 'SELECT `'.$field->name.'` FROM '.$info->name.' WHERE `'.$field->name.'`=\''.$_POST[$field->name][0].'\';';
				$resource = mysql_query($sql_query);
				if($resource){
				  $row = mysql_fetch_row($resource);
				  if(!empty($row)){
					//print_r($row);
					//echo fail($info , '資料已經存在' , $selection);
					return -1;
				  }
				}
				else{
					//echo fail($info , '資料庫連接失敗 , 請聯絡相關單位' , $selection);
					return -2;
				}
			  }
			}
		  }
	}

	return true;

}

//one of condition is key=>value
function genLIKEQuery($conditions , $end="" , $conj='AND')
{
	if(empty($conditions))
	  return false;

	$sql_query = 'WHERE ';
	$check = 'WHERE ';
	$count = 0;
	foreach($conditions as $field){
		//if(empty($field)||empty($field['key'])||empty($field['value']))
		 // continue;
		$sql_query .= '`'.$field['key'].'` LIKE \'%'.$field['value'].'%\' ';
		if($count+1 == count($conditions))
			$sql_query .= $end;
		else
		  $sql_query .= $conj;
		$count++;
	}

	return ($check == $sql_query) ? false  : $sql_query;
	
}

function genQuery($info , $fields)
{
	$sql_query = 'INSERT INTO `'.$info->name.'` (';
	foreach($fields as $index=>$field){
		$sql_query .= '`'.$field->name.'`';
		if(((int)$index + 1) == count($fields))
			$sql_query .= ')';
		else
			$sql_query .= ',';	
	}
	$sql_query .= ' VALUES(';
	foreach($fields as $index=>$field){
		if($field->input[0] == 'checkbox'){
			$sql_query .= '\'';
			$count = 0;
			foreach($_POST[$field->name] as $col){
				$sql_query .= $col;
				if((int)$count+1 == count($_POST[$field->name]))
					$sql_query .= '\'';
				else
					$sql_query .= ' ';
					$count++;
			}
		}
		else
		  $sql_query .= '\''.$_POST[$field->name][0].'\'';

		if(((int)$index + 1) == count($fields))
			$sql_query .= ');';
		else
			$sql_query .= ',';	
	}

	return $sql_query;
}

function success($info , $selection , $division)
{
  	$message = new Message("報名成功" , 'success');
	$panel = new Panel(array("返回"=>"submit") , "location.replace('index.php')" );
	$view = generateView($info , $message.$panel , "normal");
	
	return $view;
}

function sign($info , $fields , $selection , $division , $instruction)
{
	//echo '<pre>'.print_r($fields , true).'</pre>';
 	$qlist = array();
	foreach($fields as $field)
	  	array_push($qlist , new Quesition($field));
	$content = new Form($qlist , $selection , $division , $instruction);
	$view = generateView($info , $content , "normal"); 
	
	return $view;
}

function fail($info , $msg , $selection , $division)
{
	$message = new Message($msg , 'error');
  	$panel = new Panel(array("返回"=>"button") , "history.back()");
	$panel->markID('error');
	$view = generateView($info , $message.$panel , "error"); 
	
	return $view;
}

function multipleMatch($value , $ref , $flag='AND')
{
  switch($flag){
		case 'AND':
		  foreach($ref as $match)
			if(!empty($match))
				if(!strstr($value , $match))
					return false;
		  return true;
		  break;
		case 'OR':
		  break;
		default:
		  break;
  }
  return false;
}

?>
