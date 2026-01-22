# Sirv PHP SDK

Official PHP SDK for the [Sirv REST API](https://apidocs.sirv.com/). This SDK provides a simple and intuitive way to interact with Sirv's image and file management, CDN, 360 spin, and media optimization services.

[![Latest Stable Version](https://poser.pugx.org/sirv/sirv-rest-api-php/v/stable)](https://packagist.org/packages/sirv/sirv-rest-api-php)
[![License](https://poser.pugx.org/sirv/sirv-rest-api-php/license)](https://packagist.org/packages/sirv/sirv-rest-api-php)

## Requirements

- PHP 7.4 or higher
- ext-json
- ext-curl
- Guzzle HTTP client 7.0+

## Installation

Install via Composer:

```bash
composer require sirv/sirv-rest-api-php
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Sirv\SirvClient;

// Initialize the client
$client = new SirvClient(
    'your-client-id',
    'your-client-secret'
);

// Get account information
$account = $client->account()->get();
echo "Account: " . $account['alias'] . "\n";
echo "CDN URL: " . $account['cdnURL'] . "\n";

// List files in a directory
$files = $client->files()->list('/');
foreach ($files['contents'] as $file) {
    echo $file['filename'] . "\n";
}
```

## Authentication

The SDK handles authentication automatically. When you make your first API call, it will obtain an access token using your client credentials. The token is automatically refreshed when it expires.

```php
// Get your API credentials from Sirv Dashboard:
// https://my.sirv.com/#/account/settings/api

$client = new SirvClient(
    'your-client-id',
    'your-client-secret'
);

// Optional: Manually authenticate to verify credentials
try {
    $token = $client->authenticate();
    echo "Authenticated successfully\n";
} catch (\Sirv\Exception\AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage() . "\n";
}
```

## API Resources

### Account

Manage your Sirv account settings and retrieve account information.

```php
// Get account information
$account = $client->account()->get();

// Update account settings
$client->account()->update([
    'minify' => ['enabled' => true],
    'fetching' => ['enabled' => true, 'type' => 'http']
]);

// Get API limits
$limits = $client->account()->getLimits();
echo "API calls used: " . $limits['used'] . "/" . $limits['limit'] . "\n";

// Get storage information
$storage = $client->account()->getStorage();
echo "Storage used: " . $storage['used'] . " bytes\n";

// List account users
$users = $client->account()->getUsers();

// Get billing plan
$plan = $client->account()->getBillingPlan();

// Search account events
$events = $client->account()->searchEvents([
    'module' => 'files',
    'level' => 'error',
    'from' => '2024-01-01T00:00:00.000',
    'to' => '2024-12-31T23:59:59.999'
]);

// Mark events as seen
$client->account()->markEventsSeen(['event-id-1', 'event-id-2']);
```

### Files

Complete file management including upload, download, copy, rename, delete, and metadata operations.

#### Basic Operations

```php
// List folder contents
$files = $client->files()->list('/images');

// Get file information
$info = $client->files()->getInfo('/images/photo.jpg');

// Upload a file
$result = $client->files()->upload('/local/path/photo.jpg', '/images/photo.jpg');

// Upload content directly
$content = file_get_contents('photo.jpg');
$result = $client->files()->uploadContent($content, '/images/photo.jpg', 'image/jpeg');

// Download a file
$content = $client->files()->download('/images/photo.jpg');
file_put_contents('downloaded.jpg', $content);

// Copy a file
$client->files()->copy('/images/photo.jpg', '/backup/photo.jpg');

// Rename/move a file
$client->files()->rename('/images/old-name.jpg', '/images/new-name.jpg');

// Delete a file
$client->files()->delete('/images/photo.jpg');

// Create a folder
$client->files()->mkdir('/images/new-folder');

// Fetch from URL
$result = $client->files()->fetch(
    'https://example.com/image.jpg',
    '/images/fetched.jpg',
    ['wait' => true]
);
```

#### Search

```php
// Search files
$results = $client->files()->search([
    'query' => 'filename:*.jpg',
    'from' => '/images',
    'size' => 100,
    'sort' => ['filename.raw' => 'asc']
]);

// Paginate through results
if (isset($results['_scroll_id'])) {
    $nextPage = $client->files()->searchScroll($results['_scroll_id']);
}
```

#### Metadata

```php
// Get all metadata
$meta = $client->files()->getMeta('/images/photo.jpg');

// Set metadata
$client->files()->setMeta('/images/photo.jpg', [
    'title' => 'My Photo',
    'description' => 'A beautiful sunset'
]);

// Approval flag
$approval = $client->files()->getApproval('/images/photo.jpg');
$client->files()->setApproval('/images/photo.jpg', true);

// Title
$client->files()->setTitle('/images/photo.jpg', 'My Photo Title');
$title = $client->files()->getTitle('/images/photo.jpg');

// Description
$client->files()->setDescription('/images/photo.jpg', 'Photo description');
$desc = $client->files()->getDescription('/images/photo.jpg');

// Product metadata
$client->files()->setProductMeta('/images/product.jpg', [
    'id' => 'SKU123',
    'name' => 'Product Name',
    'brand' => 'Brand Name'
]);
$product = $client->files()->getProductMeta('/images/product.jpg');

// Tags
$client->files()->setTags('/images/photo.jpg', ['nature', 'sunset', 'beach']);
$tags = $client->files()->getTags('/images/photo.jpg');
$client->files()->deleteTags('/images/photo.jpg');
```

#### Batch Operations

```php
// Create ZIP file
$job = $client->files()->zip([
    '/images/photo1.jpg',
    '/images/photo2.jpg',
    '/images/folder'
], 'my-archive.zip');

// Check ZIP status
$result = $client->files()->getZipResult($job['token']);
if ($result['status'] === 'completed') {
    echo "ZIP URL: " . $result['url'] . "\n";
}

// Batch delete
$job = $client->files()->deleteBatch([
    '/images/old1.jpg',
    '/images/old2.jpg'
]);
$result = $client->files()->getDeleteBatchResult($job['token']);
```

#### Media Conversion

```php
// Convert spin to video
$result = $client->files()->spinToVideo('/spins/product.spin', [
    'width' => 1920,
    'height' => 1080
]);

// Convert video to spin
$result = $client->files()->videoToSpin('/videos/product.mp4', [
    'framesPerRow' => 36
]);
```

#### Export to Marketplaces

```php
// Export to Amazon
$result = $client->files()->exportToAmazon('/spins/product.spin', $options);

// Export to Walmart
$result = $client->files()->exportToWalmart('/spins/product.spin', $options);

// Export to Home Depot
$result = $client->files()->exportToHomeDepot('/spins/product.spin', $options);

// Export to Lowe's
$result = $client->files()->exportToLowes('/spins/product.spin', $options);

// Export to Grainger
$result = $client->files()->exportToGrainger('/spins/product.spin', $options);
```

#### Folder Options & Points of Interest

```php
// Folder options
$options = $client->files()->getFolderOptions('/images');
$client->files()->setFolderOptions('/images', [
    'scanSpins' => true
]);

// Points of interest
$poi = $client->files()->getPoi('/images/photo.jpg');
$client->files()->setPoi('/images/photo.jpg', [
    'x' => 0.5,
    'y' => 0.5
]);
$client->files()->deletePoi('/images/photo.jpg');

// Get JWT-protected URL
$jwt = $client->files()->getJwtUrl('/private/photo.jpg', ['expiry' => 3600]);
echo "Protected URL: " . $jwt['url'] . "\n";
```

### Statistics

Retrieve usage statistics for your account.

```php
// HTTP transfer statistics
$httpStats = $client->stats()->getHttp('2024-01-01', '2024-01-31');

// Using DateTime objects
$from = new DateTime('2024-01-01');
$to = new DateTime('2024-01-31');
$httpStats = $client->stats()->getHttp($from, $to);

// Spin viewer statistics (max 5 days range)
$spinStats = $client->stats()->getSpinViews('2024-01-01', '2024-01-05');

// Storage statistics
$storageStats = $client->stats()->getStorage('2024-01-01', '2024-01-31');
```

### User

Retrieve user information.

```php
// Get current user info
$user = $client->user()->get();
echo "Email: " . $user['email'] . "\n";

// Get specific user info
$user = $client->user()->get('user-id-here');
```

## Error Handling

The SDK provides specific exception types for different error scenarios:

```php
use Sirv\SirvClient;
use Sirv\Exception\AuthenticationException;
use Sirv\Exception\ApiException;
use Sirv\Exception\RateLimitException;
use Sirv\Exception\ValidationException;

try {
    $client = new SirvClient('client-id', 'client-secret');
    $files = $client->files()->list('/');
} catch (AuthenticationException $e) {
    // Invalid credentials or authentication failed
    echo "Auth error: " . $e->getMessage() . "\n";
} catch (RateLimitException $e) {
    // Rate limit exceeded
    echo "Rate limited. Retry after: " . $e->getRetryAfter() . " seconds\n";
    echo "Limit: " . $e->getRateLimit() . "\n";
    echo "Remaining: " . $e->getRateLimitRemaining() . "\n";
} catch (ValidationException $e) {
    // Invalid request parameters
    echo "Validation error: " . $e->getMessage() . "\n";
    print_r($e->getValidationErrors());
} catch (ApiException $e) {
    // Other API errors
    echo "API error: " . $e->getMessage() . "\n";
    echo "HTTP Status: " . $e->getHttpStatusCode() . "\n";
    echo "Request ID: " . $e->getRequestId() . "\n";
    print_r($e->getErrorDetails());
}
```

## Configuration Options

### Timeout

```php
// Set custom timeout (in seconds)
$client = new SirvClient('client-id', 'client-secret', 60);
```

### Custom Guzzle Options

```php
// Pass additional Guzzle HTTP client options
$client = new SirvClient('client-id', 'client-secret', 30, [
    'proxy' => 'http://proxy.example.com:8080',
    'verify' => false, // Disable SSL verification (not recommended for production)
]);
```

## Testing

```bash
# Run tests
composer test

# Run static analysis
composer phpstan

# Run code style check
composer cs-check

# Fix code style
composer cs-fix
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- [Sirv Documentation](https://sirv.com/help/)
- [API Documentation](https://apidocs.sirv.com/)
- [GitHub Issues](https://github.com/sirv/sirv-rest-api-php/issues)
- [Sirv Support](https://sirv.com/contact/)

## Links

- [Sirv Website](https://sirv.com/)
- [Sirv Dashboard](https://my.sirv.com/)
- [Packagist Package](https://packagist.org/packages/sirv/sirv-rest-api-php)
