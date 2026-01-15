<?php
/**
 * Employee List
 * View all employees with search and pagination
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login and proper role
require_login();
require_role(['Admin', 'HR', 'Manager']);

// Handle search
$search = sanitize_input($_GET['search'] ?? '');
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE u.first_name LIKE ? OR u.last_name LIKE ? OR e.employee_id LIKE ? OR e.position LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param, $search_param];
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM employees e 
                  INNER JOIN users u ON e.user_id = u.id $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_employees = $stmt->fetch()['total'];
    $total_pages = ceil($total_employees / ITEMS_PER_PAGE);
    
    // Get employees
    $sql = "SELECT e.*, u.first_name, u.last_name, u.email, u.phone, u.user_status, d.name as department_name
            FROM employees e
            INNER JOIN users u ON e.user_id = u.id
            LEFT JOIN departments d ON e.department_id = d.id
            $where_clause
            ORDER BY e.created_at DESC
            LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $employees = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Employee list error: " . $e->getMessage());
    $employees = [];
    $total_employees = 0;
    $total_pages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body class="dashboard-page">
    
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>Employees</h1>
                <p>Manage employee records</p>
            </div>
            
            <?php display_flash_message(); ?>
            
            <!-- Search and Actions -->
            <div class="table-controls">
                <form method="GET" action="" class="search-form">
                    <input type="text" name="search" placeholder="Search employees..." 
                           value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                    <button type="submit" class="btn btn-secondary">🔍 Search</button>
                    <?php if ($search): ?>
                        <a href="list.php" class="btn btn-outline">Clear</a>
                    <?php endif; ?>
                </form>
                
                <?php if (has_role(['Admin', 'HR'])): ?>
                <a href="add.php" class="btn btn-primary">➕ Add Employee</a>
                <?php endif; ?>
            </div>
            
            <!-- Employees Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($employees)): ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <?php echo $search ? 'No employees found matching your search.' : 'No employees found.'; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employees as $emp): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($emp['employee_id']); ?></td>
                                    <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($emp['position']); ?></td>
                                    <td><?php echo htmlspecialchars($emp['email']); ?></td>
                                    <td><?php echo htmlspecialchars($emp['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $emp['user_status'] === 'Active' ? 'success' : 'danger'; ?>">
                                            <?php echo htmlspecialchars($emp['user_status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="view.php?id=<?php echo $emp['id']; ?>" class="btn btn-sm btn-info" title="View">👁️</a>
                                        <?php if (has_role(['Admin', 'HR'])): ?>
                                        <a href="edit.php?id=<?php echo $emp['id']; ?>" class="btn btn-sm btn-warning" title="Edit">✏️</a>
                                        <a href="delete.php?id=<?php echo $emp['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this employee?')">🗑️</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="btn btn-secondary">« Previous</a>
                <?php endif; ?>
                
                <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="btn btn-secondary">Next »</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
