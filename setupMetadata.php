<?php
ini_set('memory_limit', '128M');
set_time_limit(0);
//initialize the Kaltura client -
require_once('kaltura-php5/KalturaClient.php');
require_once('Vidyo2KalturaConfig.php');

$metadataSchemaFile = 'VidyoReplayCustomMetadata.xsd';
$ProfileSystemName = 'vidyoreplaymetadata00';

$kConfig = new KalturaConfiguration(Vidyo2KalturaConfig::PARTNER_ID);
$client = new KalturaClient($kConfig);
$ks = $client->generateSession(Vidyo2KalturaConfig::ADMIN_SECRET, 'vidyoSyncClient', KalturaSessionType::ADMIN, Vidyo2KalturaConfig::PARTNER_ID);
$client->setKs($ks);
$filter = new KalturaMetadataProfileFilter();
$filter->systemNameEqual = $ProfileSystemName;
$pager = new KalturaFilterPager();
$pager->pageSize = 1;
$pager->pageIndex = 1;
//first test if this accountn already has such metadata profile (testing for system name == vidyoreplaymetadata)
$results = $client->metadataProfile->listAction($filter, $pager);
echo 'testing for profile existance: '.$results->totalCount;
echo PHP_EOL;
if($results->totalCount == 0) {
	//if such profile doesn't exist, ingest our xsd:
	$xsdData = file_get_contents($metadataSchemaFile);
	$metadataProfile = new KalturaMetadataProfile();
	$metadataProfile->metadataObjectType = KalturaMetadataObjectType::ENTRY;
	$metadataProfile->name = $ProfileSystemName;
	$metadataProfile->systemName = $ProfileSystemName;
	$metadataProfile->createMode = KalturaMetadataProfileCreateMode::API;
	$metadataProfile->description = 'VidyoReplay Recordings Metadata - Original recording details from VidyoReplay are saved in these fields.';
	$viewsData = "";
	$metadataProfile = $client->metadataProfile->add($metadataProfile, $xsdData, $viewsData);
	echo 'added a new metadata profile, id: '.$metadataProfile->id.PHP_EOL;
	$configFile = file_get_contents('Vidyo2KalturaConfig.php');
	$configFile = str_replace('\'%metadataprofileid%\'', $metadataProfile->id, $configFile);
	file_put_contents('Vidyo2KalturaConfig.php', $configFile);
	echo PHP_EOL.'config file updated: '.PHP_EOL;
} else {
	$metadataProfile = $results->objects[0];
	echo 'found existing metadata profile, id: '.$metadataProfile->id.PHP_EOL;
        $configFile = file_get_contents('Vidyo2KalturaConfig.php');
        $configFile = str_replace('\'%metadataprofileid%\'', $metadataProfile->id, $configFile);
        file_put_contents('Vidyo2KalturaConfig.php', $configFile);
        echo PHP_EOL.'config file updated: '.PHP_EOL;
}
$metadataProfileId = $metadataProfile->id;
$results = $client->metadataProfile->listfields($metadataProfileId);
echo 'the list of metdata fields (on profile id '.$metadataProfileId.'):'.PHP_EOL;
$metadataTemplate = '<metadata>'.PHP_EOL;
foreach ($results->objects as $metadataField) {
	$metadataTemplate .= '<'.$metadataField->key.'>VALUE_HERE:'.$metadataField->key.'</'.$metadataField->key.'>'.PHP_EOL;
}
$metadataTemplate .= '</metadata>'.PHP_EOL;
echo $metadataTemplate;
?>
