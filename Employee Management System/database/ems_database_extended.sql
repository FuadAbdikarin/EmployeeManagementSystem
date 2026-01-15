-- Extended Database Schema for Employee Management System
-- Additional tables for attendance, leave requests, payroll, and notifications
-- Run this after ems_database.sql

USE `ems_database`;

-- --------------------------------------------------------
-- Table structure for `attendance`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('Present','Absent','Late','Half Day','On Leave') DEFAULT 'Present',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_date_unique` (`employee_id`, `date`),
  KEY `idx_date` (`date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `leave_requests`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('Sick Leave','Casual Leave','Annual Leave','Maternity Leave','Paternity Leave','Unpaid Leave') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `review_date` datetime DEFAULT NULL,
  `review_notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_dates` (`start_date`, `end_date`),
  CONSTRAINT `fk_leave_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leave_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `payroll`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `payroll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `month` int(2) NOT NULL,
  `year` int(4) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `allowances` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_status` enum('Pending','Paid','Failed') DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_month_year` (`employee_id`, `month`, `year`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_month_year` (`month`, `year`),
  CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payroll_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `notifications`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('Info','Success','Warning','Danger') DEFAULT 'Info',
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_read` (`user_id`, `is_read`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_notification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for `system_settings`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` varchar(50) DEFAULT 'string',
  `description` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Insert default system settings
-- --------------------------------------------------------

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('company_name', 'Employee Management System', 'string', 'Company name'),
('company_email', 'info@ems.local', 'string', 'Company email address'),
('company_phone', '+1234567890', 'string', 'Company phone number'),
('leave_annual_days', '20', 'integer', 'Annual leave days per year'),
('leave_sick_days', '10', 'integer', 'Sick leave days per year'),
('leave_casual_days', '5', 'integer', 'Casual leave days per year'),
('payroll_tax_rate', '15', 'float', 'Default tax rate percentage'),
('attendance_grace_period', '15', 'integer', 'Grace period in minutes for late check-in'),
('session_timeout', '1800', 'integer', 'Session timeout in seconds');

-- --------------------------------------------------------
-- Insert sample data for testing
-- --------------------------------------------------------

-- Sample attendance records
INSERT INTO `attendance` (`employee_id`, `date`, `check_in`, `check_out`, `status`) VALUES
(1, CURDATE(), '09:00:00', '17:00:00', 'Present'),
(2, CURDATE(), '09:15:00', '17:30:00', 'Present'),
(3, CURDATE(), '09:30:00', NULL, 'Present');

-- Sample leave requests
INSERT INTO `leave_requests` (`employee_id`, `leave_type`, `start_date`, `end_date`, `days`, `reason`, `status`) VALUES
(3, 'Annual Leave', DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), 5, 'Family vacation', 'Pending');

-- Sample notifications
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `is_read`) VALUES
(1, 'Info', 'Welcome to EMS', 'Welcome to the Employee Management System!', 0),
(2, 'Info', 'System Update', 'New features have been added to the system.', 0),
(4, 'Warning', 'Leave Request', 'Your leave request is pending approval.', 0);

-- --------------------------------------------------------
-- End of Extended Schema
-- --------------------------------------------------------
