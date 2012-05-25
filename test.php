<?php
// Disables the local cache; Use during development.
// Should be turned OFF for production otherwise might impact performance
ini_set("soap.wsdl_cache_enabled", "0");

//include the vidyoReplay management soap client
require_once 'vidyomanage/VidyoReplayContentManagementService.php';

echo 'Running on VidyoReply: http://66.9.247.51/';

// initilize the vidyoReplay management client
$vidyoClient = new VidyoReplayContentManagementService(array(
								'login' => 'super',
							   	'password' => 'password',
							   	'trace' => 1, 
							   	'exceptions' => 1,
							   	'soap_version' => SOAP_1_2), 
								'http://66.9.247.51/replay/services/VidyoReplayContentManagementService?wsdl') 
								or exit("Unable to create soap client!");

// create a new records search object, make it sort by date, ascending
$recordsSearch = new RecordsSearchRequest(null, null, null, null, null, sortBy::date, sortDirection::ASC, null, null, null);
// invoke the vidyoReplay client search method passing the records search object 
$recordsSearchResult = $vidyoClient->RecordsSearch($recordsSearch);
//echo records array:
echo '<pre>'.print_r($recordsSearchResult, true).'</pre>';
$recordingsnumber = count($recordsSearchResult->records);
echo '<p>the number of recordings: '.$recordingsnumber.'</p>';
// access the url of the first recording in the returned result
if ($recordingsnumber > 1) {
  $fileUrl = 'http://66.9.247.51'.$recordsSearchResult->records[0]->fileLink;
} else {
  if ($recordingsnumber == 1) {
    $fileUrl = 'http://66.9.247.51'.$recordsSearchResult->records->fileLink;
  }
}
echo PHP_EOL.'<p>'.$fileUrl.'</p>';

// that's how we download the FLV of the recording (using CURL)
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $fileUrl); // the url to the FLV
curl_setopt($ch, CURLOPT_USERPWD, "super:password"); // vidyoReplay requires BASIC HTTP authentication, username:password
echo PHP_EOL.'<p>below the actual video file:</p>';
$result = curl_exec($ch);
if($result === false)
{
    echo PHP_EOL.'<p>Curl error:</p><pre>' . print_r(curl_error($ch), true).'</pre>';
    echo PHP_EOL.'<p>Curl info:</p><pre>' . print_r(curl_getinfo($ch), true).'</pre>';
}
else
{
    echo PHP_EOL.'<p>Curl info:</p><pre>' . print_r(curl_getinfo($ch), true).'</pre>';
    var_dump($result);
}
