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
#}else if( isset($_REQUEST['returnValues']) ){
#	array_push($result,"BOOM generate some code");
	

# SCAN SOURCE CODE FOR VARIABLES
} else if (isset($_GET["variables"]) || isset($_REQUEST['returnValues'])  ){
	$fullUrl = "";
	$codeUrl = "";
	$generate = false;
	$code = "";
	
	if( isset($_REQUEST['returnValues']) ){
	//if(!isset($_GET["variables"])){
	//	var_dump($_POST);
	}
	
	#array_push($result,$_REQUEST['returnValues']);
	
	# OPTION TO SCAN
	if (isset($_GET["variables"])){
		$fileName = check_input($_GET["variables"]);
		$fullUrl = "source/"  . $fileName . "/" . $fileName . ".ino";
	}
	
	# OPTION TO GENERATE NEW CODE
	//if(isset($_REQUEST['sourceName']) && isset($_REQUEST['codeName'])  && isset($_REQUEST['returnValues'])){
	if( isset($_REQUEST['returnValues']) ){ // isset($_REQUEST['sourceName']) && isset($_REQUEST['codeName'])  &&
		$sourceName = $_POST['sourceName'];
		#$codeName = trim($_POST['codeName']);
		//
		//$codeName = preg_replace("~^\w+( \w+)*$~","",$_POST['codeName']);
		$codeName = preg_replace("/[^A-Za-z0-9_ ]/",'',$_POST['codeName']);
		$codeName = str_replace(' ', '_', $codeName);
		$returnValues = [];
		$returnValues = $_POST['returnValues'];
		#print_r($returnValues);
		$path = './code/' . $codeName . '/' . $codeName . '.ino';
		$fullUrl = "source/" . $sourceName . "/" . $sourceName . ".ino";
		$codeUrl = "code/" . $codeName . "/" . $codeName . ".ino";
		#array_push($result,"Let's generate some code");
		$generate = true;
		# er it iets mis in: $returnValues[$index]
		
	}


	try {
		$contents = file_get_contents($fullUrl);
		$parsed = get_string_between($contents, '* SETTINGS */', '/* END OF SETTINGS');
		
		$arr = explode("* SETTINGS */", $contents, 2);
		$code = $arr[0];
		
		$arr = explode("/* END OF SETTINGS", $contents, 2);
		$last = $arr[1];
		
		$lines = preg_split('/\r\n|\n|\r/', trim($parsed));
		
		$index = 0;
		foreach($lines as $line) {
			
			if (strpos($line, 'MY_ENCRYPTION_SIMPLE_PASSWD') !== false) {
				continue;
			}
			
			$parts = explode('//', $line);
			$comment = check_input(array_pop($parts));

			// DETECT NORMAL STRING
			if (preg_match("~(\/\/)?\s?\w\w\s?\=\s?(.*)\;(.*)~", $line)) { 
				$variable = 42;
				$variable = trim(get_string_between($line, '=', ';'));
				$variable = str_replace('"', '', $variable);
				if($generate){
					//$replace = array();
					$updatedLine = str_replace($variable, $returnValues[$index], $line);
					$code = $code . '\n'. $updatedLine . '\n';
					#array_push($result,$updatedLine);
					$index++;
				}else{
					array_push($result, ["input" => $variable, "comment" => $comment]);
				}
				
			// NOW WE GET COMPLEX DEFINE WITH A VALUE
			} else if (preg_match("~^(\/\/)?\s?\B#define\b\s\w+\s\"?(\w+)\"?~", $line)) {
				//array_push($result,$line);
				$matches = ["","",""];
				preg_match("~^(\/\/)?\s?\B#define\b\s\w+\s\"?(\w+)\"?~", $line, $matches);
				if($generate){
					#echo "__" . $index;
					#echo $returnValues[1];
					#echo $returnValues[$index];
					$matcho = strval($matches[2]);
					#echo "matcho=" . $matcho;
					#$matcha = $returnValues[$index];
					$updatedLine = str_replace($matcho, $returnValues[$index], $line);
					$code = $code . '\n'. $updatedLine . '\n';
					#array_push($result,$updatedLine);
					$index++;
				}else{
					array_push($result,["input" => $matches[2], "comment" => $comment]);
				}
				
			// NOW WE GET ANY OTHER SIMPLE DEFINE, WHICH CAN BE TOGGLED.
			} else if (preg_match("~^(\/\/)?\s?\B#define\b~", $line)) {
				$cleanLine = "";
				if(substr( $line, 0, 2 ) === "//"){
					$toggleStatus = 0;
					$cleanLine = substr($line, 2);
				} else if(substr( $line, 0, 1 ) === "#"){
					$toggleStatus = 1;
					$cleanLine = $line;
				}
				if($generate){
					if($returnValues[$index] == 0){
						$cleanLine = "//" . $cleanLine;
					}
					$code = $code . '\n'. $cleanLine . '\n';
					#array_push($result,$cleanLine);
					$index++;
				}else{
					array_push($result,["checkbox" => $toggleStatus, "comment" => $comment]);
				}
			}
			
		}
		
		if($generate){
			$code = $code . '\n' . $last; // finally complete the code.
			
			if (!file_exists('code/' . $codeName)) {
				mkdir('code/' . $codeName, 0755);
				$myfile = fopen($path, "wb");
				fwrite($myfile, $code);
				fclose($myfile);
				array_push($result,"OK");
			} else {
				array_push($result,"Error - already exists");
			}
			

		}
		
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

