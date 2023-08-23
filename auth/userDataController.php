<?php 
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require ($_SERVER['DOCUMENT_ROOT'].'/Exam_Registration_System/vendor/autoload.php'); // Include PHPMailer autoloader 
require ($_SERVER['DOCUMENT_ROOT'].'/Exam_Registration_System/config/connection.php');
$success = array();
$errors = array();


//if user Sign-Up button
if(isset($_POST['reg-btn'])){
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
    // Check the name validation
    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        $errors['username'] = "Only letters and numbers allowed";
    }
    // Check the E-mail validation
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    // Check the Password validation
    elseif (strlen($_POST["password"]) <= '8') {
        $errors['password'] = "Your Password Must Contain At Least 8 Characters!";
    }
    elseif(!preg_match("#[0-9]+#", $password)) {
        $errors['password'] = "Your Password Must Contain At Least 1 Number!";
    }
    elseif(!preg_match("#[A-Z]+#", $password)) {
        $errors['password'] = "Your Password Must Contain At Least 1 Capital Letter!";
    }
    elseif(!preg_match("#[a-z]+#", $password)) {
        $errors['password'] = "Your Password Must Contain At Least 1 Lowercase Letter!";
    }
    elseif(!preg_match("/[\'^£$%&*()}{@#~?><>,|=_+¬-]/", $password)) {
        $errors['password'] = "Your Password Must Contain At Least 1 Special Character !";
    }
    // Check password and confirm password are the same
    elseif($password !== $cpassword){
        $errors['cpassword'] = "Confirm password not matched!";
    }

    if(count($errors) === 0) {
        // Find the student is a registered student in our university or not...
        $email_check = "SELECT * FROM student_check WHERE regNo = '$username' && email = '$email'";
        $email_check_res = mysqli_query($con, $email_check);
        if(mysqli_num_rows($email_check_res) === 0){
            $errors['email'] = "Sorry, Your registration number does not exist!";
        } else {
            $fetch_email_check_res = mysqli_fetch_assoc($email_check_res);
            $fetch_user_status = $fetch_email_check_res['status'];

            // Find the email is already exist or not...
            if($fetch_user_status === 'active'){
                $errors['email'] = "Email that you have entered is already exist!";
            }
        }

    }
    
    // Enter the user data into the database
    if(count($errors) === 0){
        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        $encpass = password_hash($password, PASSWORD_DEFAULT);
        $code = rand(999999, 111111);
        $status = "not_verified";
        
        $insert_data = "INSERT INTO user(userName, email, password, role, verificationCode, status)
                        values('$username', '$email', '$encpass', 'student', '$code', '$status')";
        $data_check = mysqli_query($con, $insert_data);

        // Mail the OTP code
        if($data_check){
            $subject = "ERS Registration - Email Verification Code";
            $message = "Your verification code for the exam registration system is $code. This code will expire in 3 minutes";
            $sender_name = "Exam Registration System | Faculty of Science";
            $sender_mail = "clipmaster120@gmail.com";

            try {
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'clipmaster120@gmail.com';
                $mail->Password = 'omijvrxkwjubmwdv';
                $mail->SMTPSecure = 'tls'; // Use TLS
                $mail->Port = 587;
            
                // Recipients and content
                $mail->setFrom($sender_mail, $sender_name);
                $mail->addAddress($email, $username);
                $mail->Subject = $subject;
                $mail->Body = $message;
            
                // Send email
                $mail->send();
                $info = "We've sent a verification code to your email - $email";
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $username;
                $_SESSION['reg-code-sent'] = true;
                header('location: reg_verification.php');
            } catch (Exception $e) {
                $errors['error'] = "Failed while sending code!";
            }
        }else{
            $errors['error'] = "Failed while inserting data into database!";
        }
    }

}

