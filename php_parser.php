<?php

class PHPObject
{
	private $name;
	private $class;
	private $fields;

	public function __construct($name , $class , $fields)
	{
			$this->name = $name;
			$this->class = $class;
			$this->fields = $fields;
	}

}

function getContext($filename)
{
	$file = fopen('test.txt' , 'r');
	if(!$file)
	  return false;

	$content = fgets($file);
	//while($line = fgets($file)){
	  //$content .= $line;
	  //echo $line;
	//}
	fclose($file);
	return $content;	
}


?>

