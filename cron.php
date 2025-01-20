<?php
include 'connection.php';

$value = time();

// SQL query to insert $value into the `test_cron` table
$query = "INSERT INTO test_cron (value) VALUES ('$value')";

// Execute the query
if (mysqli_query($conn, $query)) {
    echo "Value inserted successfully: $value";
} else {
    echo "Error inserting value: " . mysqli_error($conn);
}

// Close the database connection
mysqli_close($conn);
?>
