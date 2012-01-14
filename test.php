<?php
// Disables the local cache; Use during development.
// Should be turned OFF for production otherwise might impact performance
ini_set("soap.wsdl_cache_enabled", "0");

//include the vidyoReplay management soap client
require_once 'vidyomanage/VidyoReplayContentManagementService.php';

echo 'Running on VidyoReply: http://160.79.219.121/replay/';

// initilize the vidyoReplay management client
$vidyoClient = new VidyoReplayContentManagementService(array(
								'login' => 'zohar',
							   	'password' => 'zohar',
							   	'trace' => 1, 
							   	'exceptions' => 1,
							   	'soap_version' => SOAP_1_2), 
								'http://160.79.219.121/replay/services/VidyoReplayContentManagementService?wsdl') 
								or exit("Unable to create soap client!");

// create a new records search object, make it sort by date, ascending
$recordsSearch = new RecordsSearchRequest(null, null, null, null, null, sortBy::date, sortDirection::DESC, null, null, null);
// invoke the vidyoReplay client search method passing the records search object 
$recordsSearchResult = $vidyoClient->RecordsSearch($recordsSearch);
// access the url of the first recording in the returned result
$fileUrl = 'http://160.79.219.121'.$recordsSearchResult->records[0]->fileLink;
echo PHP_EOL.'<br />'.$fileUrl;

// that's how we download the FLV of the recording (using CURL)
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $fileUrl); // the url to the FLV
curl_setopt($ch, CURLOPT_USERPWD, "zohar:zohar"); // vidyoReplay requires BASIC HTTP authentication, username:password
$result = curl_exec($ch);
curl_close($ch);
//echo $result;
echo '<pre>'.print_r($recordsSearchResult, true).'</pre>';