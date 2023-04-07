<?php

echo " +-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+
Script to create a random user
+-++-++-++-++-++-+ +-++-++-++-++-+ +-++-+ +-++-++-+ \n";

// ----------- Globals ----------------------------------
require 'utilities.php';
require 'simple_html_dom.php';

$master_token = "380588a6-84f5-45fb-b25a-00fd57d762c2";

$user = json_decode(httpGet("https://randomuser.me/api/"))->results[0];

$new_user->username = $user->login->username;
$new_user->email = str_replace('example.com', "gmail.com", $user->email);
$new_user->picture = $user->picture->large;
$new_user->location = $user->location->city . ", " . $user->location->state;
$new_user->fullname = $user->name->first . " " . $user->name->last;
$new_user->password = "fresh1";

$curl_call = 'curl -H "Authorization: Bearer ' . $master_token .
'" --data "username=' . $new_user->username .
'&_uid=1' .
'&password=' . $new_user->password .
'&fullname=' . $new_user->fullname .
//  '&picture=' . $new_user->picture .
'&picture=' . "https://talkwithstranger.com/randomavatar/" . rand(2, 500).
'&email=' . $new_user->email .
'&location=' . $new_user->location .
    '" https://discussplaces.com/api/v2/users 2>&1';

shell_exec($curl_call);

