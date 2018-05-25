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
}