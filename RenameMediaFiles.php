<?php
class RenameMediaFiles
{
    private $_path;
    private $_getId3Pathname;
    private $_filenamePattern = '.+';
    private $_dryrun = false;
    
    public function __construct($path, $getId3Pathname, array $options = array())
    {
        if (is_string($path) && is_dir($path)) {
            $this->_path = $path;
        } else {
            throw new Exception("Invalid path to directory");
        }
        
        if (is_string($getId3Pathname) && is_file($getId3Pathname)) {
            $this->_getId3Pathname = $getId3Pathname;
        } else {
            throw new Exception("Invalid path to getID3 library file");
        }
        
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'filenamePattern':
                    if (is_string($value)) {
                        $this->_filenamePattern = $value;
                    }
                    break;
                case 'dryrun':
                    $this->_dryrun = (bool) $value;
                    break;
                default:
                    break;
            }
        }
    }
    
    public function run()
    {
        $dir = new DirectoryIterator($this->_path);
        foreach ($dir as $info) {
            if ($info->isFile() && $this->_isValidFilename($info->getFilename())) {
                $this->_renameFile($info->getPathname());
            }
        }
    }
    
    private function _isValidFilename($filename)
    {
        return preg_match("/{$this->_filenamePattern}/", $filename);
    }
    
    private function _renameFile($pathname)
    {
        if (!$newPathname = $this->_getNewPathname($pathname)) {
            echo 'Error renaming "' . basename($pathname) . '"' . "\n\n";
            return;
        }
        
        echo 'Renaming "' . basename($pathname) . '" to "' . basename($newPathname) . '"'. "\n\n";
        if (!$this->_dryrun) {
            rename($pathname, $newPathname);
        }
    }
    
    private function _getNewPathname($pathname)
    {
        require_once($this->_getId3Pathname);
        $getId3 = new getID3;
        $info = $getId3->analyze($pathname);
        getid3_lib::CopyTagsToComments($info);
        
        if (null !== $info['error']) {
            return false;
        }
        
        $artist = $info['comments_html']['artist'][0];
        $title = $info['tags']['id3v2']['title'][0];
        
        $newFilename = $this->_formatFilename($artist) 
                     . '_' 
                     . $this->_formatFilename($title);
        
        if ('_' == $newFilename) {
            $newFilename = $info['filename'];
        } else {
            $newFilename .= '.' . $info['fileformat'];
        }
        
        return $info['filepath'] 
             . DIRECTORY_SEPARATOR 
             . $newFilename;
    }
    
    private function _formatFilename($string)
    {
        $string = str_replace(' ', '-', $string);
        return preg_replace('/[^a-z0-9-]/i', '', $string);
    }
}