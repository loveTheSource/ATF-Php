<?php

namespace ATFApp\Helper;

use ATFApp\Helper;

/**
 * 'handleUpload' handles multiple file post uploads
 * (in one file input multiple)
 * in 4 steps:
 * 
 * - check if file was uploaded successfully
 * - move file to quarantine using a temporary name
 * - validate file (mimetype matches extension, image recreation, ...)
 * - move file to destination folder setting the final name
 * - cleanup (TODO)
 */
class UploadMulti {

    private $filesizeMin = 128; // bytes
    private $filesizeMax = 1024 * 1024; // 1MB
    private $destination = null;

    // min/max image dimensions
    private $imageWidthMin = null;
    private $imageWidthMax = null;
    private $imageHeightMin = null;
    private $imageHeightMax = null;

    private $fileInputId = null;
    private $filePrefix = '';
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

    public function handleUpload($fileInputId, $destination, $prefix='') {
        $this->fileInputId = $fileInputId;
        $this->destination = $destination;
        $this->filePrefix = $prefix;

        $this->checkAndHandleAll();

        return [
            'success' => empty($this->errors),
            'errors' => $this->errors,
            'succeededFiles' => $this->succeededFiles,
            'filesData' => $this->filesData
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
        try {
            if (!is_dir($this->destination)) {
                // try to create missing dir
                mkdir($this->destination);
            }    
        } catch(\Throwable $e) {
            $this->addError($this->fileInputId, $e->getMessage(), false, true);
        }

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
                        $this->succeededFiles[$id] = [
                            'filename' => $data['desiredFilename'],
                            'extension' => $data['extension'],
                            'originalName' => $data['originalName']
                        ];
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
                foreach ($_FILES[$this->fileInputId]['tmp_name'] as $tmpFile) {
                    if (is_file($tmpFile)) {
                        unlink($tmpFile);
                    }
                }
                if (!array_key_exists($id, $this->succeededFiles)) {
                    // TODO cleanup
                    // delete from quarantine
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

    private function getUniqueName($addPrefix=false) {
        $unique = md5(time() . uniqid());
        if ($addPrefix && !empty($this->filePrefix)) {
            return $this->filePrefix . '-' . $unique;
        } else {
            return $unique;
        }
    }

    private function checkForUploadSuccess() {
        try {
            if (!isset($_FILES[$this->fileInputId])) {
                $this->addError($this->fileInputId, "file_upload_failed", true);
                $this->filesData[$this->fileInputId] = [];
                $this->filesData[$this->fileInputId][0] = ['uploadSuccess' => false ];
            } else {
                $total = count($_FILES[$this->fileInputId]['name']);
                $uploadIndex = 0;
                for ($i=0; $i<$total; $i++) {
                    $fileKey = $this->fileInputId.'-'.$i;

                    if (empty($_FILES[$this->fileInputId]['name'][$i]) || empty(Format::cleanupFilename($_FILES[$this->fileInputId]['name'][$i]))) {
                        break;
                    }
                    if (empty($_FILES[$this->fileInputId]['tmp_name'][$i])) {
                        break;
                    }

                    $uploadIndex++;

                    $pathInfo = pathinfo($_FILES[$this->fileInputId]['name'][$i]);
                    if (empty($pathInfo['filename'])) {
                        $this->addError($fileKey, "file_invalid", true);
                        $nameWithoutExt = '';
                    } else {
                        $nameWithoutExt = $pathInfo['filename'];
                    }
                    if (empty($pathInfo['extension'])) {
                        $this->addError($fileKey, "file_invalid_ext", true);
                        $extOnly = '';
                    } else {
                        $extOnly = $pathInfo['extension'];
                    }

                    $this->filesData[$fileKey] = [
                        'originalName' => Format::cleanupFilename($_FILES[$this->fileInputId]['name'][$i], true),
                        'tmpName' => $_FILES[$this->fileInputId]['tmp_name'][$i],
                        'filename' => $nameWithoutExt,
                        'extension' => $extOnly,
                        'desiredFilename' => $this->getUniqueName(true),
                        'uploadSuccess' => true,
                        'uploadIndex' => $uploadIndex
                    ];
                }
            }    
        } catch (\Throwable $e) {
            $this->addError($this->fileInputId, $e->getMessage(), false, true);
        }
    }

    private function addError($id, $error, $returnToUser=false, $catchedException=false) {
        if (!array_key_exists($id, $this->errors)) {
            $this->errors[$id] = [];
        }
        $this->errors[$id][] = [
            'msg' => $error,
            'return' => ($returnToUser !== false),
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
                    $this->addError($id, "check_failed_image", true);
                    return false;
                } else {
                    return $this->validateImage($id, $quarantineFile, $file);
                }
                break;
    
                case "document":
                if (!$file->isDocument()) {
                    $this->addError($id, "check_failed_document", true);
                    return false;
                }
                break;
    
                case "audio":
                if (!$file->isAudio()) {
                    $this->addError($id, "check_failed_audio", true);
                    return false;
                }
                break;
    
                case "video":
                if (!$file->isVideo()) {
                    $this->addError($id, "check_failed_video", true);
                    return false;
                }
                break;
    
                case "archive":
                if (!$file->isArchive()) {
                    $this->addError($id, "check_failed_archive", true);
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
            $this->addError($id, "dimensions_width_gt_max", true);
            return false;
        }
        if ($this->imageHeightMax !== 0 && $imgH > $this->imageHeightMax) {
            $this->addError($id, "dimensions_height_gt_max", true);
            return false;    
        }
        // min dimensions
        if ($this->imageWidthMin !== 0 && $imgW < $this->imageWidthMin) {
            $this->addError($id, "dimensions_width_lt_max", true);
            return false;
        }
        if ($this->imageHeightMin !== 0 && $imgH < $this->imageHeightMin) {
            $this->addError($id, "dimensions_height_lt_max", true);
            return false;    
        }

        // recreate image
        $recreated = $this->imageHelper->recreateImage($file);
        if (!$recreated) {
            $this->addError($id, "recreation_failed", true);
            return false;
        }

        return true;
    }
} 