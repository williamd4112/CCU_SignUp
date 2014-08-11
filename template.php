<?php

function selection($options , $name , $class , $attach="")
{
	$str = '<select name="'.$name.'" class="'.$class.'" '.$attach.'>';
	foreach($options as $key=>$value)
	  $str .= '<option value="'.$value.'">'.$key.'</option>';
	$str .= '</select>';
	
	return $str;
}

function table($fields , $content , $class)
{
	$str = '<table class="'.$class.'">';
	$str .= '<tr class="'.$class.'">';
	foreach($fields as $value)
	  $str .= '<td class="'.$class.'">'.$value.'</td>';
	$str .= '</tr>';

	foreach($content as $item){
	  $str .= '<tr class="'.$class.'">';
	  foreach($item as $column){
		$str .= '<td class="'.$class.'">'.$column.'</td>';
	  }
	  $str .= '</tr>';
	}
	$str .= '</table>';

	return $str;
}

//obtain basic input information , $attach is used to add some advanced info
function input($type , $name , $class , $attach="")
{
	$str = '<input type="'.$type.'"" name="'.$name.'" class="'.$class.'" '.$attach.'>';

	 return $str;
}

function input_hidden($name , $value)
{
	return '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}

//given each type of buttons and panel class return a panel
function panel($types , $class)
{
	$str = '<div class="'.$class.'">';
	foreach($types as $text=>$type)
		$str .= '<button type="'.$type.'" class="'.$class.'">'.$text.'</button>';
	$str .= '</div>';

	return $str;
}

function instruction($msg , $class='instruction')
{
	return '<p class="'.$class.'">'.$msg.'</p>';
}

function form_start($method , $action="")
{
	return '<form method="'.$method.'" action="'.$action.'">';
}

function form_end()
{
	return '</form>';
}

function headerinfo($title , $division)
{
	$str = '<head>';
	$str .= '<script src="'.$division.'.js" type="text/javascript"></script>';
	$str .= '<link rel=stylesheet type="text/css" href="'.$division.'.css">';
	$str .= '<title>'.$title.'</title></head>';

	return $str;
}

function logo()
{
	return '<img src="class.png">';
}

//given a field info , return a question
function quesition($field)
{
	$str = '<div class="question">';
	$str .= '<p id="qtitle">'.$field['name'].'</p>';
	//has some hint
	if(!empty($field['hint']))
	  $str .= '<p id="qhint">'.$field['hint'].'</p>';
	//proccess input area
	if(is_array($field['input'])){
		switch($field['input'][0]){
			case 'checkbox':
			{
				for($i = 1 ; $i < count($field['input']) ; $i++){
				  $str .= '<p>'.$field['input'][$i].'</p>';
				  $str .= '<input value="'.$field['input'][$i].'" type="checkbox" name="'.$field['name'].'">';
				}
				break;
			}
			case 'select':
			{
				$str .= '<select name="'.$field['name'].'">';
				for($i = 1 ; $i < count($field['input']) ; $i++)
				  $str .= '<option value="'.$field['input'][$i].'">'.$field['input'][$i].'</option>';
				$str .= '</select>';
				break;
			}
		}
	}
	else{
		switch($field['input']){
			case 'text':
				$str .= '<input type="text" name="'.$field['name'][0].'">';
				break;
			case 'textarea':
				$str .= '<input type="textarea" name="'.$field['name'][0].'"';
				break;
			default:
				break;
		}
	}
	
	$str .= '</div>';
	
	return $str;
}

function generateForm($fields , $tablename , $division , $deadline)
{
	//head
	$output = '<?php ';
	$output .= 'require_once(\'../../template.php\');';
	$output .= 'require_once(\'../../form_template.php\');';
	$output .= 'require_once(\'../../validation.php\');';
	
	//info
	$output .= '$info = new Info("'.$tablename.'" , "'.$division.'" , "'.$deadline.'");';
	$output .= '$fields = array();';
	foreach($fields as $field){
		 $output .= '$inputs = array();';
		 foreach($field['input'] as $col)
			 $output .= 'array_push($inputs , "'.$col.'");';	   
		 if(!isset($field['hint']) || empty($field['hint']))
			 $field['hint'] = "";
		 $output .= '$field = new Field("'.$field['require'].'" , "'.$field['name'].'" , $inputs , "'.$field['hint'].'" , '.$field['only'].');';
		 $output .= 'array_push($fields , $field);';
	}
	
	//controller
	$output .= 'run($info , $fields)';
	$output .= ' ?>';

	return $output;
}

function generateView($info , $content , $status="normal")
{
	//head
    //$output = '<html>';
	//$output .= $info;

	//body
	//$output .= '<body>';
	//body-form
	$output = '<div class="block" id="form">';
		//body-form-title
		$output .= '<div class="title" id="'.$status.'"><span>';
			$output .= new Title($info->name , $info->deadline);
		$output .= '</span></div>';
		//body-form-content
		$output .= '<div class="content">';
			$output .= $content;
		$output .= '</div>';	
	$output .= '</div>';
	//$output .= '</body>';
	//$output .= '</html>';
	
	return $output;
}

function generateMain($content , $sidebar , $control)
{	
	//Left Side
	$output = '<div class="leftside">';
	foreach($sidebars as $sidebar)
	  $output .= $sidebar;
	$output .= '</div>';
	//Center
	$output = '<div class="center">';
		$output .= '<div class="control">';
		$output .= '</div>';
	$output .= '</div>';
	$output .= '</div>';
}
