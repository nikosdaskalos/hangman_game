<?php
require('config.php');
// Αρχικοποίηση Συνόδου
session_start();

// Έλεγχος αν ο χρήστης έχει κάνει login, αν όχι τον στέλνω στο login.php
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Ορίζω τις μέγιστες πρσπάθειες 5
define('NUM_TRIES',5);





//Αρχικοποίηση Μεταβλητών

// Δημιουργία λιστών
$highScoreListWins= array();
$highScoreListLosses= array();
$scoreList=array();
$played=array();
//Έλεγχος για νίκη ή ήττα
$isLoss=0;
$isWin=0;

//Δημιουργία Βοήθειας
$help=0;
if(isset($_SESSION['help'])){
	$help=$_SESSION['help'];
}
//Έλεγχος αν υπάρχει επανέναρξη
$isReset=0;


//Ορισμός σκορ στην σύνοδο
$score=0;
if(isset($_SESSION['score'])){
	$score=$_SESSION['score'];
}

//Ορισμός όνομα χρήστη στην σύνοδο
$username= $_SESSION['username'];

$wordIndex=0;
if(isset($_SESSION['wordIndex'])){
	$wordIndex=$_SESSION['wordIndex'];
}

//Συμβολοσειρά για όλες τις λάθος μαντεψιές
$incorrectGuesses="";
if(isset($_SESSION['incorrectGuesses'])){
	$incorrectGuesses= $_SESSION['incorrectGuesses'];
}

//Ορισμός της λέξης στην σύνοδο
if(isset($_SESSION['word'])){
	$word=$_SESSION['word'];
}

$gameInProgress = 0;
// Έλεγχος αν το παιχνίδι παίζεται
if(isset($_SESSION['inProgress'])) {
	$gameInProgress = $_SESSION['inProgress'];
}

//Όχι διαχειριστής αρχικά
$isAdmin=0;
$_SESSION['isAdmin']=0;

if($username=='admin'){
	$_SESSION['isAdmin']=1;
	$isAdmin=1;
}

//Αριθμός γραμμάτων μαντεψιάς
$numFailedGuess=0;
if(isset($_SESSION['numFailedGuess'])){
	$numFailedGuess=$_SESSION['numFailedGuess'];
}

//Για σφάλαματα
$gameError="";

//Τέλος Αρχικοποίησης Μεταβλητών








//Θέματα Βάσης Δεοδμένων
//Σύνδεση με βάση δεδομένων και εμφάνιση scoreboard κλπ
$link=mysqli_connect('localhost','mis19018','misp@ss','mis19018')or die('Your DB connection has failed or is misconfigured, please enter correct values in the config file and try again');

