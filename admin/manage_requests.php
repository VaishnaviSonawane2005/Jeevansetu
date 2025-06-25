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

// Apply filters
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

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM ($query) as total";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_requests = $stmt->fetchColumn();
$total_pages = ceil($total_requests / $limit);

// Add sorting and pagination
$query .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Handle request actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $request_id = $_GET['id'];
    
    if ($_GET['action'] === 'delete') {
        try {
            $pdo->beginTransaction();
            
            // Delete matches first
            $stmt = $pdo->prepare("DELETE FROM request_matches WHERE request_id = ?");
            $stmt->execute([$request_id]);
            
            // Then delete the request
            $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
            $stmt->execute([$request_id]);
            
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
            $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $request_id]);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests | JeevanSetu Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../components/admin_header.php'; ?>
    
    <main class="container">
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
                        <tr>
                            <td colspan="8" class="text-center">No requests found</td>
                        </tr>
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
                                        <a href="request_details.php?id=<?= $request['id'] ?>" class="btn btn-small">View</a>
                                        
                                        <?php if ($request['status'] === 'pending' || $request['status'] === 'matched'): ?>
                                            <a href="manage_requests.php?action=complete&id=<?= $request['id'] ?>" class="btn btn-small btn-success" onclick="return confirm('Mark this request as completed?')">Complete</a>
                                            <a href="manage_requests.php?action=cancel&id=<?= $request['id'] ?>" class="btn btn-small btn-warning" onclick="return confirm('Cancel this request?')">Cancel</a>
                                        <?php elseif ($request['status'] === 'cancelled'): ?>
                                            <a href="manage_requests.php?action=reinstate&id=<?= $request['id'] ?>" class="btn btn-small btn-primary" onclick="return confirm('Reinstate this request?')">Reinstate</a>
                                        <?php endif; ?>
                                        
                                        <a href="manage_requests.php?action=delete&id=<?= $request['id'] ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this request?')">Delete</a>
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
    
    <?php include '../components/footer.php'; ?>
</body>
</html>