<?php
require_once("config.php");
require_once('twitteroauth/twitteroauth.php');
$connection = new TwitterOAuth($config['key'],$config['secret'],$config['access_token'],$config['access_token_secret']);
$status = $connection->post('statuses/update', array('status' => 'http://thebot.net 4'));
if(empty($status->errors)) echo "Not a duplicate".$status->entities->urls[0]->url;
else echo "Is a duplicate";
echo '<pre>';
var_dump($status);
echo '</pre>';
?>