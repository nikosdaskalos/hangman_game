<?php
// αρχείο βάσης
require_once "config.php";
 
// ορισμός και αρχικοποίηση μεταβλητών
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
 
// επεξεργασία δεδομένων
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // επικύρωση ονόματος χρήστη
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        // δημιουργία ερωτήματος
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // σετάρισμα παραμάτρων
            $param_username = trim($_POST["username"]);
            
            // προσπάθεια εκτέλεσης
            if(mysqli_stmt_execute($stmt)){
                //αποθήκευση αποτελέσματος
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // κλείσιμο
        mysqli_stmt_close($stmt);
    }
    
    // επικύρωση κωδικού
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // επικύρωση 2
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // έλεγχος πριν τα ανεβάσω στην βάση
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        
        // δημιουργία ερωτήματος
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
           
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
            
            // σετάρισμα παραμέτρων
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // δημιουργία hash
            
            // εκτέλεση ερωτήματος
            if(mysqli_stmt_execute($stmt)){
                // στέλνω τον χρήστη στο login 
                header("location: login.php");
            } else{
                echo "Something went wrong. Please try again later.";
            }
        }
         
        // κλείσιμο
        mysqli_stmt_close($stmt);
    }
    
    // κλείσιμο
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hangman Register</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; background-image: url(warm.jpg);
			background-repeat: no-repeat;
			background-size:cover; }
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
        <h2>Hangman Register</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-default" value="Reset">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>    
</body>
</html>