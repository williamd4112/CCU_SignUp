<?php

require_once('database.php');

class Input
{
	private $info;
	private $name;

	public function __construct($info , $name)
	{
		$this->info = $info;
		$this->name = $name;
	}

	public function __toString()
	{
	  $str = "";
	  if(is_array($this->info)){
	  	switch($this->info[0]){
			case 'select':
			{
				$str = '<select name="'.$this->name.'[0]">';
				for($index = 1 ; $index < count($this->info) ; $index++)
					$str .= '<option value="'.$this->info[$index].'">'.$this->info[$index].'</option>';
				$str .= '</select>';
				break;
			}
			case 'checkbox':
			{
				for($index = 1 ; $index < count($this->info) ; $index++){
					$str .= '<div class="checkbox">';
					$str .= '<span>'.$this->info[$index].'</span>';
					$str .= '<input type="checkbox" name="'.$this->name.'['.($index-1).']'.'" value="'.$this->info[$index].'">';
					$str .= '</div>';
				}
				break;
			}
			case 'hidden':
			{
					if(count($this->info) == 2)
					  $str .= '<input type="hidden" name="'.$this->name.'" value="'.$this->info[1].'">';
					break;			
			}
			default:
				break;
		}
	  }
	  else{
		switch($this->info){
			case 'text':
			{
				$str = '<input type="text" name="'.$this->name.'[0]">';
				break;
			}
			case 'textarea':
			{
				$str = '<textarea rows="10" cols="40" name="'.$this->name.'[0]"></textarea>';	   
				break;
			}

		}
	  }
	  return $str;
	}
}

class Quesition
{
	private $field;

	public function __construct($field)
	{
		$this->field = $field;
	}

	public function __toString()
	{
		$str = '<div class="quesition">';
		$str .= '<span id="qtitle">'.$this->field->name.'</span><br>'; 
		$str .= '<span id="qhint">'.$this->field->hint.'</span><br>';		
		$str .= new Input($this->field->input , $this->field->name);
		$str .= '<br></div>';

		return $str;	
	}	  
}

class Panel
{
	private $pair;
	private $script;
	private $id;

	public function __construct($pair=array('確定'=>'submit' , '重設'=>'reset') , $script="")
	{
		$this->pair = $pair;
		$this->script = $script;
		$this->id = "";
	}

	public function markID($id)
	{
		$this->id = $id;
	}
	
	public function __toString()
	{
		$this->script = (!empty($this->script)) ? 'onclick="'.$this->script.'"' : "";
		$str = '<div class="panel" id="'.$this->id.'">';
		foreach($this->pair as $key=>$value)
		  $str .= '<button id="'.$this->id.'" type="'.$value.'" '.$this->script.'>'.$key.'</button>';
		$str .= '</div>';
		return $str;
	}
}

class Form
{
	private $qlist;
	private $selection;
	private $division;
	private $instruction;

	public function __construct($qlist , $selection , $division , $instruction="")
	{
		$this->qlist = $qlist;
		$this->selection = $selection;
		$this->division = $division;
		$this->instruction = $instruction;
	}

	public function __toString()
	{
		$str = '<form method="POST" action="">';
		$str .= '<div class="instruction"><span>項目說明<br></span><div class="text">'.nl2br($this->instruction).'</div></div>';
		foreach($this->qlist as $quesition)
		  $str .= $quesition;
		$str .= '<input type="hidden" name="selection" value="'.$this->selection.'">';
		$str .= '<input type="hidden" name="division" value="'.$this->division.'">';
		$str .= new Panel();
		$str .= '</form>';

		return $str;
	}

	public function __get($property)
	{
		return (isset($this->$property)) ? $this->$property : null;
	}
}

class Info
{
	private $name;
	private $division;
	private $deadline;

	public function __construct($name , $division , $deadline)
	{
		$this->name = $name;
		$this->division = $division;
		$this->deadline = $deadline;
	}

	public function __get($property)
	{
		return (isset($this->$property)) ? $this->$property : null;
	}