//Ξεκίνημα παιχνιδιού
if($_SERVER["REQUEST_METHOD"]=="POST"){
	//Αν ο χρήστης πατήσει play hangman το παιχνίδι ξεκινάει
	if(isset($_POST['start'])){
		$_SESSION['inProgress']=1;
		$gameInProgress= $_SESSION['inProgress'];
		
		//Παίρω λέξη απο την βάση και την βάζω στην σύνοδο
		//αν υπάρχει στην βάση
		$query="SELECT * FROM hangman_words";
		$result=mysqli_query($link,$query);
		$numRows= mysqli_num_rows($result);
		
		//Παίρνω τυχαία λέξη απο την λίστα
		$wordIndex=rand(1,$numRows);
		$_SESSION['wordIndex']=$wordIndex;
		
		//Έλεγχος αν γύρισαν λέξεις
		if($numRows==0){
			$gameError="No words available, please login as Admin and upload some words.";
			
			//κλείσιμο παιχνιδιού
			$_SESSION['inProgress']=0;
			$gameInProgress=$_SESSION['inProgress'];
		}
		else{
			//λέξεις που παίχθηκαν
			$query="SELECT * FROM games_played WHERE username='$username'";
			$result=mysqli_query($link,$query);
			$row=mysqli_fetch_assoc($result);
		
			//επιλογή διαφορετικής λέξης κάθε φορά
			$played=$row['word'];
			$query="SELECT word FROM hangman_words WHERE id= $wordIndex AND word <> '$played'";
			$result=mysqli_query($link,$query);
			$row=mysqli_fetch_assoc($result);
		
			//Επιλογή λέξης απο το τυχαίο id
			$word=$row['word'];
			if($played==$word){
				while($played==$word){
				$query="SELECT word FROM hangman_words WHERE id=". $wordIndex;
				$result=mysqli_query($link,$query);
				$row=mysqli_fetch_assoc($result);
				$word=$row['$word'];
				}
			}
		
			//Σετάρισμα μεταβλητής συνόδου για την λέξη 
			$_SESSION['word']=$word;

	
			
			//Μήκος Λέξης
			$_SESSION['wordLength']= strlen($word);
			
			//Άδειος πίνακας με παύλες αντί για τα γράμματα της λέξης
			$_SESSION['hiddenWord']=array_fill(0,$_SESSION['wordLength'],'_');
			
			//score
			$query="SELECT points FROM hangman_words WHERE id=". $wordIndex;
			$result=mysqli_query($link,$query);
			$row=mysqli_fetch_assoc($result);
			$score=$row['points'];
			$_SESSION['score']=$score;
			

		}
	}
	
	//Επανέναρξη του παιχνιδιού αν το διαλέξει ο χρήστης
	if(isset($_POST['reset'])){
		$isReset=1;
	}
	
	//Ανέβασμα λίστας απο τον διαχειριστή
	if(isset($_POST['submit_files'])){
		
		//δημιουργία πίνακα
		$filename=$_FILES['upload_file']['tmp_name'];
		$fileArray=file($fileName, FILE_SKIP_EMPTY_LINES);
		
		//δκιαγραφή όλων
		$query="DELETE FROM hangman_words";
		mysqli_query($link,$query);
		
		//διαγραφή id
		$query="ALTER TABLE hangman_words DROP id";
		mysqli_query($link,$query);
		
		foreach($fileArray as $line){
			//ανέβασμα λέξεων στην βάση
			$line=preg_replace('/\s+/', '', $line);
			$line= strtoupper($line);
			$line=mysqli_real_escape_string($link,$line);
			$query="INSERT INTO hangman_words (word) VALUES ('" . $line . "')";
			//εισαγωγή στην βάση
			mysqli_query($link,$query);
		}
	}
	
}


//Τέλος Θεμάτων Βάσης Δεδομένων










//έλεγχος για game over
$gameOver=0;
if(isset($_SESSION['gameOver'])){
	if($_SESSION['gameOver']==1){
		$gameOver=1;
	}
}

//Αρχικοποίηση των σκορ αν το επιλέξει ο διαχειριστής
if($_SERVER["REQUEST_METHOD"]=="GET" && isset($_GET['reset']) && $isAdmin==1){
	$query="UPDATE users SET wins=0, losses=0, score=0";
	mysqli_query($link,$query);
}

//έλεγχος αν ένα γράμμα μαντεύθηκε όσο το παιχνίδι παιζότα, 
if($_SERVER["REQUEST_METHOD"]=="GET" && $gameInProgress==1 && $gameOver==0){
	$alpha="";
	if(isset($_GET['guess'])){
		
		$alpha=$_GET['guess'];
		
		//έλεγχος αν είναι μέσα στην λέξη
		$correct=1;
	
		//αν όχι είναι incorrect guess
		if(strpos($word,$alpha)===false){
			$correct=0;
		}else{
		//αν είναι μέσα στην λέξη, γίνεται αντικατάσταση
		for($i=0; $i<$_SESSION['wordLength'];$i++){
			if($word[$i]==$alpha){
				$_SESSION['hiddenWord'][$i]=$word[$i];
			}
		}
	}
	

	
	//αν δεν είναι στην λέξη ανέβασμα των λαθών
	if($correct!=1){
		$numFailedGuess++;
		$_SESSION['numFailedGuess']= $numFailedGuess;
		
	
		//εισαγωγή στις λάθος μαντεψιές
		if(!isset($S_SESSION['incorrectGuesses'])){
			$_SESSION['incorrectGuesses']=$incorrectGuesses;
		}
		
		$_SESSION['incorrectGuesses'].=$alpha. ", ";
		///rray_push($_SESSION['incorrectGuesses'],$alpha);
		
	
		$incorrectGuesses=$_SESSION['incorrectGuesses'];
		
		//if(!isset($_SESSION['incorrectGuesses'])){
		//	$arrayGuess=array();
		//}
		//else{
		//	$arrayGuess= $_SESSION['incorrectGuesses'];
		//}
		//if(isset($_POST['$alpha'])){
		//	array_push($arrayGuess,$_POST['$alpha']);
		//}
		//$_SESSION['incorrectGuesses']=$arrayGuess;
		
		
	}else{
		//έλεγχος νίκης
		if (strpos(implode("", $_SESSION['hiddenWord']), '_') === false) {
				$isWin = 1;
				$_SESSION['isWin'] = 1;
				$_SESSION['gameOver'] = 1;
				$_SESSION['gameMessage'] = "You have won! Hit reset to play again and see if you are on the highscore board!";
				
			
			}
	}
	
	//έλεγχος αποτυχίας τελευταίας μαντεψιάς
	if($numFailedGuess == 5) {
			// αποτελεί ήττα
			$isLoss = 1;
			$_SESSION['isLoss'] = 1;
			$_SESSION['gameOver'] = 1;
			// Μήνυμα ήττας
			$_SESSION['gameMessage'] = "You have lost! Hit reset to play again!";
		} 
		
}
}

