<?php

if (!function_exists('apc_cache_info')) { die('APC is not installed/running');}

$data = apc_cache_info(); $expired=array();

if (empty($data['cache_list'])) { die('no cached files');}

foreach ($data['cache_list'] as $file) {
    if (!empty($file['mtime']) && !empty($file['filename']) && (!file_exists($file['filename']) || (int)$file['mtime']<filemtime($file['filename']))) {$expired[]=$file['filename'];}
}

if (!empty($expired)) {apc_delete_file($expired);} 

echo count($expired),' deleted';

?>