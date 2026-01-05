-- Create database
CREATE DATABASE IF NOT EXISTS university_db;
USE university_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'registrar', 'librarian') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    teacher_id INT,
    credits INT DEFAULT 3,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Enrollments table
CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id)
);

-- Grades table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    grade VARCHAR(2),
    teacher_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Library books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 1,
    available INT DEFAULT 1
);

-- Book borrowings table
CREATE TABLE IF NOT EXISTS borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert sample data
INSERT INTO users (username, password, role, full_name, email) VALUES
('admin', '$2y$10$YourHashedPasswordHere', 'admin', 'System Administrator', 'admin@university.edu'),
('teacher1', '$2y$10$YourHashedPasswordHere', 'teacher', 'Dr. John Smith', 'john.smith@university.edu'),
('student1', '$2y$10$YourHashedPasswordHere', 'student', 'Alice Johnson', 'alice@university.edu'),
('registrar1', '$2y$10$YourHashedPasswordHere', 'registrar', 'Robert Brown', 'robert@university.edu'),
('librarian1', '$2y$10$YourHashedPasswordHere', 'librarian', 'Mary Wilson', 'mary@university.edu');

INSERT INTO courses (course_code, course_name, teacher_id, credits) VALUES
('CS101', 'Introduction to Programming', 2, 3),
('MATH201', 'Calculus I', 2, 4),
('ENG101', 'English Composition', 2, 3);

INSERT INTO books (isbn, title, author, quantity, available) VALUES
('978-0134685991', 'Effective Java', 'Joshua Bloch', 5, 5),
('978-0262033848', 'Introduction to Algorithms', 'Thomas H. Cormen', 3, 3),
('978-1491950357', 'Learning PHP, MySQL & JavaScript', 'Robin Nixon', 4, 4);

-- Note: Passwords should be hashed using password_hash() in PHP