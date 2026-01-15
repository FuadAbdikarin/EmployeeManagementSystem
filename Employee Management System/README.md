# Employee Management Syste

A complete, professional web-based Employee Management System built with PHP and MySQL. This system provides comprehensive employee data management with secure authentication, role-based access control, and full CRUD operations.

## 👥 Group Members
   
      Class BIT29-A
1. Mohamed Ali Ahmed                   IT22129108
2. Fuad Abdikarin Maalin Abukar        IT22129086
3. Abdirahman Mohamed Ahmed            IT22129041


## 📋 Table of Contents
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Default Credentials](#default-credentials)
- [Usage Guide](#usage-guide)
- [Security Features](#security-features)
- [Contributing](#contributing)

## ✨ Features

### Authentication & Authorization
- ✅ Secure user registration with validation
- ✅ Login system with sessions and cookies
- ✅ "Remember Me" functionality (30-day persistent login)
- ✅ Password recovery system
- ✅ Automatic session expiry
- ✅ Role-based redirection (Admin/HR/Manager vs Employee)

### Admin Management
- 📊 **Dynamic Dashboard**: Real-time stats (users, employees, leaves, payroll)
- 🏢 **Department Management**: Full CRUD for company departments
- 🕒 **Attendance Control**: Track daily presence, lateness, and absences
- 💰 **Payroll Management**: Process monthly salaries and disbursements
- 📝 **Leave Approval**: Review and approve/reject employee leave requests
- 📂 **Reporting System**: Export data to CSV (Employees, Attendance, Payroll, Leaves)
- � **User Management**: Control system access and roles

### Employee Self-Service
- � **Personal Profile**: Manage contact info and profile picture
- 🕒 **My Attendance**: View personal attendance history and rates
- � **My Payslips**: View and download professional printable payslips
- � **Leave Requests**: Apply for leave and track approval status
- � **Notifications**: Real-time system alerts and center

### Security
- 🔒 bcrypt password hashing
- 🛡️ SQL injection prevention (PDO prepared statements)
- ✔️ CSRF token protection
- 🚫 Input sanitization and validation
- 📁 Secure file upload restrictions
- ⏱️ Session security and timeout management

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Server**: Apache (XAMPP recommended)
- **Architecture**: MVC-inspired structure

## 📥 Installation

### Prerequisites
- XAMPP (or LAMP/WAMP) installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge, Safari)

### Step-by-Step Installation

1. **Clone the repository** (or download ZIP)
   ```bash
   git clone <your-repository-url>
   ```

2. **Move to XAMPP htdocs**
   ```
   Copy the "Employee Management System" folder to:
   C:\xampp\htdocs\
   ```

3. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start Apache
   - Start MySQL

4. **Continue to Database Setup** (see next section)

## 🗄️ Database Setup

### Method 1: Using phpMyAdmin (Recommended)

1. Open phpMyAdmin:
   - Navigate to `http://localhost:81/phpmyadmin` (or `http://localhost/phpmyadmin` if using default port)
   
2. Import the database:
   - Click on "Import" tab
   - Click "Choose File"
   - Select: `Employee Management System/database/ems_database.sql`
   - Click "Go" button at the bottom
   
3. Verify:
   - You should see `ems_database` in the left sidebar
   - Click on it to see the tables: `users`, `employees`, `departments`, `activity_logs`, `site_content`

### Method 2: Using MySQL Command Line

```bash
cd C:\xampp\mysql\bin
mysql -u root -p
```

Then run:
```sql
source "C:/xampp/htdocs/Employee Management System/database/ems_database.sql";
```

## 🔑 Default Credentials

After importing the database, you can login with:

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Test HR Account:**
- Username: `johndoe`
- Password: `admin123`

**Test Manager Account:**
- Username: `janesmith`
- Password: `admin123`

**Test Employee Account:**
- Username: `mikejohnson`
- Password: `admin123`

> ⚠️ **Important**: Change the admin password immediately after first login in a production environment!

## 📖 Usage Guide

### Accessing the System

1. **Public Homepage**
   ```
   http://localhost/Employee Management System/
   ```

2. **Login Page**
   ```
   http://localhost/Employee Management System/auth/login.php
   ```

3. **Registration Page**
   ```
   http://localhost/Employee Management System/auth/register.php
   ```

### Common Tasks

#### Adding a New Employee
1. Login as Admin or HR
2. Navigate to Dashboard → Employees → Add Employee
3. Fill in all required fields (marked with *)
4. Upload profile picture (optional)
5. Click "Add Employee"

#### Searching Employees
1. Go to Employee List page
2. Use the search box at the top
3. Enter name, position, or employee ID
4. Click "Search"

#### Managing User Status
1. Login as Admin
2. Go to User Management
3. Click activate/deactivate icon next to user
4. User status will update immediately


## 🔐 Security Features

### Implemented Security Measures

1. **Password Security**
   - bcrypt hashing (cost factor: 10)
   - Minimum password length enforcement
   - No plain-text storage

2. **Database Security**
   - PDO with prepared statements
   - SQL injection prevention
   - Parameterized queries

3. **Session Security**
   - Secure session configuration
   - Session ID regeneration
   - Automatic timeout (5 minutes)
   - Activity-based session refresh

4. **File Upload Security**
   - File type validation
   - File size restrictions (5MB max)
   - No PHP execution in uploads directory
   - Unique filename generation

5. **Input Validation**
   - Server-side validation
   - Client-side validation
   - XSS prevention (htmlspecialchars)
   - CSRF token protection

6. **Access Control**
   - Role-based permissions
   - Authentication checks on all protected pages
   - User status verification

## 🧪 Testing the System

### Manual Testing Checklist

- [ ] Register a new user
- [ ] Login with credentials
- [ ] Test "Remember Me" functionality
- [ ] Wait 5 minutes for session expiry
- [ ] Add a new employee
- [ ] Search for employees
- [ ] Edit employee information
- [ ] Delete an employee
- [ ] Activate/deactivate users (as Admin)
- [ ] Upload profile pictures
- [ ] Test logout functionality
- [ ] Verify activity logs

## 🐛 Troubleshooting

### Common Issues

**Issue: Database connection failed**
- Solution: Check `config/config.php` database credentials
- Verify MySQL is running in XAMPP

**Issue: Session expired immediately**
- Solution: Check folder permissions for session storage
- Ensure `session.save_path` is writable

**Issue: Profile pictures not uploading**
- Solution: Check `uploads/profiles/` folder exists and is writable
- Verify file permissions (755 or 777)

**Issue: Page shows blank/white screen**
- Solution: Enable error reporting in `config/config.php`
- Check PHP error logs in XAMPP


## 📄 License

This project is created for educational purposes.

## 🤝 Contributing

This is an academic project. For improvements or bug fixes:
1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📞 Support

For issues or questions:
- Check the troubleshooting section
- Review the code comments
- Contact your instructor

---

**Built with ❤️ for Academic Excellence**

*Last Updated: January 12, 2026*
