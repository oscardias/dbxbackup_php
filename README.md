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

The class made available here will dump your MySQL databases , compress your folders and save them into a folder (defined by you - test if the script can write to this folder).

Next, it will connect to Dropbox. First time your run it will ask you to go to a Dropbox url and authorize this app. It will save the authorization information into a file named dbxbackup. This way you only need to authorize once.

When you execute it a second time, the token info will be already available and the script won't authorize itself twice (unless you delete the file dbxbackup).

Finally, the files will be uploaded to Dropbox using the PHP SDK.

Options
------

**Database:**

Define your database details in the following command:

```php
$dbxobj->setDatabase('localhost', 'root', '', array('dbname'));
```

Add more databases in the array to banckup more than one using `array('dbname1', 'dbname2')`.

**Folders:**

Define the folders that will to be compressed and backed up:

```php
$dbxobj->setFolder(array('/var/www'));
```

Add more folders in the array to backup more than one using `array('/var/www/site1', '/var/www/site2')`.

Define files/folders that should be ignored when compressing the folder:

```php
$dbxobj->setIgnore(array('.git'));
```

**Backup mode:**

Define how the file should be saved to DropBox:

- `'single'`: if the routine should always overwrite the same file
- `'hour'`: append the hour to the filename - one file for each hour that you execute it
- `'day'`: append the day to the filename - one file for each day that you execute it
- `'hour_day'`: append the hour and day to the filename - one file for each hour and day that you execute it
- `'week'`: append the week day (1, 2, 3 ... 7) to the filename - one file for each day of the week that you execute it

```php
$dbxobj->setBackupMode('single');
```

**Local and Dropbox Paths**

Where the files will be saved locally and inside Dropbox. First the local folder (the routine must have permission for that) and the second parameter is for Dropbox:

```php
$dbxobj->setWorkFolders('/tmp/', '/backups/');
```

References
----------

Most of this code came from the web, so these are the sources:

* [PHP Core API - Dropbox](https://www.dropbox.com/developers/core/start/php "Basic example of the PHP API")
* [u1backup Project](https://github.com/oscardias/u1backup "Original backup class, for Ubuntu One")