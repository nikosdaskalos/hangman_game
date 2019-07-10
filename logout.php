<?php
// αρχικοποίηση συνόδου
session_start();
 
// μηδενίζω όλες τις μεταβληές συνόδου
$_SESSION = array();
 
// καταστροφή συνόδου
session_destroy();
 
// στέλνω τον χρήστη στην σελίδα εισόδου
header("location: login.php");
exit;
?>