	public function __toString()
	{
		$str = '<head><script src="'.$this->division.'.js" type="text/javascript"></script>';
		$str .= '<link rel=stylesheet type="text/css" href="'.$this->division.'.css">';
		$str .= '<title>'.$this->name.'</title></head>';  
		return $str;
	}
}

class Message
{
	private $text;
	private $id;

	public function __construct($text , $id='normal')
	{
		$this->text = $text;
		$this->id = $id;
	}

	public function __toString()
	{
		return '<div class="message" id="'.$this->id.'"><span>'.$this->text.'</span></div>';
	}
}

class Title
{
	private $text;
	private $date;
	
	public function __construct($text , $date)
	{
		$this->text = $text;
		$this->date = $date;
	}

	public function __toString()
	{
		$str = '<span class="text">'.$this->text.'</span><br>';
		$str .= '<span class="date">截止日期: '.$this->date.'</span>';

		return $str;
	}

	public function __get($property)
	{
		return (isset($this->$property)) ? $this->$property : null;
	}
}

class Field
{
	private $require;
	private $name;
	private $hint;
	private $input;
	private $only;

	public function __construct($require , $name , $input , $hint="" , $only=false)
	{
		$this->require = $require;
		$this->name = $name;
		$this->hint = $hint;
		$this->input = $input;	
		$this->only = $only;
	}

	public function __get($property)
	{
		return (isset($this->$property)) ? $this->$property : null;
	}
}


//Back-end control page
class Toolbar
{
	private $items;

	public function __construct()
	{
		$this->items = array();
	}

	public function add($item)
	{
		array_push($this->items , $item);
	}

	public function __toString()
	{
		$str = '<div class="toolbar">';
				foreach($this->items as $item)
					$str .= $item;
		$str .= '</div>';
		
		return $str;
	}
}

class Sidebar
{
	private $title;
	private $items;
	
	public function __construct($title)
	{
		$this->title = $title;
		$this->items = "";
	}

	public function add($item)
	{
		$this->items .= $item;
	}

	public function __toString()
	{
		$str = '<div class="sidebar">';
		$str .= '<div class="title">';
		$str .= '<span>'.$this->title.'</span>';
		$str .= '</div>';
		$str .= $this->items;
		$str .= '</div>';

		return $str;
	}
}

class Block
{
	private $title;
	private $items;
	private $class;

	public function __construct($title , $class="block")
	{
		$this->title = $title;
		$this->class = $class;
		$this->items = array();
	}

	public function add($item)
	{
		array_push($this->items , $item);
	}

	public function __toString()
	{
		 $str = '<div class='.$this->class.'>';
			$str .= '<div class="title"><span>'.$this->title.'</span></div>';
			$str .= '<form action="" method="POST">';
			foreach($this->items as $item)
			  $str .= '<div class="content">'.$item.'</div>';	  
			$str .= '</div>';
			$str .= '</form>';
		$str .= '</div>';
		
		return $str;
	}
}

class IndexItem
{
	private $name;
	private $field;
	private $info;

	public function __construct($name , $field , $info="")
	{
		$this->name = $name;
		$this->field = $field;
		$this->info = $info;
	}

	public function __toString()
	{
		$str = '<ul><button name="'.$this->field.'" type="submit" value="'.$this->name.'">'.$this->name;
		if(is_array($this->info))
		  foreach($this->info as $value)
			$str .= '<br><span>'.$value.'</sapn>';
		else
		  $str .= '<br><span>'.$this->info.'</span>';
		$str .= '</button></ul>';
		
		return $str;
	}
}

class Text
{
	 protected $name;
	 protected $readonly;
	 protected $placeholder;
	 protected $script;

	 public function __construct($name , $readonly="" , $placeholder="" , $script="")
	 {
			$this->name = $name;
			$this->readonly = ' readonly"'.$readonly.'"';
			$this->placeholder = ' placeholder="'.$placeholder.'"';
			$this->script = $script;
	 }	

