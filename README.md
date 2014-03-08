dbxbackup_php
=============

Files and MySQL Backup script that syncs with Dropbox

How to
------

The class code is defined in dbxbachup.php and a simple usage example is at backup.php.
You can (should) create a cron job to execute your version of backup.php in a regular basis.

There is a file caled app_info.json where you need to input the details of your app in Dropbox.

What it does
------------

The class made available here will dump your MySQL databases , compress your folders and save them into a folder
(defined by you - test if the script can write to this folder).

Next, it will connect to Dropbox. First time your run it will ask you to go to a Dropbox
url and authorize this app. It will save the authorization information into a file named dbxbackup.
This way you only need to authorize once.

When you execute it a second time, the token info will be already available and the script
won't authorize itself twice (unless you delete the file dbxbackup).

Finally, the files will be uploaded to Dropbox using the PHP SDK.

References
----------

Most of this code came from the web, so these are the sources:

* [PHP Core API - Dropbox](https://www.dropbox.com/developers/core/start/php "Basic example of the PHP API")
* [u1backup Project](https://github.com/oscardias/u1backup "Original backup class, for Ubuntu One")