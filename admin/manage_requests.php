<?php
require_once '../includes/admin_check.php';
require_once '../includes/db_connect.php';

$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// Build base query
$query = "SELECT r.*, u.name as requester_name FROM requests r 
          JOIN users u ON r.user_id = u.id 
          WHERE 1=1";
$params = [];

if (in_array($status, ['pending', 'matched', 'completed', 'cancelled'])) {
    $query .= " AND r.status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $query .= " AND (r.patient_name LIKE ? OR r.hospital_city LIKE ? OR r.blood_group LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$count_query = "SELECT COUNT(*) FROM ($query) as total";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_requests = $stmt->fetchColumn();
$total_pages = ceil($total_requests / $limit);

$query .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $request_id = $_GET['id'];
    
    if ($_GET['action'] === 'delete') {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM request_matches WHERE request_id = ?")->execute([$request_id]);
            $pdo->prepare("DELETE FROM requests WHERE id = ?")->execute([$request_id]);
            $pdo->commit();
            $_SESSION['success'] = "Request deleted successfully!";
            header("Location: manage_requests.php");
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Error deleting request: " . $e->getMessage();
        }
    } elseif (in_array($_GET['action'], ['complete', 'cancel', 'reinstate'])) {
        $new_status = '';
        if ($_GET['action'] === 'complete') $new_status = 'completed';
        elseif ($_GET['action'] === 'cancel') $new_status = 'cancelled';
        elseif ($_GET['action'] === 'reinstate') $new_status = 'pending';

        try {
            $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?")->execute([$new_status, $request_id]);
            $_SESSION['success'] = "Request status updated to " . $new_status . "!";
            header("Location: manage_requests.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating request status: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Requests | JeevanSetu Admin</title>
  <link rel="stylesheet" href="../css/style.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
        body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      background: #f8f9fa;
    }
    .container {
      padding: 2rem;
      margin-top: 80px;
    }
    h1 {
       margin-bottom: 1rem; color:rgba(1, 2, 65, 0.92);
    }
    .alert {
      padding: 1rem;
      border-radius: 5px;
      margin-bottom: 1rem;
    }
    .alert-success {
      background: #d4edda;
      color: #155724;
    }
    .alert-danger {
      background: #f8d7da;
      color: #721c24;
    }
    .admin-filters {
      background: #fff;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .filter-form {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      align-items: flex-end;
    }
    .filter-group {
      flex: 1;
      min-width: 200px;
    }
    select, input[type="text"] {
      width: 100%;
      padding: 0.6rem;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    .btn {
      padding: 0.6rem 1.2rem;
      border: none;
      border-radius: 5px;
      font-weight: 500;
      cursor: pointer;
      transition: 0.2s;
    }
    .btn-primary { background: #1d3557; color: #fff; }
    .btn-secondary { background: #adb5bd; color: #fff; }
    .btn-success { background: #28a745; color: #fff; }
    .btn-warning { background: #ffc107; color: #212529; }
    .btn-danger { background: #dc3545; color: #fff; }
    .btn-small {
      padding: 0.3rem 0.6rem;
      font-size: 0.8rem;
    }
    .admin-table-container {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      overflow-x: auto;
    }
    table.admin-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.95rem;
    }
    table th, table td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #dee2e6;
    }
    table th {
      background: #f1f3f5;
      font-weight: 600;
    }
    .status-badge {
      padding: 0.3rem 0.7rem;
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: 600;
      display: inline-block;
      text-transform: capitalize;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-matched { background: #d1ecf1; color: #0c5460; }
    .status-completed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    .action-buttons {
      display: flex;
      gap: 0.3rem;
      flex-wrap: wrap;
    }
    .pagination {
      margin-top: 1.5rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .wrapper {
      display: flex;
      flex-direction: row;
    }

    .main {
      margin-left: 260px;
      padding: 2rem;
      margin-top: 70px;
      width: 100%;
      transition: all 0.3s ease;
      min-height: calc(100vh - 70px);
    }

    .main.full {
      margin-left: 0;
    }

    @media (max-width: 992px) {
      .main {
        margin-left: 0;
      }
    }

    .btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  font-size: 16px;
  color: #fff;
  text-decoration: none;
  transition: background 0.2s ease;
}

.btn-icon i {
  margin: 0;
}

.btn-primary {
  background-color: #1d3557;
}
.btn-success {
  background-color: #28a745;
}
.btn-warning {
  background-color: #ffc107;
  color: #212529;
}
.btn-danger {
  background-color: #dc3545;
}

.btn-icon:hover {
  opacity: 0.85;
}

    
  </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<div class="wrapper">
  <main class="main" id="main">
    <h1>Manage Requests</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="admin-filters">
      <form method="get" class="filter-form">
        <div class="filter-group">
          <label for="status">Filter by Status:</label>
          <select id="status" name="status">
            <option value="">All Requests</option>
            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="matched" <?= $status === 'matched' ? 'selected' : '' ?>>Matched</option>
            <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
          </select>
        </div>

        <div class="filter-group">
          <label for="search">Search:</label>
          <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Patient, City or Blood Group">
        </div>

        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="manage_requests.php" class="btn btn-secondary">Reset</a>
      </form>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Patient</th>
            <th>Blood Group</th>
            <th>Hospital</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($requests)): ?>
            <tr><td colspan="8" class="text-center">No requests found</td></tr>
          <?php else: ?>
            <?php foreach ($requests as $request): ?>
              <tr>
                <td><?= $request['id'] ?></td>
                <td><?= ucfirst($request['type']) ?></td>
                <td><?= htmlspecialchars($request['patient_name']) ?></td>
                <td><?= $request['blood_group'] ?></td>
                <td><?= htmlspecialchars($request['hospital_name']) ?>, <?= htmlspecialchars($request['hospital_city']) ?></td>
                <td><span class="status-badge <?= $request['status'] ?>"><?= ucfirst($request['status']) ?></span></td>
                <td><?= date('d M Y', strtotime($request['created_at'])) ?></td>
               <td>
  <div class="action-buttons">
    <!-- View -->
    <a href="request_details.php?id=<?= $request['id'] ?>" class="btn-icon btn-primary" title="View Request">
      <i class="fas fa-eye"></i>
    </a>

    <?php if (in_array($request['status'], ['pending', 'matched'])): ?>
      <!-- Complete -->
      <a href="manage_requests.php?action=complete&id=<?= $request['id'] ?>" class="btn-icon btn-success" title="Mark as Completed" onclick="return confirm('Mark this request as completed?')">
        <i class="fas fa-check-circle"></i>
      </a>
      <!-- Cancel -->
      <a href="manage_requests.php?action=cancel&id=<?= $request['id'] ?>" class="btn-icon btn-warning" title="Cancel Request" onclick="return confirm('Cancel this request?')">
        <i class="fas fa-times-circle"></i>
      </a>
    <?php elseif ($request['status'] === 'cancelled'): ?>
      <!-- Reinstate -->
      <a href="manage_requests.php?action=reinstate&id=<?= $request['id'] ?>" class="btn-icon btn-primary" title="Reinstate Request" onclick="return confirm('Reinstate this request?')">
        <i class="fas fa-undo"></i>
      </a>
    <?php endif; ?>

    <!-- Delete -->
    <a href="manage_requests.php?action=delete&id=<?= $request['id'] ?>" class="btn-icon btn-danger" title="Delete Request" onclick="return confirm('Are you sure you want to delete this request?')">
      <i class="fas fa-trash-alt"></i>
    </a>
  </div>
</td>



              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="manage_requests.php?page=<?= $page-1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" class="btn">Previous</a>
        <?php endif; ?>
        <span>Page <?= $page ?> of <?= $total_pages ?></span>
        <?php if ($page < $total_pages): ?>
          <a href="manage_requests.php?page=<?= $page+1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>" class="btn">Next</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php include '../components/footer.php'; ?>
</body>
</html>
