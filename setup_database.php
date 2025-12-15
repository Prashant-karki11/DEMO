<?php
require_once 'config/database.php';

echo "<h2>Database Setup for CareerPath</h2>";

// Check and create tables
$tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        user_type ENUM('job_seeker', 'recruiter', 'mentor', 'admin') NOT NULL DEFAULT 'job_seeker',
        phone VARCHAR(20),
        profile_image VARCHAR(255),
        bio TEXT,
        skills TEXT,
        experience TEXT,
        education TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'companies' => "CREATE TABLE IF NOT EXISTS companies (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        company_name VARCHAR(100) NOT NULL,
        industry VARCHAR(100),
        description TEXT,
        website VARCHAR(255),
        logo VARCHAR(255),
        location VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    
    'jobs' => "CREATE TABLE IF NOT EXISTS jobs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        recruiter_id INT NOT NULL,
        company_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        requirements TEXT NOT NULL,
        skills_required TEXT,
        location VARCHAR(100) NOT NULL,
        job_type ENUM('full_time', 'part_time', 'contract', 'internship') NOT NULL,
        salary_range VARCHAR(50),
        experience_level ENUM('entry', 'mid', 'senior', 'executive') NOT NULL,
        status ENUM('active', 'closed', 'draft') DEFAULT 'active',
        posted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        deadline DATE,
        FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $table_name => $sql) {
    echo "Creating/Checking table: $table_name<br>";
    if ($conn->query($sql)) {
        echo "✓ $table_name table is ready<br><br>";
    } else {
        echo "✗ Error with $table_name: " . $conn->error . "<br><br>";
    }
}

// Check for foreign key constraints
echo "<h3>Checking Foreign Key Constraints</h3>";
$fk_check = $conn->query("
    SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN ('companies', 'jobs')
");

if ($fk_check->num_rows > 0) {
    echo "Foreign key constraints found:<br>";
    while ($row = $fk_check->fetch_assoc()) {
        echo "✓ {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} → {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}<br>";
    }
} else {
    echo "No foreign key constraints found. Creating them...<br>";
    
    // Add foreign keys if they don't exist
    $foreign_keys = [
        "ALTER TABLE companies ADD CONSTRAINT fk_companies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE jobs ADD CONSTRAINT fk_jobs_recruiter FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE jobs ADD CONSTRAINT fk_jobs_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE"
    ];
    
    foreach ($foreign_keys as $fk_sql) {
        if ($conn->query($fk_sql)) {
            echo "✓ Foreign key added<br>";
        } else {
            echo "✗ Error adding foreign key: " . $conn->error . "<br>";
        }
    }
}

// Test data insertion
echo "<h3>Testing Data Insertion</h3>";

// Test user insertion
$test_email = "test_recruiter_" . time() . "@example.com";
$test_password = password_hash('password123', PASSWORD_DEFAULT);
$test_user_sql = "INSERT IGNORE INTO users (name, email, password, user_type) 
                  VALUES ('Test Recruiter', '$test_email', '$test_password', 'recruiter')";

if ($conn->query($test_user_sql)) {
    $test_user_id = $conn->insert_id;
    echo "✓ Test user created (ID: $test_user_id)<br>";
    
    // Test company insertion
    $test_company_sql = "INSERT IGNORE INTO companies (user_id, company_name) 
                         VALUES ('$test_user_id', 'Test Company Inc.')";
    
    if ($conn->query($test_company_sql)) {
        $test_company_id = $conn->insert_id;
        echo "✓ Test company created (ID: $test_company_id)<br>";
        
        // Test job insertion
        $test_job_sql = "INSERT INTO jobs (recruiter_id, company_id, title, description, requirements, location, job_type, experience_level, deadline) 
                         VALUES ('$test_user_id', '$test_company_id', 'Test Job Position', 'This is a test job description.', 'Test requirements here.', 'Remote', 'full_time', 'mid', '2024-12-31')";
        
        if ($conn->query($test_job_sql)) {
            $test_job_id = $conn->insert_id;
            echo "✓ Test job created (ID: $test_job_id)<br>";
        } else {
            echo "✗ Error creating test job: " . $conn->error . "<br>";
        }
    } else {
        echo "✗ Error creating test company: " . $conn->error . "<br>";
    }
} else {
    echo "✗ Error creating test user: " . $conn->error . "<br>";
}

// Show current data
echo "<h3>Current Data Summary</h3>";
$tables_to_check = ['users', 'companies', 'jobs'];
foreach ($tables_to_check as $table) {
    $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
    echo "• $table: $count records<br>";
}

echo "<h3 style='color: green;'>Setup Complete!</h3>";
echo "<p><a href='index.php'>Go to Homepage</a></p>";
echo "<p><a href='login.php'>Login</a></p>";
?>