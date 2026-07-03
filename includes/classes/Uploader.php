<?php
/**
 * Future Vision College – File Uploader
 * Save to: includes/classes/Uploader.php
 */

declare(strict_types=1);

class Uploader
{
    private string $uploadDir;
    private array  $allowedTypes;
    private int    $maxSize;
    private string $error = '';

    public function __construct(
        string $uploadDir,
        array  $allowedTypes = [],
        int    $maxSize      = 0
    ) {
        $this->uploadDir    = rtrim($uploadDir, '/') . '/';
        $this->allowedTypes = $allowedTypes ?: ALLOWED_IMAGE_TYPES;
        $this->maxSize      = $maxSize      ?: MAX_FILE_SIZE;
    }

    /**
     * Upload a file from $_FILES array
     *
     * @param  array  $file   $_FILES['field_name']
     * @param  string $prefix Optional filename prefix (e.g. 'student_')
     * @return string|false   Stored filename on success, false on failure
     */
    public function upload(array $file, string $prefix = ''): string|false
    {
        // 1. Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error = $this->uploadErrorMessage($file['error']);
            return false;
        }

        // 2. Validate file size
        if ($file['size'] > $this->maxSize) {
            $maxMB = round($this->maxSize / (1024 * 1024), 1);
            $this->error = "File too large. Maximum allowed size is {$maxMB} MB.";
            return false;
        }

        // 3. Validate MIME type using finfo (not just extension)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedTypes, true)) {
            $allowed = implode(', ', $this->allowedTypes);
            $this->error = "Invalid file type '{$mimeType}'. Allowed: {$allowed}.";
            return false;
        }

        // 4. Build safe filename
        $extension = $this->mimeToExtension($mimeType);
        $filename  = $prefix . uniqid('', true) . '_' . time() . '.' . $extension;

        // 5. Ensure directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // 6. Move file
        $destination = $this->uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->error = 'Failed to move uploaded file. Check server permissions.';
            return false;
        }

        return $filename;
    }

    /**
     * Delete an uploaded file
     */
    public function delete(string $filename): bool
    {
        if (empty($filename)) return false;
        $path = $this->uploadDir . $filename;
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Get last error message
     */
    public function getError(): string
    {
        return $this->error;
    }

    // ── Helpers ───────────────────────────────────────────────

    private function mimeToExtension(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            'application/pdf' => 'pdf',
            default      => 'bin',
        };
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File exceeds maximum allowed size.',
            UPLOAD_ERR_PARTIAL   => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE   => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            default              => 'Unknown upload error.',
        };
    }
}
