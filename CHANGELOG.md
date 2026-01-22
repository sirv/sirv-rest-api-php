# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-21

### Added
- Initial release of the Sirv PHP SDK
- Authentication with automatic token refresh
- Account management operations
  - Get account information
  - Update account settings
  - Get API limits and storage usage
  - List account users
  - Get billing plan details
  - Search and manage account events
- Complete file management
  - List, upload, download, copy, rename, delete files
  - Create folders
  - Fetch files from remote URLs
  - Search files with pagination
- Metadata operations
  - Get/set file metadata
  - Manage approval flags, titles, descriptions
  - Product metadata support
  - Tag management
- Batch operations
  - Create ZIP archives
  - Batch delete files
- Media conversion
  - Spin to video conversion
  - Video to spin conversion
- Export to marketplaces
  - Amazon, Walmart, Home Depot, Lowe's, Grainger
- Folder options management
- Points of interest support
- JWT-protected URL generation
- Statistics API
  - HTTP transfer statistics
  - Spin viewer statistics
  - Storage statistics
- User information retrieval
- Comprehensive error handling
  - AuthenticationException
  - ApiException
  - RateLimitException
  - ValidationException
- Full documentation and examples
