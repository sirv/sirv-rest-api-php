<?php

declare(strict_types=1);

namespace Sirv\Resources;

use Sirv\Exception\ApiException;
use Sirv\Exception\AuthenticationException;

/**
 * File management operations.
 *
 * @link https://apidocs.sirv.com/#files
 */
class Files extends AbstractResource
{
    // ==================== File Operations ====================

    /**
     * List folder contents.
     *
     * @param string $dirname Directory path to list
     * @return array Folder contents
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function list(string $dirname): array
    {
        return $this->httpClient->get('/v2/files/readdir', ['dirname' => $dirname]);
    }

    /**
     * Get file information.
     *
     * @param string $filename File path
     * @return array File information
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getInfo(string $filename): array
    {
        return $this->httpClient->get('/v2/files/stat', ['filename' => $filename]);
    }

    /**
     * Upload a file from local path.
     *
     * @param string $localPath Local file path
     * @param string $remotePath Remote file path on Sirv
     * @return array Upload result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function upload(string $localPath, string $remotePath): array
    {
        return $this->httpClient->upload('/v2/files/upload', $localPath, basename($remotePath), [
            'filename' => $remotePath,
        ]);
    }

    /**
     * Upload file content directly.
     *
     * @param string $content File content
     * @param string $remotePath Remote file path on Sirv
     * @param string $contentType MIME type of the content
     * @return array Upload result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function uploadContent(string $content, string $remotePath, string $contentType = 'application/octet-stream'): array
    {
        return $this->httpClient->uploadContent('/v2/files/upload', $content, $contentType, [
            'filename' => $remotePath,
        ]);
    }

    /**
     * Download a file.
     *
     * @param string $filename File path to download
     * @return string File contents
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function download(string $filename): string
    {
        return $this->httpClient->download('/v2/files/download', ['filename' => $filename]);
    }

    /**
     * Delete a file or empty folder.
     *
     * @param string $filename File or folder path to delete
     * @return array Delete result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function delete(string $filename): array
    {
        return $this->httpClient->delete('/v2/files/delete', ['filename' => $filename]);
    }

    /**
     * Copy a file.
     *
     * @param string $from Source file path
     * @param string $to Destination file path
     * @return array Copy result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function copy(string $from, string $to): array
    {
        return $this->httpClient->post('/v2/files/copy', [
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Rename/move a file or folder.
     *
     * @param string $from Current path
     * @param string $to New path
     * @return array Rename result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function rename(string $from, string $to): array
    {
        return $this->httpClient->post('/v2/files/rename', [
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Create an empty folder.
     *
     * @param string $dirname Folder path to create
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function mkdir(string $dirname): array
    {
        return $this->httpClient->post('/v2/files/mkdir', [], ['dirname' => $dirname]);
    }

    /**
     * Fetch a file from a remote URL.
     *
     * @param string $url Remote URL to fetch from
     * @param string $filename Destination path on Sirv
     * @param array $options Additional options
     *        - wait (bool): Wait for fetch to complete
     * @return array Fetch result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function fetch(string $url, string $filename, array $options = []): array
    {
        $data = array_merge(['url' => $url], $options);
        return $this->httpClient->post('/v2/files/fetch', $data, ['filename' => $filename]);
    }

    // ==================== Search Operations ====================

    /**
     * Search files.
     *
     * @param array $params Search parameters
     *        - query (string): Search query
     *        - from (string): Start path
     *        - size (int): Number of results
     *        - sort (array): Sort configuration
     * @return array Search results
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function search(array $params): array
    {
        return $this->httpClient->post('/v2/files/search', $params);
    }

    /**
     * Scroll/paginate through search results.
     *
     * @param string $scrollId Scroll ID from previous search
     * @return array Search results
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function searchScroll(string $scrollId): array
    {
        return $this->httpClient->post('/v2/files/search/scroll', ['scrollId' => $scrollId]);
    }

    // ==================== Metadata Operations ====================

    /**
     * Get all file metadata.
     *
     * @param string $filename File path
     * @return array File metadata
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getMeta(string $filename): array
    {
        return $this->httpClient->get('/v2/files/meta', ['filename' => $filename]);
    }

    /**
     * Set file metadata.
     *
     * @param string $filename File path
     * @param array $meta Metadata to set
     * @return array Updated metadata
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function setMeta(string $filename, array $meta): array
    {
        return $this->httpClient->post('/v2/files/meta', $meta, ['filename' => $filename]);
    }

    /**
     * Get file approval flag.
     *
     * @param string $filename File path
     * @return array Approval status
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getApproval(string $filename): array
    {
        return $this->httpClient->get('/v2/files/meta/approval', ['filename' => $filename]);
    }

    /**
     * Set file approval flag.
     *
     * @param string $filename File path
     * @param bool $approved Approval status
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function setApproval(string $filename, bool $approved): array
    {
        return $this->httpClient->post('/v2/files/meta/approval', ['approved' => $approved], ['filename' => $filename]);
    }

    /**
     * Get file description.
     *
     * @param string $filename File path
     * @return array Description
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getDescription(string $filename): array
    {
        return $this->httpClient->get('/v2/files/meta/description', ['filename' => $filename]);
    }

    /**
     * Set file description.
     *
     * @param string $filename File path
     * @param string $description Description text
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function setDescription(string $filename, string $description): array
    {
        return $this->httpClient->post('/v2/files/meta/description', ['description' => $description], ['filename' => $filename]);
    }

    /**
     * Get file title.
     *
     * @param string $filename File path
     * @return array Title
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getTitle(string $filename): array
    {
        return $this->httpClient->get('/v2/files/meta/title', ['filename' => $filename]);
    }

    /**
     * Set file title.
     *
     * @param string $filename File path
     * @param string $title Title text
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function setTitle(string $filename, string $title): array
    {
        return $this->httpClient->post('/v2/files/meta/title', ['title' => $title], ['filename' => $filename]);
    }

    /**
     * Get product metadata.
     *
     * @param string $filename File path
     * @return array Product metadata
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getProductMeta(string $filename): array
    {
        return $this->httpClient->get('/v2/files/meta/product', ['filename' => $filename]);
    }

    /**
     * Set product metadata.
     *
     * @param string $filename File path
     * @param array $product Product metadata
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function setProductMeta(string $filename, array $product): array
    {
        return $this->httpClient->post('/v2/files/meta/product', $product, ['filename' => $filename]);
    }

    /**
     * Get file tags.
     *
     * @param string $filename File path
     * @return array Tags
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getTags(string $filename): array
    {
        return $this->httpClient->get('/v2/files/meta/tags', ['filename' => $filename]);
    }

    /**
     * Set file tags.
     *
     * @param string $filename File path
     * @param array $tags Tags to set
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function setTags(string $filename, array $tags): array
    {
        return $this->httpClient->post('/v2/files/meta/tags', ['tags' => $tags], ['filename' => $filename]);
    }

    /**
     * Delete file tags.
     *
     * @param string $filename File path
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function deleteTags(string $filename): array
    {
        return $this->httpClient->delete('/v2/files/meta/tags', ['filename' => $filename]);
    }

    // ==================== Batch Operations ====================

    /**
     * Create a ZIP file from multiple files/folders.
     *
     * @param array $filenames Array of file/folder paths to include
     * @param string|null $zipFilename Optional name for the ZIP file
     * @return array Job information with token for checking status
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function zip(array $filenames, ?string $zipFilename = null): array
    {
        $data = ['filenames' => $filenames];
        if ($zipFilename !== null) {
            $data['zipFilename'] = $zipFilename;
        }
        return $this->httpClient->post('/v2/files/zip', $data);
    }

    /**
     * Get ZIP job result.
     *
     * @param string $token Job token from zip() call
     * @return array ZIP job status and result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getZipResult(string $token): array
    {
        return $this->httpClient->get('/v2/files/zip/result', ['token' => $token]);
    }

    /**
     * Delete multiple files in batch.
     *
     * @param array $filenames Array of file paths to delete
     * @return array Job information
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function deleteBatch(array $filenames): array
    {
        return $this->httpClient->post('/v2/files/delete/batch', ['filenames' => $filenames]);
    }

    /**
     * Get batch delete job result.
     *
     * @param string $token Job token from deleteBatch() call
     * @return array Delete job status and result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getDeleteBatchResult(string $token): array
    {
        return $this->httpClient->get('/v2/files/delete/batch/result', ['token' => $token]);
    }

    // ==================== Media Conversion ====================

    /**
     * Convert spin to video.
     *
     * @param string $filename Spin file path
     * @param array $options Conversion options
     * @return array Conversion result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function spinToVideo(string $filename, array $options = []): array
    {
        return $this->httpClient->post('/v2/files/spin2video', $options, ['filename' => $filename]);
    }

    /**
     * Convert video to spin.
     *
     * @param string $filename Video file path
     * @param array $options Conversion options
     * @return array Conversion result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function videoToSpin(string $filename, array $options = []): array
    {
        return $this->httpClient->post('/v2/files/video2spin', $options, ['filename' => $filename]);
    }

    // ==================== Export Operations ====================

    /**
     * Export spin to Amazon.
     *
     * @param string $filename Spin file path
     * @param array $options Export options
     * @return array Export result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function exportToAmazon(string $filename, array $options = []): array
    {
        return $this->httpClient->post('/v2/files/export/amazon', $options, ['filename' => $filename]);
    }

    /**
     * Export spin to Grainger.
     *
     * @param string $filename Spin file path
     * @param array $options Export options
     * @return array Export result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function exportToGrainger(string $filename, array $options = []): array
    {
        return $this->httpClient->post('/v2/files/export/grainger', $options, ['filename' => $filename]);
    }

    /**
     * Export spin to Walmart.
     *
     * @param string $filename Spin file path
     * @param array $options Export options
     * @return array Export result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function exportToWalmart(string $filename, array $options = []): array
    {
        return $this->httpClient->post('/v2/files/export/walmart', $options, ['filename' => $filename]);
    }

    /**
     * Export spin to Home Depot.
     *
     * @param string $filename Spin file path
     * @param array $options Export options
     * @return array Export result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function exportToHomeDepot(string $filename, array $options = []): array
    {
        return $this->httpClient->post('/v2/files/export/homedepot', $options, ['filename' => $filename]);
    }

    /**
     * Export spin to Lowe's.
     *
     * @param string $filename Spin file path
     * @param array $options Export options
     * @return array Export result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function exportToLowes(string $filename, array $options = []): array
    {
        return $this->httpClient->post('/v2/files/export/lowes', $options, ['filename' => $filename]);
    }

    // ==================== Folder & POI Operations ====================

    /**
     * Get folder options.
     *
     * @param string $dirname Folder path
     * @return array Folder options
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getFolderOptions(string $dirname): array
    {
        return $this->httpClient->get('/v2/files/folder/options', ['dirname' => $dirname]);
    }

    /**
     * Set folder options.
     *
     * @param string $dirname Folder path
     * @param array $options Folder options to set
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function setFolderOptions(string $dirname, array $options): array
    {
        return $this->httpClient->post('/v2/files/folder/options', $options, ['dirname' => $dirname]);
    }

    /**
     * Get points of interest for a file.
     *
     * @param string $filename File path
     * @return array Points of interest
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getPoi(string $filename): array
    {
        return $this->httpClient->get('/v2/files/poi', ['filename' => $filename]);
    }

    /**
     * Set points of interest for a file.
     *
     * @param string $filename File path
     * @param array $poi Points of interest data
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function setPoi(string $filename, array $poi): array
    {
        return $this->httpClient->post('/v2/files/poi', $poi, ['filename' => $filename]);
    }

    /**
     * Delete points of interest from a file.
     *
     * @param string $filename File path
     * @return array Result
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function deletePoi(string $filename): array
    {
        return $this->httpClient->delete('/v2/files/poi', ['filename' => $filename]);
    }

    /**
     * Get JWT-protected URL for a file.
     *
     * @param string $filename File path
     * @param array $options JWT options
     *        - expiry (int): Token expiry time in seconds
     * @return array JWT URL information
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function getJwtUrl(string $filename, array $options = []): array
    {
        $query = ['filename' => $filename];
        if (isset($options['expiry'])) {
            $query['expiry'] = $options['expiry'];
        }
        return $this->httpClient->get('/v2/files/jwt', $query);
    }
}
