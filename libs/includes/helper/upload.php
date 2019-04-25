<?php

namespace ATFApp\Helper;

use ATFApp\Helper;

/**
 * 'handleUpload' handles file uploads via post
 * in 4 steps:
 * 
 * - check if file was uploaded successfully
 * - move file to quarantine using a temporary name
 * - validate file (mimetype matches extension, image recreation, ...)
 * - move file to destination folder setting the final name
 */
class Upload {

    private $filesizeMin = 128; // bytes
    private $filesizeMax = 1024 * 1024; // 1MB
    private $destination = null;

    // min/max image dimensions
    private $imageWidthMin = null;
    private $imageWidthMax = null;
    private $imageHeightMin = null;
    private $imageHeightMax = null;

    private $files = [];    // ['file_id' => 'filename', 'file2_id' => 'filename2'] filenames without extension
    private $filesData = [];
    private $succeededFiles = [];
    private $allowedTypes = [];

    private $mimeHelper = null;
    private $imageHelper = null;

    private $errors = [];

    public function __construct() {
        $this->mimeHelper = new Helper\Mimetype();
        $this->imageHelper = new Helper\Image();
    }

    public function handleUpload($files, $destination) {
        $this->files = $files;
        $this->destination = $destination;

        $this->checkAndHandleAll();

        return [
            'success' => empty($this->errors),
            'errors' => $this->errors,
            'succeededFiles' => $this->succeededFiles
        ];
    }

    public function setAllowedTypes($allowedTypes) {
        $this->allowedTypes = $allowedTypes;
    }

    public function setMinFilesize($size) {
        $this->filesizeMin = $size;
    }

    public function setMaxFilesize($size) {
        $this->filesizeMax = $size;
    }

    public function setMaxImageDimensions($width=0, $height=0) {
        $this->imageHeightMax = $height;
        $this->imageWidthMax = $width;
    }

    public function setMinImageDimensions($width=0, $height=0) {
        $this->imageHeightMin = $height;
        $this->imageWidthMin = $width;
    }


    private function checkAndHandleAll() {
        // check if upload was successful
        $this->checkForUploadSuccess();

        // move files to quarantine
        $this->moveToQuarantine();

        // validate files in quarantine
        // if file is an image it will be recreated using GD
        $this->validateQuarantineFiles();

        // move to destination folder 
        $this->moveToDestination();

        // delete files from quarantine and tmp folder
        $this->cleanup();
    }

    private function moveToDestination() {
        foreach ($this->filesData as $id => $data) {
            try {
                if ($data['uploadSuccess'] && !array_key_exists($id, $this->errors)) {
                    // move to final destination
                    $quarantineFile = QUARANTINE_PATH . $data['moveToFile'];
                    $destinationFilePath = $this->destination . $data['desiredFilename'] . '.' . $data['extension'];
                    $result = rename($quarantineFile, $destinationFilePath); // TODO move file
                    if (!$result) {
                        $this->addError($id, 'unable to move file "' . $quarantineFile . '" to final destination: ' . $destinationFilePath);
                    } else {
                        $this->succeededFiles[$id] = $data['desiredFilename'] . '.' . $data['extension'];
                    }
                }
            } catch (\Throwable $e) {
                $this->addError($id, $e->getMessage(), false, true);
            }
        }
    }

    private function cleanup() {
        foreach ($this->filesData as $id => $data) {
            try {
                if (!array_key_exists($id, $this->succeededFiles)) {
                    // TODO cleanup
                }
            } catch (\Throwable $e) {
                $this->addError($id, $e->getMessage(), false, true);
            }
        }
    }

    private function validateQuarantineFiles() {
        foreach ($this->filesData as $id => $data) {
            try {
                if ($data['uploadSuccess'] && !array_key_exists($id, $this->errors)) {
                    if (!$this->validateFile($id, $data)) {
                        $this->addError($id, "file invalid");
                    }
                }
            } catch (\Throwable $e) {
                $this->addError($id, $e->getMessage(), false, true);
            }
        }
    }

    private function moveToQuarantine() {
        foreach ($this->filesData as $id => $data) {
            try {
                if ($data['uploadSuccess'] && !array_key_exists($id, $this->errors)) {
                    $moveToFile = $this->getTmpId() . '.' . $data['extension'];
                    $this->filesData[$id]['moveToFile'] = $moveToFile;
                    $result = move_uploaded_file($data['tmpName'], QUARANTINE_PATH . $moveToFile);
                    if ($result !== true) {
                        $this->addError($id, 'failed to move file "' . $data['tmpName'] . '" to quarantine "' . $moveToFile . '"');
                    }
                }
            } catch (\Throwable $e) {
                $this->addError($id, $e->getMessage(), false, true);
            }
        }
    }

    private function getTmpId() {
        return md5(time()) . md5(uniqid());
    }

