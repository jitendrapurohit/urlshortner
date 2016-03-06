<?php
require_once('config.php');

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
// Create database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_DATABASE;
if ($conn->query($sql) === TRUE) {
  echo nl2br("Database created successfully.\n");
} else {
  echo "Error creating database: " . $conn->error;
}
mysqli_select_db($conn, DB_DATABASE);

$createTable = "CREATE TABLE IF NOT EXISTS " . DB_TABLENAME . "(
  id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  long_url VARCHAR(255) NOT NULL,
  short_url VARBINARY(6) NOT NULL,

  PRIMARY KEY (id),
  KEY short_url (short_url)
)
ENGINE=InnoDB;";

if ($conn->query($createTable) === TRUE) {
  echo nl2br("\nTable created successfully.");
} else {
  echo "Error creating table: " . $conn->error;
}

$conn->close();
