<?php
// Include the database connection file
include('db_connection.php');

function insertMessage($fullname, $email, $subject, $message) {
    global $pdo; // Use the global PDO connection

    // Get the current timestamp for 'created_at' field
    $created_at = date('Y-m-d H:i:s');

    // Prepare the SQL insert query
    $sql = "INSERT INTO information (fullname, email, subject, message, created_at)
            VALUES (:fullname, :email, :subject, :message, :created_at)";

    try {
        // Prepare the statement
        $stmt = $pdo->prepare($sql);
        
        // Bind the parameters to the SQL query
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':created_at', $created_at);

        // Execute the query
        $stmt->execute();
        
        // Return success message
        return "Message successfully inserted!";
    } catch (PDOException $e) {
        // Handle any errors
        return "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $result = insertMessage($fullname, $email, $subject, $message);

    if (strpos($result, 'successfully') !== false) {
        // Redirect back to the same page with a success message
        header('Location: index.php');
        exit();
    } else {
        echo $result; // Display error message
    }
}
?>
