<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Candle manager allows you to expand your privacy friendly DIY smart home. It can generate and upload Arduino code for privacy friendly sensors and other devices.">
    <meta name="author" content="Candle">

	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">

    <title>Candle manager</title>

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <!-- MetisMenu CSS
    <link href="vendor/metisMenu/metisMenu.min.css" rel="stylesheet" type="text/css"> -->

    <link href="css/sb-admin-2.min.css" rel="stylesheet" type="text/css">
	<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
	<link href="css/codemirror.css" rel="stylesheet"  type="text/css">
	<link href="css/main.css" rel="stylesheet" type="text/css">
	
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php">Candle manager</a>
            </div>
            <!-- 

            <ul class="nav navbar-top-links navbar-right">
                
				<li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-envelope fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-messages">
                        <li>
                            <a href="#">
                                <div>
                                    <strong>John Smith</strong>
                                    <span class="pull-right text-muted">
                                        <em>Yesterday</em>
                                    </span>
                                </div>
                                <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <strong>John Smith</strong>
                                    <span class="pull-right text-muted">
                                        <em>Yesterday</em>
                                    </span>
                                </div>
                                <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <strong>John Smith</strong>
                                    <span class="pull-right text-muted">
                                        <em>Yesterday</em>
                                    </span>
                                </div>
                                <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a class="text-center" href="#">
                                <strong>Read All Messages</strong>
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-tasks fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-tasks">
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>Task 1</strong>
                                        <span class="pull-right text-muted">40% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
                                            <span class="sr-only">40% Complete (success)</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>Task 2</strong>
                                        <span class="pull-right text-muted">20% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: 20%">
                                            <span class="sr-only">20% Complete</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>Task 3</strong>
                                        <span class="pull-right text-muted">60% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                            <span class="sr-only">60% Complete (warning)</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <p>
                                        <strong>Task 4</strong>
                                        <span class="pull-right text-muted">80% Complete</span>
                                    </p>
                                    <div class="progress progress-striped active">
                                        <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%">
                                            <span class="sr-only">80% Complete (danger)</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a class="text-center" href="#">
                                <strong>See All Tasks</strong>
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-bell fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-alerts">
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-comment fa-fw"></i> New Comment
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-twitter fa-fw"></i> 3 New Followers
                                    <span class="pull-right text-muted small">12 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-envelope fa-fw"></i> Message Sent
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-tasks fa-fw"></i> New Task
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <i class="fa fa-upload fa-fw"></i> Server Rebooted
                                    <span class="pull-right text-muted small">4 minutes ago</span>
                                </div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a class="text-center" href="#">
                                <strong>See All Alerts</strong>
                                <i class="fa fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                        </li>
                        <li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="login.html"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <!--<li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="button">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                            </div>
                        </li>-->
                        <li class="menuitem" id="menu-one">
                            <a href="#one"><i class="fa fa-list-alt fa-fw"></i> Overview</a>
                        </li>
                        <li class="menuitem" id="menu-two">
                            <a href="#two"><i class="fa fa-cogs fa-fw"></i> Settings</a>
						</li>
                        <li class="menuitem" id="menu-three">
                            <a href="#three"><i class="fa fa-plus-circle fa-fw"></i> New</a>
                        </li>
                        <li class="menuitem" id="menu-four">
                            <a href="#four"><i class="fa fa-stethoscope fa-fw"></i> Device doctor</a>
                        </li>
                        <li class="menuitem" id="menu-five" style="display:none">
                            <a href="#five"><i class="fa fa-wifi fa-fw"></i> Found devices</a>
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
            <!-- /.row -->
            <div id="one" class="togglerow row">
                <div class="col-lg-12">
                    <h1 class="page-header">Dashboard</h1>
                </div>
                <div id="the-original" class="col-lg-3 col-md-6" style="display:none">
                    <div class="panel panel-primary">
                        <a class="newPanel" href="#newSettings">
							<div class="panel-heading">
								<div class="row">
									<!--<div class="col-xs-3">
										<i class="fa fa-sticky-note fa-5x"></i>
									</div>-->
									<div class="col-xs-9 text-left">
										<div><h2>Name</h2></div>
									</div>
								</div>
                        	</div>
                            <div class="panel-footer">
                                <span class="pull-left">Create from template</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
                <div id="the-original-arduino" class="col-lg-3 col-md-6 foundArduino" style="display:none">
                    <div class="panel panel-primary">
                        <a class="newPanel" href="#newSettings">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-12 text-left">
										<div><h3 class="foundArduinoName">Name</h3></div>
										<div class="foundArduinoPort advanced"></div>
									</div>
								</div>
                        	</div>
                            <div class="panel-footer">
                                <span class="pull-left">Upload to this device</span>
                                <span class="pull-right"><i class="fa fa-arrow-circle-up"></i></span>
                                <div class="clearfix"></div>
                            </div>
                        </a>
                    </div>
                </div>
				<div class="col-lg-12">
                    <h3 class="page-header">Generated code</h3>
					<div id="codelist">
					
					</div>
                </div>
            </div>
            <!-- /.row -->
			<div id="two" class="togglerow row">
                <div class="col-lg-12">
						<h1 class="page-header">General settings</h1>
					</div>
					<div id="security" class="col-lg-6">
						<div class="panel panel-default">
							<div class="panel-heading">
								Security
							</div>
							<div class="panel-body">
								<div id="securityPasswordGroup" class="form-group">
										<label for="securityPassword">Encryption password</label>
										<input id="securityPassword" class="form-control" type="text" name="securityPassword" >
										<p class="help-block">This will be used between all devices in your network. If you change it you will have to re-upload all devices. You can disable wireless security by saving an empty password.</p>
								</div>
								<button id="securityPasswordSaveButton" class="btn btn-warning">Save</button>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="panel panel-default">
							<div class="panel-heading">
								Automatically download required libraries
							</div>
							<div class="panel-body">
								<form id="interface" method="get" action="manage.php" _lpchecked="1">
									<div class="form-group">
										<label>Libraries</label>
										<div class="checkbox">
											<label>
												<input id="autoGetLibraries" type="checkbox" value="">Try to automatically install missing libraries<p class="help-block">This is only actually possible if the device is connected to the internet.</p>
											</label>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
			</div>
            <!-- /.row  THIS IS THE NEW DEVICE PAGE -->
			<div id="three" class="togglerow row">
				
                <div class="col-lg-12">
                    <h1 class="page-header">New</h1>
					<div id="source-list" ></div>
				</div>
				
				<a name="newSettings"></a>
				
				<div id="newSettings" style="display:none;margin-bottom:10vh">
					<div class="col-lg-12">
						<h3 class="page-header">Creating new <span id="newDeviceSourceName">device</span></h3>
					</div>
					<div class="col-lg-6">
						<div class="panel panel-primary">
							<div class="panel-heading">
								Settings
							</div>
							<div class="panel-body">
								<form id="addNewCode" method="get" action="manage.php" _lpchecked="1">
									<div id="nameGroup" class="form-group">
										<label for="newName">Name</label>
										<input id="newName" class="form-control" type="text" name="newName" placeholder="Name">
									</div>
									<div id="nameGroupHolder"></div>
									<a class="btn" href="#newSettings"><button type="submit" class="btn btn-primary">Next</button></a>
								</form>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div id="aboutNewDevice" class="well">
						</div>
					</div>
				</div>
				
			</div>
			
			
			
			<!--  UPLOAD WIZARD  -->
			
			<div id="uploadWizard" class="row" style="display:none;margin-bottom:10vh">
                <div class="col-lg-12">
                    <h1 class="page-header">Uploading <span id="uploadheaderName"></span></h1>
				</div>				
				<div class="col-lg-12" id="" >
					<div id="disconnect" class="well">

						<h4>Uploading (step 1 of 3)</h4>
						<p>Here you can upload the code that has been generated to fit with your prefered settings.</p>
						
						<img class="wizard-image" src="images/detach.png"/>
						
						<p>To continue with the upload, make sure that the Arduino you want to program is currently NOT connected to the USB port.</p>
						
						<div class="alert alert-info" id="firstScanning" style="display:none"><i class="fa fa-spinner fa-fw"></i> Doing initial scan of the system. Please wait..</div>
						<button id="firstScanButton" class="btn btn-primary btn-lg">It's disconnected</button>
						<button id="scanForAllDevicesButton" class="btn btn-default btn-sm ">Show all USB devices</button>
					</div>
					<div id="plugitin" style="display:none" class="well">
						<h4>Uploading (step 2 of 3)</h4>
						
						<img class="wizard-image" src="images/plug-it-in.png"/>
						
						<p>Now connect your device via the USB port.</p>
						
						<div id="foundDevicesHolder" class="panel panel-primary" style="display:none;">
							<div class="panel-heading">
								Found devices:
							</div>
							<div class="panel-body">
							</div>
						</div>

						<div class="alert alert-info" id="rescanning" style="display:none;clear:both"><i class="fa fa-spinner fa-fw"></i> Scanning for plugged in USB devices. Please wait..</div>
						<button id="backScanButton" class="btn btn-default btn-sm" style="clear:both">Back</button> <button id="rescanButton" class="btn btn-primary" style="clear:both">It's connected</button>
					</div>
					<div id="currentlyUploading" style="display:none" class="well">
						<h4>Uploading (step 3 of 3)</h4>
						<p>Code is now being uploaded to your device. You can check the progress below. Don't detach the Arduino until the process is complete. This should take at most a minute.</p>
						
						<p><button id="showUploadDebug" class="btn btn-default btn-sm ">Show details</button></p>
						<textarea id="uploadDebugOutput" style="display:none"></textarea>
						<div id="info-uploading" class="alert alert-info">
							<i class="fa fa-spinner fa-fw"></i> Uploading...
						</div>
						<div id="info-uploadError" class="alert alert-danger" style="display:none;">
							Something is wrong with the code you are trying to upload. You can <a href="#" id="compileErrorEditLink" class="alert-link">inspect it</a>, or try to re-create it with different settings.
						</div>
						<div id="info-uploadComplete" class="alert alert-success" style="display:none;">
							The code was uploaded succesfully. You can <a href="#" id="info-listenToNewDevice" class="alert-link">listen to your new device</a> to find out if it's working ok.
						</div>
					</div>						
				</div>
			</div>
			
			
			<!--  DEVICE DOCTOR  -->
			
			<div id="deviceDoctor" class="togglerow row" style="display:none;margin-bottom:10vh">
                <div class="col-lg-12">
                    <h1 class="page-header">Device doctor</h1>
				</div>				
				<div class="col-lg-3 well">
					<div>

						<h4>Connect the device and press the scan button</h4>
						<div class="panel panel-primary">
							<div class="panel-heading">
								Found devices:
							</div>
							<div class="panel-body">
								<div id="patientsHolder"></div>
								<div class="alert alert-info" id="scanningForPatients" style="display:none"><i class="fa fa-spinner fa-fw"></i> Scanning for connected devices. Please wait..</div>
							</div>
						</div>
						
						<button id="patientsScanButton" class="btn btn-default" style="clear:both">Scan</button>
					</div>				
				</div>
				<div class="col-lg-9" >
					<div class="panel panel-primary">
						<div class="panel-heading">
							What the device is saying:
						</div>
						<div class="panel-body">
							<textarea autocomplete="off" id="listenDebugOutput">Scan for devices first.</textarea>
							<button id="stopListeningToPatientButton" class="btn btn-default" style="clear:both;display:none">Stop listening</button>
						</div>
					</div>
				</div>					
			</div>
			
			
			
			<div id="mysensorsList" class="togglerow row" style="display:none;margin-bottom:10vh">
                <div class="col-lg-12">
                    <h1 class="page-header">Found wireless devices</h1>
				</div>			
				<div class="col-lg-12" >
					<p>These devices have succesfully presented themselves wirelessly. All of these can be added as things in the Mozilla Gateway (using the (+) button on the things page).</p>
					
					<div class="panel panel-primary">
						<div class="panel-heading">
							Presented devices:
						</div>
						<div class="panel-body" id="presentedDevicesHolder">
							
							
						</div>
					</div>
					<button id="rescanPresentedDevices" class="btn btn-default btn-sm" style="clear:both">rescan</button>
				</div>					
			</div>
			
			
			
            <!--  CODE EDITOR  -->
			
			<div id="editRow" class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Editing <span id="editing-h1"></span></h1>
					<textarea autocomplete="off" id="codeHolder">
					</textarea>
					<br/>
					<button class="btn btn-default" id="saveCode">Save</button> <button class="btn btn-default" id="saveAndUpload">Save and upload</button>
                </div>
			</div>	
			
			
			
        </div>
		
		
		
        <!-- /#page-wrapper -->

		
		<!-- MODAL -->
		<div class="modal fade in" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none; padding-left: 0px;">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="myModalLabel"></h4>
					</div>
					<div class="modal-body text-center">
						<h4></h4>
					</div>
					<div class="modal-footer">
						<button id="closeAlerteModal" type="button" class="btn btn-default" data-dismiss="modal">OK</button>
					</div>
				</div>
			</div>
		</div>
    </div>
	
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
	
    <!-- Custom Theme JavaScript
    <script src="js/sb-admin-2.js"></script> -->
	
	<!-- Code mirror: creates pretty code -->
	<script src="./js/codemirror.js"></script>
	<script src="./js/matchbrackets.js"></script>
	<script src="./js/mode/clike/clike.js"></script>
	<script>
		
	var cEditor = CodeMirror.fromTextArea(document.getElementById("codeHolder"), {
        lineNumbers: false,
        matchBrackets: true,
        mode: "text/x-csrc"
    });	
		
	</script>


	<script src="main.js"></script>

</body>

</html>
