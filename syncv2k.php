<?php
set_time_limit(0);
//initialize the Kaltura client -
require_once('kaltura-php5/KalturaClient.php');
require_once('Vidyo2KalturaConfig.php');
class syncv2k 
{
	private $clients = array();
	private $vidyoClients = array();
	private $currdir;
	private $kalturaentries;
	const logFilename = 'syncVidyo2Kaltura.log';
	public $initializeSuccess = false;
	
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
        require_once 'vidyomanage/VidyoReplayContentManagementService.php';
        for ($i = 0; $i < count(Vidyo2KalturaConfig::$conf); $i++)
        {
            //initialize the Vidyo client -
            $this->vidyoClients[$i] = new VidyoReplayContentManagementService(array(
                    'login' => Vidyo2KalturaConfig::$conf[$i]['VIDYO_USER'],
                    'password' => Vidyo2KalturaConfig::$conf[$i]['VIDYO_PASSWORD'],
                    'trace' => 1,
                    'exceptions' => 1,
                    'soap_version' => SOAP_1_2),
                Vidyo2KalturaConfig::$conf[$i]['VIDYO_REPLAY_SERVER'] . '/replay/services/VidyoReplayContentManagementService?wsdl')
            or exit("Unable to create soap client!");

            $this->logToFile('SUCCESS initializing Vidyo success config ' . $i);

            $this->initializeSuccess = true;
        }
	}
	
	/**
	 * lists both Kaltura and Vidyo, checks if their synchronized, if not, copies new recordings from Vidyo to Kaltura
	 * @access public
	 */
	public function syncVidyo2Kaltura ()
	{
		// list Kaltura entries that are Vidyo recording, descending by date
		$pager = new KalturaFilterPager();
		$pager->pageSize = 100;
        for ($i = 0; $i < count(Vidyo2KalturaConfig::$conf); $i++)
        {
            // Initialize the Kaltura client (it has singleton parts)
            $kConfig = new KalturaConfiguration(Vidyo2KalturaConfig::$conf[$i]['PARTNER_ID']);
            $kConfig->serviceUrl=Vidyo2KalturaConfig::$conf[$i]['SERVICE_URL'];
            $this->clients[$i] = new KalturaClient($kConfig);
            $ks = $this->clients[$i]->generateSession(Vidyo2KalturaConfig::$conf[$i]['ADMIN_SECRET'], 'vidyoSyncClient', KalturaSessionType::ADMIN, Vidyo2KalturaConfig::$conf[$i]['PARTNER_ID']);
            $this->clients[$i]->setKs($ks);

            $pager->pageIndex = 1;
            $listFilter = new KalturaMediaEntryFilter();
            $listFilter->orderBy = KalturaMediaEntryOrderBy::CREATED_AT_DESC; //in Flacon we can do: PARTNER_SORT_VALUE_DESC;
            $listFilter->statusIn = '-2,-1,0,1,2,4,5,6,7'; //add 3 to include DELETED entries
            $listFilter->tagsLike = Vidyo2KalturaConfig::$conf[$i]['VIDYO_KALTURA_TAGS'];
            $results = $this->clients[$i]->media->listAction($listFilter, $pager);
            $entry = null;
            $recordingStartSearch = 0;
            $hasRecordingsInKaltura = count($results->objects);
            $this->logToFile('INFO ' . ($hasRecordingsInKaltura > 0 ? 'has recordings in Kaltura' : 'no recordings in Kaltura found') . ' (config ' . $i . ')' );
            $this->kalturaentries = array();
            if ($hasRecordingsInKaltura > 0)
            {
                $entry = $results->objects[0];
                while (count($results->objects) > 0) {
                    foreach ($results->objects as $kalturaEntry){
                        $this->kalturaentries[$kalturaEntry->referenceId] = $entry;
                    }
                    $pager->pageIndex += 1;
                    $results = $this->clients[$i]->media->listAction($listFilter, $pager);
                }
                $this->logToFile('INFO last vidyoRecording on Kaltura (config ' . $i . ') is: ' . $entry->partnerSortValue . ', GUID: ' . $entry->referenceId);
                //	$this->kalturaentries[$entry->referenceId] = $entry;
                //$recordingStartSearch = $entry->partnerSortValue;
            }

            // list Vidyo recordings, ascending by date
            // this will bring the oldest 200 (VidyoReplay's API max limit), we'll need to loop through all the recordings till we find the last
            // recording that was synced with Kaltura (or begin synchronization with recording 0)
            //$start = $recordingStartSearch; //our start is either 0 (in case we have no recordings in Kaltura, or we want to resync all)
            $start = 0; //always start from 0 since Vidyo has a bug with paging, this will allow us to loop through everything in vidyoreplay and decide ourselves what to sync and what not.
            $bundles = 100; //this must always be larger than 1 and max 200/null (defines how many recordings to pull each request)
            $recordingsArray = array();
            $recordsSearch = new RecordsSearchRequest(null, null, null, null, null, sortBy::date, sortDirection::ASC, $bundles, $start, null);
            $recordsSearchResult = $this->vidyoClients[$i]->RecordsSearch($recordsSearch);

            //if we got only one record back -
            if (!is_array($recordsSearchResult->records) && $recordsSearchResult->records != null)
            {
                $recording = $recordsSearchResult->records;
                if (!isset($this->kalturaentries[$recording->guid]))
                { //make sure we don't already have this recording in Kaltura
                    $recordingsArray[] = $recording;
                }
            }

            //if we have more than a single recording in the vidyoreplay library -
            //run through the rest of the VidyoReplay library to get all of the recordings using paging (start and bundles)
            while (is_array($recordsSearchResult->records) && count($recordsSearchResult->records) > 0)
            {
                // VidyoReplay tends to return a single recording as an object on its own instead of inside an Array
                foreach ($recordsSearchResult->records as $recording)
                {
                    if (!isset($this->kalturaentries[$recording->guid]))
                    { //make sure we don't already have this recording in Kaltura
                        $recordingsArray[] = $recording;
                    }
                }
                $start += $bundles;
                $recordsSearch = new RecordsSearchRequest(null, null, null, null, null, sortBy::date, sortDirection::ASC, $bundles, $start, null);
                $recordsSearchResult = $this->vidyoClients[$i]->RecordsSearch($recordsSearch);
            }

            $vidyoRecordsCount = count($recordingsArray);
            $this->logToFile('INFO syncing ' . $vidyoRecordsCount . ' new recordings');
            if (($vidyoRecordsCount == 0) || ($entry && $recordingsArray[$vidyoRecordsCount - 1]->id == $entry->partnerSortValue))
            {
                $this->logToFile('INFO All VidyoReplay recordings are synced in Kaltura. Exiting syncer cycle. config: ' . $i);
            }
            else
            {
                $multiRequestBatches = 50;
                $count = 0;
                $this->clients[$i]->startMultiRequest();
                foreach ($recordingsArray as $recording)
                {
                    $this->logToFile('INFO syncing ' . $recording->id . ', GUID: ' . $recording->guid);
                    $this->copyVidyoRecording2Kaltura($recording, $i);
                    $this->logToFile('SUCCESS synchronized recording guid: ' . $recording->guid);
                    if ($count % $multiRequestBatches == 0)
                    {
                        try
                        {
                            $multiRequest = $this->clients[$i]->doMultiRequest();
                            $this->logToFile('SUCCESS pushed a batch of recordings using multirequest to Kaltura config ' . $i);
                        }
                        catch (Exception $ex)
                        {
                            $this->logToFile('ERROR failed to push a batch of recordings to config ' . $i . ' using multirequest: ' . $ex->getMessage());
                        }
                        $this->clients[$i]->startMultiRequest();
                    }
                    $count += 1;
                }
                //clean the last batch of multirequest (send it to Kaltura) -
                if ($this->clients[$i]->isMultiRequest() == true)
                {
                    try
                    {
                        $multiRequest = $this->clients[$i]->doMultiRequest();
                        $this->logToFile('SUCCESS pushed a batch of recordings using multirequest to Kaltura config ' . $i);
                    }
                    catch (Exception $ex)
                    {
                        $this->logToFile('ERROR failed to push a batch of recordings using multirequest: ' . $ex->getMessage());
                    }
                }
            }
            $this->logToFile('SUCCESS Kaltura and Vidyo are synced! (' . $vidyoRecordsCount . ' recordings) config ' . $i);
        }
	}
	
	/**
	 * helper function to copy a recording video and its metadata from Vidyo to Kaltura 
	 * @access private
	 */
	private function copyVidyoRecording2Kaltura ($recording, $configNum)
	{
        // here we deconstruct the URL VidyoReplay is giving us, and we add the http User and Passwrod to it
        // that way, we allow Kaltura to import the file directly from the VidyoReplay server
        $url = $recording->fileLink;
        $urlParts = parse_url($url);
        $recordingVideoFileUrl = $urlParts["scheme"] . '://' . Vidyo2KalturaConfig::$conf[$configNum]['VIDYO_USER'] . ':' . Vidyo2KalturaConfig::$conf[$configNum]['VIDYO_PASSWORD'] . '@' . $urlParts["host"] . $urlParts["path"] . '?' . $urlParts["query"];
        // create a new Kaltura Media Entry and copy the Vidyo Recording metadata to it
        $entry = new KalturaMediaEntry();
        $entry->mediaType = KalturaMediaType::VIDEO;
        $entry->referenceId = $recording->guid;
        $entry->partnerSortValue = $recording->id;
        $entry->userId = $recording->userName;
        $entry->tags = Vidyo2KalturaConfig::$conf[$configNum]['VIDYO_KALTURA_TAGS'] . ',' . $recording->tags;
        $entry->name = $recording->title;
        $entry->description = $recording->comments;
        $entry->categories = Vidyo2KalturaConfig::$conf[$configNum]['KALTURA_VIDYO_RECORDINGS_CATEGORY'];
        $entry = $this->clients[$configNum]->media->add($entry);
        $this->logToFile('SUCCESS creating new Kaltura Entry Id: ' . $entry->id . ' of recording: ' . $recording->id);

        // make Kaltura import the recording file from the VidyoReplay server
        $resource = new KalturaUrlResource();
        $resource->url = $recordingVideoFileUrl;
        $this->clients[$configNum]->media->addContent($entry->id, $resource);

        // if we have custom metadata profile setup -
        if (Vidyo2KalturaConfig::$conf[$configNum]['KALTURA_VIDYOREPLAY_METADATA_PROFILE_ID'] != '%metadataprofileid' . $configNum. '%')
        {
            // fill the entry custom metadata (the vidyoReplay profile)
            $dateCreated = strtotime($recording->dateCreated);
            $endTime = strtotime($recording->endTime);
            $customMetadata = "<metadata><RecordingId>{$recording->id}</RecordingId><RecordingGuid>{$recording->guid}</RecordingGuid><TenantName>{$recording->tenantName}</TenantName><UserName>{$recording->userName}</UserName><UserFullName>{$recording->userFullName}</UserFullName><DateCreated>{$dateCreated}</DateCreated><EndTime>{$endTime}</EndTime><DateCreatedString>{$recording->dateCreatedString}</DateCreatedString><Pin>{$recording->pin}</Pin><RecordScope>{$recording->recordScope}</RecordScope><RoomName>{$recording->roomName}</RoomName><RecorderId>{$recording->recorderId}</RecorderId><Locked>{$recording->locked}</Locked></metadata>";
            $metadata = $this->clients[$configNum]->metadata->add(Vidyo2KalturaConfig::$conf[$configNum]['KALTURA_VIDYOREPLAY_METADATA_PROFILE_ID'], KalturaMetadataObjectType::ENTRY, $entry->id, $customMetadata);
            $this->logToFile('SUCCESS synced custom metadata fields to entry id: ' . $entry->id);
        }

        $this->logToFile('SUCCESS importing Vidyo recording: ' . $recordingVideoFileUrl . ' to Kaltura Entry: ' . $entry->id);
    }
}

$syncer = new syncv2k();
if ($syncer->initializeSuccess == true) {
	$syncer->syncVidyo2Kaltura();
}
