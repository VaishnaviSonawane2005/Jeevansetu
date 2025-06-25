<?php if (isset($_SESSION['user_id'])): ?>
    <nav class="user-nav">
        <div class="container">
            <ul>
                <?php if ($_SESSION['role'] === 'donor'): ?>
                    <li><a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="register_donation.php" class="<?= $current_page === 'register_donation.php' ? 'active' : '' ?>">Donate</a></li>
                    <li><a href="donation_history.php" class="<?= $current_page === 'donation_history.php' ? 'active' : '' ?>">History</a></li>
                    <li><a href="update_profile.php" class="<?= $current_page === 'update_profile.php' ? 'active' : '' ?>">Profile</a></li>
                <?php else: ?>
                    <li><a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="request_form.php" class="<?= $current_page === 'request_form.php' ? 'active' : '' ?>">New Request</a></li>
                    <li><a href="request_status.php" class="<?= $current_page === 'request_status.php' ? 'active' : '' ?>">My Requests</a></li>
                    <li><a href="search_donors.php" class="<?= $current_page === 'search_donors.php' ? 'active' : '' ?>">Search Donors</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
<?php endif; ?>