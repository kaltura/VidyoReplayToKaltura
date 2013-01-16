'Syncer' daemon script to synchronize recordings of Vidyo meetings (from VidyoReplay) to Kaltura accoutns
====================
API connector that synchronizes recordings from VidyoReplay server (http://www.vidyo.com) to Kaltura accounts (corp.kaltura.com).

First - Prepare The Syncer server
====================
# Make sure that the server where the syncer is running has the following installed:
## Linux
## PHP 5.3+
## curl and php-curl
## PHP support for SoapClient (since the VidyoReplay WSDL client extends SoapClient: http://php.net/manual/en/class.soapclient.php)
# Make sure that the server has HTTP access to both the VidyoReplay server and Kaltura server
# Have access to the SUPER user of VidyoReplay and API Admin credentials to the Kaltura publisher account
# If you plan to synchronize all VidyoReplay Record fields to Kaltura custom metadata, make sure your account has custom metadata enabled
# Download all the syncer files (all files in this repo) and place them in a folder that is not accessible by outside users (It should be running as protected limitted user)
# Open the Vidyo2KalturaConfig.php file, and fill in all the credentials and configurations required (follow the instructions in the file comments)
# If you'd like to have all VidyoReplay Record fields synced to Kaltura's custom metadata:
## Make sure that user you use has write permissions to the Vidyo2KalturaConfig.php file
## Run ```php php setupMetadata.php``` to add the VidyoReplay Record metadata profile to your Kaltura publisher account
## It will automatically configure the newly created metadata profile Id in the Vidyo2KalturaConfig.php file
# Make sure that all files will be owned by the same user, and that user will be running rund.sh
# Make sure that the user running the syncer has write permissions to the following:
## syncVidyo2Kaltura.log file
## runner.log file
## /tmp/syncv2klockdir folder
