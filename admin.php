<?php

session_start();

if(!isset($_SESSION['username'])){
  Header('Location: login.php');
  exit;
}

require_once("database.php");
require_once("dirwalk.php");
require_once("simple_html_dom.php");
require_once("template.php");
require_once("form_template.php");
require_once("data.php");
require_once("view.php");
require_once("define.php");
require_once("validation.php");
require_once('php_parser.php');

//reset search
if(isset($_POST['search_navi']) && isset($_POST['db_operation']))
  if($_POST['db_operation'] == 'main')
	unset($_POST['search_navi']);

//default value
$selection = (isset($_POST['selection'])) ? $_POST['selection'] : "";
$operation = (isset($_POST['operation'])) ? $_POST['operation'] : 'explore';
$search_navi = (isset($_POST['search_navi'])) ? $_POST['search_navi'] : array();
$search_block = (isset($_POST['search_block'])) ? $_POST['search_block'] : array();
//$search_remove = (isset($_POST['search_remove'])) ? $_POST['search_remove'] : array();

$db_operation = (isset($_POST['db_operation'])) ? $_POST['db_operation'] : 'main';
$page_index = (isset($_POST['page'])) ? $_POST['page'] : '1';

//value filter
$operation = ($operation == 'confirm_tablename' && empty($_POST['new_tablename'])) ? 'explore' : $operation; 
$operation = ($operation == 'confirm_deadline' && empty($_POST['new_deadline'])) ? 'explore' : $operation;

//search filter
foreach($search_block as $index=>$condition){
  if(!empty($condition)){
	foreach($condition as $value){
	  if(empty($value)){
		unset($search_block[$index]);
		break;
	  }
	}
  }
}


//Static View
$head = new Head('selectDate' , 'admin' , '學務處報名系統管理介面');
$frame = new Frame();
$body = new Body();
//navigator
$navigator = new Navigator('學務處報名系統管理介面');
$navigator->add(new Button('submit' , '首頁' , 'db_operation' , 'main'));
$navigator->add(new Button('submit' , '新增' , 'db_operation' , 'new'));
$navigator->add(new Searchbar('search_navi' , $search_navi));
//put components together
$body->add($navigator);
$frame->add($head);
$frame->add($body);

$content = $frame;

//Get directory info
$fileslist = dirwalk('./form' , 'xml');
$tables = array();

foreach($fileslist as $file){
	$title = explode('.',end(explode('/' , $file)))[0];
	$tables[$title] = $file;
}