	 public function __toString()
	 {
			return '<input type="text" name="'.$this->name.'"'.$this->readonly.$this->placeholder.$this->script.'>';
	 }
}

class Password extends Text
{
	public function __toString()
	{
		return '<input type="password" name="'.$this->name.'"'.$this->readonly.$this->placeholder.$this->script.'>';
	}
}

class Textarea
{
	 private $name;
	 private $rows;
	 private $cols;	 

	 public function __construct($name , $rows=10 , $cols=40)
	 {
		$this->name = $name;
		$this->rows = $rows;
		$this->cols = $cols;
	 }

	 public function __toString()
	 {
		return '<textarea rows="'.$this->rows.'" cols="'.$this->cols.'" name="'.$this->name.'"></textarea>'; 
	 }
}

class Checkbox
{
	private $name;
	private $value;

	public function __construct($name , $value)
	{
		$this->name = $name;
		$this->value = $value;
	}

	public function __toString()
	{
		 return '<input type="checkbox" name="'.$this->name.'" value="'.$this->value.'">';
	}
}

class QuesitionItem
{
	private $title;
	private $input;
	private $flag;

	public function __construct($title , $input="" , $flag=false)
	{		
			$this->input = array();
			$this->title = $title;
			$this->flag = $flag;
			array_push($this->input , $input);
	}

	public function add($input)
	{
			array_push($this->input , $input);
	}

	public function __toString()
	{
		$str = '<div class="quesitionitem">';
		if($this->flag){
			$str .= '<table>';
			$str .= '<tr><td><span>'.$this->title.'</td></span><div class="inputarea">';
			foreach($this->input as $input)
			  $str .= '<td>'.$input.'</td>';
			$str .= '</tr>';
			$str .= '</table>';
		}else{
			$str .= '<div class="title"><span>'.$this->title.'</span></div>';
			$str .= '<div class="inputarea">';
				foreach($this->input as $input)
					$str .= '<div class="inputitem">'.$input.'</div>';
			$str .= '</div>';
		}
		$str .= '</div>';
		return $str;
	}
}

class Book
{
	private $items;
	private $per;

	public function __construct($items , $num=3)
	{
		$this->items = $items;
		$this->per = $num;
	}

	public function add($item)
	{
		array_push($this->items , $item);
	}
	
	//print items directly in html
	public function getPage($page)
	{
		$str = "";
		if(empty($this->items)){
			$str .= new Message('No Data' , 'system');
			return $str;
		}
		for($index = $this->per * ((int)$page - 1) ; $index < $this->per * ((int)$page) ; $index++) 
			if(isset($this->items[$index]))
				$str .= '<div class="item">'.$this->items[$index].'</div>';
			else
			  break;
		$str .= '<div class="panel">';
		for($index = 0 ; $index < count($this->items)/$this->per ; $index++)
			$str .= '<span><button value="'.($index+1).'" name="page">'.($index+1).'</button></span>';
		$str .= '</div>';
		
		return $str;
	}
	
	//print item in table form
	public function getTablePage($page)
	{
		$str = '<table>';
		for($index = $this->per * ((int)$page - 1) ; $index < $this->per * ((int)$page) ; $index++) 
		  if(isset($this->items[$index])){
			$str .= '<tr id="r'.$index.'">';
			foreach($this->items[$index] as $col)
				$str .= '<td>'.$col.'</td>';
			$str .= '</tr>';
		  }
		  else
			break;
		$str .= '</table>';
		
		$str .= '<div class="panel">';
		for($index = 0 ; $index < count($this->items)/$this->per ; $index++)
			$str .= '<span><button value="'.($index+1).'" name="page">'.($index+1).'</button></span>';
		$str .= '</div>';	
		return $str;
	}	
}

class Button
{
	private $type;
	private $text;
	private $value;
	private $event;

	public function __construct($type , $text , $name="" ,  $value="" , $event="")
	{
		$this->type = ' type="'.$type.'" ';
		$this->text = $text;
		$this->name = ' name="'.$name.'" ';
		$this->value = ' value="'.$value.'"';
		$this->event = $event;
	}

