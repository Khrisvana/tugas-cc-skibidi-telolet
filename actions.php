<?php
// Include the database connection file
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
    // Get form input values
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // FTP connection details
    $ftpHost = 'ftp_server';
    $ftpUsername = 'user';
    $ftpPassword = 'password';

    // Check if file is uploaded and there's no error
    if (!isset($_FILES['image']) || $_FILES['image']['error'] != UPLOAD_ERR_OK) {
        echo "No image uploaded or there was an error.";
        exit();
    }

    // File upload handling
    $tempPath = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $ftpPath = "/" . $fileName;

    // Connect to the FTP server
    $ftp_connection = ftp_connect($ftpHost);
    $ftp_login = ftp_login($ftp_connection, $ftpUsername, $ftpPassword);

    if (!$ftp_connection || !$ftp_login) {
        echo "Could not connect to the FTP server.";
        exit();
    }

    // if (ftp_pasv($ftp_connection, true) === false) {
    //     throw new \Exception("could not enable passive mode");
    // }

    $remoteDirectory = dirname($ftpPath);

    $files = ftp_nlist($ftp_connection, ".");

    // Check if directory exists and change to it
    if (!ftp_chdir($ftp_connection, $remoteDirectory)) {
        echo "Error: Could not change to remote directory: $remoteDirectory";
        ftp_close($ftp_connection);
        exit();
    }

    // Upload the file to the FTP server
    if (!ftp_put($ftp_connection, $ftpPath, $tempPath, FTP_BINARY)) {
        echo "Failed to upload the image to the FTP server.";
        ftp_close($ftp_connection);
        exit();
    }

    // Insert the record into the database
    $result = insertMessage($fullname, $email, $subject, $message, $ftpPath);

    // Close the FTP connection
    ftp_close($ftp_connection);

    // Redirect or display result
    if (strpos($result, 'successfully') !== false) {
        // Redirect back to the same page with a success message
        header('Location: index.php');
        exit();
    } else {
        // Display error message
        echo $result;
    }
}
