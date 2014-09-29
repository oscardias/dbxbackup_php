<?php
/*
dbxBackup_php v1.0.1
Copyright (C) 2013  Oscar de Souza Dias

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

require 'dbxbackup.php';

// Instantiate and execute
$dbxobj = new dbxBackup();

// Settings
// Define database connection
$dbxobj->setDatabase('localhost', 'root', '', array('dbname')); //array('dbname1','dbname2')

// Define folders for backup
$dbxobj->setFolder(array('/var/www')); //array('/var/www/site1', '/var/www/site2')

// Define files/folders that should be ignored
$dbxobj->setIgnore(array('.git')); //array('.git', 'wp-admin', 'LICENSE')

// Backup mode - always overwrite same file or add week day
$dbxobj->setBackupMode('single'); // or 'week'

// Local path for files (must have permission) and Dropbox path
$dbxobj->setWorkFolders('/tmp/', '/backups/');

// Execute
$dbxobj->execute();