//if user click verification code submit button
if(isset($_POST['verify-otp'])){
    $email = $_SESSION['email'];
    $username = $_SESSION['username'];

    $number1 = mysqli_real_escape_string($con, $_POST['number1']);
    $number2 = mysqli_real_escape_string($con, $_POST['number2']);
    $number3 = mysqli_real_escape_string($con, $_POST['number3']);
    $number4 = mysqli_real_escape_string($con, $_POST['number4']);
    $number5 = mysqli_real_escape_string($con, $_POST['number5']);
    $number6 = mysqli_real_escape_string($con, $_POST['number6']);
    
    $enteredOTP = $number1*100000 + $number2*10000 + $number3*1000 + $number4*100 + $number5*10 + $number6;

    $pull_code_query = "SELECT * FROM user WHERE userName = '$username' && email = '$email'";
    $pull_code_res = mysqli_query($con, $pull_code_query);
    $fetch_verification_code = mysqli_fetch_assoc($pull_code_res);
    $verification_code = $fetch_verification_code['verificationCode'];

    if($enteredOTP == $verification_code){
        // Updating the user table status 
        $code = 0;
        $status = 'verified';
        $update_otp = "UPDATE user SET verificationCode = $code, status = '$status' WHERE userName = '$username' && email = '$email'";
        $update_res = mysqli_query($con, $update_otp);

        // Updating the student_check table status
        $status = 'active';
        $update_status = "UPDATE student_check SET status = '$status' WHERE regNo = '$username' && email = '$email'";
        $update_res = mysqli_query($con, $update_status);
        if($update_res){
            header('location: ../login/index.php');
            exit();
        }else{
            $errors['otp-error'] = "Something went wrong!";
        }
    }else{
        $errors['wrong-otp'] = "You've entered incorrect code!";
    }
}

//if user click login button
if(isset($_POST['login-btn'])){
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $check_email = "SELECT * FROM user WHERE userName = '$username'";
    $res = mysqli_query($con, $check_email);

    if(mysqli_num_rows($res) > 0){
        $fetch = mysqli_fetch_assoc($res);
        $fetch_pass = $fetch['password'];

        if(password_verify($password, $fetch_pass)){
            $status = $fetch['status'];
            $email = $fetch['email'];

            if($status == 'verified'){
                $role = $fetch['role'];
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                if ($role === 'student') {
                    header('location: ../../student/');
                } elseif ($role === 'student-admin') {
                    header('location: ../../admin/student');
                } elseif ($role === 'subject-admin') {
                    header('location: ../../admin/subject');
                } else {
                    header('location: ../../admin/master');
                }
                
            }else{
                $info = "It's look like you haven't still verify your email";
                $_SESSION['info'] = $info;
                header('location: ../register/reg_verification.php');
            }
        }else{
            $errors['login-error'] = "Incorrect email or password!";
        }
    }else{
        $errors['login-error'] = "It's look like you didn't register yet! Click the bottom link to signup.";
    }
}

//if user click continue button in forgot password form
if(isset($_POST['forgot-pw-submit-btn'])){
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email = mysqli_real_escape_string($con, $_POST['email']);

    $check_email = "SELECT * FROM user WHERE userName = '$username' AND email='$email'";
    $run_sql = mysqli_query($con, $check_email);

    if(mysqli_num_rows($run_sql) > 0){
        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        $code = rand(999999, 111111);
        $insert_code = "UPDATE user SET verificationCode = $code WHERE userName = '$username' AND email = '$email'";
        $run_query =  mysqli_query($con, $insert_code);

        if($run_query){
            $subject = "ERS Registration - Email Verification Code";
            $message = "Your verification code for the exam registration system is $code. This code will expire in 3 minutes";
            $sender_name = "Exam Registration System | Faculty of Science";
            $sender_mail = "clipmaster120@gmail.com";

            try {
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'clipmaster120@gmail.com';
                $mail->Password = 'omijvrxkwjubmwdv';
                $mail->SMTPSecure = 'tls'; // Use TLS
                $mail->Port = 587;
            
                // Recipients and content
                $mail->setFrom($sender_mail, $sender_name);
                $mail->addAddress($email, $username);
                $mail->Subject = $subject;
                $mail->Body = $message;
            
                // Send email
                $mail->send();
                $info = "We've sent a verification code to your email - $email";
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $username;
                $_SESSION['code-sent'] = true;
                header('location: user_verification.php');
            } catch (Exception $e) {
                $errors['error'] = "Failed while sending code!";
            }
        }else{
            $errors['error'] = "Something went wrong!";
        }
    }else{
        $errors['error'] = "This username or email does not exist!";
    }
}

if (isset($_GET['reg-code-resend'])) {
    // Initialize PHPMailer
    $mail = new PHPMailer(true);

    $email = $_SESSION['email'];
    $username = $_SESSION['username'];
    $code = rand(999999, 111111);
    $insert_code = "UPDATE user SET verificationCode = $code WHERE userName = '$username' && email = '$email'";
    $run_query =  mysqli_query($con, $insert_code);
    if($run_query){
        $subject = "ERS Registration - Email Verification Code";
        $message = "Your verification code for the exam registration system is $code. This code will expire in 3 minutes";
        $sender_name = "Exam Registration System | Faculty of Science";
        $sender_mail = "clipmaster120@gmail.com";
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'clipmaster120@gmail.com';
            $mail->Password = 'omijvrxkwjubmwdv';
            $mail->SMTPSecure = 'tls'; // Use TLS
            $mail->Port = 587;
        
            // Recipients and content
            $mail->setFrom($sender_mail, $sender_name);
            $mail->addAddress($email, $username);
            $mail->Subject = $subject;
            $mail->Body = $message;
        
            // Send email
            $mail->send();
            $info = "We've sent a verification code to your email - $email";
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            $_SESSION['code-sent'] = true;
            header('location: register/reg_verification.php');
        } catch (Exception $e) {
            $errors['error'] = "Failed while sending code!";
        }
    }else{
        $errors['error'] = "Something went wrong!";
    }
}


