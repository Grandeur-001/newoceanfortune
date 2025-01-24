<?php
session_start(); // Start the session
include 'connection.php'; // Include the database connection

// Initialize totals
$total_amount = 0;
$total_earnings = 0;
$investments = [];

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']); // Get user_id from session

    // Query to fetch investments
    $query_investments = "
        SELECT 
            investments.investment_id,
            investments.amount,
            investments.roi,
            investments.earnings,
            investments.started_at,
            investments.completed_at,
            investments.last_earning_time,
            investments.status,
            cryptos.crypto_name,
            cryptos.symbol
        FROM 
            investments
        INNER JOIN 
            cryptos
        ON 
            investments.crypto_id = cryptos.crypto_id
        WHERE 
            investments.user_id = $user_id
            AND (
                investments.status = 'pending' 
                OR (
                    investments.status = 'completed' 
                    AND TIMESTAMPDIFF(HOUR, investments.completed_at, NOW()) <= 24
                )
            )
    ";
    $result_investments = mysqli_query($conn, $query_investments);

    if ($result_investments && mysqli_num_rows($result_investments) > 0) {
        while ($investment = mysqli_fetch_assoc($result_investments)) {
            $investments[] = $investment; // Store investments in an array
        }
    }

    // Query to calculate totals
    $query_totals = "
        SELECT 
            SUM(amount) AS total_amount, 
            SUM(earnings) AS total_earnings 
        FROM 
            investments 
        WHERE 
            user_id = $user_id
            AND (
                status = 'pending' 
                OR (
                    status = 'completed' 
                    AND TIMESTAMPDIFF(HOUR, completed_at, NOW()) <= 24
                )
            )
    ";
    $result_totals = mysqli_query($conn, $query_totals);

    if ($result_totals && $row = mysqli_fetch_assoc($result_totals)) {
        $total_amount = $row['total_amount'] ?: 0;
        $total_earnings = $row['total_earnings'] ?: 0;
    }
} else {
    // Redirect to login page if user is not logged in
    header("Location: login.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
        <!-- ============TITLE============= -->
        <title>Ocean Fortune</title>
    
        <!-- ============HEAD-ICON-LOGO============= -->
        <link rel="icon" type="image/png" href="assets/images/logo.png">
    
        <!-- ============CSS-LINKS============= -->
        <link rel="stylesheet" href="assets/css/swap.css">
        <!-- <link rel="stylesheet" href="assets/css/history.css"> -->
        <link rel="stylesheet" href="assets/css/market.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <!-- <link rel="stylesheet" href="assets/css/table-wallet.css"> -->
        <link rel="stylesheet" href="assets/css/mediaquery.css">
        <link rel="stylesheet" href="assets/css/main-mediaquery.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/5.4.5/css/swiper.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    
    
    
        <!-- ============FONT-AWESOME-LINKS============= -->
        <link rel="stylesheet" href="assets/font-awesome/css/font-awesome.min.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


        <noscript> Powered by <a href=“https://www.smartsupp.com” target=“_blank”>Smartsupp</a></noscript>
    </head>


        <?php
            
            include 'session_handler.php';

        ?>


    <style>
        html{
          animation: none;
        }
        .tradingview-widget-container{
            width: auto;
            height: auto;
            position: relative;
            bottom: auto;
            z-index: auto;
        }

        .main_content{
            margin-top: 8rem;
        }
        .app-container{
            margin-bottom: 70px;

        }
        @media (max-width: 768px) {
            .app-container{
                margin-bottom: 200px;
            }
        }

        :root {
            --background: #1a1a1a;
            --surface: #222222;
            --text-color: #F5F5F5;
            --secondary-text: #A9A9A9;
            --primary-dark: #A6841C;
            --primary-color: #C9A227;
            --border-color: #2A2A2A;
            --hover-color: rgba(255, 255, 255, 0.05);
            --positive-color: #00c853;
            --negative-color: #ff3d3d;
        }
        .app-container {
            margin: 0 auto;
            padding: 27px;
            min-height: 100vh;
        }
        .breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        font-size: 0.875rem;
        }




        .input-group-navbar {
        position: relative;
        margin-bottom: 24px;
        }

        .input-group-navbar input {
        width: 100%;
        padding: 12px 48px 12px 16px;
        background: var(--background);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        font-size: 16px;
        transition: all 0.2s ease;
        color: var(--secondary-text);
        
        }
        .input-group-navbar input::placeholder{
        color: var(--secondary-text);
        }

        .input-group-navbar input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .input-group-navbar button {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        padding: 8px;
        cursor: pointer;
        color: var(--secondary-text);
        }

        .error_message{
        padding: 8px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: rgba(190, 31, 55, 0.201);
        text-align: center;
        color: var(--negative-color);
        border-radius: 5px;
        transition: all 0.7s ease-in-out;
        font-size: 11px;
        font-weight: bold;
        width: 100%;
        letter-spacing: 0.8px;
        display: none;
        }










        @media (min-width: 768px) {
        .breadcrumb {
            font-size: 1rem;
        }
        }

        .breadcrumb a {
        color: var(--text-color);
        text-decoration: none;
        }

        .breadcrumb span {
        color: var(--secondary-text);
        }

        .stats {
        margin-bottom: 2rem;
        }

        .total-amount {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
        }

        @media (min-width: 768px) {
        .total-amount {
            font-size: 3rem;
        }
        }

        .total-earnings {
        font-size: 1.125rem;
        color: var(--secondary-text);
        margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
        .total-earnings {
            font-size: 1.25rem;
        }
        }

        .percentage {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        }

        .percentage::before {
        content: "↓";
        color: var(--secondary-text);
        }

        .no-investments {
        color: var(--secondary-text);
        margin-bottom: 2rem;
        font-size: 0.875rem;
        }

        @media (min-width: 768px) {
        .no-investments {
            font-size: 1rem;
        }
        }

        .table-container {
        width: 100%;
        overflow-x: auto;
        /* background-color: var(--surface); */
        border-radius: 0.5rem;
        position: relative;
        height: 90000vh;
        }

        table {
        width: 100%;
        border-collapse: collapse;
        /* min-width: 320px; */
        }

        th, td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
        }

        th {
        color: var(--text-color);
        font-size: 0.975rem;
        font-weight: 600;
        background-color: var(--surface);
        border-bottom: 1px solid var(--border-color);


        }

        td {
        font-size: 0.875rem;
        }


        .positive {
        color: var(--positive-color);
        }

        .negative {
        color: var(--negative-color);
        }

        .action-button {
                width: 32px;
                height: 32px;
            }
        


        .btn-group {
            position: relative;
            border-radius: 50%;
        }



        
        .dropdown-toggle {
            background: var(--surface);
            border-radius: 50%;
            border: none;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--secondary-text);
        }
        .user-table tr:hover .dropdown-toggle{
            background: var(--base-clr);
        }

        

        .action-dropdown-menu {
            position: absolute;
            right: 20%;
            top: 100%;
            background: var(--surface);
            border-radius: 12px;
            padding: 8px 10px;
            margin-top: 8px;
            min-width: 180px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 200;
        
        }
        
        .action-dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            padding: 12px 16px;
            display: block;
            color: var(--secondary-text);
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.2s ease;
            border-radius: 10px;
            border: none;
            background: transparent;
            width: 100%;
            display: flex;
            cursor: pointer;
        
        }
        
        .dropdown-item:hover {
            background-color: var(--hover-color);
            color: var(--text-color);
        }


        .update_transactions{
            visibility: hidden;
            opacity: 0;
        }
        .action_overlay{
        position: fixed;
        width: 100%;
        max-width: 100vw;
        background: #000000b1;
        backdrop-filter: blur(8px); 
        top: 0;
        width: 100%;
        z-index: 100000;
        height: 100vh;
        transition: all 0.6s ease;
        left: 0;
        right: 0;
        display: grid;
        place-content: center;
        place-items: center;

        }
        .show_action{
        display: block;
        }
        .action_overlay > .wrapper{
        background: var(--surface);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 30px;
        border-radius: 10px;
        justify-content: center;
        width: 420px;

        }
        .action_overlay > .wrapper > header{
        display: flex;
        justify-content: space-between;
        font-size: 20px;
        border-bottom: 1px solid rgba(128, 128, 128, 0.315);
        padding-bottom: 12px;
        cursor: default;
        }
        .action_overlay > .wrapper > header img{
        cursor: pointer;
        background: var(--text-color);
        padding: 2px;
        width: 25px;
        scale: 0.88;
        }
        .action_overlay > .wrapper > main{
        display: flex;
        font-size: 18px;
        border-bottom: 1px solid rgba(128, 128, 128, 0.315);
        padding-bottom: 20px
        }

        .action_overlay > .wrapper > main select{
        width: 200px;
        padding: 0.50rem 0.6rem;
        background-color: var(--background);
        border: 1px solid rgba(128, 128, 128, 0.315);
        border-radius: 4px;
        color: white;
        }

        .action_overlay > .wrapper > main label{
        color: var(--secondary-text);
        font-size: 14px;
        }
        .action_overlay > .wrapper > main input,
        .action_overlay > .wrapper > main textarea{
        background-color: var(--background);
        border: 1px solid rgba(128, 128, 128, 0.315);
        padding: 0.50rem 0.6rem;
        color: var(--text-clr);
        font-size: 1rem;
        transition: all 0.3s ease;
        width: 200px;

        }
        .action_overlay > .wrapper > main textarea{
        max-width: 100%;
        min-width: 100%;
        width: 100%;

        max-height: 180px;
        min-height: 180px;
        height: 180px;
        }
        .action_overlay > .wrapper > main select:focus,
        .action_overlay > .wrapper > main input:focus,
        .action_overlay > .wrapper > main textarea:focus{

        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(94, 99, 255, 0.1);
        }


        .negative_btn,
        .positive_btn{
        padding: 11px 20px;
        border: none;
        outline: none;
        cursor: pointer;
        color: var(--background);
        border-radius: 5px;
        background: var(--text-color);

        }
        .positive_btn{
        background: var(--primary-color);
        color: var(--text-color);


        }

                
        .checkbox-container {
        /* padding: 20px; */
            margin-top: 10px;
        }

        .custom-checkbox {
        display: flex;
        align-items: center;
        position: relative;
        padding-left: 35px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 16px;
        user-select: none;
        color: #fff;
        }

        .custom-checkbox input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
        }

        .checkmark {
            position: absolute;
            left: 0;
            height: 20px;
            width: 20px;
            border: 2px solid #3d4356;
            border-radius: 4px;
            background-color: transparent;
        }

        .custom-checkbox:hover input ~ .checkmark {
            border-color: #4a5169;
        }

        .custom-checkbox input:checked ~ .checkmark {
            background-color: transparent;
            border-color: var(--primary-color);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
        }

        .custom-checkbox .checkmark:after {
        left: 6px;
        bottom: 3px;
        width: 5px;
        height: 10px;
        border: solid var(--primary-color);
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
        }

        .label-text {
        margin-left: 10px;
        color: #b4b9c7;
        }

        @media (max-width: 500px) {
            .action_overlay > .wrapper{
                scale: 0.88;
            }
        }
        @media (max-width: 400px) {
            .action_overlay > .wrapper{
                scale: 0.68;
            }
        }
        @media (max-width: 370px) {
            .action_overlay > .wrapper{
                scale: 0.58;
            }
        }

        

  
    </style>
