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
		$this->initializeClients();
	}
	
	function logToFile($msg)
	{
		$fd = fopen(syncv2k::logFilename, "a");
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
				'login' => 'zohar',
				'password' => 'zohar',
				'trace' => 1,
				'exceptions' => 1,
				'soap_version' => SOAP_1_2),
				'http://160.79.219.121/replay/services/VidyoReplayContentManagementService?wsdl')
				or exit("Unable to create soap client!");
		$this->logToFile('SUCCESS initializing Vidyo success');
	}
	
	/**
	 * lists both Kaltura and Vidyo, checks if their synchronized, if not, copies new recordings from Vidyo to Kaltura
	 * @access public
	 */
	public function syncVidyo2Kaltura ()
	{
		// list Kaltura entries that are Vidyo recording, ascending by date
		$listFilter = new KalturaMediaEntryFilter();
		$listFilter->orderBy = KalturaMediaEntryOrderBy::CREATED_AT_DESC;
		$listFilter->tagsLike = Vidyo2KalturaConfig::VIDYO_KALTURA_TAGS;
		$results = $this->client->media->listAction($listFilter);
		foreach ($results->objects as $entry) 
		{
			$this->kalturaentries[$entry->referenceId] = $entry;
		}
		
		// list Vidyo recordings, ascending by date
		$recordsSearch = new RecordsSearchRequest(null, null, null, null, null, sortBy::date, sortDirection::DESC, null, null, null);
		$recordsSearchResult = $this->vidyoClient->RecordsSearch($recordsSearch);
		$this->logToFile('SUCCESS list Vidyo success');
		
		foreach ($recordsSearchResult->records as $recording) 
		{
			if (isset($this->kalturaentries[$recording->guid])) {
				$this->logToFile('--- already in Kaltura: '.$recording->guid);
				continue;
			}
			$this->logToFile('==== syncing '.$recording->guid);
			$this->copyVidyoRecording2Kaltura($recording);
			$this->logToFile('==== SUCCESS syncing '.$recording->guid.' ====');
		}
		
		$this->logToFile('**** SUCCESS Kaltura and Vidyo are synced! ('.$recordsSearchResult->searchCount.' recordings)');
	}
	
	/**
	 * helper function to copy a recording video and its metadata from Vidyo to Kaltura 
	 * @access private
	 */
	private function copyVidyoRecording2Kaltura ($recording) 
	{
		$recordingVideoFileUrl = 'http://160.79.219.121'.$recording->fileLink;
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
	    $entry->userId = $recording->userFullName;
	    $entry->tags = Vidyo2KalturaConfig::VIDYO_KALTURA_TAGS.','.$recording->tags;
	    $entry->name = $recording->title;
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