if (isset($_GET['pw-code-resend'])) {
    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    
    $email = $_SESSION['email'];
    $username = $_SESSION['username'];
    $code = rand(999999, 111111);
    $insert_code = "UPDATE user SET verificationCode = $code WHERE userName = '$username' && email = '$email'";
    $run_query =  mysqli_query($con, $insert_code);
    if($run_query){
        $subject = "ERS Registration - Email Verification Code";
        $message = "Your verification code for the exam registration system is $code. This code will expire in 3 minutes";
        $sender_name = "Exam Registration System | Faculty of Science";
        $sender_mail = "clipmaster120@gmail.com";
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'clipmaster120@gmail.com';
            $mail->Password = 'omijvrxkwjubmwdv';
            $mail->SMTPSecure = 'tls'; // Use TLS
            $mail->Port = 587;
        
            // Recipients and content
            $mail->setFrom($sender_mail, $sender_name);
            $mail->addAddress($email, $username);
            $mail->Subject = $subject;
            $mail->Body = $message;
        
            // Send email
            $mail->send();
            $info = "We've sent a verification code to your email - $email";
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            $_SESSION['code-sent'] = true;
            header('location: login/user_verification.php');
        } catch (Exception $e) {
            $errors['error'] = "Failed while sending code!";
        }
    }else{
        $errors['error'] = "Something went wrong!";
    }
}

//if user click verification code submit button
if(isset($_POST['verify-pw-otp'])){
    $email = $_SESSION['email'];
    $username = $_SESSION['username'];

    $number1 = mysqli_real_escape_string($con, $_POST['number1']);
    $number2 = mysqli_real_escape_string($con, $_POST['number2']);
    $number3 = mysqli_real_escape_string($con, $_POST['number3']);
    $number4 = mysqli_real_escape_string($con, $_POST['number4']);
    $number5 = mysqli_real_escape_string($con, $_POST['number5']);
    $number6 = mysqli_real_escape_string($con, $_POST['number6']);
    
    $enteredOTP = $number1*100000 + $number2*10000 + $number3*1000 + $number4*100 + $number5*10 + $number6;

    $pull_code_query = "SELECT * FROM user WHERE userName = '$username' && email = '$email'";
    $pull_code_res = mysqli_query($con, $pull_code_query);
    $fetch_verification_code = mysqli_fetch_assoc($pull_code_res);
    $verification_code = $fetch_verification_code['verificationCode'];

    if($enteredOTP == $verification_code){
        // Updating the user table status 
        $code = 0;
        $status = 'verified';
        $update_otp = "UPDATE user SET verificationCode = $code, status = '$status' WHERE userName = '$username' && email = '$email'";
        $update_res = mysqli_query($con, $update_otp);

        if($update_res){
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            $_SESSION['code-verified'] = true;
            header('location: reset_password.php');
            exit();
        }else{
            $errors['otp-error'] = "Something went wrong!";
        }
    }else{
        $errors['wrong-otp'] = "You've entered incorrect code!";
    }
}

//if user click change password button
if(isset($_POST['reset-pw-btn'])){
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    // Check the Password validation
    if (strlen($_POST["password"]) <= '8') {
        $errors['password'] = "Your Password Must Contain At Least 8 Characters!";
    }
    elseif(!preg_match("#[0-9]+#", $password)) {
        $errors['password'] = "Your Password Must Contain At Least 1 Number!";
    }
    elseif(!preg_match("#[A-Z]+#", $password)) {
        $errors['password'] = "Your Password Must Contain At Least 1 Capital Letter!";
    }
    elseif(!preg_match("#[a-z]+#", $password)) {
        $errors['password'] = "Your Password Must Contain At Least 1 Lowercase Letter!";
    }
    elseif(!preg_match("/[\'^£$%&*()}{@#~?><>,|=_+¬-]/", $password)) {
        $errors['password'] = "Your Password Must Contain At Least 1 Special Character !";
    }

    // Check the password and confirm password are same
    if ($password !== $cpassword){
        $errors['cpassword'] = "Confirm password not matched!";
    } else {
        $code = 0;
        $email = $_SESSION['email']; //getting this email using session
        $email = $_SESSION['username']; //getting this username using session
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $update_pass = "UPDATE user SET verificationCode = $code, password = '$encpass' WHERE userName = '$username' AND email = '$email'";
        $run_query = mysqli_query($con, $update_pass);
        if($run_query){
            unset($_SESSION['email']);
            unset($_SESSION['username']);
            header('Location: index.php');
        }else{
            $errors['error'] = "Failed to change your password!";
        }
    }
}


