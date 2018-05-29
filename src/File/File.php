<?php

namespace Mindk\Framework\File;

class File
{
    /**
     * Moves the file to the specified directory
     * 
     * @param $filename
     * @param $destination
     * @return bool
     */
    public function move($filename, $destination){
        
        return move_uploaded_file( $filename , $destination);
    }

    /**
     * Delete file
     * 
     * @param $file_path
     * @return bool
     */
    public function delete($file_path){
        return unlink($file_path);
    }

    /**
     * Checks the file for existence
     * 
     * @param $file_path
     * @return bool
     */
    public function exist($file_path){
        return file_exists($file_path);
    }

    /**
     * Checks if the file is an image
     * 
     * @param $file
     * @return bool
     */
    public function isImage($file):bool {
        $mimes = ['image/jpeg', 'image/png'];
        $extensions = ['jpg', 'jpeg', 'png'];
        $file_info = @getimagesize($file['tmp_name']);
        $file_extension = $this->extension($file['name']);
        
        return $file_info && in_array($file_info['mime'], $mimes) 
               && in_array($file_extension, $extensions);
    }

    /**
     * Get file extension
     *
     * @param $filename
     * @return string
     */
    public function extension($filename): string {
        $segments = explode('.', $filename);
        return strtolower(array_pop($segments));
    }

    /**
     * Checks the file for a valid size
     *
     * @param $file
     * @param int $size
     * @return bool
     */
    public function isValidSize($file, int $size):bool {
        return $size >= $file['size'];
    }

}