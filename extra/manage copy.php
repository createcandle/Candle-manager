<?php

header('Content-type: application/json');

$goback = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Code manager</title></head><body><h1>Done!</h1><a href="index.html">GO BACK</a></body></html>';


$result = [];

$_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

// Initial directory variables 
$dir = 'code/';

// Check and validate arguments

$new_file = (isset($_REQUEST['new'])) ? filter_var($_REQUEST['new'], FILTER_SANITIZE_STRING) : "";

if ($new_file) {
    // Validing input file name
    if ($new_file === "") {
		array_push($result,"Invalid name!");
    } else if (ctype_alnum(str_replace(' ','',$new_file))) {
        // Valid file name
		if(!is_dir($dir.$new_file)){
			mkdir( $dir.$new_file , 0755, true);
		}
        $fileHandle = fopen($dir.$new_file."/".$new_file.".ino", "w");
		fclose($fileHandle);
		array_push($result,"Created succesfully!");
    } else {
		array_push($result,"Please enter letters or digits or space only!");
    }
    
} else if (isset($_GET["toDelete"])) {
    $to_delete = check_input($_GET["toDelete"]);
    unlink($dir.$to_delete."/".$to_delete.".ino"); 
	//unlink($dir.$to_delete);
	rmdir($dir.$to_delete);
	array_push($result,"Succesfully removed.");
	
} else if (isset($_GET["save"])) {
    $save = check_input($_GET["save"]);
    $content = $_GET["content"];
	try {
    	file_put_contents($dir.$save."/".$save.".ino", $content);
		array_push($result,"Sucessfully saved.");
	}catch (Exception $e) {
		array_push($result,"Error saving!");
    	#echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	

} else if (isset($_GET["list"])) {
    $target = check_input($_GET["list"]);
	if($target == "code" || $target == "source"){
		foreach (new DirectoryIterator($target) as $file) {
			if ($file->isDot()) continue;
			if ($file->isDir()) {
				array_push($result,$file->getFilename());
			}
		}
		sort($result);
	}
	
# SCAN SOURCE CODE FOR VARIABLES
} else if (isset($_GET["variables"]) ||  isset($_REQUEST['returnValues'])  ){

	$fullUrl = "";
	$codeUrl = "";
	$generate = false;
	
	if (isset($_GET["variables"])){
		$fileName = check_input($_GET["variables"]);
		$fullUrl = "source/"  . $fileName . "/" . $fileName . ".ino";
	}


	# GENERATE NEW CODE
	if(isset($_REQUEST['sourceName']) && isset($_REQUEST['codeName'])  && isset($_REQUEST['returnValues'])){
		$sourceName = $_POST['sourceName'];
		$codeName = $_POST['codeName'];
		$returnValues = $_POST['returnValues'];
		$path = 'code/' . $codeName . '/' . $codeName . '.ino';
		$fullUrl = "source/" . $sourceName . "/" . $sourceName . ".ino";
		$codeUrl = "code/" . $codeName . "/" . $codeName . ".ino";
		array_push($result,"Let's generate some code");
		$generate = true;
	}



	try {
		$contents = file_get_contents($fullUrl);
		$parsed = get_string_between($contents, '* SETTINGS */', '/* END OF SETTINGS');
		$lines = preg_split('/\r\n|\n|\r/', trim($parsed));
		foreach($lines as $line) {
			$parts = explode('//', $line);
			$comment = check_input(array_pop($parts));
			//if (preg_match("~(\/\/)?\s?[a-zA-Z_]\s[a-zA-Z0-9_]\s?\=\s?(.*)\;(.*)~", $line)) {
			if (preg_match("~(\/\/)?\s?\w\w\s?\=\s?(.*)\;(.*)~", $line)) { // detect normal string
				$variable = 42;
				
				$variable = trim(get_string_between($line, '=', ';'));
				//array_push($result,"YES!");
				
				//$matches = array();
				
				//preg_match('~\w\s\w\s?\=\s\"?(\w)\"?', $line, $matches);
				
				// made for define: preg_match('~[A-Z_]+\s(\"?[]+\"?)id=([0-9]+)\?~', $line, $matches);
				
				//array_push($result, $matches[1]);
				
				//$variable = preg_match("~/=(.+)/;~", $line);
				array_push($result, ["input" => $variable, "comment" => $comment]);
			
			//} else if (preg_match("~^(\/\/)?\s?\B#define\b[A-Z0-9_]\s\"?[a-zA-Z0-9_]\"?(.*)~", $line)) { // check if it's a define that actually defines something.
			} else if (preg_match("~^\B#define\b[A-Z0-9_]\s\"?[a-zA-Z0-9_]\"?(.*)~", $line)) { // check if it's a define that actually defines something.

				//array_push($result,"normal var define");
				
				if (preg_match("~^(\/\/)?\s?\B#define\b[A-Z0-9_]\s\"?[a-zA-Z0-9_]\"?(.*)~", $line)){
				}
				
				if( substr( $line, 0, 1  ) === "#" ){ // starts with a # meaning it's a variable being defined
				}
				
				
				// NOW WE GET COMPLEX DEFINE WITH A VALUE
				
			} else if (preg_match("~^(\/\/)?\s?\B#define\b\s\w+\s\"?(\w+)\"?~", $line)) {
				//array_push($result,"define with value");
				//$toggleStatus = !substr( $line, 0, 2 ) === "//";
				
				$matches = [];
				preg_match("~^(\/\/)?\s?\B#define\b\s\w+\s\"?(\w+)\"?~", $line, $matches);

				//print_r($matches);
				
				//if (preg_match("~^(\/\/)?\s?\B#define\b[\s\t]+~", $line)){
				//}
				
				array_push($result,["input" => $matches[2], "comment" => $comment]);
				
				
				
				
				// NOW WE GET ANY OTHER SIMPLE DEFINE, WHICH CAN BE TOGGLED.
				
			} else if (preg_match("~^(\/\/)?\s?\B#define\b~", $line)) {
				//array_push($result,"minimal define");
				//$toggleStatus = !substr( $line, 0, 2 ) === "//";
				
				if(substr( $line, 0, 2 ) === "//"){
					$toggleStatus = 0;
				} else if(substr( $line, 0, 1 ) === "#"){
					$toggleStatus = 1;
				}
				
				array_push($result,["checkbox" => $toggleStatus, "comment" => $comment]);
				
			}
			
			
			
			else if (strpos($line, '#define') !== false) { // starts with a //# meaning it's probably a toggle.
				if(substr( $line, 0, 2 ) === "//"){
					$currentValue = 0;
				} else if(substr( $line, 0, 1 ) === "#"){
					$currentValue = 1;
				}
				
				array_push($result,["checkbox" => $toggleStatus, "comment" => $comment]);
				//echo $line . "<br/>";
				//echo "<br/>";
			}else {
				#echo "- A match was not found<br/>";
			}
		}
		
		array_push($result,"OK");
		
		//array_push($result,"Variables received");
	}catch (Exception $e) {
		array_push($result,"Error saving!");
    	#echo 'Caught exception: ',  $e->getMessage(), "\n";
	}


# SAVE CODE FROM EDITOR
} else if(isset($_REQUEST['name']) && isset($_REQUEST['code'])){
	$fileName = check_input($_POST['name']);
	$path = 'code/' . $fileName . '/' . $fileName . '.ino'; // '.' for current
	$code = urldecode($_POST['code']);
	try {
		file_put_contents($path, $code);
		array_push($result,"OK");
	}catch (Exception $e) {
		array_push($result,"Error saving!");
    	#echo 'Caught exception: ',  $e->getMessage(), "\n";
	}





// Simple sanitizer data
function check_input($data) {
    $data = trim($data);
    return $data;
}

/**
 * Used to process perform activities realated to the read page
 */
function readController($dir) {
    $array = array();
    $fileName = (isset($_REQUEST['fileName'])) ? filter_var($_REQUEST['fileName'], FILTER_SANITIZE_STRING) : "";
    array_push($array, $fileName);

    if ($fileName) {
        $file_handle = fopen($dir.$fileName."/".$fileName. ".ino", "r"); 
        $file_string = fread($file_handle, filesize($dir.$fileName."/".$fileName.".ino"));
        fclose($file_handle);
        array_push($array, $file_string);
    }
}

/**
 * Used to process perform activities realated to the edit page
 */
function editController($dir) {
    $array = array();
    $fileName = (isset($_REQUEST['fileName'])) ? filter_var($_REQUEST['fileName'], FILTER_SANITIZE_STRING) : "";
    array_push($array, $fileName);

    if ($fileName) {
        $file_handle = fopen($dir.$fileName."/".$fileName.".ino", "r"); 
		 $file_string = fread($file_handle, filesize($dir.$fileName."/".$fileName.".ino"));
        fclose($file_handle);
        array_push($array, $file_string);
    }
}


//=================Controller Above ========================================== 


echo(json_encode($result));


function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}


?>