//controller
//datatable operation
if(!empty($selection)){
  $data = new Datamodel($selection);

  //cookies check no repeat
  for($cookie_index = 0 ; $cookie_index < $max_cookies ; $cookie_index++){
	if(!isset($_COOKIE['selection'.$cookie_index])){
	  if(!isRepeatCookieSeries('selection' , $selection)){
		setcookie('selection'.$cookie_index , $selection , time()+3600);
		break;
	  }
	}  
  }
  
  //toolbar
  $toolbar = new Toolbar();
  $toolbar->add(new Button('submit' , '瀏覽' , 'operation' , 'explore'));
  $toolbar->add(new Button('submit' , '輸出' , 'operation' , 'export'));
  $toolbar->add(new Button('submit' , '查詢' , 'operation' , 'search'));
  $toolbar->add(new Button('submit' , '刪除' , 'operation' , 'remove'));
  //$toolbar->add(new Button('submit' , '新增' , 'operation' , 'insert'));
  //$toolbar->add(new Button('submit' , '修改' , 'operation' , 'update'));
  $toolbar->add(new Button('submit' , '設定' , 'operation' , 'alter'));
  $toolbar->add(new Input(array('hidden' , $selection) , 'selection'));

  $toolbar_alter = new Toolbar();
  $toolbar_alter->add(new Button('submit' , '修改截止時間' , 'operation' , 'alter_deadline'));
  $toolbar_alter->add(new Button('submit' , '修改名稱' , 'operation' , 'alter_tablename'));
  $toolbar_alter->add(new Input(array('hidden' , $selection) , 'selection'));

  //content block
  $block = new Block($selection);
  $block->add($toolbar);
  
  //export operation
  if(isset($_POST['export_option']))
		switch($_POST['export_option']){
			case 'csv':
				echo $data->toCSV();
				exit;
			case 'xls':
				echo $data->toXLS();
				exit;
			case 'doc':
				echo $data->toDOC();
				exit;
			default;
				break;
		}

  switch($operation){ 	
  case 'export':{
		$itemlist = new Itemlist(array(new Button('submit' , '.csv	(Excel檔案)' , 'export_option' , 'csv'),
									   new Button('submit' , '.xls	(Excel檔案)' , 'export_option' , 'xls'),
									   new Button('submit' , '.doc	(Word檔案)' , 'export_option' , 'doc')));
		$block->add($itemlist);
		$content .= $block;
		break;
  }
  case 'search':{
		$toolbar_search = new Toolbar();
		$xml = simplexml_load_file($tables[$selection]);
		if(!$xml){
			$msg = new Message('檔案讀取失敗' , 'error');
			$block->add($msg);
		}else{
			$nodes = $xml->children();
			$search_class = array();
			foreach($nodes->fields->field as $field)
			  array_push($search_class , $field->name);
			$toolbar_search->add(new AdvancedSearchbar('operation' , 'search' , 'search_block' , $search_class , $search_block));
			$block->add($toolbar_search);

			//generate LIKE query
			if($query = genLIKEQuery($search_block))
				$block->add($data->toTable($query));
			else
				$block->add($data->toTable());
			//$block->add(genLIKEQuery($search_block));
			//$block->add('<pre>'.print_r($search_block , true).'</pre>');
		}
		$content .= $block;
		break;
  }
  case 'insert':
		break;
  case 'remove':{
		$toolbar_search = new Toolbar();
		$xml = simplexml_load_file($tables[$selection]);
		if(!$xml){
			$msg = new Message('系統錯誤: 檔案讀取失敗' , 'error');
			$block->add($msg);
		}else{
			$nodes = $xml->children();
			$search_class = array();
			foreach($nodes->fields->field as $field)
			  array_push($search_class , $field->name);
			$toolbar_search->add('<span>請設定刪除條件</span>');
			$toolbar_search->add(new AdvancedSearchbar('operation' , 'remove' , 'search_block' , $search_class , $search_block));
			$toolbar_search->add(new Button('submit' , '刪除' , 'operation' , 'confirm_remove'));

			//generate LIKE query
			if($query = genLIKEQuery($search_block)){
			   	$block->add($toolbar_search);
				$block->add($data->toTable($query));
				$toolbar_search->add(new Input(array('hidden' , $query) , 'remove_query'));
			}
			else{
				$block->add($toolbar_search);
				$block->add($data->toTable());
			}
		}
		$content .= $block;
		break;
  }
  case 'confirm_remove':{
	if(isset($_POST['remove_query'])){
		$query = $_POST['remove_query'];
		$result = $data->toTable($query);
		if($data->remove($query)){
			$msg = new Message('刪除成功' , 'error');	
			$block->add($msg);
			$block->add($result);
		}
	}else{
		$block->add('empty');
	}
	$content .= $block;
	break;
  }
  case 'explore':{
		$book = new Book($data->toTableItems() , 10);
		$block->add($book->getTablePage($page_index));
		$content .= $block;
		break;
  }
  case 'update':
	break;
  case 'alter':{
		$xml = simplexml_load_file($tables[$selection]);
		$nodes = $xml->children();
		$info_assoc = array('名稱'=>$nodes->info->name , '單位'=>array_search($nodes->info->division , $list_divisions) , '截止日期'=>$nodes->info->deadline , 
		'項目說明'=>nl2br($nodes->instruction));
		$fields = array('最低輸入量','不可重複','欄位名','說明','輸入型態');
	    $items = array();
		foreach($nodes->fields->field as $item)
			array_push($items , xmlobjToArray($item));
		$table = new Table($info_assoc , $fields , $items);
		$block->add($toolbar_alter);
		$block->add($table);
		$content .= $block;
		break;
  }
  case 'alter_tablename':{
		$input_tablename = new Text('new_tablename');
		$quesition_tablename = new QuesitionItem('請輸入新資料表名' , $input_tablename);
		$block->add($quesition_tablename);
		$block->add(new Input(array('hidden' , 'confirm_tablename') , 'operation'));
		$block->add(new Panel());	
		$content .= $block;
		break;	
  }
  case 'alter_deadline':{
		$input_deadline = new Text('new_deadline' , 'readonly' , '點一下選擇日期' , 'onfocus="HS_setDate(this)"');
		$quesition_deadline = new QuesitionItem('請輸入截止日期' , $input_deadline);
		$block->add($quesition_deadline);
		$block->add(new Input(array('hidden' , 'confirm_deadline') , 'operation'));
		$block->add(new Panel());	
		$content .= $block;
		break;
  }
  case 'confirm_tablename':{
		$xml = simplexml_load_file($tables[$selection]);
		$nodes = $xml->children();
		$msg = "";
		//rewrite tablename
		if(isset($_POST['new_tablename'])){
		   $nodes->info->name = $_POST['new_tablename'];
		   //alter database table name
		   $sql_query = 'ALTER TABLE '.$selection.' RENAME TO '.$_POST['new_tablename'].';';
		   $resource = mysql_query($sql_query);
		   //check database
		   if(!$resource){
				$msg = new Message('資料庫修改失敗' , 'error');
		   }
		   else{
				$msg = new Message('更新名稱成功<br>'.$_POST['new_tablename'] , 'success');
				$division = explode('/' , $tables[$selection])[2];
				//$block->add($division);
				$file = fopen($tables[$selection] , 'w');
				//check file
				if($file){
				  $output = rewriteXMLForm($nodes);
				  fputs($file , $output);
				  fclose($file);
				  rename($tables[$selection] , './form/'.$division.'/'.$_POST['new_tablename'].'.xml');
				  //reset $selection , to avoid the next operation errror
				  $block->add(new Input(array('hidden' , $_POST['new_tablename']) , 'selection'));
				}
				else{
				  $msg = new Message('系統錯誤:	檔案寫入失敗' ,'error');
				}
		   }
		}
		else
			$msg = new Message('資料不完整' , 'error');
		$block->add($msg);
		$content .= $block;
		break;
  }
  case 'confirm_deadline':{
		$xml = simplexml_load_file($tables[$selection]);
		$nodes = $xml->children();
		//rewrite deadline
		if(isset($_POST['new_deadline'])){
		   $nodes->info->deadline = $_POST['new_deadline'];
		   //$block->add($nodes->info->deadline);
		   $message = new Message('更新截止日期<br>'.$nodes->info->deadline , 'success');
		   $block->add($message);
		   $output = rewriteXMLForm($nodes);
		   $file = fopen($tables[$selection] , 'w');
		   fputs($file , $output);
		   fclose($file);
		}
		$content .= $block;
		break;
  }
  default:
		echo 'defaut';
		$block->add(print_r($_POST , true));
		$content .= $block;
		break;
  }
}
else{
	switch($db_operation){ 
		case 'main':{
			$block = new Block('目錄');
			$items = array();
			foreach($tables as $key=>$value){
			  if(multipleMatch($key , $search_navi)){
				$xml = simplexml_load_file($value);
				$nodes = $xml->children();
				array_push($items , new IndexItem($key , 'selection' , array(array_search($nodes->info->division , $list_divisions) , $nodes->info->deadline)));
			  }
			}
			$book = new Book($items , 15);	
			$block->add($book->getPage($page_index));
			$content .= $block;	
			break;
		}	
		case 'new':{
			$block = new Block('新增報名表');
			//division
			$input_division = new Selection('division' , $list_divisions , true);
			$quesition_division = new QuesitionItem('請選擇單位' , $input_division);
			$block->add($quesition_division);
			//tablename
			$input_tablename = new Text('tablename');
			$quesition_tablename = new QuesitionItem('請輸入報名表名稱' , $input_tablename);
			$block->add($quesition_tablename);
			//cfield
			$input_cfield = new Text('cfield');
			$quesition_cfield = new QuesitionItem('請輸入欄位數' , $input_cfield);
			$block->add($quesition_cfield);
			//deadline
			$input_deadline = new Text('deadline' , 'readonly' , '點一下選擇日期' , 'onfocus="HS_setDate(this)"');
			$quesition_deadline = new QuesitionItem('請輸入截止日期' , $input_deadline);
			$block->add($quesition_deadline);
			//instruction
			$input_instruction = new Textarea('instruction' , 10 , 70);
			$quesition_instruction = new QuesitionItem('報名項目說明' , $input_instruction);
			$block->add($quesition_instruction);
			//next db_operation
			$input_next_db_operation = new Input(array('hidden' , 'field') , 'db_operation');
			$block->add($input_next_db_operation);
			//panel
			$block->add(new Panel());
			$content .= $block;		
			break; 
		}
		case 'field':{
			$validation = validatePOST(array('division' , 'tablename' , 'cfield' , 'deadline'));
			if(!$validation){
				$block = new Block('資料不完整');
				$message = new Message('資料不完整,請返回上一頁繼續填寫' , 'error');
				$input_db_operation = new Input(array('hidden' , 'new') , 'db_operation');
				$panel = new Panel(array('返回'=>'submit'));
				$block->add($message.$input_db_operation.$panel);
				$content .= $block;
			}
			else{
				$block = new Block('請填寫欄位資料');
				$items = array();
				for($index = 0 ; $index < (int)$_POST['cfield'] ; $index++){
					//default value
					$input_default_require = new Input(array('hidden' , '1') , 'field['.$index.'][require]');
					$input_default_only = new Input(array('hidden' , 'false') , 'field['.$index.'][only]');
					//field name
					$input_field_name = new Text('field['.$index.'][name]');
					$quesition_field_name = new QuesitionItem('請輸入欄位名稱' , $input_field_name);
					//field hint
					$input_field_hint = new Text('field['.$index.'][hint]');
					$quesition_field_hint = new QuesitionItem('請輸入說明(選填)' , $input_field_hint);
					//field require
					$input_field_require = new Checkbox('field['.$index.'][require]' , '0');
					$quesition_field_require = new QuesitionItem('選填' , $input_field_require);
					//field only
					$input_field_only = new Checkbox('field['.$index.'][only]' , 'true');
					$quesition_field_only = new QuesitionItem('不可重複' , $input_field_only);
					//feild input
					$input_field_input = new Selection('field['.$index.'][input][0]' , $list_inputs , true);
					$input_field_input->attach_body('onclick="varStart(this , 0 , \'field['.$index.'][input]\');"');
					$quesition_field_input = new QuesitionItem('請選擇輸入型態' , $input_field_input);
					//field content
					$content_field = $input_default_require.$input_default_only.$quesition_field_name.$quesition_field_hint;
					$content_field .= $quesition_field_require.$quesition_field_only.$quesition_field_input.'<br><hr>';
		 			array_push($items , $content_field);
				}
				foreach($items as $item)
				  $block->add($item);
				$block->add(new Input(array('hidden' , 'confirm') , 'db_operation'));
				$block->add(new Input(array('hidden' , $_POST['tablename']) , 'tablename'));	
				$block->add(new Input(array('hidden' , $_POST['division']) , 'division'));
				$block->add(new Input(array('hidden' , $_POST['deadline']) , 'deadline'));
				$block->add(new Input(array('hidden' , $_POST['instruction']) , 'instruction'));
				$block->add(new Panel());
				$content .= $block;	
			}
			break;
		}
		case 'confirm':{
			//validation
			$validation = true;
			foreach($_POST['field'] as $field)
			  if(!isset($field['name']) || empty($field['name'])){
				$validation = false;
				break;
			  }

			if($validation){
				$block = new Block('資料表格式確認');
				$infos = array('名稱'=>$_POST['tablename'] , '單位'=>array_search($_POST['division'] , $list_divisions) , '截止日期'=>$_POST['deadline'] , '項目說明'=>nl2br($_POST['instruction']));
				$fields = array('最低輸入量','不可重複','欄位名','說明','輸入型態');
				$items = array();
				foreach($_POST['field'] as $row)
				  array_push($items , $row);
				$table = new Table($infos , $fields , $items);
				$panel = new Panel(array('確定'=>'submit' , '返回'=>'button') , 'history.back()');
				$block->add($table);
				$block->add($panel);
				$block->add(new Input(array('hidden' , 'build') , 'db_operation'));
				$block->add(new Input(array('hidden' , $_POST['tablename']) , 'tablename'));
				$block->add(new Input(array('hidden' , $_POST['division']) , 'division'));
				$block->add(new Input(array('hidden' , $_POST['deadline']) , 'deadline'));
				$block->add(new Input(array('hidden' , $_POST['instruction']) , 'instruction'));
				foreach($_POST['field'] as $index=>$field)
				  foreach($field as $key=>$col)
					if(!is_array($col))
						$block->add(new Input(array('hidden' , $col) , 'field['.$index.']['.$key.']'));				 
					else
					    foreach($col as $count=>$cell)
						  $block->add(new Input(array('hidden' , $cell) , 'field['.$index.']['.$key.']['.$count.']'));
				$content .= $block;
			}
			else{
				$block = new Block('資料不完整');
				$message = new Message('資料不完整,請返回上一頁繼續填寫' , 'error');
				$input_db_operation = new Input(array('hidden' , 'field') , 'db_operation');	
				$panel = new Panel(array('返回'=>'submit'));
				$block->add($message.$input_db_operation.$panel);
				$block->add(new Input(array('hidden' , $_POST['tablename']) , 'tablename'));
				$block->add(new Input(array('hidden' , count($_POST['field'])) , 'cfield'));
				$block->add(new Input(array('hidden' , $_POST['division']) , 'division'));
				$block->add(new Input(array('hidden' , $_POST['deadline']) , 'deadline'));
				$content .= $block;			  
			}
			break;	
		}
		case 'build':{
			if(!createTable($_POST['tablename'] , $_POST['field'])){
				$block = new Block('建立失敗');
				$message = new Message('無法建立資料庫' , 'error');		
				$panel = new Panel(array('確定'=>'submit'));
				$block->add($message.$panel);	
			}else{
				$info = new Info($_POST['tablename'] , $_POST['division'] , $_POST['deadline']);
				$instruction = $_POST['instruction'];
				$block = new Block('建立成功');
				$message = new Message('報名表已建立' , 'success');		
				$panel = new Panel(array('確定'=>'submit'));
				$block->add($message.$panel);
				$file = fopen('./form/'.$_POST['division'].'/'.$_POST['tablename'].'.xml' , 'w');
				fputs($file , toXML($info , $_POST['field'] , $instruction));
				fclose($file);
			}
			$content .= $block;
			
			//$output = generateForm($_POST['field'] , $_POST['tablename'] , $_POST['division'] , $_POST['deadline']);
			//$file = fopen('./form/'.$_POST['division'].'/'.$_POST['tablename'].'.xml' , 'w');
			//fputs($file , toXML($info , $_POST['field']));
			//fclose($file);
			//$file = fopen('./form/'.$_POST['division'].'/'.$_POST['tablename'].'.php' , 'w');
			//fputs($file , $output);
			//fclose($file);	
			break;
		}
		default:
		  break;
	}
}


