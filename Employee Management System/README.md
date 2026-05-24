# Employee Management System

A complete, professional web-based Employee Management System built with PHP and MySQL. This system provides comprehensive employee data management with secure authentication, role-based access control, and full CRUD operations.

## 👥 Group Members
   
      Class BIT29-A
1. Mohamed Ali Ahmed                   
2. Fuad Abdikarin Maalin Abukar        
3. Abdirahman Mohamed Ahmed           



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
