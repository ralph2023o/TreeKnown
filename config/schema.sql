CREATE DATABASE IF NOT EXISTS treeknown;
USE treeknown;

-- USERS table
CREATE TABLE IF NOT EXISTS USERS (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','teacher','student') NOT NULL,
    student_id VARCHAR(20) NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ;

-- TREE_LIBRARY
CREATE TABLE IF NOT EXISTS TREE_LIBRARY (
    treelib_id INT AUTO_INCREMENT PRIMARY KEY,
    tree_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) ;

-- TREE_SUBMISSIONS
CREATE TABLE IF NOT EXISTS TREE_SUBMISSIONS (
    tree_id INT AUTO_INCREMENT PRIMARY KEY,

    species_guess VARCHAR(100) NOT NULL,   
    species_id INT NULL,                  

    location_name VARCHAR(100) NOT NULL,
    lat DECIMAL(10,7) NULL,
    lng DECIMAL(10,7) NULL,

    photo VARCHAR(255) NULL,

    submitted_by INT NOT NULL,
    verified_by INT NULL,

    review_note TEXT,

    status ENUM('pending','approved','rejected') DEFAULT 'pending',

    date_submitted DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (species_id) REFERENCES TREE_LIBRARY(treelib_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    FOREIGN KEY (submitted_by) REFERENCES USERS(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (verified_by) REFERENCES USERS(user_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ;

-- Sample Users
INSERT INTO USERS (name,email,password,role,student_id,status) VALUES
('Admin User','admin@treeknown.com','admin123','admin',NULL,'approved'),
('Teacher One','teacher1@treeknown.com','teach123','teacher',NULL,'approved'),
('Student One','student1@treeknown.com','stud123','student','120','approved');