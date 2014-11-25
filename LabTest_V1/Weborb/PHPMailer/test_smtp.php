<?php

include_once('class.phpmailer.php');
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

$mail             = new PHPMailer();

$body             = $mail->getFile('contents.html');
$body             = eregi_replace("[\]",'',$body);

$mail->IsSMTP(); // telling the class to use SMTP

//$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier

$mail->SMTPAuth = true;
$mail->Username = "smtpmail@arteika.com";
$mail->Password = "atkmail";
$mail->Host       = "mail.arteika.com"; // SMTP server
$mail->Port       = 25;   // Puerto del SMTP
$mail->SMTPDebug = true;

$mail->From       = "smtpmail@arteika.com";
$mail->FromName   = "Mario Galicia";

$mail->Subject    = "Prueba del SMPT";

$mail->AltBody    = "Comentarios Opcionales"; //Comentarios opcionales

$body = "<html>
		<head>
		<title>
		</title>
		</head>
		<body>
			Esto es una prueba.
		</body>";

$mail->MsgHTML($body); //Cuerpo en HTML

$mail->AddAddress("galicia.mario@gmail.com", "Mario Galicia");//Aquien lo envio

//$mail->AddAttachment("images/phpmailer.gif");// Añadir un archivo

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}

?>
