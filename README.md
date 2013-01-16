Syncer daemon script to synchronize recordings of Vidyo meetings (from VidyoReplay) to Kaltura accoutns
=============

API connector that synchronizes recordings from VidyoReplay server (http://www.vidyo.com) to Kaltura accounts (corp.kaltura.com).

First - Prepare The Syncer server
-------------

1. Make sure that the server where the syncer is running has the following installed:
    1. Linux
    1. PHP 5.3+
    1. curl and php-curl
    1. PHP support for SoapClient (since the VidyoReplay WSDL client extends SoapClient: http://php.net/manual/en/class.soapclient.php)
1. Make sure that the server has HTTP access to both the VidyoReplay server and Kaltura server
1. Have access to the SUPER user of VidyoReplay and API Admin credentials to the Kaltura publisher account
1. If you plan to synchronize all VidyoReplay Record fields to Kaltura custom metadata, make sure your account has custom metadata enabled
1. Download all the syncer files (all files in this repo) and place them in a folder that is not accessible by outside users (It should be running as protected limitted user)
1. Open the Vidyo2KalturaConfig.php file, and fill in all the credentials and configurations required (follow the instructions in the file comments)
1. If you'd like to have all VidyoReplay Record fields synced to Kaltura's custom metadata:
    1. Make sure that user you use has write permissions to the Vidyo2KalturaConfig.php file
    1. Run ```php php setupMetadata.php``` to add the VidyoReplay Record metadata profile to your Kaltura publisher account
    * (Note: It will automatically configure the newly created metadata profile Id in the Vidyo2KalturaConfig.php file)
1. Make sure that all files will be owned by the same user, and that user will be running rund.sh
1. Make sure that the user running the syncer has write permissions to the following:
    1. syncVidyo2Kaltura.log file
    1. runner.log file
    1. /tmp/syncv2klockdir folder