//έλεγχος επανέναρξης απο τον χρήστη
if($isReset == 1) {
	// Reset guesses to 0
	$_SESSION['numFailedGuess'] = 0;
	$numFailedGuess = 0;
	// Turn game off
	$_SESSION['inProgress'] = 0;
	$gameInProgress = 0;
	// Set game over to not over
	$_SESSION['gameOver'] = 0;
	// Reset incorrect guesses
	$_SESSION['incorrectGuesses'] = "";
	// Erase game message
	$_SESSION['gameMessage'] = "";
	// Check if resetting after a win, so dont count as loss
	if(isset($_SESSION['isWin']) && $_SESSION['isWin'] == 1) {
		// Reset win condition detected so don't count as loss and reset win to 0
		$_SESSION['isWin'] = 0;
	} else if(isset($_SESSION['isLoss']) && $_SESSION['isLoss'] == 1) {
		// Reset is loss to 0
		$_SESSION['isLoss'] = 0;
	} else {
		// Count as loss 
		$isLoss = 1;
	}
}

// άμα υπάρξει ήττα, προσθήκη στις ήττες
if($isLoss == 1) {
	// Get current users losses
	$query = "SELECT * FROM users WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_assoc($result);
	// αύξηση ήττας
	$currentLosses = $row['losses'];
	$currentLosses++;
	// ανέβασμα στην βάση
	$query = "UPDATE users SET losses=" . $currentLosses . " WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	mysqli_query($link, $query);
	// σετάρισμα is loss
	$isLoss = 0;
}

// άμα υπάρξει νίκη, προσθήκη στις νίκες
if($isWin == 1) {
	// νίκες που υπάρχουν ήδη
	$query = "SELECT * FROM users WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_assoc($result);
	// αύξηση νικών χρήστη
	$currentWins = $row['wins'];
	$currentWins++;
	// ανέβασμα στην βάση
	$query = "UPDATE users SET wins=" . $currentWins . " WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	mysqli_query($link, $query);
	//score
	$query="SELECT * FROM users WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_assoc($result);
	$score=$score+$row['score'];
	$query = "UPDATE users SET score=" . $score . " WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
	mysqli_query($link, $query);
	//ανέβασμα παιγμένης λέξης
	$query="INSERT INTO games_played (username,word) VALUES ('$username','$word')";
	mysqli_query($link,$query);
	
	// σετάρισμα is loss
	$isWin = 0;
}





if($_SESSION['help']==1){
	echo $word;
}





// εμφάνιση top-10 σκορ
$query = "SELECT username, wins, losses, score FROM users ORDER BY wins DESC LIMIT 10";
$result = mysqli_query($link, $query);
// Αποθήκευση των αποτελεσμάτων σε πίνακες
while($row = mysqli_fetch_assoc($result)) {
	// Αποθήκευση νίκης ήττας
	$highScoreListWins[$row['username']] = $row['wins'];
	$highScoreListLosses[$row['username']] = $row['losses'];
	$scoreList[$row['username']]= $row['score'];
}
?>

