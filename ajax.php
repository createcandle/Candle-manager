<?php

# Initial directory variables 
$codeDir = 'code/';
$sourceDir = "source/";


$result = []; # This will be filled with messages and turned into JSON at the end of the file.

# Cleanup the incoming variables.
$_GET   = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);


# ARDUINO COMMAND LINE FUNCTIONS (such as uploading to the board)
if (isset($_GET["cli"])) {
	$command = $_GET["cli"];
	
	if($command == "forgetOldBoards"){
		# Here we check for the current serial ports / connected devices, and save them to a file. We can then later compare to this snapshot.
		try {
			file_put_contents("boards.txt", "[]");
			array_push($result,"OK");
		}catch (Exception $e) {
			array_push($result,"Error removing old device list!");
    		#echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}

	if($command == "firstScan"){
		# Here we check for the current serial ports / connected devices, and save them to a file. We can then later compare to this snapshot.
		try {
			$foundBoards = get_board_list();
			$json_data = json_encode($foundBoards);
			file_put_contents("boards.txt", $json_data);
			array_push($result,"OK");
		}catch (Exception $e) {
			array_push($result,"Error doing before-scan!");
    		#echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}
	
	if( $command == "rescan" ){
		file_put_contents("uploadoutput.txt", '');			# empty the debug output while we're at it.
		$the_data = file_get_contents("boards.txt");
		$the_previously_connected_boards = json_decode($the_data);
		$the_now_connected_boards = get_board_list();
		$new_boards = array_diff($the_now_connected_boards, $the_previously_connected_boards);
		foreach($new_boards as $board) { # user might have plugged in two boards.
			$explodedBoard = preg_split("/[\t]/", $board);
			$boardDetails = array('FQBN'=>trim($explodedBoard[0]), 'port' => $explodedBoard[1], 'productID' => $explodedBoard[2], 'name' => trim($explodedBoard[3]));
			array_push($result,$boardDetails);
		}
	}

	if( $command == "scanForPatients" ){
		$new_boards = get_board_list();
		foreach($new_boards as $board) { # user might have plugged in two boards. Crazy!
			$explodedBoard = preg_split("/[\t]/", $board);
			$boardDetails = array('FQBN'=>trim($explodedBoard[0]), 'port' => $explodedBoard[1], 'productID' => $explodedBoard[2], 'name' => trim($explodedBoard[3]));
			array_push($result,$boardDetails);
		}
	}
	   
	if( $command == "listenToPatient" && isset($_GET["port"]) ){
		$port = $_GET["port"];
		
		file_put_contents('./listenoutput.txt', "__wait__\n");
		$shellCommand = 'screen -X quit; screen -d -m -L listenoutput.txt ' . $port . ' 115200'; # had nohup before it.
		shell_exec($shellCommand);
	}
	
	if( $command == "stopListeningToPatient"){
		$shellCommand = 'screen -X quit'; 
		shell_exec($shellCommand);
		file_put_contents('./listenoutput.txt', "__wait__\n");
	}
	
	# Uploading code to an Arduino board
	if( $command == "upload" && isset($_GET["name"]) && isset($_GET["port"])){
		file_put_contents("boards.txt", "[]");
		$port = $_GET["port"]; // still have to clean this.
		$fileName = cleanShortString($_GET["name"]);
		$path = './code/' . $fileName;
		$shellCommand = './arduino-cli compile -v --fqbn arduino:avr:nano ' . $path . ' > uploadoutput.txt';
		$shellCommand .= '; ./arduino-cli upload -p ' . $port . ' --fqbn arduino:avr:nano:cpu=atmega328old ' . $path . ' -t -v 2>&1  | tee -a uploadoutput.txt 2>/dev/null >/dev/null &';
		array_push($result,$shellCommand);
		shell_exec($shellCommand);
		array_push($result,"OK");
	}
	
	
# REMOVE CODE
} else if (isset($_GET["remove"])) {
    $to_delete = cleanShortString($_GET["remove"]);

    try {
		//Get a list of all of the file names in the folder.
		$files = glob($codeDir.$to_delete . '/*');
 
		//Loop through the file list.
		foreach($files as $file){
			//Make sure that this is a file and not a directory.
			if(is_file($file)){
				//Use the unlink function to delete the file.
				unlink($file);
			}
		}
		rmdir($codeDir.$to_delete);
		array_push($result,"OK");
	}catch (Exception $e) {
		array_push($result,"Error - removing failed!");
    	#echo 'Caught exception: ',  $e->getMessage(), "\n";
	}

# SAVE EDITED CODE
}else if(isset($_REQUEST['name']) && isset($_REQUEST['code'])){
	$fileName = cleanShortString($_POST['name']);
	$code = urldecode($_POST['code']);
	$codePath = 'code/' . $fileName . '/' . $fileName . '.ino';
	try {
		file_put_contents($codePath, $code);
		array_push($result,"OK");
	}catch (Exception $e) {
		array_push($result,"Error - saving failed!");
    	#echo 'Caught exception: ',  $e->getMessage(), "\n";
	}


# SAVE PASSWORD
}else if( isset($_REQUEST['newPassword']) ){
	$newPassword = urldecode($_POST['newPassword']);
	
	if ( strlen($newPassword) > 0 && strlen($newPassword) < 26 ){
		try{
			file_put_contents('./simpleSecurityPassword.txt', $newPassword);
			array_push($result,"OK");
		}catch (Exception $e) {
			array_push($result,"Error - saving failed!");
			#echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}else if (strlen($newPassword == 0)){
		file_put_contents('./simpleSecurityPassword.txt', "");
		array_push($result,"Network security has been disabled. Use at your own risk!");
	}else if (strlen($newPassword > 25)){
		array_push($result,"Your password was too long. It would use too much memory.");
	}else{
		array_push($result,"Unknown password saving error.");
	}


# AUTO-GET LIBRARIES?
}else if( isset($_GET['getLibraries']) ){
	$getLibraries = $_GET['getLibraries'];
	
	if( $getLibraries == "yes" ){
		file_put_contents('./autoInstallLibraries.txt', "1");
		array_push($result,"OK");
	} else if( $getLibraries == "no" ){
		file_put_contents('./autoInstallLibraries.txt', "0");
		array_push($result,"OK");
	} else if($getLibraries == "unknown"){
		$answer = file_get_contents('./autoInstallLibraries.txt');
		array_push($result,$answer);
	} else if($getLibraries == "check" && isset($_GET['codeName']) ){
		
		$allowedToGet = file_get_contents("autoInstallLibraries.txt");
		if($allowedToGet == "1" ){
			$codeName = cleanShortString($_GET['codeName']);
			$codePath = './code/' . $codeName . '/' . $codeName . '.ino';
			$code = file_get_contents($codePath);
			
			$includeLines = array();
			preg_match_all("/^#include(.*)$/m",$code,$includeLines);

			$neededLibs = array(); # The libraries we should have installed for this code to function.
			foreach($includeLines[0] as $line) {
				
				// Get the best library name. If there is a commented name to seach for, we prefer that.
				preg_match("~^#include\s(\"|\<)(\w+)[.h]+(\"|\<)\s*(\/?\/?\s?\"([\w+\s-]*)\")?~", $line, $matches);
				$searchForMe = "";
				if(isset($matches[5])){
					$searchForMe = $matches[5];
				}else if( isset($matches[2]) ){
					$searchForMe = $matches[2];
				}
				
				// Check if the library should be modified or excluded.
				if(strtolower(substr($searchForMe, 0, 3) == "avr")) {
					$searchForMe = "";
				}
				
				// Add the name to the list of libraries to check if they are already installed.
				if( strlen($searchForMe) > 0 ){
					array_push($neededLibs,$searchForMe);
				}
			}

			// Get the list of installed libraries, so we can check against that.
			$installedLibs = array();
			$libListOutput = shell_exec('./arduino-cli lib list --all');
			$libListOutputLines = explode("\n", $libListOutput);
			foreach($libListOutputLines as $line){
				$tabsArray = explode("\t", $line, 2);
				$libName = trim($tabsArray[0]);
				if($libName != "" && $libName != "Name"){
					array_push($installedLibs,$libName);
				}
			}
			#array_push($result,$neededLibs);
			#array_push($result,$installedLibs);
			

			$libsToDownload = array_diff($neededLibs, $installedLibs); // Compare the list or required and installed libraries.

			#array_push($result,$libsToDownload);
			#array_push($result,$libsToDownload);
			

			foreach($libsToDownload as $lib){
				$done = shell_exec('./arduino-cli lib install "' .  $lib . '"');
				if ( strpos($done, "\nInstalled ") !== false ){
					//echo 'true';
					array_push($result,"Installed new library: " . $lib);
				}else if( strpos($done, "already downloaded\n") !== false ){
					//echo 'true';
				}
				#array_push($result,$done);
			}
			
		}
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
	if( isset($_REQUEST['returnValues']) ){ // && isset($_REQUEST['sourceName']) && isset($_REQUEST['codeName'])
		$sourceName = cleanShortString($_POST['sourceName']);
		$codeName = cleanShortString($_POST['codeName']);
		$returnValues = [];
		$returnValues = $_POST['returnValues'];
		$fullUrl = "source/" . $sourceName . "/" . $sourceName . ".ino";
		$path = './code/' . $codeName . '/' . $codeName . '.ino';
		$generate = true;
	}

	try {
		$contents = file_get_contents($fullUrl);
		$settingsCode = get_string_between($contents, '* SETTINGS */', '/* END OF SETTINGS');
		
		$arr = explode("* SETTINGS */", $contents, 2);
		$code = $arr[0] . '* SETTINGS */' . PHP_EOL;
		
		$last = "";
		
		$lines = preg_split('/\r\n|\n|\r/', trim($settingsCode));
		
		$index = 0;
		foreach($lines as $line) {
			
			if (strpos($line, 'MY_SECURITY_SIMPLE_PASSWD') !== false) { // skip over the encryption part of you find it.
				if($generate){
					# Check if a security password should be added to the code
					$password = file_get_contents('simpleSecurityPassword.txt');
					if( strlen($password) > 0 ){	
						$passwordLine = '#define MY_ENCRYPTION_SIMPLE_PASSWD "' . $password . '"    // This is the password used to encrypt and sign the wireless communication.';
						$last .= $passwordLine . PHP_EOL . PHP_EOL;
					}
				}
				continue;
			}
			
			#$parts = explode('//', $line);
			#$comment = array_pop($parts); // perhaps add security here, as it gets added to the interface.

			// DETECT NORMAL STRING
			// for example: char phone1[14] = "+31123456789";  // Phone number of user #1
			// \w+\s\w+\[?[0-9]*?\]?\s?\=\s?\"?(.*)\"?\;\s*\/?\/?\s?(.*)
			#if (preg_match("~(\/\/)?\s?\w\w\[[0-9]\]?\s?\=\s?(.*)\;(.*)~", $line)) { 
			if (preg_match("~\w+\s\w+\[?[0-9]*?\]?\s?\=\s?\"?([\w\+\!\@\#\$\%\ˆ\&\*\(\)\.\,\-]*)\"?\;\s*\/?\/?\s?(.*)~", $line)) {
				$variable = "";
				$comment = "";
				preg_match("~\w+\s\w+\[?[0-9]*?\]?\s?\=\s?\"?([\w\+\!\@\#\$\%\ˆ\&\*\(\)\.\,\-]*)\"?\;\s*\/?\/?\s?(.*)~", $line, $matches);
				$variable = $matches[1];
				if(isset($matches[2])){
					$comment = $matches[2];
				}
				#$variable = trim(get_string_between($line, '=', ';'));
				#$variable = str_replace('"', '', $variable);
				if($generate){
					$updatedLine = str_replace($variable, cleanShortString($returnValues[$index]), $line);
					$code = $code . PHP_EOL . $updatedLine . PHP_EOL;
					$index++;
				}else{
					array_push($result, ["input" => $variable, "comment" => $comment]);
				}
				
			// NOW WE GET COMPLEX DEFINE WITH A VALUE
			} else if (preg_match("~^\B#define\b\s\w+\s\"?(\w+)\"?\s*\/?\/?\s?(.*)~", $line)) {
				preg_match("~^^\B#define\b\s\w+\s\"?(\w+)\"?\s*\/?\/?\s?(.*)~", $line, $matches);
				if($generate){
					$variable = strval($matches[1]);
					$updatedLine = str_replace($variable, cleanShortString($returnValues[$index]), $line);
					$code = $code . PHP_EOL . $updatedLine . PHP_EOL;
					$index++;
				}else{
					$comment = "";
					if(isset($matches[2])){
						$comment = $matches[2];
					}
					array_push($result,["input" => $matches[1], "comment" => $comment]);
				}
				
			// NOW WE GET ANY OTHER SIMPLE DEFINE, WHICH CAN BE TOGGLED.
			} else if (preg_match("~^(\/\/)?\s?\B#define\b\s\w+\s*\/?\/?\s?(.*)~", $line)) {
				preg_match("~^(\/\/)?\s?\B#define\b\s\w+\s*\/?\/?\s?(.*)~", $line, $matches);
				$comment = "";
				
				if($matches[1] === "//"){
					$toggleStatus = 0;
					$line = substr($line, 2);
					if(isset($matches[2])){
						$comment = $matches[2];
					}
				}else {
					$toggleStatus = 1;
					if(isset($matches[2])){
						$comment = $matches[2];
					}
				}
				
				if($generate){
					if($returnValues[$index] == 0){
						$line = "//" . $line;
					}
					$code = $code . PHP_EOL . $line . PHP_EOL;
					$index++;
				}else{
					array_push($result,["checkbox" => $toggleStatus, "comment" => $comment]);
				}
			}
			
		}
		
		if($generate){
			
			# add the rest of the code
			$arr = explode("/* END OF SETTINGS", $contents, 2);
			$last .= '/* END OF SETTINGS' . $arr[1];
			
			$code = $code . PHP_EOL . $last; // finally complete the code.
			
			if (!file_exists('code/' . $codeName)) {
				mkdir('code/' . $codeName, 0755);
				$myfile = fopen($path, "wb");
				fwrite($myfile, $code);
				fclose($myfile);
				array_push($result,"OK");
			} else {
				array_push($result,"That name already exists");
			}
			

		}
		
	}catch (Exception $e) {
		array_push($result,"Error saving!");
    	#echo 'Caught exception: ',  $e->getMessage(), "\n";
	}


# SAVE CODE FROM EDITOR
} else if(isset($_REQUEST['name']) && isset($_REQUEST['code'])){
	$fileName = cleanShortString($_POST['name']);
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
    $data = strip_tags($data);
    return $data;
}
	
function cleanShortString($name)
{
	$name = urldecode(trim($name));
	if (strlen($name) > 45){
		$name = substr($name, 0, 45);
	}
	$name = preg_replace("/[^A-Za-z0-9_\- ]/",'',$name);
	$name = str_replace(' ', '_', $name);
	return $name;
}


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


# output JSON code
header('Content-type: application/json');
echo(json_encode($result));


?>

