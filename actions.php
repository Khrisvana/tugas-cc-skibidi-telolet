<?php
// Include the database connection file
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

include('db_connection.php');

// Function to insert a message into the database
function insertMessage($fullname, $email, $subject, $message, $image) {
    global $pdo; // Use the global PDO connection

    // Get the current timestamp for 'created_at' field
    $created_at = date('Y-m-d H:i:s');

    // Prepare the SQL insert query
    $sql = "INSERT INTO information (fullname, email, subject, message, created_at, image)
            VALUES (:fullname, :email, :subject, :message, :created_at, :image)";

    try {
        // Prepare the statement
        $stmt = $pdo->prepare($sql);

        // Bind the parameters to the SQL query
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':created_at', $created_at);
        $stmt->bindParam(':image', $image);

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

    $ftpHost = $_ENV['FTP_HOST'];
    $ftpUsername = $_ENV['FTP_USERNAME'];
    $ftpPassword = $_ENV['FTP_PASSWORD'];

    if (!isset($_FILES['image']) || $_FILES['image']['error'] != UPLOAD_ERR_OK) {
        echo "No image uploaded or there was an error.";
        exit();
    }

    $tempPath = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $ftpPath = "/" . $fileName;

    $ftp_connection = ftp_connect($ftpHost);
    $ftp_login = ftp_login($ftp_connection, $ftpUsername, $ftpPassword);

    if (!$ftp_connection || !$ftp_login) {
        echo "Could not connect to the FTP server.";
        exit();
    }

    $remoteDirectory = dirname($ftpPath);

    $files = ftp_nlist($ftp_connection, ".");

    if (!ftp_chdir($ftp_connection, $remoteDirectory)) {
        echo "Error: Could not change to remote directory: $remoteDirectory";
        ftp_close($ftp_connection);
        exit();
    }

    if (!ftp_put($ftp_connection, $ftpPath, $tempPath, FTP_BINARY)) {
        echo "Failed to upload the image to the FTP server.";
        ftp_close($ftp_connection);
        exit();
    }

    $result = insertMessage($fullname, $email, $subject, $message, $ftpPath);

    ftp_close($ftp_connection);

    if (strpos($result, 'successfully') !== false) {
        header('Location: index.php');
        exit();
    } else {
        echo $result;
    }
}
