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

require_once "Dropbox/autoload.php";

class dbxBackup{
    // Database connection
    var $dbhost;
    var $dbuser;
    var $dbpass;
    var $dbname;
    
    // Folders
    var $folder;

    // Mode
    var $mode;
    
    // Paths
    var $local;
    var $remote;
    
    // Compressed files
    var $zip_files;
    
    /*
     * Set database details
     */
    public function setDatabase ($host, $user, $pass, $name)
    {
        $this->dbhost = $host;
        $this->dbuser = $user;
        $this->dbpass = $pass;
        $this->dbname = $name;
    }
    
    /*
     * Set folder to backup
     */
    public function setFolder ($folder)
    {
        $this->folder = $folder;
    }

    /*
     * Set backup mode
     */
    public function setBackupMode ($mode)
    {
        switch ($mode) {
            case 'week':
                $this->mode = date('N');
                break;

            default:
                $this->mode = '';
                break;
        }
    }
    
    /*
     * Set local and Dropbox path
     */
    public function setWorkFolders ($local, $remote)
    {
        $this->local = $local;
        $this->remote = $remote;
    }
    
    /*
     * Execution
     */
    public function execute()
    {
        // Prepare array for zip files
        $this->zip_files = array();
        
        // Dump databases
        $this->dumpDatabase();
        
        // Compress folder
        $this->compressFolder();
        
        // Sync to Ubuntu One
        $this->syncFiles();
    }
    
    /*
     * MySQL Database dump
     */
    public function dumpDatabase()
    {
        foreach ($this->dbname as $value) {
            $this->_dumpSingle($value);
        }
    }
    
    /*
     * Compress directories
     */
    public function compressFolder()
    {
        foreach ($this->folder as $folder) {
            $this->_compressFolder($folder);
        }
    }

    /*
     * Sync files to Dropbox
     */
    public function syncFiles()
    {
        if(!file_exists($this->local . 'dbxbackup')) {
            $data = $this->_authorize ();
        } else {
            $data = file_get_contents($this->local . 'dbxbackup');
        }
        
        $tokenA = json_decode($data, TRUE);
        
        // Set up the token for use in OAuth requests
        $accessToken = $tokenA['token'];

        $dbxClient = new Dropbox\Client($accessToken, "dbxbackup/1.0");

        // Send files
        foreach ($this->zip_files as $file) {
            $this->_sendFile($dbxClient, $file, $this->remote);
        }
    }
    
    /*
     * Athorize app with Ubuntu One
     */
    private function _authorize()
    {
        $appInfo = Dropbox\AppInfo::loadFromJsonFile("app_info.json");
        $webAuth = new Dropbox\WebAuthNoRedirect($appInfo, "dbxbackup/1.0");
        
        $authorizeUrl = $webAuth->start();
        
        echo "1. Go to: " . $authorizeUrl . "\n";
        echo "2. Click \"Allow\" (you might have to log in first).\n";
        echo "3. Copy the authorization code.\n";
        $authCode = trim(readline("Enter the authorization code here: "));
        
        list($accessToken, $dropboxUserId) = $webAuth->finish($authCode);

        $data = array(
            'token' => $accessToken,
            'userid' => $dropboxUserId
        );
        
        file_put_contents($this->local . 'dbxbackup', json_encode($data));
        
        return json_encode($data);
    }
    
    /*
     * Dump specific database
     */
    private function _dumpSingle($dbname)
    {
        $filename = $dbname . $this->mode . '.sql';
        $backupfile = $this->local . $filename;
        
        if($this->dbpass)
            system("mysqldump -h $this->dbhost -u $this->dbuser -p$this->dbpass $dbname > $backupfile");
        else
            system("mysqldump -h $this->dbhost -u $this->dbuser $dbname > $backupfile");
        
        // Compress
        $zip = new ZipArchive();
        
        $zipFilename = $this->local . $dbname . $this->mode . '.zip';
        
        if ($zip->open($zipFilename, ZIPARCHIVE::CREATE) !== TRUE) {
            die ("Could not open target file!");
        }
        
        $zip->addFile($backupfile, $filename) or die ("Could not add file: $filename");
        
        $zip->close();
        
        // Add to zip files array
        $this->zip_files[] = $zipFilename;
    }
    
    /*
     * Compress single folder
     */
    private function _compressFolder($folder)
    {
        // Zip object
        $zip = new ZipArchive();
        
        // Use folder path for file name
        $filename = $this->local . str_replace('\\', '_', str_replace('/', '_', $folder)) . $this->mode . '.zip';

        // Open target
        if ($zip->open($filename, ZIPARCHIVE::CREATE) !== TRUE) {
            die ("Could not open target file!");
        }

        // Initialize an iterator with the folder
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder));
        
        // Set basepath for zip file
        $basepath = basename($folder);

        // Iterate over the directory and add each file found to the archive
        foreach ($iterator as $key => $value) {
                $zip->addFile(realpath($key), str_replace($folder, $basepath, $key)) or die ("Could not add file: $key");
        }

        // Close and save archive
        $zip->close();
            
        // Add to zip files array
        $this->zip_files[] = $filename;
    }
    
    /*
     * Send single file to Ubuntu One
     */
    private function _sendFile($dbxClient, $file, $path)
    {
        $f = fopen($file, "rb");
        $result = $dbxClient->uploadFile($path . '/' . basename($file), Dropbox\WriteMode::force(), $f);
        fclose($f);
    }
}