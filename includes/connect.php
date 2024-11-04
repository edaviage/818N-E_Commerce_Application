<?php 

require __DIR__ . '/vendor/autoload.php';  

use Aws\Sdk;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Database connection parameters
$con = new mysqli('webappdatabase.c92ki8662z0f.us-east-1.rds.amazonaws.com', 'admin', '0157cs131023', 'ecommerce_1');

// Check connection
if ($con->connect_error) {
    die("Database connection failed: " . $con->connect_error);
}

function moveToS3($imageType, $s3fileName, $localFileName)
{
    $s3Client = new S3Client([
        'version' => 'latest',
        'region'  => 'us-east-1'
    ]);

    $bucket = 'webapp-cdn-bucket';

    switch ($imageType) {
        case 'User':
            $key = 'user_images/' . $s3fileName;
            break;
        case 'Admin':
            $key = 'admin/admin_images/' . $s3fileName;
            break;
        case 'Product':
        default:
            $key = 'admin/product_images/' . $s3fileName;
            break;
    }

    try {
        $s3Client->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $localFileName
        ]);
    } catch (AwsException $e) {
        echo "S3 Upload Error: " . $e->getMessage();
    }
}

function getImagesFromS3($filePath) {
    $s3Client = new S3Client([
        'version' => 'latest',
        'region'  => 'us-east-1'
    ]);

    try {
        // Get the object from S3
        $result = $s3Client->getObject([
            'Bucket' => 'webapp-cdn-bucket',
            'Key' => $filePath
        ]);

        // Get the content of the file
        $imageContent = $result['Body']->getContents();
        // Get the MIME type of the file
        $mimeType = $result['ContentType'];

        // Return the raw image content and MIME type in an array
        return [
            'mimeType' => $mimeType,
            'content' => $imageContent
        ];
    } catch (AwsException $e) {
        echo "S3 Retrieval Error: " . $e->getMessage();
        return null;
    }
}

?>