	public function __toString()
	{

		return '<button '.$this->type.$this->name.$this->value.$this->event.'>'.$this->text.'</button>';
	}	  

	public function __get($property)
	{
		return (isset($this->$property)) ? $this->$property : null;
	}
}

class Selection
{
	private $options;
	private $name;
	private $attachment_body;
	private $attachment_end;


	public function __construct($name , $options="" , $flag=false , $attachment_body="" , $attachment_end="")
	{
		$this->attachment_body = $attachment_body;
		$this->attachment_end = $attachment_end;
		$this->name = $name;
		$this->options = "";
		if($flag)
		  foreach($options as $key => $value)
			$this->options .= '<option value='.$value.'>'.$key.'</option>';
		else
		  foreach($options as $option)
			$this->options .= '<option value="'.$option.'">'.$option.'</option>';
		
	}

	public function attach_end($item)
	{
		$this->attachment_end .= $item;
	}

	public function attach_body($item)
	{
		$this->attachment_body .= $item;
	}

	public function add($option , $flag=false , $key)
	{
		if($flag)
		  $this->option .= '<option value="'.$option.'">'.$key.'</option>';
		else
		  $this->option .= '<option value="'.$option.'">'.$option.'</option>';
	}

	public function adds($options , $flag=false)
	{
		if($flag)
		  foreach($options as $key => $value)
			$this->options .= '<option value='.$value.'>'.$key.'</option>';
		else
		  foreach($options as $option)
			$this->options .= '<option value="'.$option.'">'.$option.'</option>';
	}

	public function __toString()
	{
		return '<select name="'.$this->name.'" '.$this->attachment_body.'>'.$this->options.'</select>'.$this->attachment_end;
	}
}

class Itemlist
{
	private $items;

	public function __construct($items=array())
	{
		$this->items = $items;
	}

	public function add($item)
	{
		array_push($this->items , $item);
	}

	public function __toString()
	{
		$str = '<div class="list">';
		if(empty($this->items)){
		  $str .= new Message('No data' , 'system');
		}
		foreach($this->items as $item){
		  $str .= '<div class="item"><ul>'.$item.'</ul></div>';
		}
		$str .= '</div>';

		return $str;
	}
}


class Navigator
{
	private $title;
	private $items;

	public function __construct($title)
	{
		$this->title = $title;
	}

	public function add($item)
	{
		$this->items .= $item;
	}

	public function adds($items)
	{
		foreach($items as $item)
		  $this->item .= $item;
	}

	public function __toString()
	{
		$str = '<div class="navigator"><form action="" method="POST">';
			$str .= '<div class="title"><span>';
				$str .= $this->title;
			$str .= '</span></div>';
			$str .= '<div class="control">';
					$str .= $this->items;
			$str .= '</div>';
		$str .= '</form></div>';
		
		return $str;
	}
}

class Searchbar
{
	private $name;
	private $conditions;

	//given array name , and array
	public function __construct($name , $conditions)
	{
		$this->name = $name;
		$this->conditions = $conditions;
	}

	public function __toString()
	{
		$str = "";	
		for($index = 0 ; $index < count($this->conditions) ; $index++)
		  $str .= '<input value="'.$this->conditions[$index].'" type="hidden" name="'.$this->name.'['.$index.']'.'">';
		$str .= '<input type="text" name="'.$this->name.'['.$index.']'.'">';
		//$str .= '<input type="hidden" name="'.$this->operation_name.'" value="search">';
		$str .= '<button type="submit">搜尋</button>';

		return $str;
	}
}

class AdvancedSearchbar
{
	private $name_button;
	private $obj_button;
	private $search_class;
	private $search_conditions;
	private $search_name;

	public function __construct($obj_button , $name_button , $search_name , $search_class , $search_conditions)
	{
			$this->obj_button = $obj_button;
			$this->name_button = $name_button;
			$this->search_name = $search_name;
			$this->search_class = $search_class;
			$this->search_conditions = $search_conditions;
	}

