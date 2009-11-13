<?php
$path           = '/path/to/dir';
$getId3Pathname = '/path/to/getid3.php';
$options        = array('filenamePattern' => '[a-z0-9]{32}\.mp3', 
                        'dryrun'  => false);

require_once 'RenameMediaFiles.php';
$rename = new RenameMediaFiles($path, $getId3Pathname, $options);
$rename->run();