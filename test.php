<?php
// Disables the local cache; Use during development.
// Should be turned OFF for production otherwise might impact performance
ini_set("soap.wsdl_cache_enabled", "0");

require_once 'vidyomanage/VidyoReplayContentManagementService.php';

echo 'Running on VidyoReply: http://160.79.219.121/replay/';

$vidyoClient = new VidyoReplayContentManagementService(array(
								'login' => 'zohar',
							   	'password' => 'zohar',
							   	'trace' => 1, 
							   	'exceptions' => 1,
							   	'soap_version' => SOAP_1_2), 
								'http://160.79.219.121/replay/services/VidyoReplayContentManagementService?wsdl') 
								or exit("Unable to create soap client!");
$recordsSearch = new RecordsSearchRequest(null, null, null, null, null, sortBy::date, sortDirection::DESC, null, null, null);
$recordsSearchResult = $vidyoClient->RecordsSearch($recordsSearch);
$fileUrl = 'http://160.79.219.121'.$recordsSearchResult->records[0]->fileLink;
echo PHP_EOL.'<br />'.$fileUrl;
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $fileUrl);
curl_setopt($ch, CURLOPT_USERPWD, "zohar:zohar");
$result = curl_exec($ch);
curl_close($ch);
echo $result;
//echo '<pre>'.print_r($recordsSearchResult, true).'</pre>';