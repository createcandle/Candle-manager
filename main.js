continueCheckingOutput = true;
continuelisteningToPatient = true;

$(document).ready(function(){
	console.log("document ready");
	
	// REGISTERING BUTTONS
	
	$('.menuitem').click(function(){
		//$('.togglerow').hide().eq($(this).index()-1).show();
		$('.togglerow').hide().eq($(this).index()).show();
		$('#editRow, #uploadWizard').hide();
		//$('#source-list').show();
		resetDeviceDoctor();
	});
	
	$('#closeAlerteModal').click(function(){
		$('#alertModal').modal('hide');
	});
	
	// Editing code
	
	$('#saveCode').click(function(){
		codeName = $('#editing-h1').text();
		console.log("Saving: " + codeName);
		saveCode(codeName);
	});
	
	$('#saveAndUpload').click(function(){
		codeName = $('#editing-h1').text();
		console.log("Saving and moving to upload wizard for: "  + codeName);
		saveCode(codeName);
		$("#three, #editRow").hide();
		resetUploadWizard();
		$('#uploadWizard').show();
		$( "#uploadWizard" ).data('name', codeName );
		$( "#uploadheaderName" ).text(codeName.replace(/\_/g, ' '));
	});	
	

	// Settings page
	
	$('#securityPasswordSaveButton').click(function(){
		console.log("Saving new password.");
		var checkstr =  confirm('Are you sure you want to change the master password? You will have to re-upload code with this password to all of your devices.');
		if(checkstr == true){
			savePassword();
		}
	});

	$('#autoGetLibraries').change(function(){
		console.log("Saving Auto-get libraries preference.");
		if ($(this).is(":checked")){ 
			$.get("ajax.php?getLibraries=yes", function(response) {
				console.log(response);
			});
		}else{
			$.get("ajax.php?getLibraries=no", function(response) {
				console.log(response);
			});
		}
	});
	
	
	// Upload wizard
	
	$('#compileErrorEditLink').click(function(){
		console.log("Going to check on the code after a failed upload.");
		$('#uploadWizard').hide();
		var codeName = $( "#uploadWizard" ).data('name');
		console.log("Will try to jump to code editor for code: " + codeName);
		editCode(codeName);
	});
	
	$('#firstScanButton').click(function(){
		console.log("Doing a first scan of connected Arduinos");
		firstScan();
		$('#firstScanButton').hide();
		$('#firstScanning').show();
	});
	
	$('#rescanButton').click(function(){
		console.log("Doing a new scan of connected Arduino's");
		rescan();
		$('#rescanButton').hide();
		$('#rescanning, #backScanButton').show();
	});

	$('#backScanButton').click(function(){
		console.log("Wizard is going back to beginning.");
		//$('#rescanButton').hide();
		//$('#rescanning').show();
		//$('#firstScanButton').show();
		resetUploadWizard();
	});

	$('#scanForAllDevicesButton').click(function(){
		console.log("User wants to see all USB devices.");
		$('#disconnect, #rescanButton, #backScanButton').hide();
		$('#plugitin, #rescanning').show();
		$.get("ajax.php?cli=forgetOldBoards", function(response) {
			console.log(response);
			if(response[0] = "OK"){
				rescan();
			}
		});
	});
	
	$('#showUploadDebug').click(function(){
		console.log("User wants to see upload debug details.");
		$(this).hide();
		$('#uploadDebugOutput').show();
	});
	

	$('#info-listenToNewDevice').click(function(){
		console.log("After uploading user wants to listen to the new device");
		//$('.togglerow').hide();
		$('#uploadWizard, #patientsScanButton').hide();
		$('#deviceDoctor, #scanningForPatients').show();
		scanForPatients();
	});
	
	
	

	// Device doctor
	
	$('#patientsScanButton').click(function(){
		console.log("Scanning for patients.");
		$('#patientsScanButton').hide();
		$('#scanningForPatients').show();
		scanForPatients();
	});
	

	$('#stopListeningToPatientButton').click(function(){
		continuelisteningToPatient = false;
		$('this').hide();
		$('#patientsHolder .panel-footer .fa').removeClass('fa-spinner').addClass('fa-arrow-circle-right');
		
		$.get("ajax.php?cli=stopListeningToPatient", function(response) {
			console.log(response);
			if(response[0] = "OK"){
				$('#listenDebugOutput').val($('#listenDebugOutput').val() + "\n __stopped listening__ \n");
			}
		});
	});
	
	
	// Found wireless devices
	
	$('#rescanPresentedDevices').click(function(){
		console.log("Rescanning for wirelessly presented devices.");
		tryLoadingWOTdata();
	});
	
	
	

	updateCodeList();
	updateSourceList();
	getInitialSettings();
	tryLoadingWOTdata();
	
	$("#addNewCode").submit(function(event){
		
		// stop form from submitting normally.
		event.preventDefault();
		event.stopPropagation();
		
		// we need to names: of the source file and of the new file name that is provided in the form.
		sourceName = $("#newSettings").data('sourceName');
		console.log("New device settings form submitted. Sending input variables to php to generate code with name: " + sourceName);
		
		codeName = $("#newName").val().replace(/\W\s/g, '');
		
		if(codeName.length > 0){
		
			console.log("New name was long enough. New code name: " + codeName);
			
			//location.href = "#newSettings";
			
			// Get values from inputs.
			returnValues = [];
			$('#nameGroupHolder :input').each(function(index){
				//console.log("ITEM");
				//console.log( $(this).val() );
				returnValue = $(this).val();
				if(returnValue == "checkbox"){
					if($(this).is(":checked")){
						returnValue = 1;
					}else{
						returnValue = 0;
					}
				}
				returnValues.push(returnValue);
			});
			//console.log(returnValues);

			// send the preferences to the PHP file that will generate a new ino file from it.
			encodedCodeName = encodeURIComponent(codeName);
			encodedSourceName = encodeURIComponent(sourceName);
			$.ajax({
				type: "POST",
				url: "ajax.php",
				//data: JSON.stringify(allTheVariables),
				data: {sourceName:encodedSourceName,codeName:encodedCodeName,returnValues:returnValues},
				//data: { code: code, name : fileName },
				dataType: "JSON",
				success: function(json){
					console.log(json);
					if(json[0] == "OK"){
						console.log("Generated new code successfully");
						updateCodeList();
						modalAlert("New code created successfully.")
						
						// We can now show part 2 - the upload wizard.
						console.log("Will try to move to upload wizard for: " + codeName);
						$("#three, #newSettings").hide();
						resetUploadWizard();
						$('#uploadWizard').show();
						$('#uploadWizard').data('name', codeName );
						$('#uploadheaderName').text(codeName.replace(/\_/g, ' '));
						
						// Try to get new libraries (if allowed, the PHP will check for that).
						$.get("ajax.php?getLibraries=check&codeName=" + encodedCodeName, function(json) {
							console.log(json);
							if(typeof json[0] === "undefined"){
								console.log("No new lbiraries were installed.");
							}else{
								modalAlert(json);
							}
							
						});
						
					}else{
						modalAlert(json);
					}
				}
			});
		}else{
			modalAlert("Please make the name longer and/or use only letters from the alphabet.");
		}
		
		return false;
	});	
	
});


	function resetUploadWizard(){		
		$("#disconnect, #firstScanButton, #rescanButton").show();
		$("#plugitin, #currentlyUploading, #firstScanning, #rescanning, #foundDevicesHolder, #info-uploadError, #info-uploadComplete").hide();
		$( "#uploadheaderName" ).text('');
		$( "#uploadDebugOutput" ).val('');
		
	}
	function resetDeviceDoctor(){		
		$("#patientsScanButton").show();
		$("#scanningForPatients, #stopListeningToPatientButton").hide();
		$('#patientsHolder').html('');
		continuelisteningToPatient = false;
		$('#patientsHolder .panel-footer .fa').removeClass('fa-spinner').addClass('fa-arrow-circle-right');
		$.get("ajax.php?cli=stopListeningToPatient", function(response) {
			console.log(response);
			if(response[0] = "OK"){
				$('#listenDebugOutput').val('');
			}
		});
		
	}



	function updateCodeList(){
		// get created code
		console.log("getting generated code list");
		$.ajax({
			//url: "yourphp.php?id="+yourid,
			url: "./ajax.php?list=code",
			dataType: "JSON",
			success: function(json){
				//here inside json variable you've the json returned by your PHP
				html = '<table class="table table-bordered table-striped" id="codelist-table">';
				for(var i=0;i<json.length;i++){
					html += '<tr><td><h4>' + json[i].replace(/\_/g, ' ') + '</h4></td>';
					html += '<td><a class="upload btn" onclick="uploadCode(\'' + json[i] + '\');"><button class="btn btn-info btn-lg">UPLOAD</button></a></td>';
					html += '<td><a class="edit btn" onclick="editCode(\'' + json[i] + '\');"><button class="btn btn-primary">EDIT</button></a></td>';
					html += '<td><a class="remove btn" onclick="removeCode(\'' + json[i] + '\');"><button class="btn btn-danger">REMOVE</button></a></td></tr>';
				}
				html += '</table>';
				$('#codelist').html(html);
			}
		});
	}
	
	function updateSourceList(){
		console.log("Getting source list");
		// get available source files
		$.ajax({
			url: "./ajax.php?list=source",
			dataType: "JSON",
			success: function(json){
				
				for(var i=0;i<json.length;i++){
					console.log(json[i]);
					$( "#the-original" ).clone().attr('id', '').appendTo( "#source-list" );
					$( "#source-list > div:last-child .panel-primary" ).string_to_color(["border-color"], json[i]);
					$( "#source-list > div:last-child .panel-heading" ).string_to_color(["background", "border-color"], json[i]);
					$( "#source-list > div:last-child a" ).data('name', json[i]);
					$( "#source-list > div:last-child .panel-heading h2").text(json[i].replace(/\_/g, ' ') );
					$( "#source-list > div:last-child").show();
				}
				
				// CREATE NEW DEVICE FROM NEW SETTINGS
				
				$( "#source-list a").click(function(){
					console.log("Clicked create new");
					sourceName = $(this).data('name');
					$('#newName').val(sourceName.replace(/\_/g, ' ') );
					
					getVariables(sourceName);
					$("#newSettings").show();
					
					sourcePath = 'source/' + sourceName + '/' + sourceName + '.ino';
					console.log(sourcePath);

					$.get(sourcePath, function(response) {
						console.log("Loading the new about text");
						

						var aboutText = response.slice(0, response.indexOf("* SETTINGS */"));
						//aboutText.replace(/\n/g,"<br/>");
						//aboutText = aboutText.replace(~[\*]~, '<br>');
						aboutText = aboutText.replace(/^\//, '');
						aboutText = aboutText.replace(/\*/g, '<br/>');
						//aboutText = aboutText.replace(/(?:\r\n|\r|\n)/g, '<br/>');
						//console.log(aboutText);
						$('#aboutNewDevice').html(aboutText);
					});
					
					
				});
			}
		});
	}

	function getInitialSettings(){
		
		$.get("ajax.php?getLibraries=unknown", function(response) {
			console.log("Auto-get libraries? " + response);
			$('#autoGetLibraries').attr('checked', response);
		});
		
		$.get("simpleSecurityPassword.txt", function(response) {
			console.log("Getting existing password length");
			placeholder = "";
			for (var i = 0; i < response.length; i++) {
				placeholder += "*";
			}
			$('#securityPassword').attr('placeholder', placeholder);
		});
	
	}



	function tryLoadingWOTdata(){
		var ip = location.host;
		ip = ip.substring(0, ip.indexOf(':'));
		ip = 'http://' + ip + ':8000/nodes';
		
		$('#presentedDevicesHolder').html('');
		
		$.get(ip, function(json) {
			//console.log(json);
			if(json[0] !== "undefined"){
				$('#menu-five').show(); // show the option in the menu
				$(json).each(function(i,val){
					console.log(val.node_id);
					console.log(val.node_name);
					$( "#the-original-arduino" ).clone().attr('id', '').appendTo( "#presentedDevicesHolder " );
					$( "#presentedDevicesHolder > div:last-child .panel-primary" ).string_to_color(["border-color"], val.node_name );
					$( "#presentedDevicesHolder > div:last-child .panel-heading" ).string_to_color(["background", "border-color"], val.node_name );
					$( "#presentedDevicesHolder > div:last-child .panel-heading h3").html( '<i class="fa fa-wifi fa-1" aria-hidden="true"></i> ' + val.node_name ); //.replace(/\_/g, ' ') 
					$( "#presentedDevicesHolder > div:last-child .panel-footer").hide();
					$( "#presentedDevicesHolder > div:last-child").show();
				});
			}
		});
	}




	
	
	function getVariables(sourceName){
		console.log("Getting variables for" + sourceName);
		
		$('#newDeviceSourceName').text(sourceName.replace(/\_/g, ' ') );
		$( "#newSettings" ).data('sourceName', sourceName);
		
		$.ajax({
			url: "./ajax.php?variables=" + sourceName,
			dataType: "JSON",
			success: function(json){
				$("#nameGroupHolder").html('');
				for(var i=0;i<json.length;i++){
					console.log(json[i]);
					
					//var sentences = $(json[i].comment) //$('#para').text() // get example text
					//.match(/[^\.\!\?]+[\.\!\?]+/g); // extract all sentences
					//console.log(sentences);
					
					//let lines = json[i].comment.split(/[\s,]+/)
					let lines = json[i].comment.split(/^([ A-Za-z0-9_@();,$%ˆ#&+-]*[\.\s|\?\s|\!\s]?)\s?(.*)/); // get first sentence.
					console.log(lines);
					
					
					
					//const regex = /.*?(\.)(?=\s[A-Z])/;
					//const str = `© 2018 Telegraph Publishing LLC Four area men between the ages of 18 and 20 were arrested early this morning on a variety of charges in an overnight burglary at the Tater Hill Golf Course in Windham. According to a Vermont State Police press release, at about 2:30 a.m. Saturday, troopers got a report that [&#8230;]`;
					//let m;

					
					
					//if ((m = regex.exec(json[i].comment)) !== null) {
					//	console.log(m[0]);
					//}
					
					
					/*
					.map(function(s){
						s=s.replace(/^\s+|\s+$/g,'');
						return count++
							? s
							: '<span style="color:red">'+s+'</span>'
					}).join(' ')
					);
					*/
					
					html = '<hr/><div class="form-group"><label>';
					
					if ('checkbox' in json[i]){
						checkedStatus = "";
						if(json[i].checkbox){
							checkedStatus = "checked";
						}
						html += '<input type="checkbox" value="checkbox" ' + checkedStatus + '>' + lines[1] + '</label><p class="help-block">' + lines[2] + '</p>';
					}
					if ('input' in json[i]){
						html += lines[1] + '</label>';
						html += '<input class="form-control " value="' + json[i].input + '"><p class="help-block">' + lines[2] + '</p>';
					}
					html += '</div>'
					$("#nameGroupHolder").append(html);
				}
			}
		});
	}


	function removeCode(codeName){
		//console.log("adding remove detection");
		console.log("Clicked remove button for: " + codeName);
		encodedCodeName = encodeURIComponent(codeName);
		var checkstr =  confirm('Are you sure you want to delete this?');
		if(checkstr == true){
			$.get("ajax.php?remove=" + encodedCodeName, function(json) {
				console.log(json);
				if(json[0] == "OK"){
					updateCodeList();
				}else{
					modalAlert(json);
				}
			});
		}
	}

	function uploadCode(codeName){
		console.log("User wants to upload: " + codeName);
		$('.togglerow').hide();
		resetUploadWizard();
		$('#uploadWizard').show();
		$( "#uploadWizard" ).data('name', codeName );
		$( "#uploadheaderName" ).text(codeName.replace(/\_/g, ' '));
	}

	function editCode(codeName){
		console.log("Editing:" + codeName);
		
		$('.togglerow').hide();
		$('#editRow').show();
		$('#editing-h1').text(codeName.replace(/\_/g, ' '));
		
		codePath = 'code/' + codeName + '/' + codeName + '.ino';
		console.log(codePath);
		
		$.get(codePath, function(response) {
			console.log("got response");
			console.log(response);
			cEditor.setValue(response);
			cEditor.clearHistory();
		});
	}


	function saveCode(codeName){
		console.log("Save code name: " + codeName);
		code = encodeURIComponent( cEditor.getValue() );
		encodedCodeName = encodeURIComponent(codeName);
		console.log("new code: " + code);

		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: { code: code, name : encodedCodeName },
			dataType: "JSON",
			success: function(json){
				if(json[0] == "OK"){
					modalAlert("The code has been saved.");
				}else{
					modalAlert(json);
				}
			}
		});
	}	


	function savePassword(){
		console.log("Saving new password");
		var newPassword = encodeURIComponent( $('#securityPassword').val() );
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: { newPassword: newPassword},
			dataType: "JSON",
			success: function(json){
				if(json[0] == "OK"){
					modalAlert("The new password has been saved.");
				}else{
					modalAlert(json);
				}
			}
		});
	}	


	
	// do the initial scan of connected devices.
	function firstScan(){
		$.ajax({
			url: "./ajax.php?cli=firstScan",
			dataType: "JSON",
			success: function(json){
				if(json[0] == "OK"){
					$('#disconnect').hide();
					$('#plugitin').show();
				}else{
					modalmodalAlert(json);
				}
			}
		});
	}
	
	
	function rescan(){
		continueCheckingOutput = true;
		$( "#foundDevicesHolder .panel-body").html('');
		$.ajax({
			url: "./ajax.php?cli=rescan",
			dataType: "JSON",
			success: function(json){
				console.log(json);
				
				$(json).each(function(i,val){
					
					$( "#the-original-arduino" ).clone().attr('id', '').appendTo( "#foundDevicesHolder .panel-body" );
					$( "#foundDevicesHolder .panel-body > div:last-child .panel-primary" ).string_to_color(["border-color"], val["productID"] );
					$( "#foundDevicesHolder .panel-body > div:last-child .panel-heading" ).string_to_color(["background", "border-color"], val["productID"] );
					$( "#foundDevicesHolder .panel-body > div:last-child a" ).data('port', val["port"] );
					$( "#foundDevicesHolder .panel-body > div:last-child a" ).data('name', val["name"] );
					//$( "#foundDevicesHolder .panel-body > div:last-child .port" ).text( val["port"] );
					displayName = val["name"];
					if(displayName == "unknown"){displayName = "Arduino";}
					$( "#foundDevicesHolder .panel-body > div:last-child .panel-heading h3").html( '<i class="fa fa-microchip fa-1" aria-hidden="true"></i> ' + displayName ); //.replace(/\_/g, ' ') 
					$( "#foundDevicesHolder .panel-body > div:last-child").show();
					
					
					// THE CLICK FUNCTION FOR EACH FOUND ARDUINO
					$( "#foundDevicesHolder .panel-body > div:last-child a").click(function(){
						console.log("Clicked on an arduino to upload to.");
						uploadPort = $(this).data('port');
						uploadName = $( "#uploadWizard" ).data('name');
						console.log(uploadName);
						console.log(uploadPort);
						$("#plugitin").hide();
						$("#uploadDebugOutput").val('');
						$("#currentlyUploading").show();
						getDebugOutput(); // Start periodically checking for debug output from the upload command.

						path = "ajax.php?cli=upload&name=" + uploadName + "&port=" + uploadPort;
						$.get(path, function(json) {
							console.log(json);
						});
					});
					
				});
				
				//$('#rescanButton').text("Rescan").show();
				$('#rescanning').hide();
				$('#foundDevicesHolder, #backScanButton').show();
				
			}
		});
	}

	function scanForPatients(){
		$( "#patientsHolder").html('');
		$.ajax({
			url: "./ajax.php?cli=scanForPatients",
			dataType: "JSON",
			success: function(json){
				console.log(json);
				$('#patientsScanButton').show();
				$('#scanningForPatients').hide();
				
				$(json).each(function(i,val){
					$( "#the-original-arduino" ).clone().attr('id', '').appendTo( "#patientsHolder" );
					$( "#patientsHolder > div:last-child .panel-primary" ).string_to_color(["border-color"], val["productID"] );
					$( "#patientsHolder > div:last-child .panel-heading" ).string_to_color(["background", "border-color"], val["productID"] );
					$( "#patientsHolder > div:last-child a" ).data('port', val["port"] );
					$( "#patientsHolder > div:last-child .port" ).text( val["port"] );
					$( "#patientsHolder > div:last-child" ).removeClass("col-lg-3").addClass("col-lg-12");
					$( "#patientsHolder > div:last-child" ).removeClass("col-md-3").addClass("col-md-12");
					$( "#patientsHolder > div:last-child .pull-right i" ).removeClass("fa-arrow-circle-up").addClass("fa-arrow-circle-right");
					
					$( "#patientsHolder > div:last-child .panel-footer .pull-left").text("Listen");
					displayName = val["name"];
					if(displayName == "unknown"){displayName = "Arduino";}
					$( "#patientsHolder > div:last-child .panel-heading h3").text( displayName ); //.replace(/\_/g, ' ') 
					$( "#patientsHolder > div:last-child").show();
					
					
					// THE CLICK FUNCTION FOR EACH FOUND PATIENT
					$( "#patientsHolder > div:last-child a").click(function(){
						console.log("Clicked on an arduino to listen to.");
						continuelisteningToPatient = true;
						listenPort = $(this).data('port');
						console.log(listenPort);
						listenToPatient(); // Start periodically checking for debug output from the listen command.
						$('#stopListeningToPatientButton').show();
						$('#patientsHolder .panel-footer .fa').removeClass('fa-spinner').addClass('fa-arrow-circle-right');
						$(this).find('.panel-footer .fa').addClass('fa-spinner').removeClass('fa-arrow-circle-right');
						
						path = "ajax.php?cli=listenToPatient&port=" + listenPort;
						$.get(path, function(json) {
							console.log(json);
						});
					});
				});
			}
		});
	}




	// runs every second to show data from the upload proces output file.
	function getDebugOutput()
	{
		$.ajax({
			url : "uploadoutput.txt",
			dataType: "text",
			success : function (data) {
				if( $("#uploadDebugOutput").val() != data){
					$("#uploadDebugOutput").val(data);
					var textarea = document.getElementById('uploadDebugOutput');
					textarea.scrollTop = textarea.scrollHeight;
					
					
					
					if (data.indexOf("Compilation failed") >= 0){
						if (data.indexOf("candidates: []") >= 0){
							console.log("A software library is missing.");
							$('#info-uploadError').text('It seems a required software library is not installed yet. For a hint, check the line above "candidates: []" in the output above.');
						}
						$('#info-uploading').slideUp();
						$('#info-uploadError').slideDown();
						continueCheckingOutput = false;
					}else if (data.indexOf("flash verified") > 0){
						$('#info-uploading').slideUp();
						$('#info-uploadComplete').slideDown();
						continueCheckingOutput = false;
					}
					
					
				}
			}
		});

		if( $('#uploadWizard').is(":visible") && continueCheckingOutput == true ){
			setTimeout(getDebugOutput, 1000);
		}
	}


	// runs every second to show data from the upload proces output file.
	function listenToPatient()
	{
		if( $('#deviceDoctor').is(":visible") && continuelisteningToPatient == true ){
			
			$.ajax({
				url : "listenoutput.txt",
				dataType: "text",
				success : function (data) {
					if( $("#listenDebugOutput").val() != data){
						$("#listenDebugOutput").val(data);
						var textarea = document.getElementById('listenDebugOutput');
						textarea.scrollTop = textarea.scrollHeight;
					}
				}
			});
			setTimeout(listenToPatient, 2000);
			
		}else{
			$('#stopListeningToPatientButton').hide();
			$.get('ajax.php?cli=stopListeningToPatient');
			$('#listenDebugOutput').val('');
		}
	}



