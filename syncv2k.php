<?php
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
	
	function __construct(KalturaClient $client = null)
	{
		$this->currdir = dirname( realpath( __FILE__ ) ) . DIRECTORY_SEPARATOR;
		$this->cleanLogFile();
		$this->initializeClients();
	}
	
	function cleanLogFile() 
	{
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
	}
	
	/**
	 * lists both Kaltura and Vidyo, checks if their synchronized, if not, copies new recordings from Vidyo to Kaltura
	 * @access public
	 */
	public function syncVidyo2Kaltura ()
	{
		// list Kaltura entries that are Vidyo recording, descending by date
		$listFilter = new KalturaMediaEntryFilter();
		$listFilter->orderBy = KalturaMediaEntryOrderBy::CREATED_AT_DESC; //in Flacon we can do: PARTNER_SORT_VALUE_DESC;
		$listFilter->statusIn = '-2,-1,0,1,2,4,5,6,7'; //add 3 to exclude DELETED entries
		$listFilter->tagsLike = Vidyo2KalturaConfig::VIDYO_KALTURA_TAGS;
		$pager = new KalturaFilterPager();
		$pager->pageSize = 1;
		$pager->pageIndex = 1;
		$results = $this->client->media->listAction($listFilter, $pager);
		$entry = null;
		$recordingStartSearch = 0;
		$hasRecordingsInKaltura = count($results->objects);
		$this->logToFile('*=== '.($hasRecordingsInKaltura > 0 ? 'has recordings in Kaltura' : 'no recordings in Kaltura found'));
		if ($hasRecordingsInKaltura) {
			$entry = $results->objects[0];
			$this->logToFile('==== last vidyoRecording on Kaltura is: '.$entry->partnerSortValue . ', GUID: ' . $entry->referenceId);
			$this->kalturaentries[$entry->referenceId] = $entry;
			$recordingStartSearch = $entry->partnerSortValue;
		}
		
		// list Vidyo recordings, ascending by date
		$recordsSearch = new RecordsSearchRequest(null, null, null, null, null, sortBy::date, sortDirection::ASC, null, $recordingStartSearch, null);
		$recordsSearchResult = $this->vidyoClient->RecordsSearch($recordsSearch);
		$this->logToFile('SUCCESS list Vidyo success');
		$vidyoRecordsCount = count($recordsSearchResult->records);
		$this->logToFile('==== syncing '.$vidyoRecordsCount.' new recordings');
		$lastVidyoRecording = null;	
		if ($vidyoRecordsCount > 1) {
			$lastVidyoRecording = $recordsSearchResult->records[($lastVidyoRecording-1)];
		} else {
			$lastVidyoRecording = $recordsSearchResult->records;
		}
		if (($vidyoRecordsCount == 0) || ($entry && $lastVidyoRecording->id == $entry->partnerSortValue)) {
			$this->logToFile('KALTURA AND VIDYO ARE ALREADY IN SYNC...');
			$this->logToFile('EXITING.');
		} else {
			if ($vidyoRecordsCount > 1) {
				$vidyoRecords = $recordsSearchResult->records;
				foreach ($vidyoRecords as $recording) 
				{
					$this->logToFile('==== syncing '.$recording->id . ', GUID: ' . $recording->guid);
					$this->copyVidyoRecording2Kaltura($recording);
					$this->logToFile('==== SUCCESS syncing '.$recording->guid.' ====');
				}
			} else {
				$this->logToFile('==== syncing '.$lastVidyoRecording->id . ', GUID: ' . $lastVidyoRecording->guid);
				$this->copyVidyoRecording2Kaltura($lastVidyoRecording);
				$this->logToFile('==== SUCCESS syncing '.$lastVidyoRecording->guid.' ====');
			}
		}

		$this->logToFile('**** SUCCESS Kaltura and Vidyo are synced! ('.$recordsSearchResult->searchCount.' recordings)');
	}
	
	/**
	 * helper function to copy a recording video and its metadata from Vidyo to Kaltura 
	 * @access private
	 */
	private function copyVidyoRecording2Kaltura ($recording) 
	{
		$recordingVideoFileUrl = Vidyo2KalturaConfig::VIDYO_REPLAY_SERVER.$recording->fileLink;
		$filePath = $this->currdir.$recording->guid.'.flv';
		
		// download the recording using CURL (we can't pass this to Kaltura, cause Kaltura's import doesn't support authorization.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $recordingVideoFileUrl);
		// vidyoReplay requires BASIC HTTP authentication, username:password
		curl_setopt($ch, CURLOPT_USERPWD, Vidyo2KalturaConfig::VIDYO_USER.":".Vidyo2KalturaConfig::VIDYO_PASSWORD); 
		$rawdata = curl_exec($ch);
	    if ( file_exists ($filePath) ) unlink($filePath);
	    file_put_contents($filePath, $rawdata);
	    curl_close ($ch);
	    $this->logToFile('==== SUCCESS downloading the file of recording: '.$filePath);
	    
	    // create a new Kaltura Media Entry and copy the Vidyo Recording metadata to it
	    $entry = new KalturaMediaEntry();
	    $entry->mediaType = KalturaMediaType::VIDEO;
	    $entry->referenceId = $recording->guid;
            $entry->partnerSortValue = $recording->id;
	    $entry->userId = $recording->userFullName;
	    $entry->tags = Vidyo2KalturaConfig::VIDYO_KALTURA_TAGS.','.$recording->tags;
	    $entry->name = $recording->title;
	    $entry->categories = Vidyo2KalturaConfig::KALTURA_VIDYO_RECORDINGS_CATEGORY;
	    $entry = $this->client->media->add($entry);
	    $this->logToFile('==== SUCCESS creating new Kaltura Entry Id: '.$entry->id);
	    
	    // upload the recording video file to Kaltura,  
	    $uploadToken = $this->client->uploadToken->add();
	    $this->logToFile('==== SUCCESS create upload token, id: '.$uploadToken->id);
	    $this->client->uploadToken->upload($uploadToken->id, $filePath);
	    $resource = new KalturaUploadedFileTokenResource();
	    $resource->token = $uploadToken->id;
	    $this->client->media->addContent($entry->id, $resource);
	    $this->logToFile('==== SUCCESS uploading the Vidyo recording to Kaltura Entry Id: '.$entry->id);
	    
	    // cleanup the downloaded recording file
	    unlink($filePath);
	}
}

$syncer = new syncv2k();
$syncer->syncVidyo2Kaltura();