$block_recent = new Block('最近使用' , 'recent');
$list_recent = new Itemlist();
for($i = 0 ; $i < $max_cookies ; $i++){
	if(isset($_COOKIE['selection'.$i]))
		$list_recent->add(new Button('submit' , $_COOKIE['selection'.$i] , 'selection' , $_COOKIE['selection'.$i]));
	else
		break;
}

$block_recent->add($list_recent);
$content .= $block_recent;

echo $content;

?>

<script LANGUAGE="JavaScript">

function varStart(node , index , name)
{	
	 if(node.value == 'select' || node.value == 'checkbox'){
		if(document.getElementById(name))
		  return;
		var vararea = document.createElement("div");
		vararea.setAttribute('id' , name);
		var count_panel = document.createElement("span");
		count_panel.innerHTML += '<button type="button" onclick="addVar(this , \''+name+'\');">+</button>';
		count_panel.innerHTML += '<button type="button" onclick="removeVar(this , \'name\');">-</button>';
		vararea.innerHTML += count_panel.innerHTML;
		vararea.innerHTML += '<br><input type="text" name="'+name+'[1]">';
		node.parentNode.appendChild(vararea);
	}
	else{
		node.parentNode.removeChild(node.nextElementSibling);
	}	
}

function addVar(node , name)
{
	 var index = (node.parentNode.getElementsByTagName('input').length) + 1;
	 var input = document.createElement('input');
	 input.setAttribute('type' , 'text');
	 input.setAttribute('name' , name + '['+index.toString()+']');
	 input.innerHTML += '<br>';
	 node.parentNode.appendChild(input);
}

