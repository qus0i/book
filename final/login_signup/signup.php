<?php
//<!--Start session-->
session_start();
include('connection.php'); 

//<!--Check user inputs-->
//    <!--Define error messages-->
$missingUsername = '<p><strong>Please enter a username!</strong></p>';
 $missingEmail = '<p><strong>Please enter your email address!</strong></p>';
$invalidEmail = '<p><strong>Please enter a valid email address!</strong></p>';
$missingPassword = '<p><strong>Please enter a Password!</strong></p>';
$invalidPassword = '<p><strong>Your password should be at least 6 characters long and inlcude one capital letter and one number!</strong></p>';
$differentPassword = '<p><strong>Passwords don\'t match!</strong></p>';
$missingPassword2 = '<p><strong>Please confirm your password</strong></p>';
//    <!--Get username, email, password, password2-->
//Get username
if(empty($_POST["username"])){
    $errors .= $missingUsername;
}else{
    $username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);   
}
//Get email
if(empty($_POST["email"])){
    $errors .= $missingEmail;   
}else{
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors .= $invalidEmail;   
    }
}
//Get passwords
if(empty($_POST["password"])){
    $errors .= $missingPassword; 
}elseif(!(strlen($_POST["password"])>6
         and preg_match('/[A-Z]/',$_POST["password"])
         and preg_match('/[0-9]/',$_POST["password"])
        )
       ){
    $errors .= $invalidPassword; 
}else{
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING); 
    if(empty($_POST["password2"])){
        $errors .= $missingPassword2;
    }else{
        $password2 = filter_var($_POST["password2"], FILTER_SANITIZE_STRING);
        if($password !== $password2){
            $errors .= $differentPassword;
        }
    }
}
//If there are any errors print error
if($errors){
    $resultMessage = '<div class="alert alert-danger">' . $errors .'</div>';
    echo $resultMessage;
    exit;
}

//no errors

//Prepare variables for the queries
$username = mysqli_real_escape_string($link, $username);
$email = mysqli_real_escape_string($link, $email);
$password = mysqli_real_escape_string($link, $password);
//$password = md5($password);
$password = hash('sha256', $password);
//128 bits -> 32 characters
//256 bits -> 64 characters
//If username exists in the users table print error
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($link, $sql);
if(!$result){
    echo '<div class="alert alert-danger">Error running the query!</div>';
    // ببعطيني اذا خربطت بكتابة اسم التيبل مثلا بالكود 
//    echo '<div class="alert alert-danger">' . mysqli_error($link) . '</div>';
    exit;
}
$results = mysqli_num_rows($result);
if($results){
    echo '<div class="alert alert-danger">That username is already registered. Do you want to log in?</div>';  exit;
}
//If email exists in the users table print error
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = mysqli_query($link, $sql);
if(!$result){
    echo '<div class="alert alert-danger">Error running the query!</div>'; exit;
}
$results = mysqli_num_rows($result);
if($results){
    echo '<div class="alert alert-danger">That email is already registered. Do you want to log in?</div>';  exit;
}
//Create a unique  activation code
$activationKey = bin2hex(openssl_random_pseudo_bytes(16));
    //byte: unit of data = 8 bits
    //bit: 0 or 1
    //16 bytes = 16*8 = 128 bits
    //(2*2*2*2)*2*2*2*2*...*2
    //16*16*...*16
    //32 characters

//Insert user details and activation code in the users table

$sql = "INSERT INTO users (`username`, `email`, `password`, `activation`) VALUES ('$username', '$email', '$password', '$activationKey')";
$result = mysqli_query($link, $sql);
if(!$result){
    echo '<div class="alert alert-danger">There was an error inserting the users details in the database!</div>'; 
    exit;
}
// من هون بتبلش المشكله
//Send the user an email with a link to activate.php with their email and activation code
//$message = "Please click on this link to activate your account:\n\n";

//please set the link of your index.php file here:
//$projectRoot = "http://localhost/NotesAppFinalFinal/";
//$message .= $projectRoot . "9.%20Notes%20App%20(Bootstrap%20PHP%20mySQL)/activate.php?email=" . urlencode($email) . "&key=$activationKey";
//if(mail($email, 'Confirm your Registration', $message, 'From:'.'qusi06@gmail.com')){
  ///     echo "<div class='alert alert-success'>Thank for your registring! A confirmation email has been sent to $email. Please click on the activation link to activate your account.</div>";
///}
   


//////////////

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load PHPMailer

$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // SMTP Server
    $mail->SMTPAuth = true;
    $mail->Username = 'qusimoh99@gmail.com'; // Your Gmail
    $mail->Password = 'feub vywj mmvf wxja'; // Your App Password (Generated from Google)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // TLS Encryption
    $mail->Port = 465;

    // Email Content
    $mail->setFrom('qusimoh99@gmail.com', 'Notes App'); // Sender
    $mail->addAddress($email); // Recipient

    $mail->isHTML(true);
    $mail->Subject = 'Confirm your Registration';

    // Activation Link
    $projectRoot = "http://localhost/NotesAppFinalFinal/";
    $activationLink = $projectRoot . "9.%20Notes%20App%20(Bootstrap%20PHP%20mySQL)/activate.php?email=" . urlencode($email) . "&key=$activationKey";
    
    $mail->Body = "<h3>Please click the link below to activate your account:</h3>
                   <p><a href='$activationLink'>$activationLink</a></p>";

    // Send Email
    if ($mail->send()) {
        echo "<div class='alert alert-success'>Thank you for registering! A confirmation email has been sent to <b>$email</b>. Please check your inbox and click the activation link.</div>";
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
}


        ?>