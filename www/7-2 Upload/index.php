<?php 
	//  ------ PHP functions ----------------	
	function redirectTo($newLocation) {
		header("Location: " . $newLocation);
		exit;
	}

	function signOut() {
		$_SESSION['logged_in'] = 0;
		redirectTo("index.php");
	}
	// --------------------------------------

	// Included for debugging
	include 'ChromePhp.php';
	
	// Start Session
	session_start();

	// Logic for switching between pages
	if (!isset($_GET['id'])) {
		$home = 0;
	} else if ($_GET['id'] == 1) {
		$home = 1;
	} else if ($_GET['id'] == 2) {
		$home = 2;
	} else if ($_GET['id'] == 3) {
		$home = 3;
	} else if ($_GET['id'] == 4) {
		$home = 4;
	} else if ($_GET['id'] == 5) {
		$home = 5;
	} else if ($_GET['id'] == 6) {
		$home = 6;
	} else if ($_GET['id'] == 7) {
		$home = 7;
	} else {
		$home = 0;
	}

	// ------ Attempt to gain access to mysql server -----

	// Settings for login into database
	$username = "root";			// Change to nonroot user - root / User_Level
	$password = "C7t32813";		// Change to new user password - perkasie / work no pass / home C7t32813#
	$dbhost = "127.0.0.1";		// Need to change for live server - nonlive 127.0.0.1
	$dbname = "user_pass";

	// Connect to DB server
	$connection = mysqli_connect($dbhost, $username, $password, $dbname);

	// Display db connection error if exists
	if (mysqli_connect_errno()) {
		die("Database Connection Failed " . mysqli_connect_error() .
			mysqli_connect_error() . 
			" (" . mysqli_errno() . ")"
		);
	} 
	else {
		// Connection is successful
		// ChromePhp::log("Am I logged_in: " . $_SESSION['logged_in']);
		// Checks to see if log_in_redirect is set

		// Checks to see if the submit button was pressed
		if (!empty($_POST['submit'])) {
			// Get username and password from POST
			$user = $_POST['username'];
			$pass = $_POST['password'];

			// SQL quere string
			$query =  "SELECT * FROM login_info ";
			$query .= 'WHERE username =  "' . $user . '" AND ';
			$query .= 'password = "' . $pass . '"';

			// See if there is a username/password match
			$match = mysqli_query($connection, $query);
			$match = mysqli_fetch_assoc($match);
			//print_r($match);
			//ChromePhp::log("not empty match: " . !empty($match));
			
			// Set session value to logged in
			if ( !empty($match) ) {
				$_SESSION['user'] = $user;		// Save username for welcome message
				$_SESSION['logged_in'] = 1;		// Sets the user login state	
				$_POST['log_in_redirect'] = 1;	// Flag for after redirecting 
				redirectTo("index.php");
			} else {
				// No username/password match
				$_SESSION['logged_in'] = 0;	
			}

		} else if(!empty($_POST['log_out'])) {
			// User clicks sign out link
			$_SESSION['logged_in'] = 0;
			$_POST['log_out'] = "";
			redirectTo("index.php");
		
		} else if (!empty($_POST['register'])) {
			// User clicks button to register
			// -- Using php because there are two buttons in form
			redirectTo("index.php?id=5");
		
		} else if (!empty($_POST['create_user'])) {
			// Attempts to register user
			
			// Checks to see if db already has username/email
			$user = $_POST['email'];

			$query = "SELECT * FROM login_info ";
			$query .= "WHERE username = " . $user . '"';

			$match = mysqli_query($connection, $query);	
			//$match = mysqli_fetch_assoc($match);

			/* If no email is found
				-- Insert new user into db
				-- Display Sucess message  
			*/
			if (empty($match)) {
				// Username doesn't exist
				 
				$insert_user = "INSERT INTO login_info ";
				$insert_user .= "(username, password, fName, lName, phone) ";
				$insert_user .= "VALUES ('" . $_POST['email'] . "', '" .
										$_POST['pass'] . "', '" .
										$_POST['firstName'] . "', '" .
										$_POST['lastName'] . "', '" .
										$_POST['phone'] . "')";
				$success_insert = mysqli_query($connection, $insert_user);

				echo "<h1>" . $insert_user . "</h1>";

				if (!empty($success_insert)) {
					echo "<h1>Success</h1>";
					// New User was sucessfully entered into DB
					$_SESSION['success_insert'] = 1;	// True
					redirectTo("index.php?id=4");		// login page
				} else {
					// Unsuccessful db query
					// TODO - Do something???
					echo "<h1>Unsuccess</h1>";
					$_SESSION['bad_query'] = 1;
				}	

			} else {
				// Username already exists
				$_SESSION['user_already_exists'] = 1;
				echo "<h1>UNSuccess</h1>";
				//redirectTo("index.php?id=4");
			}

			/*	Else
				-- redirect to login page
				-- Display email is already used 
			*/
		} else if (!empty($_POST['pass_reset_button'])) {
			// Forgot Password Button was pushed
			redirectTo("index.php?id=6");

		} else if (!empty($_POST['forgot_pass'])) {
			// Resets user password

			// Get all POST variables needed
			$user_forgot = $_POST['user_forgot'];
			$pass_forgot = $_POST['pass_forgot'];
			$last_forgot = $_POST['last_forgot'];

			// Checks to see if username and lName exists
			$query = "SELECT * FROM login_info ";
			$query .= "WHERE username = '" . $user_forgot . "' AND ";
			$query .= "lName = '" . $last_forgot . "'";

			$match = mysqli_query($connection, $query);
			//$match = mysqli_fetch_assoc($match);

			if (!empty($match)) {
				// If the entry exists, update db with new password

				$forgot_update = "UPDATE login_info ";
				$forgot_update .= "SET password= '" . $pass_forgot . "' ";
				$forgot_update .= "WHERE username= '" . $user_forgot . "'";

				$success_update = mysqli_query($connection, $forgot_update);

				if (!empty($success_update)) {
				// Update was successful
					// Set session variable to display output on login screen
					$_SESSION['pass_reset_good'] = 1;

					// redirect to login page
					redirectTo("index.php?id=4");
				} else {
				// Update was unsuccessful
					echo $query;
					$_SESSION['pass_reset_bad'] = 1;
				}
			} else {
				// No username/lName matches found
				//echo $query;
				$_SESSION['pass_reset_bad'] = 1;	// Display 
			}

			/* else
				-- redirect to sign in page
				-- Display message that username is not valid
			*/

		}
		
		else {
			// Connection error 
			if (empty($_SESSION['logged_in'])) {
				// Keeps user logged out if never logged in
				$_SESSION['logged_in'] = "";
			}

			if ($_SESSION['logged_in'] != 1 && !empty($_POST['log_in_redirect']))
				$_SESSION['logged_in'] = 0;
			//ChromePhp::log("Not logged_in: " . $_SESSION['logged_in']);
		}
	}
	// ---------------------------------------------------
	
	/*
	// ----- Actions for when login form is submitted -------
	if (isset($_POST['submit'])) {	// Submit was pushed at least once
		ChromePhp::log("submit was pushed");
		
		// Check for redirect to login or failure
		if ($_SESSION['logged_in'] == 1) {		// Successful login
			//redirectTo("index.php");
		} else { 								// Uncessful login atempt
			// Code......
		}

	} else {	// Submit was never hit yet
		$submit = 0;
	}
	// ----------------------------------------------------
	*/
 ?>
