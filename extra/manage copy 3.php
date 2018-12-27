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

} else if (isset($_GET["cli"])) {
	$command = $_GET["cli"];
	
	#$filename = './output.txt';
	#echo $filename . ': ' . filesize($filename) . ' bytes'; // idea to check filesize of output text file. To create async execution, and rate limiting.
	
	if($command == "firstScan"){
		# here we check for the current serial ports / connected devices.
		try {
			$foundBoards = get_board_list();
			//array_push($result,$foundBoards);
			$json_data = json_encode($foundBoards);
			file_put_contents("./boards.txt", $json_data);

			# empty the debug output while we're at it.
			fclose(fopen('uploadoutput.txt','w'));
			array_push($result,"OK");
		}catch (Exception $e) {
			array_push($result,"Error doing before-scan!");
    		#echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
		
	}
	
	if( $command == "rescan" ){
		// Recovering
		$the_data = file_get_contents("boards.txt");
		$the_previously_connected_boards = json_decode($the_data);
		$the_now_connected_boards = get_board_list();
		#array_push($result,$the_previously_connected_boards);
		#array_push($result,$the_now_connected_boards);
		$new_boards = array_diff($the_now_connected_boards, $the_previously_connected_boards);
		#array_push($result,$new_boards);
		foreach($new_boards as $board) { # user might have plugged in two boards. Crazy!
			#if( in_array($board, $the_now_connected_boards) ){ // Only get the actually newly connected boards. Edge case is that the user disconnected a board instead.
			#$explodedBoard = explode('\t',$board);
			$explodedBoard = preg_split("/[\t]/", $board);
			$boardDetails = array('FQBN'=>trim($explodedBoard[0]), 'port' => $explodedBoard[1], 'productID' => $explodedBoard[2], 'name' => trim($explodedBoard[3]));
			array_push($result,$boardDetails);
			#}
		}
	}
	
	# uploading
	if( $command == "upload" && isset($_GET["name"]) && isset($_GET["port"])){
		
		$port = $_GET["port"]; // still have to clean this.
		$fileName = cleanShortString($_GET["name"]);
		$path = './code/' . $fileName; // . '/' . $fileName . '.ino';
		# 1. compile
		
		shell_exec('./arduino-cli compile --fqbn arduino:avr:nano ' . $path . ' -v > "./uploadoutput.txt"; ./arduino-cli upload -p ' . $port . ' --fqbn arduino:avr:nano:cpu=atmega328old ' . $path . ' -t -v > "./uploadoutput.txt"');
		#./arduino-cli upload -p /dev/ttyUSB0 --fqbn arduino:avr:nano:cpu=atmega328old $HOME/Arduino/MyFirstSketch -t -v
		
		array_push($result,"OK");
		#./arduino-cli compile --fqbn arduino:avr:nano $HOME/Arduino/MyFirstSketch
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
# REMOVE CODE
} else if (isset($_GET["toDelete"])) {
    $to_delete = cleanShortString($_GET["toDelete"]);
    unlink($dir.$to_delete."/".$to_delete.".ino"); 
	rmdir($dir.$to_delete);
	array_push($result,"Succesfully removed.");

# SAVE CODE
} else if (isset($_GET["save"])) {
    $save = cleanShortString($_GET["save"]);
    $content = $_GET["content"];
	try {
    	file_put_contents($dir.$save."/".$save.".ino", $content);
		array_push($result,"Sucessfully saved.");
	}catch (Exception $e) {
		array_push($result,"Error saving!");
    	#echo 'Caught exception: ',  $e->getMessage(), "\n";
	}

# GET LIST OF SOURCE CODE TEMPLATES
} else if (isset($_GET["list"])) {
    $target = cleanShortString($_GET["list"]);
	if($target == "code" || $target == "source"){ // only allow thes two directories to be scanned
		foreach (new DirectoryIterator($target) as $file) {
			if ($file->isDot()) continue;
			if ($file->isDir()) {
				array_push($result,$file->getFilename());
			}
		}
		sort($result);
	}

# SCAN SOURCE CODE FOR VARIABLES
} else if (isset($_GET["variables"]) || isset($_REQUEST['returnValues'])  ){
	$fullUrl = "";
	$codeUrl = "";
	$generate = false;
	$code = "";
	
	# PATH A -  JUST SCAN SOURCE CODE FOR SETTINGS LINES
	if (isset($_GET["variables"])){
		$fileName = cleanShortString($_GET["variables"]);
		$fullUrl = "source/"  . $fileName . "/" . $fileName . ".ino";
	}
	
	# PATH B - TO GENERATE NEW CODE, WHILE UPDATING PREFERENCES IN THE SETTINGS LINES.
	//if(isset($_REQUEST['sourceName']) && isset($_REQUEST['codeName'])  && isset($_REQUEST['returnValues'])){
	if( isset($_REQUEST['returnValues']) ){ // isset($_REQUEST['sourceName']) && isset($_REQUEST['codeName'])  &&
		$sourceName = $_POST['sourceName'];
		$codeName = $_POST['codeName'];
		$sourceName = cleanShortString($sourceName);
		$codeName = cleanShortString($codeName);
		$returnValues = [];
		$returnValues = $_POST['returnValues'];
		$fullUrl = "source/" . $sourceName . "/" . $sourceName . ".ino";
		$path = './code/' . $codeName . '/' . $codeName . '.ino';
		#$codeUrl = "code/" . $codeName . "/" . $codeName . ".ino";
		$generate = true;
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
					$updatedLine = str_replace($variable, cleanShortString($returnValues[$index]), $line);
					$code = $code . '\n'. $updatedLine . '\n';
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
					$matcho = strval($matches[2]);
					$updatedLine = str_replace($matcho, cleanShortString($returnValues[$index]), $line);
					$code = $code . '\n'. $updatedLine . '\n';
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
	
function cleanShortString($name)
{
	$name = trim($name);
	if (strlen($name) > 32){
		$name = substr($name, 0, 32);
	}
	$name = preg_replace("/[^A-Za-z0-9_ ]/",'',$name);
	$name = str_replace(' ', '_', $name);
	return $name;
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



function get_board_list()
{
	$output = shell_exec('./arduino-cli board list'); # > "./output.txt"

	$boards = array();
	$lines = preg_split('/\r\n|\n|\r/', $output);
	foreach($lines as $line) {
		if(substr( $line, 0, 8 ) === "Discover"){
		} else if(substr( $line, 0, 4 ) === "FQBN"){
		} else if( strlen(trim($line)) == 0){ # if line just contains spaces, ignore it too.
		} else if(empty($line)){
		}else{
			array_push($boards,$line); // This is the data we're after.
		}
	}
	return $boards;
}



?>

