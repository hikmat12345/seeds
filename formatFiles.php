<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Scrape Answers.com and post topics
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';

$path = 'questions/postedQuestions';
$all_files = scandir($path);

// Remove duplicate questions
foreach ($all_files as $file) {
    if (strpos($file, ".txt") !== false) {
        $lines = file($path . "/" . $file);
        $lines = array_unique($lines);
        file_put_contents($path . "/" . $file, implode($lines));
        echo "Formatted " . $path . "/" . $file . "\n";
    }
}

randomUpvote();

// Remove empty files
$path = 'questions';
$all_files = scandir($path);
foreach ($all_files as $file) {
    if (strpos($file, ".txt") !== false) {
        if (filesize($path . "/" . $file)  <=1 ) {
            unlink($path . "/" . $file);
            echo "Removed " . $path . "/" . $file . "\n";
        }
    }
}

randomUpvote();
