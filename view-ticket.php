<?php
/* BLOCK 1: Infrastructure & Configuration Core */
// Incorporates systemic routing parameters ($con) alongside external asset tracking metrics ($balance), and establishes structural bypass check flags.
include 'includes/connect.php';
include 'includes/wallet.php';
$continue=0;

/* BLOCK 2: Customer Authorization & Secure Record Filtering Matrix */
// Assesses if the session identity explicitly matches an authenticated user. It tracks incoming ticket parameters, checks database row validation counts, and builds descriptive layout strings.
if($_SESSION['customer_sid']==session_id())
{
    $ticket_id = $_GET['id'];
    $sql1 = "SELECT * FROM tickets where poster_id = $user_id AND id = $ticket_id AND not deleted;";
    if(mysqli_num_rows(mysqli_query($con,$sql1))>0){
      $row = $con->query($sql1)->fetch_assoc();
      $type = $row['type'];
      $subject = $row['subject'];
      $description = $row['description'];
      $date = $row['date'];
      $status = $row['status'];
      $continue=1;
    }
    else {
      $continue = 0;  
    }
}

if($continue){
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Ticket No. <?php echo $ticket_id.' - '. $type;?></title>
  <link rel="icon" href="images/SKLogo.png">
  <link href="css/style.css" type="text/css" rel="stylesheet">
</head>

<body class="customer-theme">

  <header>
    <div class="nav-wrapper">
      <div>
        <span class="logo-text">Logo</span>
      </div>
      <div>
        <span class="balance-box">Wallet Balance: $<?php echo $balance;?></span>
      </div>
    </div>
  </header>
  <div class="dashboard-grid">

    <aside>
      <div class="user-details">
        <span class="profile-name"><?php echo $name;?></span>
        <p class="user-role"><?php echo $role;?></p>
        <a href="routers/logout.php" class="logout-link">Logout</a>
      </div>
      
      <ul class="side-nav-links">
        <li><a href="index.php">Order Food</a></li>
        <li>
          <a href="orders.php">Orders</a>
          <ul>
            <?php
              /* BLOCK 7: Parametric Client Order Sub-Menu Link Generator */
              // Scans distinct processing states tied exclusively to the active customer ID to generate dynamic sorting filters.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM orders WHERE customer_id = $user_id;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="orders.php?status='.$row['status'].'">'.$row['status'].'</a></li>';
              }
            ?>
          </ul>
        </li>
        <li class="active">
          <a href="tickets.php">Tickets</a>
          <ul>
            <?php
              /* BLOCK 8: Ticket Filter Categories Loop Tracker */
              // Isolates different status milestones within open tickets created by this specific customer to render category navigation shortcuts.
              $sql = mysqli_query($con, "SELECT DISTINCT status FROM tickets WHERE poster_id = $user_id;");
              while($row = mysqli_fetch_array($sql)){
                echo '<li><a href="tickets.php?status='.$row['status'].'">'.$row['status'].'</a></li>';
              }
            ?>
          </ul>
        </li>       
        <li><a href="details.php">Edit Details</a></li>       
      </ul>
    </aside>
    <main>
      <h2 class="breadcrumbs-title">Provide Order Details</h2>
      <p class="caption">Receipt</p>
      <div class="divider"></div>

      <div class="ticket-info-banner">
        <h3>Ticket No. #<?php echo $ticket_id; ?></h3>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($subject); ?></p>
        <p><strong>Status:</strong> 
          <span class="badge-ui <?php echo ($status == 'Closed') ? 'bg-dark' : 'bg-info'; ?>">
            <?php echo $status; ?>
          </span>
        </p>  
        <p><strong>Type:</strong> <?php echo htmlspecialchars($type); ?></p>                      
        
        <div style="margin-top: 1rem;">
          <form method="post" action="routers/ticket-status.php">
            <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">                    
            <input type="hidden" name="status" value="<?php echo ($status != 'Closed' ? 'Closed' : 'Open'); ?>">
            <button class="btn <?php echo ($status != 'Closed') ? 'btn-danger' : ''; ?>" type="submit">
              <?php echo ($status != 'Closed' ? 'Close Ticket' : 'Reopen Ticket'); ?>
            </button>
          </form>
        </div>
      </div>                    

      <ul class="thread-container">
        <?php
        $sql1 = mysqli_query($con, "SELECT * from ticket_details WHERE ticket_id = $ticket_id;");
        while($row1 = mysqli_fetch_array($sql1)){
          $sql2 = "SELECT * FROM users WHERE id = ".$row1['user_id'].";";
          $name_poster = "Unknown User";
          $role1 = "Customer";
          if(mysqli_num_rows(mysqli_query($con,$sql2))>0){
            $row2 = $con->query($sql2)->fetch_assoc();
            $name_poster = $row2['name'];
            $role1 = $row2['role'];                    
          }
          ?>
          <li class="thread-message-block">
            <div class="thread-meta-header">
              <div><strong><?php echo htmlspecialchars($name_poster); ?></strong> (<?php echo $role1; ?>)</div>
              <div>Date: <?php