// If file upload form is submitted 
if(isset($_POST["user-image-submit-btn"])){ 
    if(!empty($_FILES["userImage"]["name"])) { 
        // Get file info 
        $fileName = basename($_FILES["userImage"]["name"]); 
        $fileType = pathinfo($fileName, PATHINFO_EXTENSION); 
         
        // Allow certain file formats 
        $allowTypes = array('jpg','png','jpeg','gif'); 
        if(in_array($fileType, $allowTypes)){ 
            $email = $_SESSION['email'];
            $image = $_FILES['userImage']['tmp_name']; 
            $imgContent = addslashes(file_get_contents($image)); 
         
            $sql = "SELECT * FROM user_images WHERE email = '$email'";
            $run_Sql = mysqli_query($con, $sql);
            if (mysqli_num_rows($run_Sql) > 0) {
                $insert = $con->query(
                    "UPDATE user_images 
                    SET email = '$email', image = '$imgContent', uploaded = NOW() WHERE email='$email'"
                ); 

                if($insert){ 
                    $success['userProfile'] = 'File uploaded successfully.'; 
                }else{ 
                    $personalDetailsErrors['userImage'] = "File upload failed, please try again."; 
                }          
            } else {
                // Insert image content into database 
                $insert = $con->query(
                    "INSERT INTO `user_images` (`email`, `image`, `uploaded`) 
                    VALUES ('$email','$imgContent', NOW())"
                );

                if($insert){ 
                    $success['userProfile'] = 'File uploaded successfully.'; 
                }else{ 
                    $personalDetailsErrors['userImage'] = "File upload failed, please try again."; 
                }    
            }         
        }else{ 
            $personalDetailsErrors['userImage'] = 'Sorry, only JPG, JPEG, PNG, & GIF files are allowed to upload.'; 
        } 
    }else{ 
        $personalDetailsErrors['userImage'] = 'Please select an image file to upload.'; 
    } 
}