<!DOCTYPE html>
<html lang="en">
<head>

	<title>Kyle O'Neill</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta charset="utf-8">
	
	<!-- Angular JS -->
	<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular.min.js"></script>

	<!-- jQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>

	<!-- Bootstrap -->
  	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

  	<!-- Stylesheets -->
	<link rel="stylesheet" type="text/css" href="./css/style.css">

	<!-- Modules -->
	<script type="text/javascript" src="./js/app.js"></script>

	 <!-- Controllers -->
    <script src="./js/controllers/MainController.js"></script>

    <!-- Directives -->
    <script src="./js/directives/academics.js"></script>
    <script src="./js/directives/techProj.js"></script>
    <script src="./js/directives/volunteer.js"></script>
    <script src="./js/directives/login.js"></script>
    <script src="./js/directives/giphy.js"></script>

	<script src="js/jquery-color-cycle-plugin-master/jquery.colorcycle.min.js"></script>
	<script type="text/javascript" src="js/webpage.js"></script> 
</head>
<body ng-app="SomeDemo">
	<div ng-controller="MainController" ng-init="display=0">

	 <!--Bootstrap Fixed Navbar -->
   <nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid" id="buttons-cont">
      
      <div class="navbar-header">
        
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="icon-bar"></span>     
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>     
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>     
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>                         
        </button>
        <a class="navbar-brand" href="index.php">Kyle O'Neill</a>


      </div>

      <div id="navbar" class="navbar-collapse collapse" aria-expanded="false">
	
		<ul class="nav navbar-nav navbar-text navbar-right" id="nav-right">
		<?php if ($_SESSION['logged_in']==0) { ?>
          <li><a href="#" onclick="location.href='index.php?id=4'">Sign In</a></li>
        <?php } else { ?>
        	<form name="sign_out" action="index.php?id=4" method="post">
        	<input type="hidden" name="log_out" value="1">
          	<li id="signout"><a href="#" onclick="signOut()">Sign Out</a></li>
          	</form>
        <?php } ?>
        </ul>
        <ul class="nav navbar-nav navbar-text navbar-left">
          <li><a href="index.php?id=1">Academic History</a></li>
          <li><a href="index.php?id=2">Technical Projects</a></li>
          <li><a href="index.php?id=3">Volunteer Projects</a></li>
        <?php if ($_SESSION['logged_in']==1) { ?>
          <li><a href="resume.pdf">Resume</a></li>
          <li><a href="bank_app/bank.php">Currency Converter</a></li>
          <li><a href="index.php?id=7">Giphy App</a></li>
        <?php } ?>
        </ul>

      </div>

    </div>
  </nav>

	<div class="container-fluid no-padding">
	