function modalAlert(content){
	$('#alertModal .modal-body h4').text(content);
	$('#alertModal').modal('show');
}



/*!
 * jquery-string_to_color
 * A jQuery plugin based on string_to_color by Brandon Corbin.
 *
 * Source:
 * https://github.com/erming/jquery-string_to_color
 *
 * Version 0.1.0
 */
(function($) {
	/**
	 * Generate hex color code from a string.
	 *
	 * @param {String} str
	 */
	$.string_to_color = function(str) {
		return "#" + string_to_color(str);
	};
	
	/**
	 * Set one or more CSS properties for the set of matched elements.
	 *
	 * @param {String|Array} property
	 * @param {String} str
	 */
	$.fn.string_to_color = function(property, string) {
		if (!property || !string) {
			throw new Error("$(selector).string_to_color() takes 2 arguments");
		}
		return this.each(function() {
			var props = [].concat(property);
			var $this = $(this);
			$.map(props, function(p) {
				$this.css(p, $.string_to_color(string));
			});
		});
	};
})(jQuery);
 
/********************************************************
Name: str_to_color
Description: create a hash from a string then generates a color
Usage: alert('#'+str_to_color("Any string can be converted"));
author: Brandon Corbin [code@icorbin.com]
website: http://icorbin.com
********************************************************/

function string_to_color(str, options) {

    // Generate a Hash for the String
    this.hash = function(word) {
        var h = 0;
        for (var i = 0; i < word.length; i++) {
            h = word.charCodeAt(i) + ((h << 5) - h);
        }
        return h;
    };

    // Change the darkness or lightness
    this.shade = function(color, prc) {
        var num = parseInt(color, 16),
            amt = Math.round(2.55 * prc),
            R = (num >> 16) + amt,
            G = (num >> 8 & 0x00FF) + amt,
            B = (num & 0x0000FF) + amt;
        return (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255))
            .toString(16)
            .slice(1);

    };
    
    // Convert init to an RGBA
    this.int_to_rgba = function(i) {
        var color = ((i >> 24) & 0xFF).toString(16) +
            ((i >> 16) & 0xFF).toString(16) +
            ((i >> 8) & 0xFF).toString(16) +
            (i & 0xFF).toString(16);
        return color;
    };

    return this.shade(this.int_to_rgba(this.hash(str)), -10);

}
