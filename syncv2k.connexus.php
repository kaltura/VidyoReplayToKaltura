<?php
set_time_limit(0);
//initialize the Kaltura client -
require_once('kaltura-php5/KalturaClient.php');
require_once('Vidyo2KalturaConfig.php');
class syncv2k 
{
	private $client;
	private $vidyoClient;
	private $currdir;
	private $kalturaentries;
	const logFilename = 'syncVidyo2Kaltura.log';
	public $initializeSuccess = false;
	const lastVidyoUploadFile = 'LastVidyoUpload.log';
	
	function __construct(KalturaClient $client = null)
	{
		$this->currdir = dirname( realpath( __FILE__ ) ) . DIRECTORY_SEPARATOR;
		$this->cleanLogFile();
		$this->initializeClients();
	}
	
	function cleanLogFile() 
	{
		//archive logs
		$oldFileName = $this->currdir.syncv2k::logFilename;
		$str = date("Y-m-d h_i_s", mktime());
		$newFileName = getcwd() . "/logs/" . $str . ".log";
		copy($oldFileName, $newFileName) or die;
		
		//clear the orig log
		$logFile = $this->currdir.syncv2k::logFilename;
		$fd = fopen($logFile, "w");
		fwrite($fd, '');
		fclose($fd);
	}
	
	function logToFile($msg)
	{
		$logFile = $this->currdir.syncv2k::logFilename;
		$fd = fopen($logFile, "a");
		$str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg;
		fwrite($fd, $str . "\n");
		fclose($fd);
	}
	
	function getLastUpload()
	{
		$lastUploadFile = $this->currdir.syncv2k::lastVidyoUploadFile;
		if ($fd = fopen($lastUploadFile, "r"))
		{
			$lastInFile = intval(file_get_contents($lastUploadFile));
			fclose($fd);
		}
		else
		{
			$lastInFile = 0;
		}
		return($lastInFile);
	}
	
	function writeLastUpload($last)
	{
		$lastUploadFile = $this->currdir.syncv2k::lastVidyoUploadFile;
		file_put_contents($lastUploadFile, $last);
	}
	
	
	
	/**
	 * initialize the Kaltura and Vidyo clients
	 * @access public
	 */
	public function initializeClients() 
	{
		$kConfig = new KalturaConfiguration(Vidyo2KalturaConfig::PARTNER_ID);
		$this->client = new KalturaClient($kConfig);
		$ks = $this->client->generateSession(Vidyo2KalturaConfig::ADMIN_SECRET, 'vidyoSyncClient', KalturaSessionType::ADMIN, Vidyo2KalturaConfig::PARTNER_ID);
		$this->client->setKs($ks);
		$this->logToFile('SUCCESS initializing Kaltura success');
		
		//initialize the Vidyo client - 
		require_once 'vidyomanage/VidyoReplayContentManagementService.php';
		$this->vidyoClient = new VidyoReplayContentManagementService(array(
				'login' => Vidyo2KalturaConfig::VIDYO_USER,
				'password' => Vidyo2KalturaConfig::VIDYO_PASSWORD,
				'trace' => 1,
				'exceptions' => 1,
				'soap_version' => SOAP_1_2),
				Vidyo2KalturaConfig::VIDYO_REPLAY_SERVER.'/replay/services/VidyoReplayContentManagementService?wsdl')
				or exit("Unable to create soap client!");

		$this->logToFile('SUCCESS initializing Vidyo success');

		$this->initializeSuccess = true;
		sleep(5);
	}
	
	
	