    private function checkForUploadSuccess() {
        foreach ($this->files as $id => $filename) {
            try {
                if (!isset($_FILES[$id])) {
                    $this->addError($id, "file_upload_failed", true);
                    $this->filesData[$id] = [
                        'uploadSuccess' => false
                    ];
                } else {
                    $pathInfo = pathinfo($_FILES[$id]['name']);
                    if (empty($pathInfo['filename'])) {
                        $this->addError($id, "file_invalid", true);
                        $nameWithoutExt = '';
                    } else {
                        $nameWithoutExt = $pathInfo['filename'];
                    }
                    if (empty($pathInfo['extension'])) {
                        $this->addError($id, "file_invalid_ext", true);
                        $extOnly = '';
                    } else {
                        $extOnly = $pathInfo['extension'];
                    }
    
                    $this->filesData[$id] = [
                        'originalName' => $_FILES[$id]['name'],
                        'tmpName' => $_FILES[$id]['tmp_name'],
                        'filename' => $nameWithoutExt,
                        'extension' => $extOnly,
                        'desiredFilename' => $filename,
                        'uploadSuccess' => true
                    ];
                }    
            } catch (\Throwable $e) {
                $this->addError($id, $e->getMessage(), false, true);
            }
        }
    }

    private function addError($id, $error, $returnToUser=false, $catchedException=false) {
        if (!array_key_exists($id, $this->errors)) {
            $this->errors[$id] = [];
        }
        $this->errors[$id][] = [
            'msg' => $error,
            'return' => $returnToUser,
            'catchedException' => $catchedException
        ];
    }

    private function validateFile($id, $fileData) {
        try {
            $quarantineFile = QUARANTINE_PATH . $fileData['moveToFile'];
            if (!is_file($quarantineFile) || !is_readable($quarantineFile)) {
                $this->addError($id, 'file not found or not readable in quarantine: ' . $quarantineFile);
                return false;
            }
            
            $file = new Helper\File($quarantineFile);
    
            // check filesize min/max
            $size = $file->getSize();
            if ($size < $this->filesizeMin) {
                $this->addError($id, "file_too_small", true);
                return false;
            }
            if ($size > $this->filesizeMax) {
                $this->addError($id, "file_too_large", true);
                return false;
            }
    
            $expectedFileType = $this->mimeHelper->getExpectedType($file);
    
            // check if extension is allowed
            if (!in_array($fileData['extension'], $this->allowedTypes)) {
                $extensionMap = $this->mimeHelper->getExtensionMapping($fileData['extension'], $expectedFileType);
                if (!$extensionMap) {
                    $this->addError($id, "file_extension_not_allowed", true);
                    return false;
                } elseif (!in_array($extensionMap, $this->allowedTypes)) {
                    $this->addError($id, "file_extension_not_allowed", true);
                    return false;
                }
            }
    
            // validate file tyoe
            switch ($expectedFileType) {
                case "image":
                if (!$file->isImage()) {
                    $this->addError($id, "check_failed_image");
                    return false;
                } else {
                    return $this->validateImage($id, $quarantineFile, $file);
                }
                break;
    
                case "document":
                if (!$file->isDocument()) {
                    $this->addError($id, "check_failed_document");
                    return false;
                }
                break;
    
                case "audio":
                if (!$file->isAudio()) {
                    $this->addError($id, "check_failed_audio");
                    return false;
                }
                break;
    
                case "video":
                if (!$file->isVideo()) {
                    $this->addError($id, "check_failed_video");
                    return false;
                }
                break;
    
                case "archive":
                if (!$file->isArchive()) {
                    $this->addError($id, "check_failed_archive");
                    return false;
                }
                break;
    
                default:
                $this->addError($id, "file type unknown '" . $file->getType());
                return false;
            }
    
            // all checks passed
            return true;
    
        } catch(\Throwable $e) {
            $this->addError($id, $e->getMessage(), true);
            return false;
        }

    }

    private function validateImage($id, $quarantineFile, \ATFApp\Helper\File $file) {
        $imgW = $this->imageHelper->getImageWidth($quarantineFile);
        $imgH = $this->imageHelper->getImageHeight($quarantineFile);
        
        // max dimensions
        if ($this->imageWidthMax !== 0 && $imgW > $this->imageWidthMax) {
            $this->addError($id, "dimensions_width_gt_max");
            return false;
        }
        if ($this->imageHeightMax !== 0 && $imgH > $this->imageHeightMax) {
            $this->addError($id, "dimensions_height_gt_max");
            return false;    
        }
        // min dimensions
        if ($this->imageWidthMin !== 0 && $imgW < $this->imageWidthMin) {
            $this->addError($id, "dimensions_width_lt_max");
            return false;
        }
        if ($this->imageHeightMin !== 0 && $imgH < $this->imageHeightMin) {
            $this->addError($id, "dimensions_height_lt_max");
            return false;    
        }

        // recreate image
        $recreated = $this->imageHelper->recreateImage($file);
        if (!$recreated) {
            $this->addError($id, "recreation_failed");
            return false;
        }

        return true;
    }
} 