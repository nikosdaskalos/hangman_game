<?php
// αρχικοποίηση συνόδου
session_start();
 
// Έλεγχος αν έχει γίνει ήδη login αν ναι τον στέλνω στο παιχνίδι
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: welcome.php");
    exit;
}
 
// περνάω την σύνδεση με την βάση
require_once "config.php";
 
// ορισμός και αρχικοποίηση μεταβλητών
$username = $password = "";
$username_err = $password_err = "";
 
// επεξεργασία δεδομένων φόρμας
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // έλεγχος αν το πεδίο χρήστη είναι άδειο
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // έλεγχος αν ο κωδικός είναι άδειος
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // επικύρωση πε΄διου ονόματος και κωδικού
    if(empty($username_err) && empty($password_err)){
        // δημιουργία ερωτήματος
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
          
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // σετάρισμα παραμέτρων
            $param_username = $username;
            
            // προσπάθεια εκτέλεσης
            if(mysqli_stmt_execute($stmt)){
                // αποθήκευση αποτελέσματος
                mysqli_stmt_store_result($stmt);
                
                // αν υπάρχει το όνομα έλεγχος κωδικού
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // αν είναι σωστός ξεκινάω νέα σύνοδο
                            session_start();
                            
                            // αποθήκευση δεδομένων στις μεταβλητές συνόδου
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // στέλνω τον χρήστη στο παιχνίδι
                            header("location: welcome.php");
                        } else{
                            // αν ο κωδικός είναι λάθος
                            $password_err = "The password you entered was not valid.";
                        }
                    }
                } else{
                    //αν το όνομα χρήστη είναι λάθος
                    $username_err = "No account found with that username.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // κλείσιμο
        mysqli_stmt_close($stmt);
    }
    
    // κλέισιμο
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hangman Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif;
        	background-image: url(warm.jpg);
			background-repeat: no-repeat;
			background-size:cover;}
       .wrapper{ width: 350px; padding: 20px;  left: 63%;
    			  top: 50%;
				  margin-left: -25%;
                  position: absolute;
                  margin-top: -25%;
                  
                 
                  display: table-cell;
   text-align: center;
   vertical-align: middle}
        
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Hangman Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>    
</body>
</html>




