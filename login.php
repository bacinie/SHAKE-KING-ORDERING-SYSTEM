<?php  
/* BLOCK 1: Session Initialization & State Inspection */
// Starts a PHP tracking session to preserve state values across multi-page workflows.
session_start(); 

/* BLOCK 2: Pre-Authenticated Portal Gatekeeper Interceptor */
// Inspects active session variables to determine if a consumer or administrator is already authenticated; if validated, it immediately forces them onto the dashboard workspace (`index.php`) to avoid duplicate login entries.
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
  <title>Login - SK Ordering System</title>

  <link rel="icon" href="images/SKLogo.png">
  <link rel="stylesheet" type="text/css" href="css/login_style.css">
</head>

<body>

  <div class="login-container" id="login-page">
    
    <form method="post" action="routers/router.php" class="login-form" id="form">
      
      <div class="row">
        <p class="login-form-text">SHAKE KING<br>Ordering System</p>
      </div>
      
      <div class="row margin">
        <div class="input-field">
          <label for="username">Username</label>
          <input name="username" id="username" type="text" required>
        </div>
      </div>
      
      <div class="row margin">
        <div class="input-field">
          <label for="password">Password</label>
          <input name="password" id="password" type="password" required>
        </div>
      </div>
      
      <div class="row">
        <button type="submit" class="btn">Login</button>
      </div>
        
      <div class="row links-container">
        <a href="register.php">Register Now!</a>
      </div>

    </form>
  </div>

</body>
</html>
<?php
}
/* BLOCK 12: Logical Processing Conditional Bracket Boundary Termination */
// Closes out structural processing logic gates declared inside early session validation blocks.
?>