<?php
/*
 * this function gets the file and prints it to the screen if not found
 * the function will print the error msg and then exit the script!
 *
 */
function getTemplate($fileName) {

    $file = file_get_contents($fileName);

    if ($file) {

        return $file;
    }
    else {
        echo ("File not found: " . $fileName);
    }
}