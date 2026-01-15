<?php
/**
 * Department Management
 * Admin page for managing departments
 */

require_once '../../includes/session.php';
require_once '../../config/db_connect.php';
require_once '../../includes/functions.php';

// Require login and non-employee access
require_login();
require_not_employee();

$errors = [];
$success = '';

// Handle department creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        
        if (empty($name)) {
            $errors[] = 'Department name is required';
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                
                log_activity($_SESSION['user_id'], 'Created department', $name);
                redirect_with_message('manage.php', 'Department created successfully!', 'success');
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors[] = 'Department name already exists';
                } else {
                    $errors[] = 'Failed to create department';
                    error_log("Create department error: " . $e->getMessage());
                }
            }
        }
    }
}

// Handle department update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $name = sanitize_input($_POST['name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        
        if (empty($name)) {
            $errors[] = 'Department name is required';
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE departments SET name = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $description, $id]);
                
                log_activity($_SESSION['user_id'], 'Updated department', $name);
                redirect_with_message('manage.php', 'Department updated successfully!', 'success');
            } catch (PDOException $e) {
                $errors[] = 'Failed to update department';
                error_log("Update department error: " . $e->getMessage());
            }
        }
    }
}

// Handle department deletion
if (isset($_GET['delete']) && is_admin()) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        
        log_activity($_SESSION['user_id'], 'Deleted department', "ID: $id");
        redirect_with_message('manage.php', 'Department deleted successfully!', 'success');
    } catch (PDOException $e) {
        $errors[] = 'Failed to delete department. It may have employees assigned.';
        error_log("Delete department error: " . $e->getMessage());
    }
}

// Fetch all departments
try {
    $stmt = $pdo->query("
        SELECT d.*, COUNT(e.id) as employee_count 
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id
        GROUP BY d.id
        ORDER BY d.name
    ");
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Fetch departments error: " . $e->getMessage());
    $departments = [];
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin_dashboard.css">
</head>
<body class="dashboard-page admin-page">
    
    <?php include '../includes/admin_header.php'; ?>
    
    <div class="dashboard-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <main class="dashboard-content">
            <div class="page-header">
                <h1>🏢 Department Management</h1>
                <p>Create and manage company departments</p>
            </div>
            
            <?php display_flash_message(); ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Create Department Form -->
            <div class="card">
                <h2>Create New Department</h2>
                <form method="POST" class="form-horizontal">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Department Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Create Department</button>
                    </div>
                </form>
            </div>
            
            <!-- Departments List -->
            <div class="card">
                <h2>All Departments</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Department Name</th>
                                <th>Description</th>
                                <th>Employees</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($departments)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No departments found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><?php echo $dept['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($dept['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($dept['description'] ?: '-'); ?></td>
                                        <td><span class="badge badge-info"><?php echo $dept['employee_count']; ?></span></td>
                                        <td><?php echo date('M d, Y', strtotime($dept['created_at'])); ?></td>
                                        <td>
                                            <button onclick="editDepartment(<?php echo $dept['id']; ?>, '<?php echo addslashes($dept['name']); ?>', '<?php echo addslashes($dept['description']); ?>')" class="btn btn-sm btn-primary">Edit</button>
                                            <?php if (is_admin() && $dept['employee_count'] == 0): ?>
                                                <a href="?delete=<?php echo $dept['id']; ?>" onclick="return confirm('Delete this department?')" class="btn btn-sm btn-danger">Delete</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Edit Department Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Department</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="form-group">
                    <label for="edit_name">Department Name *</label>
                    <input type="text" id="edit_name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Department</button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../../assets/js/main.js"></script>
    <script>
        function editDepartment(id, name, description) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description || '';
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>
