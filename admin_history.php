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
        <link rel="stylesheet" href="assets/css/history.css">
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/table-wallet.css">
        <link rel="stylesheet" href="assets/css/mediaquery.css">
        <link rel="stylesheet" href="assets/css/main-mediaquery.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/5.4.5/css/swiper.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    
    
    
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
          <div style="height:62px; background-color: #1e293b; overflow:hidden; box-sizing: border-box; border: 1px solid #282E3B; border-radius: 4px; text-align: right; line-height:14px; block-size:62px; font-size: 12px; font-feature-settings: normal; text-size-adjust: 100%; box-shadow: inset 0 -20px 0 0 #262B38;padding:1px;padding: 0px; margin: 0px; width: 100%;">
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
              <div style="color: #1e293b; line-height: 14px; font-weight: 400; font-size: 11px; box-sizing: border-box; padding: 2px 6px; width: 100%; font-family: Verdana, Tahoma, Arial, sans-serif;">
                  <a href="https://coinlib.io" target="_blank" style="font-weight: 500; color: #626B7F; text-decoration:none; font-size:11px"></a>
              </div>
          </div>
        </div>
      </header>

      <?php

        include 'admin_confirm_decline.php'                  

      ?>

    
        
    <aside class="sidebar">
        <div class="wrapper">


            <div class="sidebar_menu">
                <ul>
                    <li>
                        <a href="admin_dashboard.php">
                            <i class="material-icons">dashboard</i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_swap.php">
                            <i class="material-icons">swap_calls</i>
                            <span>Swap</span>
                        </a>
                    </li>

                    <li>
                        <a href="users.php">
                            <i class="fa fa-user-o"></i>
                            <span>Users</span>
                        </a>
                    </li>

                    <li>
                        <a href="admin_history.php">
                            <i class="material-icons">history</i>
                            <span>History</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_features.php">
                            <i class="material-icons">widgets</i>
                            <span>Features</span>
                        </a>
                    </li>
                    <li>
                        <a href="admin_market.php">
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

            <div class="app-container">
                <header class="app-header">
                    <h1>Latest Transactions</h1>



                    <!-- APPEARS ONLY ON ADMIN ACCOUNT -->
                    <div class="dropdown">
                        <button class="dropdown-button">Sort</button>
                        <div class="dropdown-menu">
                            <button type="submit" class="dropdown-item">All</button>
                            <button type="submit" class="dropdown-item">Pending</button>
                            <button type="submit" class="dropdown-item">Withdrawal</button>
                            <button type="submit" class="dropdown-item">Deposit</button>
                            <button type="submit" class="dropdown-item">Default</button>
                        </div>
                    </div>



                    
                </header>

                <div class="transactions">
                    
                  <?php
                  
                  displayTransactions();
                  ?>          
                          
                </div>
            </div>

        </main>

      
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
                    <a href="admin_dashboard.php">
                        <i class="material-icons">dashboard</i>
                        <span>Home</span>
                    </a>
                </li>
            </ul>

            <ul>
                <li>
                    <a href="admin_swap.php">
                        <i class="material-icons">swap_calls</i>
                        <span>Swap</span>
                    </a>
                </li>
            </ul>

            <ul>
                <li>
                    <a href="admin_history.php">
                        <i class="material-icons">history</i>
                        <span>History</span>
                    </a>
                </li>
            </ul>

            <ul>
                <li>
                    <a href="admin_features.php">
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
    



      <!-- CONFIRM / DECLINE TRASACTION  (POP-UP BOX)-->
      <!-- <section class="confirm_decline">
        <div class="bg_overlay">
            <div class="popup_box">
            <div class="wrapper">
                <header>
                <span>
                    Confirm/Decline   
                    <samp class="firstname"></samp>
                    <samp class="lastname"></samp>
                    transaction
                </span>
                <i class="material-icons close_confirm_decline">close</i>
                </header>
                <main class="question">
                <span>Are you sure you want to confirm/decline this transaction?</span>
                </main>
                <footer class="confirm_decline_buttons">
                <div class="wrapper">
                    <button id="close" class="close" type="button">Close</button>
                    <button onclick="confirmTransaction()" name="confirm" id="confirm" class="btn confirm" type="button">Confirm</button>
                    <button onclick="declineTransaction()" name="decline" id="decline" class="btn decline" type="button">Decline</button>
                    
                </div>
                </footer>
            </div>
            </div>
        </div>
        <div class="toast-container" id="toastContainer"></div>
        </section> -->

     
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                    const popupTriggers = document.querySelectorAll('.popup_trigger');
                    const confirmDecline = document.querySelector('.confirm_decline');
                    const popupBox = document.querySelector('.popup_box');
                    const closeButtons = document.querySelectorAll('.close_confirm_decline, #close');
                    const toastContainer = document.getElementById('toastContainer');

                    let currentTransactionId = null;

                    // Open popup on button click
                    popupTriggers.forEach(trigger => {
                        trigger.addEventListener('click', function () {
                            const status = this.getAttribute('data-status').trim().toLowerCase();
                            currentTransactionId = this.getAttribute('data-transaction-id');
                            const firstname = this.getAttribute('data-firstname');
                            const lastname = this.getAttribute('data-lastname');

                            if (status === 'pending') {
                                confirmDecline.querySelector('.firstname').textContent = firstname + " ";
                                confirmDecline.querySelector('.lastname').textContent = lastname + " ";
                                confirmDecline.style.visibility = 'visible';
                                confirmDecline.style.opacity = '1';
                                popupBox.style.opacity = '1';
                                popupBox.style.transform = 'translateY(0)';
                            } else {
                                alert('This transaction cannot be confirmed or declined because it is not pending.');
                            }
                        });
                    });

                    // Close popup on button click
                    closeButtons.forEach(btn => {
                        btn.addEventListener('click', function () {
                            confirmDecline.style.visibility = 'hidden';
                            confirmDecline.style.opacity = '0';
                            popupBox.style.opacity = '0';
                            popupBox.style.transform = 'translateY(-90%)';
                        });
                    });

                    // Show toast message
                    function showToast(type, message) {
                        const toast = document.createElement('div');
                        toast.className = `toast ${type}`;
                        toast.innerHTML = `
                            <div class="toast-icon">${type === 'success' ? '✓' : '✕'}</div>
                            <div class="toast-content">
                                <div class="toast-title">${type === 'success' ? 'Success' : 'Error'}</div>
                                <div class="toast-message">${message}</div>
                            </div>
                            <button class="close-btn" onclick="this.parentElement.remove()">×</button>
                        `;
                        toastContainer.appendChild(toast);
                        setTimeout(() => toast.remove(), 4000);
                    }

                    // Confirm transaction
                    window.confirmTransaction = function () {
                        
                        if (!currentTransactionId) return showToast('error', 'No transaction selected.');

                        fetch('admin_confirm_decline.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ transaction_id: currentTransactionId, action: 'confirm' })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showToast('success', 'Transaction confirmed successfully.');
                                const transactionCard = document.querySelector(`[data-transaction-id="${currentTransactionId}"]`);
                                if (transactionCard) {
                                    const statusBadge = transactionCard.querySelector('.status-badge');
                                    statusBadge.textContent = 'Completed';
                                }
                            } else {
                                showToast('error', data.message || 'Failed to confirm transaction.');
                            }
                        })
                        .catch(() => showToast('error', 'An error occurred while confirming the transaction.'))
                        .finally(() => closePopup());
                    };

                    // Decline transaction
                    window.declineTransaction = function () {
                        if (!currentTransactionId) return showToast('error', 'No transaction selected.');

                        fetch('admin_confirm_decline.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ transaction_id: currentTransactionId, action: 'decline' })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showToast('success', 'Transaction declined successfully.');
                                const transactionCard = document.querySelector(`[data-transaction-id="${currentTransactionId}"]`);
                                if (transactionCard) {
                                    const statusBadge = transactionCard.querySelector('.status-badge');
                                    statusBadge.textContent = 'Failed';
                                }
                            } else {
                                showToast('error', data.message || 'Failed to decline transaction.');
                            }
                        })
                        .catch(() => showToast('error', 'An error occurred while declining the transaction.'))
                        .finally(() => closePopup());
                    };

                    // Close popup helper
                    function closePopup() {
                        confirmDecline.style.visibility = 'hidden';
                        confirmDecline.style.opacity = '0';
                        popupBox.style.opacity = '0';
                        popupBox.style.transform = 'translateY(-90%)';
                    }
            });
        </script>
</body>
<script src="assets/user/javascript/popup.js"></script>
<script src="assets/user/javascript/function.js"></script>

</html>

