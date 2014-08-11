<?php

function xmlobjToArray($xmlObject)
{
	$out = array();
	foreach ( (array) $xmlObject as $index => $node  )
			$out[$index] = ( is_object ( $node  )  ) ? xmlobjToArray ( $node  ) : $node;

	return $out;
}
function toCSV($name)
{
  $list = toList($_SESSION['selection']);
  header('Content-type:application/force-download');
  header('Content-Transfer-Encoding: UTF8');
  header('Content-Disposition:attachment;filename='.$name.'.csv');
  foreach($list as $row){
	foreach($row as $key=>$value){
		$key = (int)$key + 1;
		echo $value;
		if($key == count($row))
		  echo "\n";
		else
		  echo ',';
	}
  }
}

function toList($sql_table)
{
  $list = array();
  $sql_query = "SELECT * FROM ".$sql_table.';';
  $resource = mysql_query($sql_query);
  //..query failed

  //get field
  $fields = array();
  while($col = mysql_fetch_field($resource))
	array_push($fields , $col->name);
  array_push($list , $fields);

  //get data
  while($row = mysql_fetch_row($resource))
	array_push($list , $row);

  return $list;
}

function rewriteXMLForm($nodes)
{
	$xml = new SimpleXMLElement('<xml/>');	
	//info
	$node_info = $xml->addChild('info');
	$node_info->addChild('name' , $nodes->info->name);
	$node_info->addChild('division' , $nodes->info->division);
	$node_info->addChild('deadline' , $nodes->info->deadline);	
	//fields
	//$block->add('<pre>'.print_r($nodes , true).'</pre>');
	$node_fields = $xml->addChild('fields');
	foreach($nodes->fields->field as $field){
		$node_field = $node_fields->addChild('field');
		foreach($field as $key=>$value)
			$node_field->addChild($key , $value);
	}

	//instruction
	$node_instruction = $xml->addChild('instruction' , $nodes->instruction);

	return $xml->asXML();
}

function toXML($info , $fields , $instruction)
{
  $xml = new SimpleXMLElement('<xml/>');
  
  //info
  $node_info = $xml->addChild('info');
  $node_info->addChild('name' , $info->name);
  $node_info->addChild('division' , $info->division);
  $node_info->addChild('deadline' , $info->deadline);

  //fields
  $node_fields = $xml->addChild('fields');
  foreach($fields as $field){
	$node_field = $node_fields->addChild('field');
	foreach($field as $key=>$value){ 
	  if(!is_array($value))
		if(!empty($value))
			$node_field->addChild($key , $value);
	    else
		    $node_field->addChild($key , " ");
	  else
		foreach($value as $child_value)
		  $node_field->addChild($key , $child_value);
	}
  }	

  //instruction
  $node_instruction = $xml->addChild('instruction' , $instruction);

  return $xml->asXML();
}

?>
