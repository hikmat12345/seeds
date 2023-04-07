<?php

require 'utilities.php';

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Post more answers from python
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";
require 'vendor/autoload.php';

use League\HTMLToMarkdown\HtmlConverter;

$converter = new HtmlConverter(array('strip_tags' => true, 'use_autolinks' => false, 'hard_break' => true));


$tokens = array('380588a6-84f5-45fb-b25a-00fd57d762c2');

$topics = (json_decode(httpGet("https://discussplaces.com/api/recent?page=8"))->topics);
$pids = [];
foreach ($topics as $topic) {
    $slug = explode("/", $category->slug)[1];
    if ($topic->postcount > 0
    && $topic->postcount < 2
    && strlen($topic->title) < 60
    && !(strpos($topic->title, 'cost of living') !== false)
    && !(strpos($topic->title, 'currency') !== false)) {

        echo ($topic->title) . "\n";
        //$content = shell_exec('python3 getAnswer.py "' . $topic->title . '"');

        // Get ngork subdomain from Craftypixels api and then call it to get google answers
        $content = httpGet("https://" .  json_decode(httpGet("https://api.craftypixels.com/ngork"))->url . ".ngrok.io/answer/" . $topic->title);
        $content = str_replace("&", "and", $content);
        $content = str_replace("...", "\n\n", $content);
        $content = str_replace("(..)", "\n", $content);

        //Remove multiple whitespaces
        $content = preg_replace('/\s+/', ' ', $content);

        $content = trim($content);

        // Convert to markdown
     //   $content = $converter->convert($content);

        if (strlen($content) >= 10) {
            // String contains a date at the end remove it
            if (preg_match("/^\d+$/", substr($content, -4))) {

                $content = substr($content, 0, -12);
            }

            echo ($content) . "\n\n";

            if (createPost($cid, $topic->title, $content, $topic->title)) {
                echo "Saving information \n";

                //    sleep(7);
                break;
            } else {
                echo "Problem saving the post" . "\n";
                echo "https://discussplaces.com/search?term=".urlencode($topic->title)."&in=titlesposts&matchWords=all&sortBy=relevance&sortDirection=desc&showAs=posts";
            }
        }
    }
}
