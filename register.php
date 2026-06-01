<?php  
/* BLOCK 1: Session State Initialization */
// Initializes or resumes an active browser-tracked session to allow user state checking.
session_start(); 

/* BLOCK 2: Pre-Authenticated User Interceptor Gatekeeper */
// Checks if the incoming visitor is already authenticated as an administrator or customer. If logged in, they are immediately bounced to the main entry layout (`index.php`) to bypass the signup screen.
if(isset($_SESSION['admin_sid']) || isset($_SESSION['customer_sid']))
{
  header("location:index.php");
}
else{
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Register - SK Ordering System</title>

  <link rel="icon" href="images/SKLogo.png">

  <link rel="stylesheet" type="text/css" href="css/login_style.css">
</head>

<body>

  <div class="login-container" id="register-page">
    
    <div class="row">
      <h1 class="login-form-text">Register</h1>
      <p class="login-form-subtext">Join SHAKE KING now!</p>
    </div>

    <form id="formValidate" method="post" action="routers/register-router.php">
      
      <div class="row">
        <div class="input-field">
          <label for="username">Username</label>
          <input name="username" id="username" type="text" required minlength="5" placeholder="Minimum 5 characters">
        </div>
      </div>
      
      <div class="row">
        <div class="input-field">
          <label for="name">Full Name</label>
          <input name="name" id="name" type="text" required minlength="5" placeholder="Enter your full name">
        </div>
      </div>
      
      <div class="row">
        <div class="input-field">
          <label for="password">Password</label>
          <input name="password" id="password" type="password" required minlength="5" placeholder="Minimum 5 characters">
        </div>
      </div>
      
      <div class="row">
        <div class="input-field">
          <label for="phone">Phone Number</label>
          <input name="phone" id="phone" type="number" required minlength="4" placeholder="Minimum 4 digits">
        </div>    
      </div>
      
      <div class="row" style="margin-top: 1.5rem;">
        <button type="submit" name="action" class="btn">Register Account</button>
      </div>
      
      <div class="row links-container">
        <p>Already have an account? <a href="login.php">Login</a></p>
      </div>

    </form>
  </div>

</body>
</html>
<?php
}
/* BLOCK 15: Structural Logic Scope Cap Termination Boundary */
// Formally closes programmatic scope switches initiated by structural session gating layers.
?>