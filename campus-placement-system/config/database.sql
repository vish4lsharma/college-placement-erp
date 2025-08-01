-- Campus Placement Management System Database
-- Database Name: Placement_db

CREATE DATABASE IF NOT EXISTS Placement_db;
USE Placement_db;

-- Table for storing different user types and permissions
CREATE TABLE user_roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Main users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES user_roles(role_id)
);

-- Colleges table
CREATE TABLE colleges (
    college_id INT PRIMARY KEY AUTO_INCREMENT,
    college_name VARCHAR(255) NOT NULL,
    address TEXT,
    contact_email VARCHAR(255),
    contact_phone VARCHAR(20),
    website VARCHAR(255),
    superadmin_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (superadmin_id) REFERENCES users(user_id)
);

-- Students table (extends users)
CREATE TABLE students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    college_id INT NOT NULL,
    student_roll VARCHAR(50),
    department VARCHAR(100),
    course VARCHAR(100),
    passing_year YEAR,
    cgpa DECIMAL(4,2),
    resume_path VARCHAR(500),
    skills TEXT,
    experience TEXT,
    projects TEXT,
    certifications TEXT,
    linkedin_url VARCHAR(255),
    github_url VARCHAR(255),
    portfolio_url VARCHAR(255),
    is_placed BOOLEAN DEFAULT FALSE,
    placement_company VARCHAR(255),
    placement_package DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (college_id) REFERENCES colleges(college_id)
);

-- Companies table
CREATE TABLE companies (
    company_id INT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(255) NOT NULL,
    industry VARCHAR(100),
    website VARCHAR(255),
    description TEXT,
    logo_path VARCHAR(500),
    hr_contact_name VARCHAR(255),
    hr_contact_email VARCHAR(255),
    hr_contact_phone VARCHAR(20),
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Job postings table
CREATE TABLE job_postings (
    job_id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    college_id INT NOT NULL,
    job_title VARCHAR(255) NOT NULL,
    job_description TEXT,
    requirements TEXT,
    skills_required TEXT,
    experience_required VARCHAR(50),
    salary_min DECIMAL(10,2),
    salary_max DECIMAL(10,2),
    job_location VARCHAR(255),
    job_type ENUM('Full-time', 'Part-time', 'Internship', 'Contract'),
    application_deadline DATE,
    interview_date DATE,
    interview_time TIME,
    interview_location VARCHAR(255),
    interview_mode ENUM('Online', 'Offline', 'Hybrid'),
    max_applications INT DEFAULT 100,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(company_id),
    FOREIGN KEY (college_id) REFERENCES colleges(college_id)
);

-- Applications table
CREATE TABLE applications (
    application_id INT PRIMARY KEY AUTO_INCREMENT,
    job_id INT NOT NULL,
    student_id INT NOT NULL,
    application_status ENUM('Applied', 'Shortlisted', 'Interview Scheduled', 'Selected', 'Rejected', 'Withdrawn') DEFAULT 'Applied',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    interview_feedback TEXT,
    final_result ENUM('Selected', 'Rejected', 'Pending') DEFAULT 'Pending',
    salary_offered DECIMAL(10,2),
    joining_date DATE,
    notes TEXT,
    FOREIGN KEY (job_id) REFERENCES job_postings(job_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id),
    UNIQUE KEY unique_application (job_id, student_id)
);

-- Notifications table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('Info', 'Success', 'Warning', 'Error') DEFAULT 'Info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Interview schedules table
CREATE TABLE interview_schedules (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    interview_round VARCHAR(100),
    interview_date DATE NOT NULL,
    interview_time TIME NOT NULL,
    interview_mode ENUM('Online', 'Offline', 'Hybrid'),
    interview_location VARCHAR(255),
    meeting_link VARCHAR(500),
    interviewer_name VARCHAR(255),
    interviewer_email VARCHAR(255),
    status ENUM('Scheduled', 'Completed', 'Cancelled', 'Rescheduled') DEFAULT 'Scheduled',
    feedback TEXT,
    result ENUM('Pass', 'Fail', 'Pending') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(application_id)
);

-- System settings table
CREATE TABLE system_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Email templates table
CREATE TABLE email_templates (
    template_id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(100) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    variables TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT INTO user_roles (role_name, permissions) VALUES
('Developer', '{"all": true}'),
('SuperAdmin', '{"manage_college": true, "manage_admins": true, "view_reports": true}'),
('Admin', '{"manage_students": true, "manage_companies": true, "manage_jobs": true, "view_reports": true}'),
('Student', '{"view_jobs": true, "apply_jobs": true, "update_profile": true}'),
('Company', '{"post_jobs": true, "view_applications": true, "manage_interviews": true}');

-- Insert default developer user
INSERT INTO users (email, password, role_id, full_name, phone) VALUES
('vishalsharma08555252@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Vishal Sharma', '1234567890');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'Campus Placement Management System', 'Name of the application'),
('admin_email', 'admin@placement.com', 'System administrator email'),
('max_file_size', '5242880', 'Maximum file upload size in bytes (5MB)'),
('allowed_file_types', 'pdf,doc,docx', 'Allowed file types for resume upload'),
('email_from_name', 'Placement System', 'From name for system emails'),
('email_from_address', 'noreply@placement.com', 'From address for system emails');

-- Insert default email templates
INSERT INTO email_templates (template_name, subject, body, variables) VALUES
('application_confirmation', 'Application Submitted Successfully', 
'Dear {{student_name}},\n\nYour application for {{job_title}} at {{company_name}} has been submitted successfully.\n\nApplication Details:\n- Job Title: {{job_title}}\n- Company: {{company_name}}\n- Applied On: {{application_date}}\n\nYou will be notified about further updates.\n\nBest regards,\nPlacement Team', 
'student_name,job_title,company_name,application_date'),
('interview_scheduled', 'Interview Scheduled', 
'Dear {{student_name}},\n\nYour interview has been scheduled for {{job_title}} at {{company_name}}.\n\nInterview Details:\n- Date: {{interview_date}}\n- Time: {{interview_time}}\n- Mode: {{interview_mode}}\n- Location: {{interview_location}}\n\nPlease be prepared and join on time.\n\nBest regards,\nPlacement Team', 
'student_name,job_title,company_name,interview_date,interview_time,interview_mode,interview_location');