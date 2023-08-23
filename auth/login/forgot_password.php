<?php require_once($_SERVER['DOCUMENT_ROOT'].'/Exam_Registration_System/auth/userDataController.php');

unset($_SESSION['email']);
unset($_SESSION['username']);
    
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="shortcut icon"
      href="../../assets/img/logo/ERS_logo_icon.ico"
      type="image/x-icon" />
    <title>ERS | Forgot Password</title>
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script
      src="https://kit.fontawesome.com/5ce4b972fd.js"
      crossorigin="anonymous"></script>
  </head>

  <body class="w-full h-full">
    <div class="login-bg flex items-center justify-center"></div>
    <div class="card h-[400px] w-10/12 absolute-center">
      <!-- Mobile n tab view design -->
      <div class="lg:hidden flex flex-col items-center">
        <img
          src="../../assets/img/logo/ERS_logo.gif"
          alt="ERS_logo"
          class="w-28 align-middle" />
        <h2 class="mt-3 mb-5 text-lg">Exam Registration System</h2>
        <h3 class="text-lg underline font-semibold text-gray-900 mb-3">
          Forgot Password
        </h3>

        <?php if (isset($errors['error'])) { ?>
            <div class="error-text"><?php echo $errors['otp-error']; ?></div>
        <?php } ?>

        <form
          action="forgot_password.php"
          method="post"
          class="flex flex-col items-center justify-around">
          
          <div class="text-input">
            <i class="fa-solid fa-user"></i>
            <div></div>
            <input type="text" name="username" placeholder="UserName" />
          </div>
          
          <div class="text-input">
            <i class="fa-solid fa-at"></i>
            <div></div>
            <input type="email" name="email" placeholder="E-mail" />
          </div>
          <input
            type="submit"
            name="forgot-pw-submit-btn"
            value="Submit"
            class="btn text-white bg-[var(--primary)] mt-5" />
        </form>
      </div>
    </div>
  </body>
</html>