	public function __toString()
	{
			$str = "";	
			for($index = 0 ; $index < count($this->search_conditions) ; $index++){
			   // if(empty($this->search_conditions[$index]['key']) || empty($this->search_conditions[$index]['value'])){
			  //	continue;
			  //}
				$str .= '<input value='.$this->search_conditions[$index]['key'].' type="hidden" name="'.$this->search_name.'['.$index.']'.'[key]">';
				$str .= '<input value='.$this->search_conditions[$index]['value']. ' type="hidden" name="'.$this->search_name.'['.$index.']'.'[value]">';
			}
			$str .= '<select name="'.$this->search_name.'['.$index.'][key]">';
			foreach($this->search_class as $class)
			  $str .= '<option value="'.$class.'">'.$class.'</option>';
			$str .= '</select>';
			$str .= '<input type="text" name="'.$this->search_name.'['.$index.'][value]">';
			$str .= '<button type="submit" name="'.$this->obj_button.'" value="'.$this->name_button.'">搜尋</button>';

			return $str;		
	}
}

class Datamodel
{
	//fields is not custion class Field
	private $fields;
	private $sql_table;

	public function __construct($sql_table)
	{
		$this->sql_table = $sql_table;
		$this->fields = array();
		//get fields information
		$sql_query = 'SELECT * FROM '.$sql_table.';';
		$resource = mysql_query($sql_query);
		if($resource)
		  while($field = mysql_fetch_field($resource))
			array_push($this->fields , $field);
	}

	public function remove($query)
	{
		$sql_query = 'DELETE FROM '.$this->sql_table.' '.$query.';';
		$resource = mysql_query($sql_query);
		
		return ($resource) ? true : false;
	}

	public function toTable($attach_query="")
	{
		$sql_query = 'SELECT * FROM '.$this->sql_table.' '.$attach_query.';';
		$resource = mysql_query($sql_query);
		$str = '<div class="datatable">';
			$str .= '<table>';
				$str .= '<tr id="r0">';
					foreach($this->fields as $field)
						 $str .= '<td>'.$field->name.'</td>';
				$str .=	'</tr>';
					while($row = mysql_fetch_row($resource)){
					  $str .= '<tr>';
						foreach($row as $col)
						  $str .= '<td>'.$col.'</td>';
					  $str .= '</tr>';
					}
			$str .= '</table>';
		$str .= '</div>';
		return $str;
	}

	//collect datas as items array
	public function toTableItems($attach_query="")
	{
		$sql_query = 'SELECT * FROM '.$this->sql_table.' '.$attach_query.';';
		$resource = mysql_query($sql_query);
		
		//query fail...

		$items = array();

		//put all fields info in
		$row_fields = array();
		foreach($this->fields as $field)
		  array_push($row_fields , $field->name);
		array_push($items , $row_fields);

		//put all datas in
		while($row = mysql_fetch_row($resource))
		  array_push($items , $row);

		return $items;	  
	}

	public function toXLS($attach_query="")
	{
		header('Content-type:application/force-download');
		header('Content-Transfer-Encoding: UTF8');
		header('Content-Disposition:attachment;filename='.$this->sql_table.'.xls');
		
		$output = '<table>';

		$output .= '<tr>';
		foreach($this->fields as $field)
		  $output .= '<td>'.$field->name.'</td>';
		$output .= '</tr>';

		$sql_query = 'SELECT * FROM '.$this->sql_table.' '.$attach_query.';';
		$resource = mysql_query($sql_query);
		if(!$resource)
		  die("query fail");

		while($row = mysql_fetch_row($resource)){
			$output .= '<tr>';
				foreach($row as $value)
				  $output .= '<td>'.$value.'</td>';
			$output .= '</tr>';
		}

		$output .= '</table>';
		
		return $output;
	}

