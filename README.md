# ClinicFlow – Smart Clinic Queue & Appointment Management System

ClinicFlow is a web-based clinic management system designed to simplify patient registration, appointment management, and queue handling. It helps receptionists and doctors manage daily clinic operations through a centralized system.

The system also provides automatic queue updates so that the current token and waiting patients can be monitored without manually refreshing the display.

## 🚀 Features

### 1. User Authentication
- Secure login system.
- Role-based access for Receptionist and Doctor.
- Access to modules based on user roles.

### 2. Patient Management
- Register new patients.
- View and manage patient records.
- Validate patient information.
- Manage deleted patient records where supported.

### 3. Appointment Management
- Schedule patient appointments.
- View appointment records.
- Update appointment status.
- Manage patient arrivals and cancellations.

### 4. Walk-in Registration
- Register patients without prior appointments.
- Generate queue tokens for walk-in patients.
- Add walk-in patients to the clinic queue.

### 5. Queue Management
- Generate queue token numbers.
- Track waiting patients.
- Manage patient queue status.
- Display the current token and waiting queue.
- Automatically refresh queue information every 5 seconds using JavaScript Fetch API.

### 6. Dashboard
- View clinic statistics.
- Monitor appointments and patient activity.
- Access important clinic operations from one place.

## 🛠️ Technologies Used

| Technology | Purpose |
|---|---|
| PHP | Backend development |
| MySQL | Database management |
| PDO | Database connectivity |
| HTML5 | Web page structure |
| CSS3 | Custom styling |
| Bootstrap 5 | Responsive layout and UI components |
| Bootstrap Icons | Icons |
| JavaScript | Frontend interactions |
| Fetch API | Automatic queue data retrieval |
| XAMPP | Local development server |
| VS Code | Code editor |
| phpMyAdmin | Database management |

## 💻 Requirements

Before running this project, install:

- XAMPP
- Visual Studio Code
- Web browser
- phpMyAdmin (included with XAMPP)

## ⚙️ Installation and Setup

### Step 1: Clone the Repository

```bash
git clone YOUR_GITHUB_REPOSITORY_URL
```

### Step 2: Move the Project

Place the ClinicFlow folder inside the XAMPP `htdocs` directory.

Example:

```text
C:/xampp/htdocs/ClinicFlow
```

### Step 3: Start XAMPP

Open XAMPP Control Panel and start:

- Apache
- MySQL

### Step 4: Create the Database

1. Open `http://localhost/phpmyadmin/`.
2. Create the database using the database name configured in the project.
3. Import the project's SQL database file, if included in the repository.

### Step 5: Configure Database Connection

Open the database configuration file in the project.

Update the database connection details according to your local MySQL setup.

The project uses PHP PDO for database connectivity.

### Step 6: Run the Project

Open your browser and visit:

```text
http://localhost/ClinicFlow/
```

## 🔄 Automatic Queue Updates

ClinicFlow uses JavaScript Fetch API and periodic polling to retrieve updated queue information from the PHP backend.

- Queue information is stored in MySQL.
- PHP retrieves the latest queue records.
- Data is returned in JSON format.
- JavaScript updates the queue display automatically.
- The display checks for updates every 5 seconds.

This reduces the need for manual page refreshing.

## 🎨 User Interface

ClinicFlow uses a dark navy and teal colour theme with Bootstrap 5 and custom CSS to provide a clean and responsive interface.



## 📁 Project Information

**Project Name:** ClinicFlow

**Project Type:** Web-Based Clinic Management System

**Project Purpose:** TYBCA Internship Project

## 👨‍💻 Developed By

**Name:** Rawal Dhruv

**Course:** Bachelor of Computer Applications (BCA)

**Project:** ClinicFlow – Smart Clinic Queue & Appointment Management System

---

⭐ If you find this project useful, consider giving the repository a star!