	/**
	 * lists both Kaltura and Vidyo, checks if their synchronized, if not, copies new recordings from Vidyo to Kaltura
	 * @access public
	 */
	public function syncVidyo2Kaltura ()
	{
		
		// Kaltura Section
		
		// list Kaltura entries that are Vidyo recording, descending by date
		
		$listFilter = new KalturaMediaEntryFilter();
		$listFilter->orderBy = KalturaMediaEntryOrderBy::CREATED_AT_DESC;   //in Flacon we can do: PARTNER_SORT_VALUE_DESC;
		$listFilter->statusIn = '-2,-1,0,1,2,4,5,6,7';   //add 3 to include DELETED entries
		$listFilter->tagsLike = Vidyo2KalturaConfig::VIDYO_KALTURA_TAGS;
		$pager = new KalturaFilterPager();
		$pager->pageSize = 20;
		$pager->pageIndex = 1;
		$results = $this->client->media->listAction($listFilter, $pager);
	
		
		$entry = null;
		$recordingStartSearch = 0;
		$hasRecordingsInKaltura = count($results->objects);
		$this->logToFile('INFO: '.($hasRecordingsInKaltura > 0 ? $hasRecordingsInKaltura.' Vidyo recordings in Kaltura' : 'no recordings in Kaltura found'));
		if ($hasRecordingsInKaltura) 
		{
			$entry = $results->objects[0];
			$this->kalturaentries[$entry->referenceId] = $entry;
			$recordingStartSearch = $entry->partnerSortValue;

			//output to log
			$this->logToFile('INFO: Kaltura File count: '.count($results->objects));
			$this->logToFile('INFO: Last '. Vidyo2KalturaConfig::VIDYO_KALTURA_TAGS . ' tag on Kaltura is: '.$entry->partnerSortValue . ', GUID: ' . $entry->referenceId);
			for($i=0; $i < count($results->objects); $i++)
			{
				$temp = $results->objects[$i];
				$this->logToFile('INFO: Kaltura ID: ' . $temp->partnerSortValue . ', GUID: '  . $temp->referenceId);
			}			
		} 
		//end Kaltura Section
		
		
		//Vidyo Section
		
		// list Vidyo recordings, ascending by date
		// this will bring the oldest 200 (VidyoReplay's API max limit), we'll need to loop through all the recordings till we find the last 
		// recording that was synced with Kaltura (or begin synchronization with recording 0)
		$start = 0; //$recordingStartSearch; //our start is either 0 (in case we have no recordings in Kaltura, or we want to resync all)
		$bundles = 100; //this must always be larger than 1 and max 200/null (defines how many recordings to pull each request)
		$recordingsArray = array();
		$recordsSearch = new RecordsSearchRequest(null, null, null, null, null, sortBy::date, sortDirection::ASC, $bundles, $start, null);
		$recordsSearchResult = $this->vidyoClient->RecordsSearch($recordsSearch);

		if (!is_array($recordsSearchResult->records) && $recordsSearchResult->records != null) {
		        // VidyoReplay tends to return a single recording as an object on its own instead of inside an Array
		        $recordingsArray[] = $recordsSearchResult->records;
		}
		while (is_array($recordsSearchResult->records) && count($recordsSearchResult->records) > 0) 
		{
		        $recordingsArray = array_merge($recordingsArray, $recordsSearchResult->records);
		        $start += $bundles;
		        $recordsSearch = new RecordsSearchRequest(null, null, null, null, null, sortBy::date, sortDirection::ASC, $bundles, $start, null);
		        $recordsSearchResult = $this->vidyoClient->RecordsSearch($recordsSearch);
		}

		$vidyoRecordsCount = count($recordingsArray);

		// log vidyo recordings
		$this->logToFile('INFO: ' . $vidyoRecordsCount . ' recordings on Replay');
		foreach($recordingsArray as $recording)
			$this->logToFile('INFO: Vidyo   ID: '.$recording->id . ', GUID: ' . $recording->guid);

		//end of vidyo import
		
		
		// Sync section
		
		// $recordingsArray = vidyo recordings from replay
		// $results = recordings on kaltura
		
		$lastUpload = $this->getLastUpload();
		$this->logToFile('INFO: Last Vidyo Upload: '. $lastUpload);
		
		foreach($recordingsArray as $recording)
			if ($recording->id > $lastUpload)
				// check to see if the Vidyo 
				if (stripos($recording->tags, Vidyo2KalturaConfig::VIDYO_BLOCK_UPLOAD_TAG) !== FALSE)
				{
					$this->logToFile('ACT: Excluded ID: '. $recording->id . ', GUID: ' . $recording->guid);
				
					//update the last upload file
					$this->writeLastUpload($recording->id);
				}
				else
				{			
					//upload the file here & log it
					$this->copyVidyoRecording2Kaltura($recording);
					$this->logToFile('ACT: Uploaded ID: '. $recording->id . ', GUID: ' . $recording->guid);
				
					//update the last upload file
					$this->writeLastUpload($recording->id);
				} //end of sync loop / if then cycle
		// end of CNX pushing
		
		/*  Original code from Kaltura->

		$this->logToFile('INFO syncing '.$vidyoRecordsCount.' new recordings');
		if (($vidyoRecordsCount == 0) || ($entry && $recordingsArray[$vidyoRecordsCount-1]->id == $entry->partnerSortValue)) 
		{
            $this->logToFile('INFO All VidyoReplay recordings are synced in Kaltura. Exiting syncer cycle.');
		} 
		else 
			{
			$multiRequestBatches = 30;
			$count = 0;
			$this->client->startMultiRequest();
			foreach ($recordingsArray as $recording)
			{
		        	$this->logToFile('INFO syncing ID: '.$recording->id . ', GUID: ' . $recording->guid);
		        	$this->copyVidyoRecording2Kaltura($recording);
		        	$this->logToFile('SUCCESS synchronized recording guid: '.$recording->guid);
				if ($count % $multiRequestBatches == 0) {
					try {
						$multiRequest = $this->client->doMultiRequest();
						$this->logToFile('SUCCESS pushed a batch of recordings using multirequest to Kaltura');
					} catch (Exception $ex) {
						$this->logToFile('ERROR failed to push a batch of recordings using multirequest: '.$ex->getMessage());
					}
					$this->client->startMultiRequest();
				}
				$count += 1;
			}
			if ($this->client->isMultiRequest() == true) {
				try {
                                        $multiRequest = $this->client->doMultiRequest();
                                        $this->logToFile('SUCCESS pushed a batch of recordings using multirequest to Kaltura');
                                } catch (Exception $ex) {
                                        $this->logToFile('ERROR failed to push a batch of recordings using multirequest: '.$ex->getMessage());
                                }
			}
		}
		$this->logToFile('SUCCESS Kaltura and Vidyo are synced! ('.$vidyoRecordsCount.' recordings)');
 
	  */ //end of Kaltura code

	} // end of sync function
	

