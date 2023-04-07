<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Script to update a category for each country
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

            //Only run on empty categories
            if (strlen($category->description) == "") {
                $count++;

                // Get Slug from full path
                $slug = explode("/", $category->slug)[1];
                echo $count . " " . $slug . "\n";

                $url = "https://www.infoplease.com/world/countries/" . $slug;
                $html = file_get_html($url);
                echo "Looking into " . $url . "\n";
                $description = "";

                if ($html != null) {
                    $def_found = false;
                    foreach ($html->find('.robot-content') as $element) {
                        if ($element->find('h5')[0]->plaintext == "Geography") {

                            if (strlen($element->find('h5')[0]->next_sibling()->plaintext) >= 10) {
                                $description .= $element->find('h5')[0]->next_sibling()->plaintext;
                            }

                            if (strlen($element->find('h5')[0]->next_sibling()->next_sibling()->plaintext) >= 10) {
                                $description .= $element->find('h5')[0]->next_sibling()->next_sibling()->plaintext;
                            }

                            $found_word = trim($found_word);

                            echo "Log: " . $found_word . "\n '" . $description . "'\n";
                        }
                    }

                } else {
                    echo "Page is empty \n";

                }
                updateCategory($category->cid, $category->name, $description, $country->flag, $master_token);
                sleep(5);

            }
        }
    }
}
