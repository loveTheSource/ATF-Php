<?php

namespace ATFApp\Helper;

use ATFApp\BasicFunctions;

class Mimetype {

    private $mimeConf = null;

    public function __construct() {
        $this->mimeConf = BasicFunctions::getConfig('mimetypes');
    }

    public function isImage(\ATFApp\Helper\File $file) {
        return $this->checkMimetypeExtension($file, 'image');
    }

    public function isDocument(\ATFApp\Helper\File $file) {
        return $this->checkMimetypeExtension($file, 'document');
    }

    public function isAudio(\ATFApp\Helper\File $file) {
        return $this->checkMimetypeExtension($file, 'audio');
    }

    public function isVideo(\ATFApp\Helper\File $file) {
        return $this->checkMimetypeExtension($file, 'video');
    }

    public function isArchive(\ATFApp\Helper\File $file) {
        return $this->checkMimetypeExtension($file, 'archive');
    }

    public function mimetypeIsDisplayable($mimetype) {
        if (array_key_exists($mimetype, $this->mimeConf['mimetype_extension']['image'])) {
            return true;
        }
        return false;
    }

    /** */
    public function getExpectedType(\ATFApp\Helper\File $file) {
        $mime = $file->getMimeType();
        foreach ($this->mimeConf['extension_mimetype'] as $group => $arr) {
            if (in_array($mime, $arr)) {
                return $group;
            }
        }
        return false;
    }

    public function getExtensionMapping($ext, $group) {
        if (isset($this->mimeConf['extensions_map'][$group])) {
            if (array_key_exists($ext, $this->mimeConf['extensions_map'][$group])) {
                return $this->mimeConf['extensions_map'][$group][$ext];
            }
        }
        return false;
    }




    private function checkMimetypeExtension(\ATFApp\Helper\File $file, $group) {
        $mime = $file->getMimeType();
        $ext = $file->getExtension();
        if ($this->isSupportedMimetype($mime) && $this->isSupportedExtension($ext)) {
            if ($this->mimetypeMatchesExtension($mime, $ext, $group)) {
                return true;
            }
        }
        return false;
    }

    private function isSupportedMimetype($mime) {
        return array_key_exists($mime, $this->mimeConf['mimetype_extension_all']);
    }

    private function isSupportedExtension($ext) {
        return array_key_exists($ext, $this->mimeConf['extension_mimetype_all']);
    }

    private function mimetypeMatchesExtension($mime, $ext, $group) {
        if (isset($this->mimeConf['mimetype_extension'][$group])) {
            if (array_key_exists($mime, $this->mimeConf['mimetype_extension'][$group])) {
                if ($this->mimeConf['mimetype_extension'][$group][$mime] === $ext) {
                    return true;
                }
            }
        }
        return false;
    }

}