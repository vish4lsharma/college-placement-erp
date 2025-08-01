<?php
require_once '../includes/auth.php';
Auth::requireRole('Student');

$current_user = Auth::getCurrentUser();
require_once '../config/database.php';

// Get student information
$stmt = $pdo->prepare("
    SELECT s.*, c.college_name 
    FROM students s 
    JOIN colleges c ON s.college_id = c.college_id 
    WHERE s.user_id = ?
");
$stmt->execute([$current_user['user_id']]);
$student = $stmt->fetch();

// Get statistics
$stats = [
    'total_applications' => 0,
    'interview_scheduled' => 0,
    'offers_received' => 0,
    'available_jobs' => 0
];

try {
    if ($student) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE student_id = ?");
        $stmt->execute([$student['student_id']]);
        $stats['total_applications'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications a JOIN interview_schedules i ON a.application_id = i.application_id WHERE a.student_id = ? AND i.status = 'Scheduled'");
        $stmt->execute([$student['student_id']]);
        $stats['interview_scheduled'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE student_id = ? AND final_result = 'Selected'");
        $stmt->execute([$student['student_id']]);
        $stats['offers_received'] = $stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM job_postings WHERE college_id = ? AND is_active = 1 AND application_deadline >= CURDATE()");
        $stmt->execute([$student['college_id']]);
        $stats['available_jobs'] = $stmt->fetch()['count'];
    }
} catch (Exception $e) {
    error_log("Error fetching student stats: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Campus Placement System</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="header">
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">Campus Placement System</a>
                <ul class="nav-links">
                    <li><a href="#">Welcome, <?php echo htmlspecialchars($current_user['full_name']); ?></a></li>
                    <li><a href="#" id="notifications-btn">🔔 <span id="notification-count">0</span></a></li>
                    <li><a href="../api/logout.php" class="btn btn-outline btn-sm">Logout</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="dashboard">
        <div class="dashboard-sidebar">
            <div class="sidebar">
                <ul class="sidebar-nav">
                    <li><a href="#" class="active" onclick="showSection('overview')">Dashboard</a></li>
                    <li><a href="#" onclick="showSection('profile')">My Profile</a></li>
                    <li><a href="#" onclick="showSection('jobs')">Available Jobs</a></li>
                    <li><a href="#" onclick="showSection('applications')">My Applications</a></li>
                    <li><a href="#" onclick="showSection('interviews')">Interviews</a></li>
                    <li><a href="#" onclick="showSection('resume')">Resume</a></li>
                </ul>
            </div>
        </div>

        <div class="dashboard-content">
            <!-- Overview Section -->
            <div id="overview-section">
                <h1>Student Dashboard</h1>
                <p class="text-secondary">Track your placement journey</p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_applications']; ?></div>
                        <div class="stat-label">Total Applications</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['interview_scheduled']; ?></div>
                        <div class="stat-label">Interviews Scheduled</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['offers_received']; ?></div>
                        <div class="stat-label">Offers Received</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['available_jobs']; ?></div>
                        <div class="stat-label">Available Jobs</div>
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
                                            <th>Date</th>
                                            <th>Activity</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-activities">
                                        <tr>
                                            <td><?php echo date('Y-m-d'); ?></td>
                                            <td>Logged into system</td>
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
                                <button class="btn btn-primary mb-2" onclick="showSection('jobs')">Browse Jobs</button>
                                <button class="btn btn-secondary mb-2" onclick="showSection('profile')">Update Profile</button>
                                <button class="btn btn-warning mb-2" onclick="showSection('resume')">Upload Resume</button>
                            </div>
                        </div>
                        
                        <?php if ($student): ?>
                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title">Profile Summary</h3>
                            </div>
                            <div class="p-3">
                                <p><strong>College:</strong> <?php echo htmlspecialchars($student['college_name']); ?></p>
                                <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department'] ?? 'Not Set'); ?></p>
                                <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course'] ?? 'Not Set'); ?></p>
                                <p><strong>CGPA:</strong> <?php echo $student['cgpa'] ?? 'Not Set'; ?></p>
                                <p><strong>Resume:</strong> 
                                    <?php echo $student['resume_path'] ? '<span class="text-success">Uploaded</span>' : '<span class="text-danger">Not Uploaded</span>'; ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Profile Section -->
            <div id="profile-section" class="d-none">
                <h1>My Profile</h1>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Personal Information</h3>
                    </div>
                    <form id="profile-form" data-validate>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($current_user['full_name']); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($current_user['email']); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Student Roll Number</label>
                                    <input type="text" name="student_roll" class="form-control" value="<?php echo htmlspecialchars($student['student_roll'] ?? ''); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Department</label>
                                    <select name="department" class="form-control form-select" required>
                                        <option value="">Select Department</option>
                                        <option value="Computer Science" <?php echo ($student['department'] ?? '') == 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                                        <option value="Information Technology" <?php echo ($student['department'] ?? '') == 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option>
                                        <option value="Electronics" <?php echo ($student['department'] ?? '') == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                                        <option value="Mechanical" <?php echo ($student['department'] ?? '') == 'Mechanical' ? 'selected' : ''; ?>>Mechanical</option>
                                        <option value="Civil" <?php echo ($student['department'] ?? '') == 'Civil' ? 'selected' : ''; ?>>Civil</option>
                                        <option value="Other" <?php echo ($student['department'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Course</label>
                                    <select name="course" class="form-control form-select" required>
                                        <option value="">Select Course</option>
                                        <option value="B.Tech" <?php echo ($student['course'] ?? '') == 'B.Tech' ? 'selected' : ''; ?>>B.Tech</option>
                                        <option value="M.Tech" <?php echo ($student['course'] ?? '') == 'M.Tech' ? 'selected' : ''; ?>>M.Tech</option>
                                        <option value="BCA" <?php echo ($student['course'] ?? '') == 'BCA' ? 'selected' : ''; ?>>BCA</option>
                                        <option value="MCA" <?php echo ($student['course'] ?? '') == 'MCA' ? 'selected' : ''; ?>>MCA</option>
                                        <option value="Other" <?php echo ($student['course'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Passing Year</label>
                                    <select name="passing_year" class="form-control form-select" required>
                                        <option value="">Select Year</option>
                                        <?php
                                        $currentYear = date('Y');
                                        for ($year = $currentYear; $year <= $currentYear + 5; $year++) {
                                            $selected = ($student['passing_year'] ?? '') == $year ? 'selected' : '';
                                            echo "<option value=\"$year\" $selected>$year</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">CGPA</label>
                                    <input type="number" name="cgpa" class="form-control" step="0.01" min="0" max="10" value="<?php echo $student['cgpa'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">LinkedIn URL</label>
                                    <input type="url" name="linkedin_url" class="form-control" value="<?php echo htmlspecialchars($student['linkedin_url'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">GitHub URL</label>
                                    <input type="url" name="github_url" class="form-control" value="<?php echo htmlspecialchars($student['github_url'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Portfolio URL</label>
                                    <input type="url" name="portfolio_url" class="form-control" value="<?php echo htmlspecialchars($student['portfolio_url'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Skills</label>
                            <textarea name="skills" class="form-control form-textarea" placeholder="List your technical skills (e.g., Java, Python, HTML, CSS, JavaScript)"><?php echo htmlspecialchars($student['skills'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Experience</label>
                            <textarea name="experience" class="form-control form-textarea" placeholder="Describe your work experience, internships, etc."><?php echo htmlspecialchars($student['experience'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Projects</label>
                            <textarea name="projects" class="form-control form-textarea" placeholder="Describe your key projects"><?php echo htmlspecialchars($student['projects'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>

            <!-- Jobs Section -->
            <div id="jobs-section" class="d-none">
                <h1>Available Jobs</h1>
                <div class="card">
                    <div class="card-header">
                        <input type="text" id="job-search" class="form-control" placeholder="Search jobs..." style="max-width: 300px;">
                    </div>
                    <div id="jobs-container">
                        <div class="text-center p-4">
                            <div class="spinner"></div>
                            <p>Loading available jobs...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applications Section -->
            <div id="applications-section" class="d-none">
                <h1>My Applications</h1>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Job Title</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="applications-tbody">
                                <tr>
                                    <td colspan="5" class="text-center">No applications found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Interviews Section -->
            <div id="interviews-section" class="d-none">
                <h1>Interview Schedule</h1>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Company</th>
                                    <th>Job Title</th>
                                    <th>Interview Date</th>
                                    <th>Time</th>
                                    <th>Mode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="interviews-tbody">
                                <tr>
                                    <td colspan="6" class="text-center">No interviews scheduled</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Resume Section -->
            <div id="resume-section" class="d-none">
                <h1>Resume Management</h1>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Upload Resume</h3>
                    </div>
                    <form id="resume-form" enctype="multipart/form-data">
                        <div class="form-group">
                            <label class="form-label">Resume File (PDF, DOC, DOCX)</label>
                            <div class="file-upload">
                                <input type="file" id="resume-file" name="resume" accept=".pdf,.doc,.docx" required>
                                <label for="resume-file" class="file-upload-label">
                                    <?php echo $student['resume_path'] ? 'Current: ' . basename($student['resume_path']) : 'Choose file to upload'; ?>
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Resume</button>
                        <?php if ($student['resume_path']): ?>
                        <a href="../<?php echo $student['resume_path']; ?>" class="btn btn-secondary" target="_blank">View Current Resume</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
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
            if (sectionName === 'jobs') {
                loadJobs();
            } else if (sectionName === 'applications') {
                loadApplications();
            } else if (sectionName === 'interviews') {
                loadInterviews();
            }
        }

        // Load jobs
        async function loadJobs() {
            const container = document.getElementById('jobs-container');
            try {
                const response = await Utils.ajax('../api/get-student-jobs.php');
                if (response.success) {
                    displayJobs(response.data);
                } else {
                    container.innerHTML = '<div class="text-center p-4"><p>No jobs available at the moment</p></div>';
                }
            } catch (error) {
                container.innerHTML = '<div class="text-center p-4"><p class="text-danger">Failed to load jobs</p></div>';
            }
        }

        // Display jobs
        function displayJobs(jobs) {
            const container = document.getElementById('jobs-container');
            if (jobs.length === 0) {
                container.innerHTML = '<div class="text-center p-4"><p>No jobs available</p></div>';
                return;
            }

            let jobsHTML = '';
            jobs.forEach(job => {
                jobsHTML += `
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-between">
                            <h4>${job.job_title}</h4>
                            <span class="badge badge-primary">${job.job_type}</span>
                        </div>
                        <div class="p-3">
                            <p><strong>Company:</strong> ${job.company_name}</p>
                            <p><strong>Location:</strong> ${job.job_location}</p>
                            <p><strong>Experience:</strong> ${job.experience_required}</p>
                            <p><strong>Salary:</strong> ${Utils.formatCurrency(job.salary_min)} - ${Utils.formatCurrency(job.salary_max)}</p>
                            <p><strong>Deadline:</strong> ${Utils.formatDate(job.application_deadline)}</p>
                            <p>${job.job_description}</p>
                            <button class="btn btn-primary" onclick="applyToJob(${job.job_id})">Apply Now</button>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = jobsHTML;
        }

        // Apply to job
        async function applyToJob(jobId) {
            if (confirm('Are you sure you want to apply to this job?')) {
                try {
                    const response = await Utils.ajax('../api/apply-job.php', 'POST', { job_id: jobId });
                    if (response.success) {
                        Utils.showAlert('Application submitted successfully!', 'success');
                        loadJobs(); // Reload jobs
                    } else {
                        Utils.showAlert(response.message, 'error');
                    }
                } catch (error) {
                    Utils.showAlert('Failed to submit application', 'error');
                }
            }
        }

        // Load applications
        async function loadApplications() {
            try {
                const response = await Utils.ajax('../api/get-student-applications.php');
                if (response.success) {
                    displayApplications(response.data);
                }
            } catch (error) {
                Utils.showAlert('Failed to load applications', 'error');
            }
        }

        // Display applications
        function displayApplications(applications) {
            const tbody = document.getElementById('applications-tbody');
            if (applications.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No applications found</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            applications.forEach(app => {
                tbody.innerHTML += `
                    <tr>
                        <td>${app.company_name}</td>
                        <td>${app.job_title}</td>
                        <td>${Utils.formatDate(app.applied_at)}</td>
                        <td><span class="badge badge-${getStatusColor(app.application_status)}">${app.application_status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick="viewApplication(${app.application_id})">View</button>
                        </td>
                    </tr>
                `;
            });
        }

        // Get status color
        function getStatusColor(status) {
            const colors = {
                'Applied': 'primary',
                'Shortlisted': 'info',
                'Interview Scheduled': 'warning',
                'Selected': 'success',
                'Rejected': 'danger'
            };
            return colors[status] || 'secondary';
        }

        // Profile form submission
        document.getElementById('profile-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await Utils.ajax('../api/update-student-profile.php', 'POST', data);
                if (response.success) {
                    Utils.showAlert('Profile updated successfully!', 'success');
                } else {
                    Utils.showAlert(response.message, 'error');
                }
            } catch (error) {
                Utils.showAlert('Failed to update profile', 'error');
            }
        });

        // Resume form submission
        document.getElementById('resume-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('../api/upload-resume.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Utils.showAlert('Resume uploaded successfully!', 'success');
                    location.reload(); // Reload to show updated resume
                } else {
                    Utils.showAlert(data.message, 'error');
                }
            } catch (error) {
                Utils.showAlert('Failed to upload resume', 'error');
            }
        });

        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadJobs();
        });
    </script>
</body>
</html>