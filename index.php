<?php

//require_once('database.php');
//require_once('pdo_database.php');
require_once('dirwalk.php');
require_once('define.php');
require_once('form_template.php');
require_once('template.php');
require_once('validation.php');
require_once('data.php');
require_once('pdo_database.php');

$page_index = (isset($_POST['page'])) ? $_POST['page'] : '1';
$division = (isset($_POST['division']) ? $_POST['division'] : 'safe');

$head = new Head('selectDate' , 'index' , '學務處報名系統');
$frame = new Frame();
$body = new Body();
//navigator
$navigator = new Navigator('學務處報名系統');
foreach($list_divisions as $key=>$item)
	$navigator->add(new Button('submit' , $key , 'division' , $item));
$body->add($navigator);
$frame->add($head);
$frame->add($body);
$content = $frame;

//Get directory info
$fileslist = dirwalk('./form/'.$division , 'xml');
$tables = array();

foreach($fileslist as $file){
	$title = explode('.',end(explode('/' , $file)))[0];
	$tables[$title] = $file;
}

if(isset($_POST['selection'])){
	$xml = simplexml_load_file('./form/'.$division.'/'.$_POST['selection'].'.xml');

	$nodes = array();
	foreach($xml->children() as $child)
	  array_push($nodes , $child);

	$instruction = $nodes[2];

	$info = new Info($nodes[0]->name , $nodes[0]->division , $nodes[0]->deadline);

	$field_args = array();
	foreach($nodes[1] as $obj)
	  array_push($field_args , xmlobjToArray($obj));

	$fields = array();
	foreach($field_args as $arg){
	  $require = $arg['require'];
	  $hint = $arg['hint'];
	  if(empty($arg['require']))
		$require = 0;
	  if(empty($arg['hint']))
		$hint = "";
	  array_push($fields , new Field($require , $arg['name'] , $arg['input'] , $hint , $arg['only']));
	}
	$view = run($db , $info , $fields , $_POST['selection'] , $_POST['division'] , $instruction);
	$content .= $view;
}
else{
	$block = new Block('目錄' , 'index');
	$items = array();
	foreach($tables as $key=>$value){
		$xml = simplexml_load_file($value);
		$nodes = $xml->children();
		array_push($items , new IndexItem($key , 'selection' , array(array_search($nodes->info->division , $list_divisions) , $nodes->info->deadline)));
	}
	$book = new Book($items , 20);
	$block->add($book->getPage($page_index));
	$block->add(new Input(array('hidden' , $division) , 'division'));
	$content .= $block;
}

//$block->add('<pre>'.print_r($tables , true).'</pre>');
//$content .= $block;

echo $content;

?>