	 /**
	 * helper function to copy a recording video and its metadata from Vidyo to Kaltura 
	 * @access private
	 */
	private function copyVidyoRecording2Kaltura ($recording) 
	{
		// here we deconstruct the URL VidyoReplay is giving us, and we add the http User and Passwrod to it
                // that way, we allow Kaltura to import the file directly from the VidyoReplay server
                $url = $recording->fileLink;
                $urlParts = parse_url($url);
                $recordingVideoFileUrl = $urlParts["scheme"].'://'.Vidyo2KalturaConfig::VIDYO_USER.':'.Vidyo2KalturaConfig::VIDYO_PASSWORD.'@'.$urlParts["host"].$urlParts["path"].'?'.$urlParts["query"];
                // create a new Kaltura Media Entry and copy the Vidyo Recording metadata to it
                $entry = new KalturaMediaEntry();
                $entry->mediaType = KalturaMediaType::VIDEO;
                $entry->referenceId = $recording->guid;
                $entry->partnerSortValue = $recording->id;
                $entry->userId = $recording->userFullName;
                $entry->tags = Vidyo2KalturaConfig::VIDYO_KALTURA_TAGS.','.$recording->tags;
                $entry->name = $recording->title;
                $entry->description = $recording->comments;
				$entry->categories = Vidyo2KalturaConfig::KALTURA_VIDYO_RECORDINGS_CATEGORY;
                $entry = $this->client->media->add($entry);
                $this->logToFile('SUCCESS creating new Kaltura Entry Id: '.$entry->id.' of recording: '.$recording->id);
		
				//$this->logToFile($entry->referenceId .' '. $entry->partnerSortValue .' '. $entry->userId.' '. $entry->name.' '.$entry->tags);
		
		// make Kaltura import the recording file from the VidyoReplay server 
                $resource = new KalturaUrlResource();
                $resource->url = $recordingVideoFileUrl;
                $this->client->media->addContent($entry->id, $resource);
	
		// if we have custom metadata profile setup -
		if (Vidyo2KalturaConfig::KALTURA_VIDYOREPLAY_METADATA_PROFILE_ID != '%metadataprofileid%') {
			// fill the entry custom metadata (the vidyoReplay profile)
			$dateCreated = strtotime($recording->dateCreated);
			$endTime = strtotime($recording->endTime);
			$this->logToFile('Error in metadata default');
			$customMetadata = "<metadata><RecordingId>{$recording->id}</RecordingId><RecordingGuid>{$recording->guid}</RecordingGuid><TenantName>{$recording->tenantName}</TenantName><UserName>{$recording->userName}</UserName><UserFullName>{$recording->userFullName}</UserFullName><DateCreated>{$dateCreated}</DateCreated><EndTime>{$endTime}</EndTime><DateCreatedString>{$recording->dateCreatedString}</DateCreatedString><Pin>{$recording->pin}</Pin><RecordScope>{$recording->recordScope}</RecordScope><RoomName>{$recording->roomName}</RoomName><RecorderId>{$recording->recorderId}</RecorderId><Locked>{$recording->locked}</Locked></metadata>";
			$metadata = $this->client->metadata->add(Vidyo2KalturaConfig::KALTURA_VIDYOREPLAY_METADATA_PROFILE_ID, KalturaMetadataObjectType::ENTRY, $entry->id, $customMetadata);	
			$this->logToFile('SUCCESS synced custom metadata fields to entry id: '.$entry->id);
		}
		
		$this->logToFile('SUCCESS importing Vidyo recording: '.$recordingVideoFileUrl.' to Kaltura Entry: '.$entry->id);
	}//end of copyvidyorecording function
	
	
}//end of class




//main

$syncer = new syncv2k();
if ($syncer->initializeSuccess == true) 
{
	$syncer->syncVidyo2Kaltura();
}
