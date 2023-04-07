<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
House keeping keywords lister
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';

$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

$countries = json_decode(httpGet("https://restcountries.eu/rest/v2/all"));
$discussplaces_categories = json_decode(httpGet("https://discussplaces.com/api/categories"))->categories;

$count = 0;
foreach ($countries as $country) {
    foreach ($discussplaces_categories as $category) {

        if ($category->name == $country->name) {

            if ($category->post_count > 0) {

                foreach ($category->posts as $post) {
                        echo "<li><a href='/category/".$category->slug."' ><img  height='18' width='28' src='".$category->backgroundImage."'/> ".$category->name."</a></li>\n";



                    // if (strpos($post->topic->title, "What are some famous") !== false) {
                    //     $text = str_replace('What are some', "", $post->topic->title);
                    //     $text = str_replace('?', "", $text);

                    //     echo  trim(strtolower($text)) . "\n";

                    // }
                }
            }

        }


    }
}
