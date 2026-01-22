<?php

/**
 * Sirv PHP SDK - Basic Usage Example
 *
 * This example demonstrates the basic functionality of the Sirv PHP SDK.
 *
 * Before running this example:
 * 1. Install the SDK: composer require sirv/sirv-rest-api-php
 * 2. Get your API credentials from: https://my.sirv.com/#/account/settings/api
 * 3. Replace 'your-client-id' and 'your-client-secret' with your actual credentials
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Sirv\SirvClient;
use Sirv\Exception\AuthenticationException;
use Sirv\Exception\ApiException;
use Sirv\Exception\RateLimitException;

// Initialize the client with your API credentials
$client = new SirvClient(
    'your-client-id',
    'your-client-secret'
);

try {
    // ==================== Account Information ====================
    echo "=== Account Information ===\n";

    $account = $client->account()->get();
    echo "Account alias: " . ($account['alias'] ?? 'N/A') . "\n";
    echo "CDN URL: " . ($account['cdnURL'] ?? 'N/A') . "\n";

    // Get storage usage
    $storage = $client->account()->getStorage();
    echo "Storage used: " . number_format($storage['used'] ?? 0) . " bytes\n";
    echo "Storage allowance: " . number_format($storage['allowance'] ?? 0) . " bytes\n";

    // Get API limits
    $limits = $client->account()->getLimits();
    echo "API calls: " . ($limits['count'] ?? 0) . " / " . ($limits['limit'] ?? 0) . "\n";

    echo "\n";

    // ==================== File Operations ====================
    echo "=== File Operations ===\n";

    // List files in root directory
    $files = $client->files()->list('/');
    echo "Files in root directory:\n";
    if (!empty($files['contents'])) {
        foreach ($files['contents'] as $file) {
            $type = $file['isDirectory'] ? '[DIR]' : '[FILE]';
            echo "  {$type} {$file['filename']}\n";
        }
    } else {
        echo "  (empty)\n";
    }

    echo "\n";

    // ==================== Upload Example ====================
    // Uncomment to test file upload
    /*
    echo "=== Upload Example ===\n";
    $localFile = '/path/to/local/image.jpg';
    $remotePath = '/test-uploads/image.jpg';

    if (file_exists($localFile)) {
        $result = $client->files()->upload($localFile, $remotePath);
        echo "File uploaded successfully to: {$remotePath}\n";
    }
    */

    // ==================== Search Example ====================
    echo "=== Search Example ===\n";

    $searchResults = $client->files()->search([
        'query' => '*',
        'size' => 5
    ]);

    echo "Found " . ($searchResults['total'] ?? 0) . " files\n";
    if (!empty($searchResults['hits'])) {
        foreach (array_slice($searchResults['hits'], 0, 5) as $hit) {
            echo "  - " . ($hit['_source']['filename'] ?? 'unknown') . "\n";
        }
    }

    echo "\n";

    // ==================== Statistics ====================
    echo "=== Statistics ===\n";

    $from = (new DateTime())->modify('-30 days')->format('Y-m-d');
    $to = (new DateTime())->format('Y-m-d');

    $storageStats = $client->stats()->getStorage($from, $to);
    echo "Storage stats retrieved for {$from} to {$to}\n";

    echo "\n=== Done! ===\n";

} catch (AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage() . "\n";
    echo "Please check your client ID and client secret.\n";
    exit(1);
} catch (RateLimitException $e) {
    echo "Rate limit exceeded!\n";
    echo "Retry after: " . $e->getRetryAfter() . " seconds\n";
    exit(1);
} catch (ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    echo "HTTP Status: " . $e->getHttpStatusCode() . "\n";
    if ($e->getRequestId()) {
        echo "Request ID: " . $e->getRequestId() . "\n";
    }
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
