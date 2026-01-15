-- Employee Management System Database
-- Created: 2026-01-12
-- Database Schema with all required tables

-- Create database
CREATE DATABASE IF NOT EXISTS `ems_database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ems_database`;

-- --------------------------------------------------------
-- Table structure for `departments`
-- --------------------------------------------------------

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'default-avatar.png',
  `user_type` enum('Admin','HR','Manager','Employee') NOT NULL DEFAULT 'Employee',
  `user_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_user_status` (`user_status`),
  KEY `idx_user_type` (`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `employees`
-- --------------------------------------------------------

CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `address` text,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_employee_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_employee_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `activity_logs`
-- --------------------------------------------------------

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `site_content`
-- --------------------------------------------------------

CREATE TABLE `site_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section_name` varchar(50) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `content` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_name` (`section_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Insert sample departments
-- --------------------------------------------------------

INSERT INTO `departments` (`id`, `name`, `description`) VALUES
(1, 'Human Resources', 'Manages employee relations, recruitment, and benefits'),
(2, 'Information Technology', 'Handles all technology infrastructure and software development'),
(3, 'Finance', 'Manages financial planning, accounting, and reporting'),
(4, 'Marketing', 'Responsible for brand promotion and customer engagement'),
(5, 'Operations', 'Oversees daily business operations and logistics');

-- --------------------------------------------------------
-- Insert default admin user
-- Password: admin123 (hashed with bcrypt)
-- --------------------------------------------------------

INSERT INTO `users` (`id`, `first_name`, `last_name`, `gender`, `username`, `email`, `password`, `phone`, `user_type`, `user_status`) VALUES
(1, 'System', 'Administrator', 'Male', 'admin', 'admin@ems.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890', 'Admin', 'Active');

-- --------------------------------------------------------
-- Insert sample site content for homepage
-- --------------------------------------------------------

INSERT INTO `site_content` (`section_name`, `title`, `content`) VALUES
('hero', 'Welcome to Employee Management System', 'Streamline your workforce management with our comprehensive employee management solution. Track, manage, and optimize your human resources efficiently.'),
('about', 'About Our System', 'Our Employee Management System provides a complete solution for managing your workforce. From employee records to performance tracking, we help you stay organized and efficient.'),
('features', 'Key Features', '<ul><li>Complete Employee Database Management</li><li>Secure Authentication & Authorization</li><li>Comprehensive CRUD Operations</li><li>Activity Logging & Audit Trails</li><li>Role-Based Access Control</li><li>Responsive Dashboard Interface</li></ul>'),
('contact', 'Get In Touch', 'For support or inquiries, please contact us at support@ems.local or call +1234567890.'),
('sidebar_welcome', 'Quick Links', '<ul><li><a href="auth/login.php">Employee Login</a></li><li><a href="auth/register.php">Register</a></li><li><a href="#about">About Us</a></li></ul>'),
('sidebar_updates', 'Latest Updates', '<ul><li>New employee portal launched</li><li>Enhanced security features added</li><li>Mobile-responsive design implemented</li></ul>');

-- --------------------------------------------------------
-- Insert sample employees (linked to existing users)
-- --------------------------------------------------------

INSERT INTO `users` (`id`, `first_name`, `last_name`, `gender`, `username`, `email`, `password`, `phone`, `user_type`, `user_status`) VALUES
(2, 'John', 'Doe', 'Male', 'johndoe', 'john.doe@ems.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567891', 'HR', 'Active'),
(3, 'Jane', 'Smith', 'Female', 'janesmith', 'jane.smith@ems.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567892', 'Manager', 'Active'),
(4, 'Mike', 'Johnson', 'Male', 'mikejohnson', 'mike.johnson@ems.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567893', 'Employee', 'Active');

INSERT INTO `employees` (`user_id`, `employee_id`, `department_id`, `position`, `salary`, `hire_date`, `address`, `emergency_contact`, `emergency_phone`) VALUES
(2, 'EMP001', 1, 'HR Manager', 75000.00, '2024-01-15', '123 Main St, City, State', 'Emergency HR Contact', '+1234567800'),
(3, 'EMP002', 4, 'Marketing Manager', 70000.00, '2024-03-10', '456 Oak Ave, City, State', 'Emergency Marketing Contact', '+1234567801'),
(4, 'EMP003', 2, 'Software Developer', 65000.00, '2024-06-01', '789 Pine Rd, City, State', 'Emergency IT Contact', '+1234567802');

-- --------------------------------------------------------
-- End of SQL Script
-- --------------------------------------------------------
