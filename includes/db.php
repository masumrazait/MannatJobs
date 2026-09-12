<?php
$host = 'localhost';
$dbName = 'mannatjobs';
$dbUser = 'root';
$dbPass = '';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    die('Database connection failed. Please create the database and update the credentials in includes/db.php.');
}

$conn->set_charset('utf8mb4');

// Keep existing installations compatible with the expanded job seeker profile.
$profileColumns = [
    'degree' => 'VARCHAR(150) DEFAULT NULL',
    'university' => 'VARCHAR(180) DEFAULT NULL',
    'stream' => 'VARCHAR(150) DEFAULT NULL',
    'profession' => 'VARCHAR(150) DEFAULT NULL',
    'projects' => 'TEXT DEFAULT NULL',
    'company' => 'VARCHAR(180) DEFAULT NULL',
    'state' => 'VARCHAR(120) DEFAULT NULL',
    'city' => 'VARCHAR(120) DEFAULT NULL',
];

$columnsResult = $conn->query("SHOW COLUMNS FROM jobseeker_profiles");
$existingColumns = [];
if ($columnsResult) {
    while ($column = $columnsResult->fetch_assoc()) {
        $existingColumns[$column['Field']] = true;
    }
}

foreach ($profileColumns as $columnName => $definition) {
    if (!isset($existingColumns[$columnName])) {
        $conn->query("ALTER TABLE jobseeker_profiles ADD COLUMN `" . $conn->real_escape_string($columnName) . "` " . $definition);
    }
}
?>