function removeVar(node , name)
{
	if(node.parentNode.getElementsByTagName('input').length > 1)
		node.parentNode.removeChild(node.parentNode.lastElementChild);
}

function startMultipleInput(element , condition , objID , name)
{
  //clear the html
  document.getElementById(objID).innerHTML = "";
  //add a button
  if(element.value == 'select' || element.value == 'checkbox'){
	document.getElementById(objID).innerHTML += '<button type="button" onclick="addMultipleInput(\''+objID+'\' , \''+name+'\');">+</button>';
	document.getElementById(objID).innerHTML += '<button type="button" onclick="removeMultipleInput(\''+objID+'\' , \''+name+'\');">-</button>';
	//add input type to first column
	var type = (element.value == 'select') ? 'select' : 'checkbox';
	document.getElementById(objID).innerHTML += '<input type="hidden" id="'+objID+'0" value="'+type+'" name="'+name+'[0]'+'">';
	document.getElementById(objID).innerHTML += '<input type="text" id="'+objID+'1" name="'+name+'[1]">';
  }	

}

function removeMultipleInput(objID , name)
{
  //find the last node
  var lastnode;
  var index = 0;
  while((lastnode = document.getElementById(objID + index.toString())) != null)
	index++;

  //never remove initial node
  if(index == 2)
		return;
  //collect previous input
  var type = document.getElementById(objID + '0').value;
  var preCollection = new Array();
  for(i = 1 ; i < index ; i++){
		var nodeID = objID + i.toString();
		preCollection[i] = document.getElementById(nodeID).value;
  }		  
  //clear the multiple input area
  document.getElementById(objID).innerHTML = "";
  document.getElementById(objID).innerHTML += '<input type="hidden" id="'+objID+'0" value="'+type+'" name="'+name+'[0]">';
  document.getElementById(objID).innerHTML += '<button type="button" onclick="addMultipleInput(\''+objID+'\' , \''+name+'\');">+</button>';
  document.getElementById(objID).innerHTML += '<button type="button" onclick="removeMultipleInput(\''+objID+'\' , \''+name+'\');">-</button>';
  //put all previous input in and a new one
  for(i = 1 ; i < index - 1 ; i++){
		var val = preCollection[i];
		document.getElementById(objID).innerHTML += '<input value="'+val+'" type="text" id="'+objID+i+'" name="'+name+'['+i+']'+'">';
  }	 
}