	public function toDOC($attach_query="")
	{
		header('Content-type:application/force-download');
		header('Content-Transfer-Encoding: UTF8');
		header('Content-Disposition:attachment;filename='.$this->sql_table.'.doc');
		
		$output = '<table border="1" >';

		$output .= '<tr>';
		foreach($this->fields as $field)
		  $output .= '<td>'.$field->name.'</td>';
		$output .= '</tr>';

		$sql_query = 'SELECT * FROM '.$this->sql_table.' '.$attach_query.';';
		$resource = mysql_query($sql_query);
		if(!$resource)
		  die("query fail");

		while($row = mysql_fetch_row($resource)){
			$output .= '<tr>';
				foreach($row as $value)
				  $output .= '<td>'.$value.'</td>';
			$output .= '</tr>';
		}

		$output .= '</table>';
		
		return $output;
	}

	public function toCSV($attach_query="")
	{
		$sql_query = 'SELECT * FROM '.$this->sql_table.' '.$attach_query.';';
		$resource = mysql_query($sql_query);
		if(!$resource)
		  die("query fail");

		header('Content-type:application/force-download');
		header('Content-Transfer-Encoding: UTF8');
		header('Content-Disposition:attachment;filename='.$this->sql_table.'.csv');
		$output = "";
		$count = count($this->fields);
		foreach($this->fields as $index => $field){
			 $output .= $field->name;
			 if(((int)$index + 1) == $count)
			   $output .= "\n";
			 else
			   $output .= ',';
		}

		while($row = mysql_fetch_row($resource)){
			 $count = count($row);
			 foreach($row as $index=>$col){
				$output .= $col;
				if(((int)$index + 1) == $count)
					$output .= "\n";
				else
				    $output .= ',';
			 }
		}

		return $output;
	}
}

class Table
{
	private $infos; // assoc array
	private $fields;
	private $items;

	public function __construct($infos , $fields , $items)
	{
			$this->infos = $infos;
			$this->fields = $fields;
			$this->items = $items;
	}

	public function __toString()
	{
			$str = "<table>";
			$colspan = count($this->fields) - 1;
			foreach($this->infos as $key=>$info){
				$str .= '<tr>';
				$str .= '<td>'.nl2br($key).'</td>';
				$str .= '<td colspan="'.$colspan.'">'.$info.'</td>';
				$str .= '</tr>';
			}

			$str .= '<tr id="r0">';
			if(is_array($this->fields))
				foreach($this->fields as $field)
					$str .= '<td>'.$field.'</td>';
			$str .= '</tr>';

			if(is_array($this->items))
				foreach($this->items as $item){
					$str .= '<tr>';
					foreach($item as $col)
						$str .= '<td>'.$this->printr($col).'</td>';
					$str .= '</tr>';
				}
			$str .= '</table>';
			return $str;
	}

	public function printr($item)
	{
		$str = "";
		if(is_array($item))
		  foreach($item as $col)
			if(is_array($col))
			  $str .= printr($col);
			else
			  $str .= $col.'<br>';
		else
		  $str .= $item.'<br>';

		return $str;
	}
}

class Head
{
	private $script;
	private $stylesheet;
	private $title;

	public function __construct($script , $stylesheet , $title)
	{
		$this->script = $script;
		$this->stylesheet = $stylesheet;
		$this->title = $title;
	}

	public function __toString()
	{
		$str = '<head>';
		$str .= '<meta name="viewport" content="width=device-width, initial-scale=1"/>';
		$str .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$str .= '<script src="'.$this->script.'.js" type="text/javascript"></script>';
		$str .= '<link rel=stylesheet type="text/css" href="'.$this->stylesheet.'.css">';
		$str .= '<title>'.$this->title.'</title>';
		$str .= '</head>';

		return $str;
	}
}

class Body
{
	private $content;

	public function __construct($content="")
	{
		$this->content = $content;
	}

	public function add($item)
	{
		$this->content .= $item;
	}

	public function __toString()
	{
		return '<body>'.$this->content.'</body>';
	}
}

class Frame
{
	private $content;

	public function __construct($content="")
	{
		$this->content = $content;
	}
	
	public function add($content)
	{
		$this->content .= $content;
	}

	public function __toString()
	{
		return $this->content;
	}
}

?>
