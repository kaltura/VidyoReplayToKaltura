<?php
class Vidyo2KalturaConfig
{
	const PARTNER_ID = 000000; //The Kaltura Publisher/Partner ID (from: http://kmc.kaltura.com/index.php/kmc/kmc4#account|integration )
	const SECRET = 'xxxxxxxxxxxxx'; //The Kaltura Publisher API USER Secret (from http://kmc.kaltura.com/index.php/kmc/kmc4#account|integration )
	const ADMIN_SECRET = 'xxxxxxxxxxxx'; //The Kaltura Publisher API ADMIN Secret (from: http://kmc.kaltura.com/index.php/kmc/kmc4#account|integration )
	const SERVICE_URL = 'http://www.kaltura.com'; //The url to the Kaltura Server
	const KALTURA_VIDYO_RECORDINGS_CATEGORY = 'MediaSpace>site>galleries>Vidyo Recordings'; //The category to which synchronized recordings will be added
	const VIDYO_KALTURA_TAGS = 'vidyorecording'; //Special tags to track VidyoRecording in Kaltura - This must be set once, and never changed
	const VIDYO_USER = 'super'; //The SUPER user for the VidyoReplay Server
	const VIDYO_PASSWORD = 'password'; //The password of the SUPER user of the VidyoReplay Server
	const VIDYO_REPLAY_SERVER = 'http://dev20-replay.vidyo.com'; //The URL to the VidyoReplay Server
	const KALTURA_VIDYOREPLAY_METADATA_PROFILE_ID = '%metadataprofileid%'; //The Metadata Profile ID in Kaltura that contains the VidyoReplay recording fields. Set by setupMeatada.php
}
