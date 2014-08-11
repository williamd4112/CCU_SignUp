<?php

	function dirwalk($path , $ext){
	  $fileslist = array();

	  $dir = opendir($path);
	  if(!$dir){
		echo 'failed tp read directory : '.$path;
		return -1;
	  }

	  while($file = readdir($dir)){
			if(strcmp($file , "..") == 0 || strcmp($file , ".") == 0)
				  continue;
			if(is_dir($path.'/'.$file)){
				$fileslist = array_merge($fileslist , dirwalk($path.'/'.$file , $ext));
			}
			else if(strcmp(pathinfo($file , PATHINFO_EXTENSION) , $ext) == 0){
				array_push($fileslist , $path.'/'.$file);
			}
	  }

	  return $fileslist;
	   
	}

	//get available files
	$fileslist = dirwalk('./form' , "html");
	
?>
