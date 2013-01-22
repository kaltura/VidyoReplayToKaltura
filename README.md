Syncer daemon script to synchronize meetings recordings from VidyoReplay to Kaltura
=============

API connector that synchronizes recordings from VidyoReplay server (http://www.vidyo.com) to Kaltura accounts (corp.kaltura.com).  
This script keeps VidyoReplay Library and Kaltura account in sync. 
The syncer daemon pulls Vidyo recording files and all of the recording's metadata from VidyoReplay and creates a new ```KalturaMediaEntry``` in Kaltura.
After the syncer script created the new entry the Kaltura server will pull the file from the VidyoReplay server and transcode the file preparing for cross-plarform delivery & playback.
  The VidyoReplay recording metadata is saved in a custom metadata profile by the name ```vidyoreplaymetadata00```. To have recordings metadata, make sure your Kaltura account has custom metadata enabled.

Background
-------------
The php script ```syncv2k.php``` is responsible for synchronizing VidyoReplay and Kaltura.  
The bash script ```rund.sh``` is responsible for running ```syncv2k.php``` as a daemon.  
The bash script ```killd.sh``` is responsible for stopping/killing both ```rund.sh``` and ```syncv2k.php``` as a daemon.  
The php script ```Vidyo2KalturaConfig.php``` is the configuration of the syncer.  
See below for logging details.  

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
    1. Run ```php setupMetadata.php``` to add the VidyoReplay Record metadata profile to your Kaltura publisher account
    (Note: It will automatically configure the newly created metadata profile Id in the Vidyo2KalturaConfig.php file)
1. Make sure that all files will be owned by the same user, and that user will be running rund.sh
1. Make sure that the user running the syncer has write permissions to the following:
    1. syncVidyo2Kaltura.log file
    1. runner.log file
    1. /tmp/syncv2klockdir folder

Running the syncer daemon
-------------
```bash
bash rund.sh &
```
Note the '&' at the end, this will make the daemon run in the background.

To make the syncer daemon run at startup
-------------
Make sure it is executable:
```bash
chmod u+x rund.sh
```
Then add it to the end (just before ```exit 0```) of your rc.local file.  
```/etc/rc.local``` for Debian or Ubuntu  
```/etc/rc.d/rc.local``` for RedHat/CentOS  
(make sure to use full path to rund.sh).  

Check if the daemon syncer is running
-------------
```bash 
bash rund.sh status
```
If syncer is running, this will return the process id too.

Killing (Stop) the daemon and syncer
-------------
```bash 
bash killd.sh
```

Logging
-------------
The syncer main log file is: syncVidyo2Kaltura.log  
This log file will be truncated every cycle (every time the script is run)  
While the syncer is runing, to monitor issues, run:  
```bash
tail -f syncVidyo2Kaltura.log
```
The log format for syncVidyo2Kaltura.log is: ```[YYYY/MM/DD HH:MM:SS] SUCCESS/INFO message```
It will either print SUCCESS (indicating command successfuly executed) or INFO (indicating status info).
Every syncer cycle will start with the following log section:
```log
[2013/01/16 08:02:36] SUCCESS initializing Kaltura success
[2013/01/16 08:02:36] SUCCESS initializing Vidyo success
[2013/01/16 08:02:36] INFO has recordings in Kaltura
[2013/01/16 08:02:36] INFO last vidyoRecording on Kaltura is: 38, GUID: 7f4f5222-3721-4c7a-a093-2c344eae421c
[2013/01/16 08:02:36] INFO VidyoList pre-while count: 70
[2013/01/16 08:02:36] INFO syncing 70 new recordings
```
Every synchronization of a specific recording will be as follow (this will repeat for every recording synced):
```log
[2013/01/16 08:02:36] INFO syncing 63, GUID: f7b86816-b57c-49c4-84d1-ac56d374bd25
[2013/01/16 08:02:37] SUCCESS creating new Kaltura Entry Id: 1_da03vztz of recording: 63
[2013/01/16 08:02:38] SUCCESS importing Vidyo recording: URLTORECORDINGFILE to Kaltura Entry: 1_da03vztz
[2013/01/16 08:02:38] SUCCESS synchronized recording guid: f7b86816-b57c-49c4-84d1-ac56d374bd25
[2013/01/16 08:02:38] SUCCESS synced custom metadata fields to entry id: 1_da03vztz
```
At the end of every syncer cycle, the following line will be printed:
```log
[2013/01/16 08:03:00] SUCCESS importing Vidyo recording: URLTORECORDINGFILE to Kaltura Entry: 1_da03vztz
```

To constantly grep for errors in the log (for monitoring purposes), use the following:
```bash
tail -f syncVidyo2Kaltura.log | grep ERROR
```
(Replace ERROR with SUCCESS or INFO for status monitoring)   

The daemon bash script, outputs any error into: runner.log
This file will be empty always. Unless some edge error will occur, in this case it should be reported and investigated on a case by case.

Potential Errors
-------------
The syncVidyo2Kaltura.log may show the following error:
```log
ERROR failed to push a batch of recordings using multirequest: Operation timed out after 10001 milliseconds with 0 bytes received
```
This does NOT necessarily mean that the request failed to reach Kaltura, it only indicates that the response from the server was taking long to arrive, and curl has expired its defined expirylimit
If this error shows up in your log, edit kaltura-php5/KalturaClientBase.php and look for the definition of $curlTimeout (around line 925), increase its value to a larger int (seconds).
