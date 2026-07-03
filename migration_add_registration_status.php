<?php
/**
 * Database Migration: Add registration_status column to students table
 * 
 * This script adds the registration_status column to the students table if it doesn't exist.
 * The column tracks whether a student's registration is pending, approved, or rejected.
 * 
 * Run this script once to set up the new column.
 */

require_once 'dbconnection.php';

// Check if registration_status column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM students LIKE 'registration_status'");

if ($checkColumn && $checkColumn->num_rows === 0) {
    // Column doesn't exist, add it
    $alterTable = "ALTER TABLE students ADD COLUMN registration_status VARCHAR(20) DEFAULT 'pending' AFTER avatar";
    
    if ($conn->query($alterTable)) {
        echo "✓ Successfully added registration_status column to students table\n";
        echo "✓ Default value: 'pending'\n";
        echo "✓ Possible values: 'pending', 'approved', 'rejected'\n";
    } else {
        echo "✗ Error adding column: " . $conn->error . "\n";
        exit(1);
    }
} else {
    echo "✓ registration_status column already exists in students table\n";
}

// Verify the migration
$verify = $conn->query("SELECT COUNT(*) as count FROM students WHERE registration_status IS NOT NULL");
if ($verify) {
    $result = $verify->fetch_assoc();
    echo "✓ Migration verified: " . $result['count'] . " student records have registration_status value\n";
}

echo "\nMigration complete!\n";
?>
