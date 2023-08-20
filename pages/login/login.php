<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      rel="shortcut icon"
      href="../../img/logo/ERS_logo_icon.ico"
      type="image/x-icon" />
    <title>ERS | Login Page</title>
    <link rel="stylesheet" href="../../css/style.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script
      src="https://kit.fontawesome.com/5ce4b972fd.js"
      crossorigin="anonymous"></script>
  </head>

  <body class="w-full h-full">
    <div class="login-bg flex items-center justify-center"></div>
    <div class="card h-[475px] w-10/12 absolute-center">
      <!-- Mobile n tab view design -->
      <div class="lg:hidden flex flex-col items-center">
        <img
          src="../../img/logo/ERS_logo.gif"
          alt="ERS_logo"
          class="w-28 align-middle" />
        <h2 class="my-5 text-lg">Exam Registration System</h2>
        <h3 class="text-lg underline font-semibold text-gray-900 mb-3">
          Sign-In
        </h3>
        <form
          action=""
          method="post"
          class="flex flex-col items-center justify-around">
          <div class="text-input">
            <i class="fa-solid fa-user"></i>
            <div></div>
            <input type="text" name="username" placeholder="UserName" />
          </div>
          <div class="text-input">
            <i class="fa-solid fa-lock"></i>
            <div></div>
            <input type="password" name="password" placeholder="Password" />
          </div>
          <input
            type="submit"
            name="login-btn"
            value="Sign-In"
            class="btn text-white bg-[var(--primary)] mt-5" />
        </form>
        <div class="text-center mt-7">
          <p>Already have an account?</p>
          <a href="../reg/register.php" class="text-[var(--primary)] underline"
            >Sign-Up</a
          >
        </div>
      </div>
    </div>
  </body>
</html>
