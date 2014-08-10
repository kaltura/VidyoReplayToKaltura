<?php
class Vidyo2KalturaConfig
{
    public static $conf = array(
        // First config
        array('PARTNER_ID' => 0000000, //The Kaltura Publisher/Partner ID (from: http://kmc.kaltura.com/index.php/kmc/kmc4#account|integration )
            'SECRET' => 'xxxxxxxxxxxxxxx', //The Kaltura Publisher API USER Secret (from http://kmc.kaltura.com/index.php/kmc/kmc4#account|integration )
            'ADMIN_SECRET' => 'xxxxxxxxxxxxx', //The Kaltura Publisher API ADMIN Secret (from: http://kmc.kaltura.com/index.php/kmc/kmc4#account|integration )
            'SERVICE_URL' => 'http://video.panda-os.com', //The url to the Kaltura Server
            'KALTURA_VIDYO_RECORDINGS_CATEGORY' => 'MediaSpace>site>galleries>Vidyo Recordings', //The category to which synchronized recordings will be added
            'VIDYO_KALTURA_TAGS' => 'vidyorecording', //Special tags to track VidyoRecording in Kaltura - This must be set once, and never changed
            'VIDYO_USER' => 'xxxxxx', //The SUPER user for the VidyoReplay Server
            'VIDYO_PASSWORD' => 'xxxxxxx', //The password of the SUPER user of the VidyoReplay Server
            'VIDYO_REPLAY_SERVER' => 'http://replay.idvideophone.com/', //The URL to the VidyoReplay Server
            'KALTURA_VIDYOREPLAY_METADATA_PROFILE_ID' => '%metadataprofileid0%'), //The Metadata Profile ID in Kaltura that contains the VidyoReplay recording fields. Set by setupMeatada.php
        // Second config
        array('PARTNER_ID' => 000000000, //The Kaltura Publisher/Partner ID (from: http://kmc.kaltura.com/index.php/kmc/kmc4#account|integration )
            'SECRET' => 'xxxxxxxxxxxxxxxx', //The Kaltura Publisher API USER Secret (from http://kmc.kaltura.com/index.php/kmc/kmc4#account|integration )
            'ADMIN_SECRET' => 'xxxxxxxxxxxxxxxxxxx', //The Kaltura Publisher API ADMIN Secret (from: http://kmc.kaltura.com/index.php/kmc/kmc4#account|integration )
            'SERVICE_URL' => 'http://www.kaltura.com', //The url to the Kaltura Server
            'KALTURA_VIDYO_RECORDINGS_CATEGORY' => 'Vidyo Recordings', //The category to which synchronized recordings will be added
            'VIDYO_KALTURA_TAGS' => 'vidyorecordin2', //Special tags to track VidyoRecording in Kaltura - This must be set once, and never changed
            'VIDYO_USER' => 'xxxxxxx', //The SUPER user for the VidyoReplay Server
            'VIDYO_PASSWORD' => 'xxxxxx', //The password of the SUPER user of the VidyoReplay Server
            'VIDYO_REPLAY_SERVER' => 'http://testreplay.myidscloud.com', //The URL to the VidyoReplay Server
            'KALTURA_VIDYOREPLAY_METADATA_PROFILE_ID' => '%metadataprofileid1%') //The Metadata Profile ID in Kaltura that contains the VidyoReplay recording fields. Set by setupMeatada.php

    );
}
