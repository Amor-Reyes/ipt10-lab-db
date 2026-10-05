# IPT10 Database Programming Laboratory

This is my submission for the IPT10 Database Programming Laboratory activity. It's a Student Record System that handles full CRUD (Create, Read, Update, Delete) operations. We had to build it two different ways to understand the differences in PHP APIs:

- **Folder 1 (`ipt10_lab/`)**: Built using the procedural **mysqli** approach.
- **Folder 2 (`ipt10_lab_pdo/`)**: Built using the object-oriented **PDO** (PHP Data Objects) approach.

## What you need to run this
- **XAMPP** (or any server with Apache and MySQL)
- **PHP 8.2+**
- A browser

## How to set it up

### 1. Install and Start XAMPP
1. Download XAMPP from [Apache Friends](https://www.apachefriends.org/download.html) if you don't have it yet.
2. Run the installer (make sure Apache, MySQL, and PHP are selected).
3. Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 2. Enable PHP Extensions
To make the database connections work, you need to enable the right extensions. 
1. In the XAMPP Control Panel, click **Config** next to Apache and open `PHP (php.ini)`.
2. Find these two lines and remove the `;` at the start to uncomment them:
   ```ini
   extension=mysqli
   extension=pdo_mysql
   ```
3. Save the file and restart Apache in XAMPP so the changes take effect.

### 3. Setup the Database
1. Go to phpMyAdmin in your browser (`http://localhost/phpmyadmin/`).
2. Open the **SQL** tab and paste this script to create the `ip10_lab` database and the `students` table:

```sql
CREATE DATABASE IF NOT EXISTS ip10_lab;
USE ip10_lab;

CREATE TABLE students (
    id CHAR(36) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    birthday DATE NOT NULL,
    sex ENUM('Male','Female') NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    student_number VARCHAR(50) UNIQUE NOT NULL,
    program VARCHAR(200) NOT NULL,
    enrolment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```
*(used UUIDs for the IDs instead of standard auto-increment integers as required for this lab)*

### 4. Run the App
1. Put this whole repository folder inside your XAMPP `htdocs` folder (usually `C:\xampp\htdocs\ipt10-lab-db`).
2. Open your browser and go to:
   - **For the MySQLi version:** `http://localhost/ipt10-lab-db/ipt10_lab/`
   - **For the PDO version:** `http://localhost/ipt10-lab-db/ipt10_lab_pdo/`

## Features I implemented
- **Viewing Students**: Lists everyone sorted by newest enrolment first, and you can click to view full details.
- **Adding Students**: Form with full server-side validation (checks if names are valid, emails are formatted right, dates exist, etc.). It keeps your input if it fails so you don't have to retype everything!
- **Editing**: Pulls the current data into the form and re-validates everything upon saving.
- **Deleting**: Added a confirmation screen so you don't accidentally delete someone.