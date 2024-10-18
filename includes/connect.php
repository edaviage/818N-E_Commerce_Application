<?php
require __DIR__ . '/vendor/autoload.php'; // Adjust the path if necessary

use Aws\Sdk;
use Aws\Exception\AwsException;

// Function to create a database connection
function createDatabaseConnection() {
    try {
        // Create an AWS SDK instance
        $sdk = new Sdk([
            'region' => 'us-east-1', // Replace with your region
            'version' => 'latest'
        ]);

        // Create a Secrets Manager client
        $secretsManager = $sdk->createSecretsManager();

        // Your secret name
        $secretName = 'MyDatabaseCredentials';

        // Retrieve the secret
        $result = $secretsManager->getSecretValue([
            'SecretId' => $secretName,
        ]);

        // Decode the secret JSON string into an array
        if (isset($result['SecretString'])) {
            $secret = json_decode($result['SecretString'], true);
            
            // Access your database credentials from the secret
            $host = $secret['DB_HOST'];
            $username = $secret['DB_USER'];
            $password = $secret['DB_PASS'];
            $db_name = $secret['DB_NAME'];
            $port = $secret['DB_PORT'];

            // Create a new mysqli connection
            $con = new mysqli($host, $username, $password, $db_name, $port);

            // Check the connection
            if ($con->connect_error) {
                die("Connection failed: " . $con->connect_error);
            }

            return $con; // Return the connection object
        }
    } catch (AwsException $e) {
        // Output error message if fails
        die("Error retrieving secret: " . htmlspecialchars($e->getMessage()));
    } catch (Exception $e) {
        die("An error occurred: " . htmlspecialchars($e->getMessage()));
    }

    return null; // Return null if the connection could not be established
}

// Example usage
$con = createDatabaseConnection();
if ($con) {
    echo "Connected successfully";
    // You can now use $con to interact with your database
}

// Close the connection when done
if ($con) {
    $con->close();
}
?>
