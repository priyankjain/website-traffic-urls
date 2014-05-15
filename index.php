<?php
require_once("config.php");
require_once('twitteroauth/twitteroauth.php');
set_time_limit(36000);
function plain_curl($url = '', $var = '', $header = false, $nobody = false) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_NOBODY, $header);
    curl_setopt($curl, CURLOPT_HEADER, $nobody);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    if ($var) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $var);
    }
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}


function fetch_value($str, $find_start, $find_end) {
    $start = strpos($str, $find_start);
    if ($start === false) {
        return "";
    }
    $length = strlen($find_start);
    $end = strpos(substr($str, $start + $length), $find_end);
    return trim(substr($str, $start + $length, $end));
}
function fetch_value_notrim($str, $find_start, $find_end) {
    $start = strpos($str, $find_start);
    if ($start === false) {
        return "";
    }
    $length = strlen($find_start);
    $end = strpos(substr($str, $start + $length), $find_end);
    return substr($str, $start + $length, $end);
}

function cleanup($response){
    $response=str_replace("\n", "",$response);
    $response=str_replace("\r", "",$response);
    $response=str_replace("\t", "",$response);
    $response=str_replace(" ", "",$response);
    return $response;
}

//Authenticate to twitter
$connection = new TwitterOAuth($config['key'],$config['secret'],$config['access_token'],$config['access_token_secret']);

//Get bit.ly API keys
$bitly_access_token = $config['bitly_access_token'];

//Get ow.ly API keys
$owly_access_token = $config['owly_access_token'];

//Get long url
$long_url = $config['longurl'];

//Get count
$file = fopen("count.txt","r");
$count = fgets($file);
fclose($file);

//Open twitterlinks file in append mode to write links to
$tco_file = fopen("tco.txt","a+");

$count_file = fopen("count.txt","w");
function shorten_url()
{
    global $bitly_access_token,$long_url,$bitly_file,$owly_file,$owly_access_token, $count_file,$count,$tco_file,$connection;
    //Get short link from bit.ly
    $api_address = "https://api-ssl.bitly.com";
    $method_address = "/v3/shorten";
    $parameters = "access_token=".$bitly_access_token."&longUrl=".$long_url."?".$count;
    $response = plain_curl ($api_address.$method_address,$parameters);
    echo $response.'<br/>';
    $response = json_decode($response);
    $status = $connection->post('statuses/update', array('status' => $response->data->url));
    var_dump($status);
    echo '<br/>';
    if(empty($status->errors))
    fputs($tco_file,$status->entities->urls[0]->url.PHP_EOL);

    //Get short link from ow.ly
    $api_address="http://ow.ly/api/1.1/url/shorten";
    $parameters = "apiKey=".$owly_access_token."&longUrl=".$long_url."?".$count;
    $response = plain_curl($api_address,$parameters);
    echo $response.'<br/>';
    $response = json_decode($response);
    $status = $connection->post('statuses/update', array('status' => $response->results->shortUrl));
    var_dump($status);
    echo '<br/>';
    if(empty($status->errors))
    fputs($tco_file,$status->entities->urls[0]->url.PHP_EOL);

    $count++;
    rewind($count_file);
    fputs($count_file,$count);
}

//Call the shortening apis and store the shortened links
for($i = 0; $i <= 1000; $i++)
shorten_url();

// $status = $connection->get('application/rate_limit_status');
// echo '<pre>';
// var_dump($status);
// echo '</pre>';

fclose($count_file);
?>