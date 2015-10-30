<?php

// CLI script to repackage SCORM content as .zip

define('CLI_SCRIPT', 1);

// Where to put the repackaged SCORM .zip files?
$repackedscormfolder = '/'; // Include trailing slash

// What is the root dir of the extracted SCORM data?
$scormdatadir = '/'; // Include trailing slash

$missingscormcontent = simplexml_load_file('missing_scorm_content.xml') or die('wtf');

foreach ($missingscormcontent->children() as $missingscormdetails) {

    $courseid = (int) $missingscormdetails->field['0'];
    $cmid = (int) $missingscormdetails->field['1'];
    $scormid = (int) $missingscormdetails->field['2'];
    $coursename = (string) $missingscormdetails->field['3'];
    $scormname = (string) $missingscormdetails->field['4'];
    $filename = (string) $missingscormdetails->field['5'];

    // Identify the directory to zip
    $scormdir = "{$scormdatadir}/{$courseid}/moddata/scorm/{$scormid}/";

    if (!file_exists($scormdir)) {

        // Sort it out
        mtrace($scormdir . ' directory  not found');
    } else {

        // Initialise archive object
        $zip = new ZipArchive();
        $zip->open($repackedscormfolder . $filename,
                ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($scormdir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

        foreach ($files as $name => $file) {

            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {

                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($scormdir));

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
        echo $scormdir . " directory found\n";flush();
        echo "{$scormdir} directory zipped to file {$filename}\n";flush();
    }
}
