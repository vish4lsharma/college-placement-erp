<?php
require_once '../includes/auth.php';
Auth::requireRole('Developer');

$current_user = Auth::getCurrentUser();
require_once '../config/database.php';

// Get statistics
$stats = [
    'total_colleges' => 0,
    'total_superadmins' => 0,
    'total_users' => 0,
    'active_sessions' => 0
];

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM colleges WHERE is_active = 1");
    $stats['total_colleges'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users u JOIN user_roles ur ON u.role_id = ur.role_id WHERE ur.role_name = 'SuperAdmin' AND u.is_active = 1");
    $stats['total_superadmins'] = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stats['total_users'] = $stmt->fetch()['count'];
    
    $stats['active_sessions'] = 1; // Simplified for now
} catch (Exception $e) {
    error_log("Error fetching stats: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard - Campus Placement System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">Campus Placement System</a>
                <ul class="nav-links">
                    <li><a href="#">Welcome, <?php echo htmlspecialchars($current_user['full_name']); ?></a></li>
                    <li><a href="../api/logout.php" class="btn btn-outline btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="dashboard">
        <div class="dashboard-sidebar">
            <div class="sidebar">
                <ul class="sidebar-nav">
                    <li><a href="#" class="active">Dashboard</a></li>
                    <li><a href="#colleges" onclick="showSection('colleges')">Manage Colleges</a></li>
                    <li><a href="#superadmins" onclick="showSection('superadmins')">Super Admins</a></li>
                    <li><a href="#system" onclick="showSection('system')">System Settings</a></li>
                    <li><a href="#logs" onclick="showSection('logs')">Activity Logs</a></li>
                </ul>
            </div>
        </div>

        <div class="dashboard-content">
            <div id="overview-section">
                <h1>Developer Dashboard</h1>
                <p class="text-secondary">Manage the entire placement system infrastructure</p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_colleges']; ?></div>
                        <div class="stat-label">Active Colleges</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_superadmins']; ?></div>
                        <div class="stat-label">Super Admins</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['active_sessions']; ?></div>
                        <div class="stat-label">Active Sessions</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Activities</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Activity</th>
                                            <th>User</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo date('H:i'); ?></td>
                                            <td>System Login</td>
                                            <td><?php echo htmlspecialchars($current_user['full_name']); ?></td>
                                            <td><span class="badge badge-success">Success</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Quick Actions</h3>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary mb-2" onclick="showAddCollegeModal()">Add New College</button>
                                <button class="btn btn-secondary mb-2" onclick="showSection('system')">System Settings</button>
                                <button class="btn btn-warning mb-2" onclick="exportSystemData()">Export Data</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colleges Section -->
            <div id="colleges-section" class="d-none">
                <div class="d-flex justify-between align-center mb-4">
                    <h1>Manage Colleges</h1>
                    <button class="btn btn-primary" onclick="showAddCollegeModal()">Add New College</button>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <input type="text" id="college-search" class="form-control" placeholder="Search colleges..." style="max-width: 300px;">
                    </div>
                    <div class="table-responsive">
                        <table id="colleges-table" class="table table-striped data-table" data-search="college-search" data-pagination>
                            <thead>
                                <tr>
                                    <th data-sort="name">College Name</th>
                                    <th data-sort="location">Location</th>
                                    <th data-sort="superadmin">Super Admin</th>
                                    <th data-sort="status">Status</th>
                                    <th data-sort="created">Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="colleges-tbody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Super Admins Section -->
            <div id="superadmins-section" class="d-none">
                <div class="d-flex justify-between align-center mb-4">
                    <h1>Super Admin Management</h1>
                    <button class="btn btn-primary" onclick="showAddSuperAdminModal()">Create Super Admin</button>
                </div>
                
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>College</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="superadmins-tbody">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- System Settings Section -->
            <div id="system-section" class="d-none">
                <h1>System Settings</h1>
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">General Settings</h3>
                            </div>
                            <form id="system-settings-form">
                                <div class="form-group">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" class="form-control" value="Campus Placement Management System">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Admin Email</label>
                                    <input type="email" class="form-control" value="admin@placement.com">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Max File Size (MB)</label>
                                    <input type="number" class="form-control" value="5">
                                </div>
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Database Status</h3>
                            </div>
                            <div class="p-3">
                                <div class="mb-3">
                                    <strong>Connection:</strong> <span class="text-success">Active</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Database:</strong> Placement_db
                                </div>
                                <div class="mb-3">
                                    <strong>Tables:</strong> 11
                                </div>
                                <button class="btn btn-warning" onclick="backupDatabase()">Backup Database</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Logs Section -->
            <div id="logs-section" class="d-none">
                <h1>Activity Logs</h1>
                <div class="card">
                    <div class="card-header">
                        <input type="text" id="logs-search" class="form-control" placeholder="Search logs..." style="max-width: 300px;">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                    <td><?php echo htmlspecialchars($current_user['full_name']); ?></td>
                                    <td>Login</td>
                                    <td>Developer login</td>
                                    <td><?php echo $_SERVER['REMOTE_ADDR'] ?? 'Unknown'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add College Modal -->
    <div id="add-college-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New College</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form id="add-college-form" data-validate>
                <div class="form-group">
                    <label class="form-label">College Name</label>
                    <input type="text" name="college_name" class="form-control" required data-field="College Name">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control form-textarea" required data-field="Address"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" class="form-control" required data-field="Contact Email">
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Phone</label>
                    <input type="tel" name="contact_phone" class="form-control" required data-field="Contact Phone">
                </div>
                <div class="form-group">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control">
                </div>
                <div class="d-flex justify-between">
                    <button type="button" class="btn btn-secondary" onclick="Modal.hide('add-college-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add College</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Super Admin Modal -->
    <div id="add-superadmin-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create Super Admin</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form id="add-superadmin-form" data-validate>
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required data-field="Full Name">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required data-field="Email">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" required data-field="Phone">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required data-field="Password">
                </div>
                <div class="form-group">
                    <label class="form-label">Assign to College</label>
                    <select name="college_id" class="form-control form-select" required data-field="College">
                        <option value="">Select College</option>
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                <div class="d-flex justify-between">
                    <button type="button" class="btn btn-secondary" onclick="Modal.hide('add-superadmin-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Super Admin</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/main.js"></script>
    <script>
        // Section management
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('[id$="-section"]').forEach(section => {
                section.classList.add('d-none');
            });
            
            // Remove active class from all nav links
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionName + '-section').classList.remove('d-none');
            
            // Add active class to clicked nav link
            event.target.classList.add('active');
            
            // Load section data
            if (sectionName === 'colleges') {
                loadColleges();
            } else if (sectionName === 'superadmins') {
                loadSuperAdmins();
            }
        }

        // Modal functions
        function showAddCollegeModal() {
            Modal.show('add-college-modal');
        }

        function showAddSuperAdminModal() {
            loadCollegeOptions();
            Modal.show('add-superadmin-modal');
        }

        // Data loading functions
        async function loadColleges() {
            try {
                const response = await Utils.ajax('../api/get-colleges.php');
                if (response.success) {
                    displayColleges(response.data);
                }
            } catch (error) {
                Utils.showAlert('Failed to load colleges', 'error');
            }
        }

        async function loadSuperAdmins() {
            try {
                const response = await Utils.ajax('../api/get-superadmins.php');
                if (response.success) {
                    displaySuperAdmins(response.data);
                }
            } catch (error) {
                Utils.showAlert('Failed to load super admins', 'error');
            }
        }

        async function loadCollegeOptions() {
            try {
                const response = await Utils.ajax('../api/get-colleges.php');
                if (response.success) {
                    const select = document.querySelector('select[name="college_id"]');
                    select.innerHTML = '<option value="">Select College</option>';
                    response.data.forEach(college => {
                        select.innerHTML += `<option value="${college.college_id}">${college.college_name}</option>`;
                    });
                }
            } catch (error) {
                Utils.showAlert('Failed to load colleges', 'error');
            }
        }

        // Display functions
        function displayColleges(colleges) {
            const tbody = document.getElementById('colleges-tbody');
            tbody.innerHTML = '';
            
            colleges.forEach(college => {
                tbody.innerHTML += `
                    <tr>
                        <td data-column="name">${college.college_name}</td>
                        <td data-column="location">${college.address || 'N/A'}</td>
                        <td data-column="superadmin">${college.superadmin_name || 'Not Assigned'}</td>
                        <td data-column="status">
                            <span class="badge ${college.is_active ? 'badge-success' : 'badge-danger'}">
                                ${college.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td data-column="created">${Utils.formatDate(college.created_at)}</td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="editCollege(${college.college_id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCollege(${college.college_id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        function displaySuperAdmins(superadmins) {
            const tbody = document.getElementById('superadmins-tbody');
            tbody.innerHTML = '';
            
            superadmins.forEach(admin => {
                tbody.innerHTML += `
                    <tr>
                        <td>${admin.full_name}</td>
                        <td>${admin.email}</td>
                        <td>${admin.college_name || 'Not Assigned'}</td>
                        <td>
                            <span class="badge ${admin.is_active ? 'badge-success' : 'badge-danger'}">
                                ${admin.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>${Utils.formatDate(admin.created_at)}</td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="editSuperAdmin(${admin.user_id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteSuperAdmin(${admin.user_id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        // Form submissions
        document.getElementById('add-college-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await Utils.ajax('../api/add-college.php', 'POST', data);
                if (response.success) {
                    Utils.showAlert('College added successfully', 'success');
                    Modal.hide('add-college-modal');
                    this.reset();
                    loadColleges();
                } else {
                    Utils.showAlert(response.message, 'error');
                }
            } catch (error) {
                Utils.showAlert('Failed to add college', 'error');
            }
        });

        document.getElementById('add-superadmin-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await Utils.ajax('../api/add-superadmin.php', 'POST', data);
                if (response.success) {
                    Utils.showAlert('Super Admin created successfully', 'success');
                    Modal.hide('add-superadmin-modal');
                    this.reset();
                    loadSuperAdmins();
                } else {
                    Utils.showAlert(response.message, 'error');
                }
            } catch (error) {
                Utils.showAlert('Failed to create super admin', 'error');
            }
        });

        // Utility functions
        function editCollege(id) {
            Utils.showAlert('Edit functionality will be implemented', 'info');
        }

        function deleteCollege(id) {
            if (confirm('Are you sure you want to delete this college?')) {
                Utils.showAlert('Delete functionality will be implemented', 'info');
            }
        }

        function editSuperAdmin(id) {
            Utils.showAlert('Edit functionality will be implemented', 'info');
        }

        function deleteSuperAdmin(id) {
            if (confirm('Are you sure you want to delete this super admin?')) {
                Utils.showAlert('Delete functionality will be implemented', 'info');
            }
        }

        function exportSystemData() {
            Utils.showAlert('Export functionality will be implemented', 'info');
        }

        function backupDatabase() {
            Utils.showAlert('Database backup initiated', 'success');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadColleges();
        });
    </script>
</body>
</html>