<!DOCTYPE html>
<html>
	<head style="background-color=#e02841">
		<meta charset="utf-8">
		<title>Hangman Game </title>
		<link rel="stylesheet" href="main.css" type="text/css">
	</head>
	<body>
		
		
		

		
		
		<div id="content">
			<h1 style="background-color:#ffaa00">
				
				Εργασία Hangman Game-Νικόλαος Δάσκαλος (mis19018)
			</h1>
			<?php if($username === "Anon") : ?>
				<div id="login_signup">
					<p> You are not logged in </p>
					<a href="redister.php">Signup</a>
					<a href="login.php">Login</a>
				</div>
			<?php else : ?>
				<div id="logout">
					<p> You are logged in as <?php echo $username; ?> </p>
					<a href="logout.php">Logout</a>
						
		
		
			<!-- Ο διαχειριστής ανεβάζζει λίστα λέξεων που αντικαθιστά την παρούσα -->
				<?php if($isAdmin == 1) : ?>
					<div id="word_upload">
						<form method="post" enctype="multipart/form-data">
						<div id="upload">
							<p><label for="upload_file">Upload a Word List</label></p>
							<input type="file" id="upload_file" name="upload_file"><br>
						</div>
						<input type="submit" name="submit_files" value="Upload Word List" id="btn">
						</form>
					</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
			
			
			
			<!-- Εμφάνιση Svoreboard-->
			<div id="scoreboard">
				<table border="1">
					<caption>Scoreboard</caption>
					<tr>
						<th>Name</th>
						<th>Score</th>
						<th>Wins</th>
						<th>Losses</th>
					</tr>
			<!-- λούπα στα σκορ -->
				<?php
					foreach ($highScoreListWins as $username => $wins) {
						$losses = $highScoreListLosses[$username];
						$score=$scoreList[$username];
						echo"<tr><td>" . $username . "</td><td>" . $score. "</td><td>" .$wins   . "</td><td>".$losses."</td></tr>"; //
					}
				?>
				<!-- 
					$link=mysqli_connect('localhost','mis19018','misp@ss','mis19018');
					$query="SELECT points FROM hangman_words WHERE word='" . mysqli_real_escape_string($link, $word). "'";
					$result=mysqli_query($link,$query);
					$row=mysqli_fetch_assoc($result);
					$score=$row['score'];
					$_SESSION['score']=$score;
					
					$query="UPDATE users SET score=".$score."WHERE username'".mysqli_real_escape_string($link,$username). "'";
					mysqli_query($link,$query);
						
				 -->
				</table>
			<!-- Ο διαχειριστής μπορέι να κάνει reset -->
				<?php if($isAdmin == 1) : ?>
				<?php echo '<br><a href="welcome.php?reset=1">Reset Scores</a>'; ?>
				<?php endif; ?>
			</div>
			<!-- Τέλος εμφάνισης Scoreboard -->	
			
			
			
			
			<!-- εμφάνιση παιχνιδιού -->
			<div id="hangman_game">
				
				<!--εμφάνιση λέξης  -->
				<?php if($gameInProgress===1) : ?>
					<div id="word_display">
						<p>The word to guess gives you...
						<?php
							$link=mysqli_connect('localhost','mis19018','misp@ss','mis19018');
							$word=$_SESSION['word'];
							$query="SELECT points FROM hangman_words WHERE word= '".mysqli_escape_string($link,$word)."';";
							$result=mysqli_query($link,$query);
							$row=mysqli_fetch_assoc($result);
							$fpoints=$row['points'];
							
							echo $fpoints."points!";
						?>
						<form method="post">
							<input type="submit" value="Help (-5 points)" name="help" id="help">
						</form>	
						<?php 
						
						function printWord() 
						{
							$link=mysqli_connect('localhost','mis19018','misp@ss','mis19018');
							//εμφάνιση λέξης
							$word=$_SESSION['word'];
							$query="SELECT help FROM hangman_words WHERE word= '".mysqli_escape_string($link,$word)."';";
							$result=mysqli_query($link,$query);
							$row=mysqli_fetch_assoc($result);
							$fword=$row['help'];
							//αφαίρεση 5 βαθμών
							$username=$_SESSION['username'];
							$score=$_SESSION['score'];
							$query= "SELECT score FROM users WHERE username='".mysqli_real_escape_string($link, $username) . "'";
							$result=mysqli_query($link,$query);
							$row=mysqli_fetch_assoc($result);
							$fscore=$row['score']-5;
							
							$query = "UPDATE users SET score=" . $fscore . " WHERE username='" . mysqli_real_escape_string($link, $username) . "'";
							mysqli_query($link, $query);
							
							
							
							echo $fword;
						}
						
						if (array_key_exists('help',$_POST)){
							printWord();
						}
						
						?>
						</p>  <!--<?php echo $word; ?>-->
					</div>
				<?php endif; ?>
				
				
				
				
				
				<h2 id="hangman_title">
					Hangman Game - 5 Guesses
				</h2>
				
				<!-- Ενέργοποίηση παιχνιδιού και κρύψιμο -->
				<?php if($gameInProgress !=1) : ?>
					<div id="game_trigger">
						<br><span class="error">
								<?php echo $gameError; ?>
							</span>
							<form method="post">
							<input type="hidden" name="start" value="start"/>
							<input type="submit" value="Play Hangman" id="btn">
						</form>	
					</div>
				<?php endif; ?>
				
				
			
				
				
				<!-- Εμφάνιση λέξης προς μανταψιά-->
				<?php if($gameInProgress == 1) : ?>
				<div id="hangman_image">
					<img src="images/hang<?php echo $numFailedGuess; ?>.gif">
					<br><span class="game_message"><?php if(isset($_SESSION['gameMessage'])) echo $_SESSION['gameMessage']; ?></span>
				</div>
				
					 <div>Time left = <span id="timer"></span></div>
				 <script>
				 	document.getElementById('timer').innerHTML = 01 + ":" + 00;
						startTimer();
						
						function startTimer() {
						  var presentTime = document.getElementById('timer').innerHTML;
						  var timeArray = presentTime.split(/[:]+/);
						  var m = timeArray[0];
						  var s = checkSecond((timeArray[1] - 1));
						  if(s==59){m=m-1}
						  //if(m<0){alert('timer completed')}
						  
						  document.getElementById('timer').innerHTML =
						    m + ":" + s;
						  setTimeout(startTimer, 1000);
						}
						
						function checkSecond(sec) {
						  if (sec < 10 && sec >= 0) {sec = "0" + sec}; // add zero in front of numbers < 10
						  if (sec < 0) {sec = "59"};
						  return sec;
						}
				 </script>
				
				<!-- Λέξη προς μανταψιά -->
				<div id="guessed_word">
					<?php
						// Εμφανίζει την λέξη με πα΄θυλες
						// Παύλα αν δεν έχει μαντεφθεί το γράμμα
						$hiddenWord = $_SESSION['hiddenWord'];
						echo '<p id="fillin">';
						for($i = 0; $i < $_SESSION['wordLength']; $i++) {
							echo "$hiddenWord[$i] ";
						}
						echo "</p>";
					?>
					<p> Incorrect Guesses - <?php echo $numFailedGuess;?> - <?php echo $incorrectGuesses;?> </p>
				</div>
				
				<div id="alpha_list">
					<p> Select a letter below to fill in the word above </p>
					<?php 
						// Αλφάβητο και γράμματα με λινκ
				
						for ($i = 65; $i <= 90; $i++) {
							// This displays the number as the char in alphabet A-Z
			    			printf('<a href="welcome.php?guess=%1$s" class="alpha">%1$s</a>  ', chr($i));
						}
					?>
				</div>
				
				
				
				<div id="sound"> 
					<audio id="myAudio">
						 <source src="horse.ogg" type="audio/ogg">
						 <source src="music.mp3" type="audio/mpeg">
						 Your browser does not support the audio element.
					</audio>
			
					<p>Music</p>
					<button onclick="playAudio()" type="button">Play</button>
					<button onclick="pauseAudio()" type="button">Pause</button> 
					<script>
						var x = document.getElementById("myAudio"); 
						
						function playAudio() { 
						  x.play(); 
						} 
						
						function pauseAudio() { 
						  x.pause(); 
						} 
					</script> 
				</div>
			
			
			
				
				<div id="reset">
					<form method="post">
						<?php if(isset($_SESSION['isWin']) && $_SESSION['isWin'] == 1) : ?>
						<p class="win"> Click Reset Game to Play Again :) </p>
						<?php elseif(isset($_SESSION['isLoss']) && $_SESSION['isLoss'] == 1) : ?>
						<p class="loss"> Click Reset Game to Play Again :( </p>
						<?php else : ?>
						<p class="reset"> Click Reset Game to Play Again - Will Count As Loss </p>
						<?php endif; ?>
						<input type="hidden" name="reset" value="reset"/>
						<input type="submit" value="Reset Game" id="btn">
					</form>
				</div>
				<?php endif; ?>
				
									
			</div>
	</body>
</html>