<!-- Top Row -->
		<div class="row" id="top-empty">	
			<!-- EMPTY -->
		</div>

		
<!-- Button Row -->
		<div class="row" id="buttons">
			<!-- Empty -->
		</div>


<!-- Content Row -->
		<div class="row" id="content">
			<div class="col col-xs-1 col-md-3">
				<!-- EMPTY -->
			</div>

			<div class="col col-xs-10 col-md-6" id="content-box">

				<div id="content-box-text">

					<div ng-hide="<?php echo $home; ?> != 0">
						<img src="img/profilepic.jpg" id="home-page-pic">
					</div>

					<academics ng-hide="<?php echo $home; ?> != 1"></academics>

					<tech-proj ng-show="<?php echo $home; ?> == 2"></tech-proj>

					<volunteer ng-show="<?php echo $home; ?> == 3"></volunteer>

					<div ng-show="<?php echo $home; ?> == 4">
						<form action="index.php?id=4" method="post">
							Username: <input type="text" name="username" value="" /><br />
							Password: <input type="password" name="password" value="" /><br />
							<br />
							<input type="submit" name="submit" value="Submit" />
							<input type="submit" name="register" value="Register" />
							<input type="submit" name="pass_reset_button" value="Forgot Password" />
							<?php 
								if (!empty($_POST['submit']) && $_SESSION['logged_in'] == 0) {
									echo "Unsuccessful Login - Please Try Again";
								} else if (!empty($_SESSION['success_insert'])) {
									echo "You are now registered - Please Login";
									$_SESSION['success_insert'] = null;
								} elseif (!empty($_SESSION['pass_reset_good'])) {
									echo "Password Successfully Reset - Please Login";
									$_SESSION['pass_reset_good'] = null;
								}	
							?>
						</form>
					</div>

					<div ng-show="<?php echo $home; ?> == 5">
						<form name="regForm" action="index.php?id=5" method="post">
						<!-- Needs to make sure all input is entered before submitting-->
							Email: <input type="email" name="email" value="" ng-model="email" required>
							<span ng-show="regForm.email.$invalid && regForm.email.$dirty">
								<span ng-show="regForm.email.$error.required">Email is Required</span>
								<span ng-show="regForm.email.$error.email">Invalid email address.</span>
							</span>
							<br />
							First Name: <input type="text" name="firstName" value="" ng-model="fName" required>
							<span ng-show="regForm.firstName.$invalid && regForm.firstName.$dirty">
								<span ng-show="regForm.firstName.$error.required">First Name is required</span>
							</span>
							<br />
							Last Name: <input type="text" name="lastName" value="" ng-model="lName" required/>
							<span ng-show="regForm.lastName.$invalid && regForm.lastName.$dirty">
								<span ng-show="regForm.lastName.$error.required">Last Name is required</span>
							</span>
							<br />
							Phone Number: <input type="text" name="phone" value="" ng-model="phone" required/>
							<span ng-show="regForm.phone.$invalid && regForm.phone.$dirty">
								<span ng-show="regForm.phone.$error.required">Phone Number is required</span>
							</span>
							<br />
							Password: <input type="password" name="pass" value="" ng-model="pass" required/>
							<span ng-show="regForm.pass.$invalid && regForm.pass.$dirty">
								<span ng-show="regForm.pass.$error.required">Password is required</span>
							</span>
							<br />
							<input type="submit" name="create_user" value="Submit" ng-disabled="regForm.email.$invalid && regForm.email.$dirty ||
											regForm.firstName.$invalid && regForm.firstName.$dirty ||
											regForm.lastName.$invalid && regForm.lastName.$dirty ||
											regForm.phone.$invalid && regForm.phone.$dirty ||
											regForm.pass.$invalid && regForm.pass.$dirty"/>
							<?php
								if (!empty($_SESSION['user_already_exists'])) {
									echo "Username already exist - Please Try Again";
									$_SESSION['user_already_exists'] = null;
								}
							?>
						</form>
					</div>

					<div ng-show="<?php echo $home; ?> == 6">
						<form name="resetPassword" action="index.php?id=6" method="post">
							Username: <input type="text" name="user_forgot" value="" />
							New Password: <input type="password" name="pass_forgot" />
							Last Name: <input type="text" name="last_forgot" value="" />
							<input type="submit" name="forgot_pass" value="Reset Password"> 
							<?php 
								if (!empty($_SESSION['pass_reset_bad'])) {
									echo "Unable to reset password. Please Try Again";
									$_SESSION['pass_reset_bad'] = null;
								}
							 ?>
						</form>
					</div>

					<div ng-show="<?php echo $home; ?> == 7">
						<div class="container-fluid" id="gif-page">
	
							<!-- Title Row -->
							<div class="row">

								<div class="col-xs-0 col-lg-3">
									<!-- empty row -->
								</div>

								<div class="col-xs-12 col-lg-6">
									<h1>Gif Generator</h1>
									<h6>Please type in a word/phrase and a random gif will appear below based on your input. Its a good way to kill an hour.</h6>
								</div>

								<div class="col-xs-0 col-lg-3">
									<!-- empty row -->
								</div>
							
							</div>

							<!-- app -->
							<div class="row">
									<input type="text" name="giphy-search" id="giphy-search">
									<!-- <br> -->
									<button type="button" id="giphy-button">Click me</button>

							</div>

						</div>
					</div>
				
				</div>

			</div>

			<div class="col">
				<!-- EMPTY -->

			</div>
		</div>

<!-- Bottom Row -->
		<div class="row" id="bottom-empty">	
			<div class="no-padding">
				<div ng-show="<?php echo $home; ?> == 7" id="the-gif-cont">
					<img src="" id="the-gif">
				</div>
			</div>
		</div>

	
	</div> <!-- End of Container Fluid -->

	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-68700814-1', 'auto');
	  ga('send', 'pageview');

	</script>

</body>
</html>

<?php 

	// 5. Free Memory
	mysqli_close($connection);
 ?>