<body>
    
<header class="dashboard_header">
          <div class="wrapper">
            <div class="logo">
              <div class="image_wrapper">
                  <img src="assets/images/logo.png" width="42" height="42" alt="">
              </div>
            </div>
            
            <div class="icons">
              <ul>
                <h4 style="color: white;"><?php echo htmlspecialchars($user_lname); ?>
                  <span class="login-status"></span>
                </h4>
                  <li class=""><a href="#"><i class="material-icons notification-icon">notifications_none</i></a>
                      <div class="notification_box">
                        <div class="wrapper">
                        <header>
                            <span>Notifications</span>
                            <a href="#" id="clearAll">Clear All</a>
                        </header>

                        <ul id="notificationList">
                            <!-- Notifications will be dynamically loaded here -->
                        </ul>

                        <div class="view_all">
                            <a href="#" id="viewToggleLink" style="display: none;">View All</a>
                        </div>



                  <li><a><i class="material-icons account-icon">account_circle</i></a>
                      <div class="profile_box">
                          <ul>
                              <li>
                                  <a href="profile.php">
                                      <i class="material-icons">person_outline</i>
                                      <span>Profile </span>
                                  </a>
                              </li>
                              <li>
                                  <a href="wallet_page.php">
                                      <i class="material-icons">account_balance_wallet</i>
                                      <span>Wallet</span>
                                  </a>
                              </li>
                              <li>
                                  <a href="logout.php">
                                      <i class="material-icons">logout</i>
                                      <span>Logout</span>
                                  </a>
                              </li>
                          </ul>
                      </div>

                      
                  </li>

                
              </ul>
            </div>
          </div>

          <script>
    // Fetch notifications when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        fetchNotifications();
    });

    let allNotifications = []; // To store all notifications
    let showAll = false; // Flag to track whether to show all notifications or not

    // Function to fetch notifications
    function fetchNotifications() {
        fetch('fetch_notifications.php') // PHP file to fetch notifications from the database
            .then(response => response.json())
            .then(data => {
                const notificationList = document.getElementById('notificationList');
                notificationList.innerHTML = ''; // Clear previous notifications
                allNotifications = data.notifications || [];

                // If there are no notifications, display "No notifications"
                if (allNotifications.length === 0) {
                    notificationList.innerHTML = '<li>No notifications</li>';
                } else {
                    // Display notifications based on whether we're showing all or not
                    const notificationsToDisplay = showAll ? allNotifications : allNotifications.slice(0, 3);
                    
                    // Loop through the notifications to append them
                    notificationsToDisplay.forEach(notification => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <a href="#">
                                <i class="fa fa-user"></i>
                                <span>${notification.message}</span> <!-- Display the message -->
                            </a>
                        `;
                        notificationList.appendChild(li);
                    });

                    // Toggle the "View All" and "View Less" link visibility and text
                    const viewToggleLink = document.getElementById('viewToggleLink');
                    if (allNotifications.length === 3) {
                        viewToggleLink.style.display = 'block';
                        viewToggleLink.textContent = 'View All';
                    } else if (allNotifications.length > 3) {
                        viewToggleLink.style.display = 'block';
                        viewToggleLink.textContent = showAll ? 'View Less' : 'View All';
                    } else {
                        viewToggleLink.style.display = 'block'; // Hide button if there are 3 or fewer notifications
                    }

                    // Make the container scrollable if there are more than 5 notifications
                    if (allNotifications.length > 5) {
                        document.querySelector('.notification-container').style.maxHeight = '300px';
                        document.querySelector('.notification-container').style.overflowY = 'auto'; // Enable scrolling
                    }
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    // Handle the toggle between "View All" and "View Less"
    document.getElementById('viewToggleLink').addEventListener('click', function(e) {
        e.preventDefault(); // Prevent default link behavior
        showAll = !showAll; // Toggle the showAll flag
        fetchNotifications(); // Reload notifications based on the new state
    });

    // Mark notifications as read when the user clicks 'Clear All'
    document.getElementById('clearAll').addEventListener('click', function() {
        fetch('clear_notifications.php', { method: 'POST' })
            .then(response => response.text())
            .then(data => {
                // Refresh the notification list after clearing
                fetchNotifications();
            })
            .catch(error => console.error('Error clearing notifications:', error));
    });
</script>


        <!-- ============ CRYPTO STICKER ============= //--AT THE TOP, BELOW THE NAV BAR--//-->
        <div class="crypto-ticker">
          <div style="height:62px; background-color: #222222; overflow:hidden; box-sizing: border-box; border: 1px solid #282E3B; border-radius: 4px; text-align: right; line-height:14px; block-size:62px; font-size: 12px; font-feature-settings: normal; text-size-adjust: 100%; box-shadow: inset 0 -20px 0 0 #262B38;padding:1px;padding: 0px; margin: 0px; width: 100%;">
              <div style="height:40px; padding:0px; margin:0px; width: 100%;">
                  <iframe src="https://widget.coinlib.io/widget?type=horizontal_v2&amp;theme=dark&amp;pref_coin_id=1505&amp;invert_hover=no" width="100%" height="36px" scrolling="auto" marginwidth="0" marginheight="0" frameborder="0" border="0" style="border:0;margin:0;padding:0;"></iframe>
                  <script>
                      document.addEventListener('contextmenu', (event) => event.preventDefault());
                          document.onkeydown = function(e) {
                              if (e.keyCode == 123 || 
                                  (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) || 
                                  (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) || 
                                  (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0))) {
                              }
                          };
                  </script>
              </div>
              <div style="color: #222222; line-height: 14px; font-weight: 400; font-size: 11px; box-sizing: border-box; padding: 2px 6px; width: 100%; font-family: Verdana, Tahoma, Arial, sans-serif;">
                  <a href="https://coinlib.io" target="_blank" style="font-weight: 500; color: #626B7F; text-decoration:none; font-size:11px"></a>
              </div>
          </div>
        </div>
      </header>



    
        
    <aside class="sidebar">
        <div class="wrapper">


            <div class="sidebar_menu">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="material-icons">dashboard</i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="swap.php">
                            <i class="material-icons">swap_calls</i>
                            <span>Swap</span>
                        </a>
                    </li>

                    <li>
                        <a href="history.php">
                            <i class="material-icons">history</i>
                            <span>History</span>
                        </a>
                    </li>
                    <li>
                        <a href="features.php">
                            <i class="material-icons">widgets</i>
                            <span>Features</span>
                        </a>
                    </li>
                    <li>
                        <a href="market.php">
                            <i class="material-icons">store</i>
                            <span>Market</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar_widgets">
            <div class="wrapper">
                <div class="image">
                <img src="assets/images/crypto-join.png" alt="">
                </div>
                <div class="text">
                <h3>Invest Now!</h3>
                <a href="">
                    Buy and Sell Coins
                </a>
                <br><br>
                </div>
            </div>
            </div>
        </div>
    </aside>

    <main class="main_content">
            <?php
                include 'users_logic.php';
            ?>

            <div class="app-container">
            <section class="stats">
                <h2>Total Amount Invested</h2>
                
                
                <div class="total-amount">$<?= htmlspecialchars(number_format($total_amount, 2)) ?></div> <!-- backend here -->
                
                <div class="total-earnings">Total Earnings: $<?= htmlspecialchars(number_format($total_earnings, 2)) ?></div>
                
                <div class="percentage">0.00 %</div>
                
                <?php if (empty($investments)): ?>
                <div class="no-investments">No investments. Stake your assests and make profit</div>
                <?php endif; ?>   






                <!-- USERS TABLE - HIDDEN -->
               
            </section>

            <!-- TRANSACTIONS / PROFIT / INVESTMENT TABLE  -->
            <div class="table-container" id="investmentTable">
                <table>
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Amount</th>
                            <th>Earnings</th>
                            <th>Change %</th>
                            <th>Due Date</th>
                            <th>Chart</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <!-- STATIC CONTENTS TO BE REPLACE DYNAMICALLY FROM THE BACKEND BELOW -->
              
                    
                    <tbody>
                    <?php if (!empty($investments)): ?>
                        <?php foreach ($investments as $investment): ?>
                        <tr>
                            <td><?= htmlspecialchars($investment['crypto_name']) ?>(<?= htmlspecialchars($investment['symbol']) ?>)</td>
                            <td>$<?= htmlspecialchars($investment['amount']) ?></td>
                            <td class="positive">+$<?= htmlspecialchars($investment['earnings']) ?></td>
                            <td class="positive">+<?= htmlspecialchars($investment['roi']) ?>%</td>
                            <td><?= htmlspecialchars($investment['completed_at']) ?></td>
                            <td><?= htmlspecialchars($investment['last_earning_time']) ?></td>

                            <td data-label="Actions">
                                <div class="btn-group">
                                    <button class="action-button dropdown-toggle" onclick="toggleDropdown(this)">⋮</button>
                                    <div class="action-dropdown-menu">
                                        <button class="dropdown-item take_profit" name="edit" onclick="openTakeProfit('take_profit_<?= htmlspecialchars($user['user_id']) ?>')">Take Profit  </button>
                                        <button class="dropdown-item update" name="delete" onclick="openUpdateTransaction('update_<?= htmlspecialchars($user['user_id']) ?>')">Update</button>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <?php endforeach; ?>
            <?php else: ?>
                <?php endif; ?>


                        <section class="update_transactions action_overlay" id="update_<?= htmlspecialchars($user['user_id']) ?>">
                                <div class="wrapper">
                                    <header>
                                        <h4>
                                            Update Transaction
                                        </h4>
                                        <img class="close_action" src="assets/images/c-close-svgrepo-com.svg" alt="" width="20">
                                    </header>
                                    <main style="display:flex; flex-direction:column; gap:10px;">
                                        <form action="" method="POST">


                                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">


                                            <div style="display: flex; gap: 7px">
                                                <button id="decrease" type="button" class="positive_btn">-</button>
                                                <input type="text" value="0" id="numberInput" style="width: 100%;">
                                                <button id="increase" type="button" class="positive_btn">+</button>

                                               



                                            </div>
                                            <div class="checkbox-container">
                                                <label class="custom-checkbox">

                                                    <!-- CHECKBOX FOR BACKEND FUNCTIONALITY -->
                                                    <input type="checkbox">
                                                    <span class="checkmark"></span>
                                                    
                                                    
                                                    <!-- BACKEND WILL CHANGE THE TEXT HERE BASE ON THE CHECKBOX -->
                                                    <span class="label-text">Can Not Takeprofit</span>
                                                    
                                                </label>
                                                
                                                <label class="custom-checkbox">
                                                    <!-- CHECKBOX FOR BACKEND FUNCTIONALITY -->
                                                    <input type="checkbox">
                                                    <span class="checkmark"></span>
                                                    <!-- BACKEND WILL CHANGE THE TEXT HERE BASE ON THE CHECKBOX -->
                                                    <span class="label-text">Downtrend</span>
                                                </label>
                                            </div>
                                        </form>
                                    </main>
                                    <div style="display: flex; gap:10px;">
                                        <button id="close" class="close_action negative_btn" type="button">Close</button>
                                    </div>
                                </div>
                            </section>
                    </tbody>
                </table>
            </div>
                   
        </div>
    </main>
    <script>

        const decreaseButton = document.getElementById('decrease');
        const increaseButton = document.getElementById('increase');
        const numberInput = document.getElementById('numberInput');
        
        numberInput.addEventListener('input', () => {
        const value = numberInput.value;
        if (isNaN(value) || value === '') {
            numberInput.value = 0;
        } else {
            numberInput.value = parseInt(value, 10);
        }
        });
        
        decreaseButton.addEventListener('click', () => {
        let value = parseInt(numberInput.value, 10) || 0;
        numberInput.value = value - 1;
        });
        
        increaseButton.addEventListener('click', () => {
        let value = parseInt(numberInput.value, 10) || 0;
        numberInput.value = value + 1;
        });

        function openUpdateTransaction(sectionId) {
            const section = document.getElementById(sectionId);
            if (!section) return; 
        
            document.querySelectorAll('.update_transactions').forEach(sec => {
                sec.style.visibility = 'hidden';
                sec.style.opacity = '0';
            });
        
            section.style.visibility = 'visible';
            section.style.opacity = '1';
        }
        
        
        document.querySelectorAll('.close_action').forEach(button => {
            button.addEventListener('click', function () {
                const actionOverlay = this.closest('.action_overlay');
                if (actionOverlay) {
                    actionOverlay.style.visibility = 'hidden'; 
                    actionOverlay.style.opacity = '0';
                }
            });
        });
            
                        const toggleDropdown = (button) => {
                        const $dropdown = $(button).next('.action-dropdown-menu');
                        $('.action-dropdown-menu.show').not($dropdown).removeClass('show');
                        $dropdown.toggleClass('show');
                    };
            
                    $(document).on('click', (event) => {
                        const $target = $(event.target);
                        const isDropdownButton = $target.closest('.dropdown-toggle').length > 0;
                        const isDropdownMenu = $target.closest('.action-dropdown-menu').length > 0;
            
                        if (!isDropdownButton && !isDropdownMenu) {
                            $('.action-dropdown-menu.show').removeClass('show');
                        }
                    });


            let currentPage = 1;
            const usersPerPage = 10;

            const searchInput = document.getElementById('searchInput'); 
            const usersTableBody = document.getElementById('usersTableBody');
            const tableResponsive = document.querySelector('.table-responsive');

            searchInput.addEventListener('input', () => {
                if (searchInput.value.trim() === "") {
                    tableResponsive.style.display = 'none';
                } else {
                    tableResponsive.style.display = 'block';
                }
            });
              

            let filteredUserRows = [];

            const getAllUserRows = () => Array.from(document.querySelectorAll('#usersTableBody tr'));

            const renderUsersForPage = (page) => {
                const startIndex = (page - 1) * usersPerPage;
                const endIndex = startIndex + usersPerPage;

                getAllUserRows().forEach(row => row.style.display = 'none'); // Hide all rows
                filteredUserRows.slice(startIndex, endIndex).forEach(row => row.style.display = ''); // Show filtered rows
            };


            const handleSearch = () => {
                const searchTerm = searchInput.value.toLowerCase();
                const allUserRows = getAllUserRows();

                filteredUserRows = allUserRows.filter((row) => {
                    const name = row.querySelector('.user-info strong')?.textContent.toLowerCase();
                    const email = row.querySelector('.sidetitle')?.textContent.toLowerCase();
                    return name.includes(searchTerm);
                });

                document.getElementById('error').style.display = filteredUserRows.length ? 'none' : 'block';

 ;               currentPage = 1;
                renderUsersForPage(currentPage);
            };

   

       


            searchInput.addEventListener('input', handleSearch);
            document.addEventListener('DOMContentLoaded', () => {
                filteredUserRows = getAllUserRows(); 
                renderUsersForPage(currentPage);
            });

    </script>
        
        
        <footer class="dashboard_footer">
            <div class="wrapper">
                <span>© 2024 <a href="index.php">Creative Fortune</a>All Right Reserved</span>
                <span><a href="#">Purchase Now</a></span>
            </div>
        </footer>
      
        <section class="bottom_nav">
            <div class="wrapper">
                <ul>
                <li>
                    <a href="dashboard.php">
                        <i class="material-icons">dashboard</i>
                        <span>Home</span>
                    </a>
                </li>
            </ul>
            
            <ul>
                <li>
                    <a href="swap.php">
                        <i class="material-icons">swap_calls</i>
                        <span>Swap</span>
                    </a>
                </li>
            </ul>
            
            <ul>
                <li>
                    <a href="history.php">
                        <i class="material-icons">history</i>
                        <span>History</span>
                    </a>
                </li>
            </ul>
            
            <ul>
                <li>
                    <a href="features.php">
                        <i class="material-icons">widgets</i>
                        <span>Features</span>
                    </a>
                </li>
            </ul>

            <ul>
                <li>
                    <a href="market.php">
                        <i class="material-icons">store</i>
                        <span>Market</span>
                    </a>
                </li>
            </ul>
        </div>
    </section>

    
    
    
    
    
    
</body>
<script src="assets/user/javascript/popup.js"></script>
<script src="assets/user/javascript/function.js"></script>

</html>

