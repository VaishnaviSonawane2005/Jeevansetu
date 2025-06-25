<?php
require_once '../includes/admin_check.php';
require_once '../includes/db_connect.php';

$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Build base query
$query = "SELECT u.id, u.name, u.email, u.role, u.created_at, d.status as donor_status 
          FROM users u 
          LEFT JOIN donors d ON u.id = d.user_id 
          WHERE 1=1";
$params = [];

// Apply filters
if ($type === 'donor') {
    $query .= " AND d.user_id IS NOT NULL";
} elseif ($type === 'requester') {
    $query .= " AND u.role = 'requester'";
}

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM ($query) as total";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Add sorting and pagination
$query .= " ORDER BY u.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];

    if ($_GET['action'] === 'delete') {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM donors WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $pdo->commit();
            $_SESSION['success'] = "User deleted successfully!";
            header("Location: manage_users.php");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
        }
    } elseif ($_GET['action'] === 'toggle_donor') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM donors WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $donor = $stmt->fetch();

            if ($donor) {
                $new_status = $donor['status'] ? 0 : 1;
                $stmt = $pdo->prepare("UPDATE donors SET status = ? WHERE user_id = ?");
                $stmt->execute([$new_status, $user_id]);
                $_SESSION['success'] = "Donor status updated!";
            }
            header("Location: manage_users.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating donor status: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Users | JeevanSetu Admin</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #f4f6f9; }
    .container { max-width: 1200px; margin: auto; padding: 2rem; }

    h1 { margin-bottom: 1rem; color: #e63946; }

    .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.2rem; }
    .alert-success { background: #d4edda; color: #155724; }
    .alert-danger { background: #f8d7da; color: #721c24; }

    .admin-filters { background: #fff; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    .filter-group { display: inline-block; margin-right: 1rem; }
    .filter-group input, .filter-group select {
      padding: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      width: 200px;
    }

    .btn {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      display: inline-block;
      font-size: 0.9rem;
      text-decoration: none;
      margin-top: 0.4rem;
    }

    .btn-primary { background: #e63946; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-warning { background: #ffc107; color: black; }
    .btn-danger { background: #dc3545; color: white; }

    .btn-small { font-size: 0.8rem; padding: 6px 10px; }

    .admin-table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      overflow: hidden;
    }

    .admin-table th, .admin-table td {
      padding: 1rem;
      border-bottom: 1px solid #eee;
      text-align: left;
    }

    .admin-table th {
      background: #f1f1f1;
      font-weight: bold;
    }

    .status-badge {
      padding: 5px 12px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.85rem;
      display: inline-block;
    }

    .active { background: #28a745; color: white; }
    .inactive { background: #ffc107; color: black; }
    .none { background: #6c757d; color: white; }

    .action-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
    }

    .pagination {
      margin-top: 2rem;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 1rem;
    }
  </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<main class="container">
  <h1>Manage Users</h1>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
  <?php endif; ?>

  <div class="admin-filters">
    <form method="get">
      <div class="filter-group">
        <label for="type">User Type</label><br>
        <select id="type" name="type">
          <option value="">All</option>
          <option value="donor" <?= $type === 'donor' ? 'selected' : '' ?>>Donors</option>
          <option value="requester" <?= $type === 'requester' ? 'selected' : '' ?>>Requesters</option>
        </select>
      </div>
      <div class="filter-group">
        <label for="search">Search</label><br>
        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Name or Email">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply</button>
      <a href="manage_users.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Reset</a>
    </form>
  </div>

  <table class="admin-table">
    <thead>
      <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Donor Status</th><th>Registered</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($users)): ?>
      <tr><td colspan="7">No users found.</td></tr>
    <?php else: foreach ($users as $user): ?>
      <tr>
        <td><?= $user['id'] ?></td>
        <td><?= htmlspecialchars($user['name']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= ucfirst($user['role']) ?></td>
        <td>
          <?php if ($user['donor_status'] !== null): ?>
            <span class="status-badge <?= $user['donor_status'] ? 'active' : 'inactive' ?>">
              <?= $user['donor_status'] ? 'Active' : 'Inactive' ?>
            </span>
          <?php else: ?>
            <span class="status-badge none">Not a donor</span>
          <?php endif; ?>
        </td>
        <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
        <td>
          <div class="action-buttons">
            <a href="user_details.php?id=<?= $user['id'] ?>" class="btn btn-small btn-secondary"><i class="fas fa-eye"></i> View</a>
            <?php if ($user['donor_status'] !== null): ?>
              <a href="manage_users.php?action=toggle_donor&id=<?= $user['id'] ?>" class="btn btn-small <?= $user['donor_status'] ? 'btn-warning' : 'btn-success' ?>">
                <i class="fas fa-exchange-alt"></i> <?= $user['donor_status'] ? 'Deactivate' : 'Activate' ?>
              </a>
            <?php endif; ?>
            <a href="manage_users.php?action=delete&id=<?= $user['id'] ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
              <i class="fas fa-trash"></i> Delete
            </a>
          </div>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>

  <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&type=<?= $type ?>&search=<?= urlencode($search) ?>" class="btn btn-secondary">&laquo; Previous</a>
      <?php endif; ?>
      <span>Page <?= $page ?> of <?= $total_pages ?></span>
      <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page+1 ?>&type=<?= $type ?>&search=<?= urlencode($search) ?>" class="btn btn-secondary">Next &raquo;</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</main>

<?php include '../components/footer.php'; ?>
</body>
</html>
