<?php
function showtable($sql_table)
{
  $sql_query = 'SELECT * FROM '.$sql_table;
  $resource = mysql_query($sql_query);

  //query fail ...

  echo '<div class="content">';
  echo '<table id="result">';
  echo '<tr>';
  while($field = mysql_fetch_field($resource))
	echo '<td>'.$field->name.'</td>';
  echo '</tr>';
  while($row = mysql_fetch_row($resource)){
	echo '<tr class="data">';
	foreach($row as $col)
	  echo '<td>'.$col.'</td>';
	echo '</tr>';
  }
  echo '</table>';
  echo '</div>';
}

function showsidebar($tables)
{
  echo '<div class="sidebar">';

  echo '<div class="sideblock">';
  echo '<form name="quickselect" action="" method="POST" class="sidebar">';
  echo '<label class="sidebar">快速選取</label>';
  echo '<select onclick="document.quickselect.submit() "class="sidebar" name="selection">';
  foreach($tables as $name=>$table)
	echo '<option value='.$table.'>'.$name.'</option>';
  echo '</select>';
  echo '</div>';
  echo '</fomr>';

  echo '<form action="" method="POST" class="sidebar">';
  echo '<div class="sideblock">';
  echo '<label class="sidebar">資料表</label>';
  echo '<button name="db_operation" type="submit" class="sidebar" value="new"><ul class="sidebar"><p>新增資料表</p></button>';
  foreach($tables as $name=>$table)
	echo '<button name="selection" type="submit" class="sidebar" value="'.$table.'"><ul class="sidebar"><p>'.$name.'</p></ul></button>';
  echo '</div>';
  echo '</form>';

  echo '</div>';
} 

function showpanel($selection)
{
  echo '<form action="" method="POST" class="panel">';
  echo '<button name="operation" class="panel" value="explore">瀏覽</button>';
  echo '<button name="operation" class="panel" value="export">匯出</button>';
  echo '<button name="operation" class="panel" value="search">查詢</button>';
  echo '<button class="panel">刪除</button>';
  echo '<button class="panel">新增</button>';
  echo '</form>';
}

function showsearch($sql_table)
{
  $sql_query = 'SELECT * FROM '.$sql_table;
  $resource = mysql_query($sql_query);

  //query fail ...

  $num = 0;

  echo '<form action="" method="POST" class="search">';
  echo '<p class="instruction">';
  echo '請選擇搜尋條件';
  echo '</p>';
  if(isset($_POST['condition'])){
	 foreach($_POST['condition'] as $num => $pair){
		if(isset($pair)){
			echo '<input type="hidden" name="condition['.$num.'][col]" value="'.$pair['col'].'">';
		    echo '<input type="hidden" name="condition['.$num.'][key]" value="'.$pair['key'].'">';
		}
	 }
	 $num++;
  }
  echo '<select class="search" name="condition['.$num.'][col]">';
  while($field = mysql_fetch_field($resource))
	echo '<option value="'.$field->name.'">'.$field->name.'</option>';
  echo '</select>';
  echo '<input required="required" type="text" class="search" name="condition['.$num.'][key]" placeholder="關鍵字">';
  echo '<input type="hidden" name="operation" value="search">';
  echo '<button class="search">';
  echo '搜尋';
  echo '</button>';
  echo '</form>';

  //show search result
  if(isset($_POST['condition'])){
	$sql_query = 'SELECT * FROM '.$sql_table.' WHERE ';
	foreach($_POST['condition'] as $key=>$pair){
	  $sql_query .= '`'.$pair['col'].'`="'.$pair['key'].'"';
	  if(((int)$key + 1) == count($_POST['condition']))
		$sql_query .= ';';
	  else
		$sql_query .= ' AND ';
    }	
  }
  $resource = mysql_query($sql_query);
 
  //query fail ...
  echo '<div class="content">'; 
  echo '<table id="result">';
  echo '<tr>';
  while($field = mysql_fetch_field($resource))
	echo '<td>'.$field->name.'</td>';
  echo '</tr>';
  while($row = mysql_fetch_row($resource)){
	echo '<tr class="data">';
	foreach($row as $value)
	  echo '<td>'.$value.'</td>';
	echo '</tr>';
  }
  echo '</div>';

}

function showaddtable()
{
  if(isset($_POST['field'])){
	echo "<pre>".print_r($_POST['field'], true)."</pre>";	
	//echo date("Y-m-d");
	//generateForm($_POST['field'] , $_POST['tablename'] , $_POST['division'] , $_POST['deadline']);
	//genForm();
  }
  else if(isset($_POST['cfield']) && isset($_POST['tablename'])){
	echo form_start('POST');
	echo instruction('請填寫欄位資料');	
	$content = array();
	for($index = 0 ;$index < (int)$_POST['cfield'] ;$index++){
		echo '<input type="hidden" name="field['.$index.'][require]" value="1">';
		echo '<input type="hidden" name="field['.$index.'][only]" value="false">';	
		echo instruction('欄位名');
		echo input('text' , 'field['.$index.'][name]' , 'table_add_field' , 'required="required"');
		echo instruction('說明');
		echo input('text' , 'field['.$index.'][hint]' , 'table_add_field');
		echo instruction('選填');
		echo input('checkbox' , 'field['.$index.'][require]' , 'table_add_field' , 'value="0"');
		echo instruction('不可重複');
		echo input('checkbox' , 'field['.$index.'][only]' , 'table_add_field' , 'value="true"');
		echo instruction('輸入型態');
		echo selection(array("單行文字"=>"text" , "多行文字"=>"textarea" , "單選項目"=>"select" , "多選項目"=>"checkbox"),
	'field['.$index.'][input][0]' , 'table_add_field' , 'onclick="startMultipleInput(this , \'select\' , \'varinput'.$index.'\' , \'field['.$index.'][input]\');"');
		echo '<p id="varinput'.$index.'"></p>';
		echo '<br><hr>';
		
	}
	echo panel(array("確認"=>"submit" , "重設"=>"reset") , 'table_add_field');
	echo input_hidden('division' , $_POST['division']);
	echo input_hidden('tablename' , $_POST['tablename']);
	echo input_hidden('cfield' , $_POST['cfield']);
	echo input_hidden('deadline' , $_POST['deadline']);
	echo input_hidden('db_operation' , $_POST['db_operation']);
	echo form_end();
  }
  else{
	echo form_start('POST');
	echo instruction('請選擇單位');
	echo selection(array('學生安全組'=>'safe' , '職涯發展中心'=>'career' , '衛生保健組'=>'health' , '課外活動組'=>'activity' , '生活事務組'=>'life',
						'學務長室'=>'affair')
						,'division' , 'table_add');
	echo '<br><br>';
	echo instruction('請輸入資料表名稱');
	echo input('text' , 'tablename' , 'table_add' , 'required="required"');
    echo instruction('請輸入欄位數');
	echo input('text' , 'cfield' , 'table_add' , 'required="required"');
	echo '<br><br>';
    echo instruction('請輸入截止日期');
	echo input('text' , 'deadline' , 'table_add' , 'onfocus="HS_setDate(this)" readonly=""');
	echo '<br><br>';
	echo panel(array('確認'=>'submit' , '重設'=>'reset') , 'table_add');
	echo input('hidden' , 'db_operation', 'table_add', 'value="'.$_POST['db_operation'].'"');
	echo form_end();
  }  
}
?>
