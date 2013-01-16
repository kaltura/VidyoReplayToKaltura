Syncer daemon script to synchronize recordings of Vidyo meetings (from VidyoReplay) to Kaltura accoutns
=============

API connector that synchronizes recordings from VidyoReplay server (http://www.vidyo.com) to Kaltura accounts (corp.kaltura.com).
This script keeps VidyoReplay Library and Kaltura account in sync.
It reads all of the metadata of all recordings in the VidyoReplay server, and submits these recordings and their metadata into a Kaltura account.
The script doesn't download or upload actual files, instead it creates a new ```KalturaMediaEntry``` of type video, populates its metadata fields using info from VidyoReplay and submits the URL to the recording file in VidyoReplay to Kaltura using the ```media.addContent``` API action.


First - Prepare The Syncer server
-------------

1. Make sure that the server where the syncer is running has the following installed:
    1. 1. Linux
    1. 2. PHP 5.3+
    1. 3. curl and php-curl
    1. 4. PHP support for SoapClient (since the VidyoReplay WSDL client extends SoapClient: http://php.net/manual/en/class.soapclient.php)
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
[2013/01/16 08:02:38] SUCCESS importing Vidyo recording: http://super:password@www.vidyoreplayserver.com/replay/flvFileStreaming.flv?file=f7b86816-b57c-49c4-84d1-ac56d374bd25 to Kaltura Entry: 1_da03vztz
[2013/01/16 08:02:38] SUCCESS synchronized recording guid: f7b86816-b57c-49c4-84d1-ac56d374bd25
[2013/01/16 08:02:38] SUCCESS synced custom metadata fields to entry id: 1_da03vztz
```
At the end of every syncer cycle, the following line will be printed:
```log
[2013/01/16 08:03:00] SUCCESS importing Vidyo recording: '.$recordingVideoFileUrl.' to Kaltura Entry: '.$entry->id
```

The daemon bash script, outputs any error into: runner.log
This file will be empty always. Unless some edge error will occur, in this case it should be reported and investigated on a case by case.