function addMultipleInput(objID , name)
{
  //find the last node
  var lastnode;
  var index = 0;
  while((lastnode = document.getElementById(objID + index.toString())) != null)
		 index++;

  //collect first column [type] (ex: checkbox , select)
  var type = document.getElementById(objID + '0').value;
  //collect previous input
  var preCollection = new Array();
  for(i = 1 ; i < index ; i++){
		var nodeID = objID + i.toString();
		preCollection[i] = document.getElementById(nodeID).value;
  }		  
  //clear the multiple input area and prepare the initial part
  document.getElementById(objID).innerHTML = "";
  document.getElementById(objID).innerHTML += '<input type="hidden" id="'+objID+'0" value="'+type+'" name="'+name+'[0]">';
  document.getElementById(objID).innerHTML += '<button type="button" onclick="addMultipleInput(\''+objID+'\' , \''+name+'\');">+</button>';
  document.getElementById(objID).innerHTML += '<button type="button" onclick="removeMultipleInput(\''+objID+'\' , \''+name+'\');">-</button>';
  //put all previous input in and a new one
  for(i = 1 ; i < index ; i++){
		var val = preCollection[i];
		document.getElementById(objID).innerHTML += '<input value="'+val+'" type="text" id="'+objID+i+'" name="'+name+'['+i+']'+'">';
  }	
  document.getElementById(objID).innerHTML += '<input type="text" id="'+objID+index+'" name="'+name+'['+index+']'+'">';

}


</script>
