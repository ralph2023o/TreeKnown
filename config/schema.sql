CREATE DATABASE IF NOT EXISTS treeknown;
USE treeknown;

-- USERS table
CREATE TABLE USERS (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- plain text for prototype (hash in production)
    role ENUM('admin','teacher','student') NOT NULL,
    student_id VARCHAR(20) NULL,      -- optional for students
    status ENUM('pending','approved','rejected') DEFAULT 'pending',  -- for user approval
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SPECIES_LIBRARY table
CREATE TABLE SPECIES_LIBRARY (
    species_id INT AUTO_INCREMENT PRIMARY KEY,
    species_name VARCHAR(100) NOT NULL,
    description TEXT
);

-- TREE_SUBMISSIONS table
CREATE TABLE TREE_SUBMISSIONS (
    tree_id INT AUTO_INCREMENT PRIMARY KEY,
    species_id INT NOT NULL,
    location_name VARCHAR(100) NOT NULL,
    lat DECIMAL(10,7) NULL,
    lng DECIMAL(10,7) NULL,
    submitted_by INT NOT NULL,        -- references USERS.user_id
    verified_by INT NULL,             -- references USERS.user_id
    date_submitted DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    photo VARCHAR(255) NULL,          -- filename in uploads/
    FOREIGN KEY (species_id) REFERENCES SPECIES_LIBRARY(species_id),
    FOREIGN KEY (submitted_by) REFERENCES USERS(user_id),
    FOREIGN KEY (verified_by) REFERENCES USERS(user_id)
);

-- Sample Users
INSERT INTO USERS (name,email,password,role,student_id,status) VALUES
('Admin User','admin@treeknown.com','admin123','admin',NULL,'approved'),
('Teacher One','teacher1@treeknown.com','teach123','teacher',NULL,'approved'),
('Student One','student1@treeknown.com','stud123','student','S123','pending');

-- Sample Species
INSERT INTO SPECIES_LIBRARY (species_name,description) VALUES
('Acacia','Fast growing tree'),
('Narra','Philippine national tree');

-- Sample Tree submissions
INSERT INTO TREE_SUBMISSIONS (species_id, location_name, submitted_by, status, photo, lat, lng) VALUES
(1,'Campus Garden',3,'pending','tree1.jpg',14.123456,121.123456);