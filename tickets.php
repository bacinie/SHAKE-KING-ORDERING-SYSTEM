<?php
/* BLOCK 1: Database Connections & Account Metrics Sync */
// Establishes a database reference connection ($con) and queries user-specific financial properties such as virtual digital wallet metrics ($balance).
include 'includes/connect.php';
include 'includes/wallet.php';

  /* BLOCK 2: Customer Authorization Session Guard */
  // Checks if the user holds a valid customer session token; blocks anonymous or administrative visitors from interacting with the client-facing desk.
  if($_SESSION['customer_sid']==session_id())
  {
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Tickets</title>

  <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>

  <header id="header">
    <div class="nav-wrapper">
      <div class="header-brand-group">
        <a href="index.php">
          <img src="images/SKLogo.png" alt="Logo" class="header-logo-img">
        </a>
        <span class="logo-text">FOOD ORDERING SYSTEM</span>
      </div>
      <div>
        <span class="balance-box">Wallet Balance: $<?php echo $balance;?></span>
      </div>
    </div>
  </header>
  <div class="dashboard-grid" id="main">

    <aside id="left-sidebar-nav">
      <div class="user-details">
        <span class="profile-name"><?php echo $name;?></span>
        <p class="user-role"><?php echo $role;?></p>
        <a href="routers/logout.php" class="logout-link">Logout</a>
      </div>

      <ul class="side-nav-links">
        <li>
          <a href="index.php">Order Food</a>
        </li>
        <li>
          <a href="orders.php">Orders</a>
          <ul>
            <li><a href="orders.php">All Orders</a></li>
            <?php
              /* BLOCK 7: Parametric Client Order Sub-Menu Link Generator */
              // Scans distinct processing states tied exclusively to the active customer ID to generate dynamic sorting filters.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders WHERE customer_id = $user_id;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="orders.php?status='.urlencode($row['status']).'">'.$row['status'].'</a></li>';
              }
            ?>
          </ul>
        </li>
        <li class="active">
          <a href="tickets.php">Tickets</a>
          <ul>
            <li class="<?php if(!isset($_GET['status'])){ echo 'active'; } ?>">
              <a href="tickets.php">All Tickets</a>
            </li>
            <?php
              /* BLOCK 8: Ticket Filter Categories Loop Tracker */
              // Isolates different status milestones within open tickets created by this specific customer to render category navigation shortcuts.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets WHERE poster_id = $user_id AND not deleted;");
              while($row = mysqli_fetch_array($sql)){
                $is_active = (isset($_GET['status']) && $_GET['status'] == $row['status']) ? 'active' : '';
                echo '<li class="'.$is_active.'"><a href="tickets.php?status='.urlencode($row['status']).'">'.$row['status'].'</a></li>';
              }
            ?>
          </ul>
        </li>         
        <li>
          <a href="details.php">Edit Details</a>
        </li>       
      </ul>
    </aside>
    <main id="content">
      <div class="content-card">
        <h5 class="breadcrumbs-title">Tickets Support Hub</h5>
        <p class="caption">If you're experiencing any issues, contact us by opening a support ticket down below.</p>
        <div class="divider"></div>

        <div class="workspace-split-row">
          <div>
            <h4 class="workspace-section-heading">Open a New Ticket</h4>
            
            <form id="formValidate" method="post" action="routers/add-ticket.php">
              
              <div class="input-field">
                <label for="subject">Subject</label>
                <input name="subject" id="subject" type="text" required minlength="5" maxlength="100" placeholder="Summary of the problem...">
              </div>

              <div class="input-field">
                <label for="description">Detailed Description</label>
                <textarea name="description" id="description" required minlength="20" maxlength="3000" placeholder="Please describe your technical or billing issue here..."></textarea>
              </div>          

              <div class="input-field" style="max-width: 300px;">
                <label for="type">Issue Categorization Type</label>
                <select name="type" id="type" required>
                  <option value="" disabled selected>Choose a category</option>
                  <option value="Support">Support</option>
                  <option value="Payment">Payment</option>
                  <option value="Complaint">Complaint</option>
                  <option value="Others">Others</option>        
                </select>
              </div>            

              <div style="text-align: right; margin-top: 1.5rem;">
                <input type="hidden" value="<?php echo $user_id;?>" name="id">
                <button class="btn" type="submit" name="action">Submit Ticket</button>
              </div>

            </form>
          </div>
        </div>

        <div class="divider"></div>
        
        <h4 class="workspace-section-heading">Your Support Tickets Archive</h4>
        <p class="caption" style="margin-bottom: 1rem;">Select any record below to view current conversation details and manager updates.</p>

        <div class="ticket-collection-group">
          <?php
            /* BLOCK 11: Parametric Ticket List Search Engine */
            // Captures sorting definitions from the URL parameters (or defaults to standard wildcards '%') to display an interactive list of matching support tickets.
            if(isset($_GET['status'])){
              $status_filter = $_GET['status'];
            } else {
              $status_filter = '%';
            }     
            
            $sql = mysqli_query($con, "SELECT * FROM tickets WHERE poster_id = $user_id AND status LIKE '$status_filter' AND not deleted;");
            while($row = mysqli_fetch_array($sql)) {                        
              echo '<a href="view-ticket.php?id='.$row['id'].'" class="ticket-list-row">';
              echo '  <span class="ticket-subject-title">'.htmlspecialchars($row['subject']).'</span>'; 
              echo '  <div><span class="badge-ui bg-info">'.htmlspecialchars($row['status']).'</span></div>';                     
              echo '  <div><span class="badge-ui bg-dark">'.htmlspecialchars($row['type']).'</span></div>';
              echo '  <span class="badge-ui bg-date">'.$row['date'].'</span>';
              echo '</a>';
            }
          ?>
        </div>

      </div>
    </main>
    </div>
  
  <footer>
    <div class="footer-content">
      <span>Copyright © 2026</span>
      <span>Design and Developed by <a href="www.linkedin.com/in/bacinie">bacinie</a></span>
    </div>
  </footer>
  </body>
</html>
<?php
  }
  /* BLOCK 13: Exception Interceptor & Multi-Role Redirect Routing */
  // Evaluates session handshake anomalies; redirects administrators to corporate support desks and logs anonymous traffic out to login tools.
  else
  {
    if($_SESSION['admin_sid']==session_id())
    {
      header("location:all-tickets.php");   
    }
    else{
      header("location:login.php");
    }
  }
?>