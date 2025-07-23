<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include the PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

try {
    // Create an instance of PHPMailer
    $mail = new PHPMailer(true); // Pass 'true' to enable exceptions

    // Server settings
    $mail->isSMTP();                                            // Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                         // Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                     // Enable SMTP authentication
    $mail->Username   = 'nguyenconghieu7924@gmail.com';           // SMTP username
    $mail->Password   = 'fdgm ilbr htgk xbev';                    // SMTP password (App password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;              // Enable implicit TLS encryption
    $mail->Port       = 465;                                      // TCP port to connect to; use 587 for STARTTLS

    // Recipients
    $mail->setFrom('nguyenconghieu7924@gmail.com', 'Mailer');


    $mail->addAddress('22111060959@hunre.edu.vn', 'KHÁCH '); 
    $mail->addAddress('conghieu79004@gmail.com', 'Khách');          

    // Attachments (Optional)
    // $mail->addAttachment('/var/tmp/file.tar.gz');               
    // $mail->addAttachment('/tmp/image.\\\\\\', 'new.jpg');          

 
    $mail->isHTML(true);                                          
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'Nguyễn công hiếu dzzz';
   

    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    // Send email
    $mail->send();
    echo 'Message has been sent';

} catch (Exception $e) {
    // Catch exceptions and display the error message
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
