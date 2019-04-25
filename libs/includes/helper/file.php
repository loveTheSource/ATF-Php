<?php

namespace ATFApp\Helper;

class File extends \SplFileInfo {
	
    private $fileBasename = null; // as given to the contructor
    private $filename = null;
    private $filenameClean = null; // after cleanup
    private $extension = null;
    private $mimetype = null;
    private $folder = null;
    private $mimeHelper = null;
    private $imageHelper = null;

    public function __construct($file) {
        $this->fileBasename = $file;
        $this->getBasicInfos($file);

        $this->mimeHelper = new Mimetype();
        $this->imageHelper = new Image();

        parent::__construct($file);
    }

    /**
     * get file name with extension and folder
     * 
     * @param boolean $clean return cleaup name
     * @param boolean $includeExtension
     * @param boolean $includeFolder
     * @return string file
     */
    public function getFile($clean=false, $includeExtension=true, $includeFolder=true) {
        $res = ($clean) ? $this->filenameClean : $this->filename;
        if ($includeExtension && $this->extension !== null) {
            $res .= '.' . $this->extension;
        }
        if ($includeFolder) {
            $res = $this->folder . DIRECTORY_SEPARATOR . $res;
        }
        
        return $res;
    }

    public function isImage() {
        return $this->mimeHelper->isImage($this) 
            && $this->imageHelper->isImage($this->getFile(), $this->getExtension());
    }
    public function isDocument() {
        return $this->mimeHelper->isDocument($this);
    }
    public function isAudio() {
        return $this->mimeHelper->isAudio($this);
    }
    public function isVideo() {
        return $this->mimeHelper->isVideo($this);
    }
    public function isArchive() {
        return $this->mimeHelper->isArchive($this);
    }
    
    /**
     * cleanup filename
     * 
     * @param string $filename (without extension)
     * @return string 
     */
    public function cleanupFilename($filename) {
        return Format::cleanupFilename($filename);
    }

	/**
	 * get the file mime type
	 * 
	 * @return string mimetype
	 */
	public function getMimeType() {
        if (is_null($this->mimetype)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $res = finfo_file($finfo, $this->fileBasename);
            finfo_close($finfo);
            if ($res !== false) {
                $this->mimetype = $res;
            }
        }
		return $this->mimetype;
    }
    
    /**
     * get filename, extension and folder
     * 
     * @param string $file
     */
    private function getBasicInfos($file) {
        $infos = pathinfo($file);

        $this->filename = $infos['filename'];
        $this->filenameClean = $this->cleanupFilename($infos['filename']);
        $this->folder = $infos['dirname'];

        if (isset($infos['extension'])) {
            $this->extension = $infos['extension'];
        }
    }
}