// MyProfile php file's user details section 
if (isset($_POST['user-details-submit-btn'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $mobile = mysqli_real_escape_string($con, $_POST['mobile']);
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $birthday = mysqli_real_escape_string($con, $_POST['birthday']);
    $country = mysqli_real_escape_string($con, $_POST['country']);
    $_SESSION['name'] = $name;

    $sql = "SELECT * FROM user WHERE email = '$email'";
    $run_Sql = mysqli_query($con, $sql);
    if (mysqli_num_rows($run_Sql) > 0) {
        // Insert userImage content into database 
        $insert = $con->query(
            "UPDATE user 
            SET name='$name', email='$email', mobile='$mobile', gender='$gender', birthday='$birthday', country='$country', date=NOW() 
            WHERE email='$email'"
        );

        if ($insert) {
            $success['userProfile'] = "Thank you Mr./Mrs. $name. Your details uploaded successfully";
        } else {
            $personalDetailsErrors['insertDetails'] = "Something went wrong!";
        }
    } else {
        // Insert userImage content into database 
        $insert = $con->query(
            "INSERT INTO `user`(`name`, `email`, `mobile`, `gender`, `birthday`, `country`, `date`) 
            VALUES ('$name','$email','$mobile','$gender','$birthday','$country', NOW())"
        );

        if ($insert) {
            $success['userProfile'] = "Thank you Mr./Mrs. $name. Your details uploaded successfully";
        } else {
            $personalDetailsErrors['insertDetails'] = "Something went wrong!";
        }

    }    
}

// MyProfile php file's user details section 
if (isset($_POST['business-details-submit-btn'])) {
    $b_name = mysqli_real_escape_string($con, $_POST['b_name']);
    $b_type = mysqli_real_escape_string($con, $_POST['b_type']);
    $b_position = mysqli_real_escape_string($con, $_POST['b_position']);
    $b_email = mysqli_real_escape_string($con, $_POST['b_email']);
    $b_mobile = mysqli_real_escape_string($con, $_POST['b_mobile']);
    $b_location = mysqli_real_escape_string($con, $_POST['b_location']);
    $b_mission = mysqli_real_escape_string($con, $_POST['b_mission']);
    $b_vision = mysqli_real_escape_string($con, $_POST['b_vision']);

    $sql = "SELECT * FROM business WHERE b_email = '$b_email'";
    $run_Sql = mysqli_query($con, $sql);
    if (mysqli_num_rows($run_Sql) > 0) {
        // Insert userImage content into database 
        $insert = $con->query(
            "UPDATE business 
            SET b_name='$b_name', b_type='$b_type', b_position='$b_position', b_email='$b_email', b_mobile='$b_mobile', b_location='$b_location', b_mission='$b_mission', b_vision='$b_vision', date=NOW() 
            WHERE b_email='$b_email'"
        );

        if ($insert) {
            $name = $_SESSION['name'];
            $success['businessProfile'] = "Thank you Mr./Mrs. $name. Your details uploaded successfully";
        } else {
            $businessDetailsErrors['businessProfile'] = "Something went wrong!";
        }
    } else {
        $email = $_SESSION['email'];
        // Insert userImage content into database 
        $insert = $con->query(
            "INSERT INTO `business`(`b_name`,`b_type`,`b_position`,`b_email`,`b_mobile`,`b_location`,`b_mission`, `b_vision`, `date`) 
            VALUES ('$b_name','$b_type','$b_position','$b_email','$b_mobile','$b_location','$b_mission','$b_vision', NOW())"
        );
        $user_business_insert = $con->query(
            "INSERT INTO user_business(email, b_email) 
            VALUES (
                (SELECT email FROM user WHERE email = '$email'), 
                (SELECT b_email FROM business WHERE b_email = '$b_email')
            );"
        );

        if ($insert && $user_business_insert) {
            $name = $_SESSION['name'];
            $success['businessProfile'] = "Thank you Mr./Mrs. $name. Your details uploaded successfully";
        } else {
            $businessDetailsErrors['businessProfile'] = "Something went wrong!";
        }

    }
    $_SESSION['b_email'] = $b_email;
}

// If file upload form is submitted 
if(isset($_POST["business-logo-submit-btn"])){ 
    $email = $_SESSION['email'];
    $sql = "SELECT * FROM user_business WHERE email = '$email'";
    $run_Sql = mysqli_query($con, $sql);
    if (mysqli_num_rows($run_Sql) > 0) {
        $fetch_info = mysqli_fetch_assoc($run_Sql);
        $b_email = $fetch_info['b_email'];
        if(!empty($_FILES["businessLogo"]["name"])) { 
            // Get file info 
            $fileName = basename($_FILES["businessLogo"]["name"]); 
            $fileType = pathinfo($fileName, PATHINFO_EXTENSION); 
             
            // Allow certain file formats 
            $allowTypes = array('jpg','png','jpeg','gif'); 
            if(in_array($fileType, $allowTypes)){ 
                $image = $_FILES['businessLogo']['tmp_name']; 
                $imgContent = addslashes(file_get_contents($image));    

                $run_logo_Sql = $con->query("SELECT * FROM `business_logos` WHERE b_email = '$b_email'"); 
                if ($run_logo_Sql->num_rows > 0) {
                    $insert = $con->query(
                        "UPDATE business_logos 
                        SET b_email = '$b_email', image = '$imgContent', created = NOW() 
                        WHERE b_email='$b_email'"
                    ); 
    
                    if($insert){ 
                        $success['businessProfile'] = 'File uploaded successfully.'; 
                    }else{ 
                        $businessDetailsErrors['businessProfile'] = "File upload failed, please try again."; 
                    }          
                } else {
                    // Insert image content into database 
                    $insert = $con->query(
                        "INSERT INTO `business_logos` (`b_email`, `image`, `created`) 
                        VALUES ('$b_email','$imgContent', NOW())"
                    );
    
                    if($insert){ 
                        $success['businessProfile'] = 'File uploaded successfully.'; 
                    }else{ 
                        $businessDetailsErrors['businessProfile'] = "File upload failed, please try again."; 
                    }    
                }         
            }else{ 
                $businessDetailsErrors['businessProfile'] = 'Sorry, only JPG, JPEG, PNG, & GIF files are allowed to upload.'; 
            } 
        }else{ 
            $businessDetailsErrors['businessProfile'] = 'Please select an image file to upload.'; 
        }     
    } else {
        $businessDetailsErrors['businessProfile'] = "Please, Fill the business details first!"; 
    }
}

?>
