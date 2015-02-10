<?php
/*
The MIT License (MIT)

Copyright (c) 2015 John Petrilli

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.	
	
*/	
class backUpDatabases {	
	var $servers = array();
	var $config = array();
	function __construct ($array){ // making it easy for later to setup a CONFIG file.
//		if(file_exists(__DIR__.'johnpetrilliconf.php')) //this is just so i dont sync passwords to git by mistake.
		if(!isset($array['total_servers']))
			$this->config['total_servers']  	= $this->askQuestion("How Many MySQL servers are you backing up today? ex: 1,2,3: ");
		else
			$this->config['total_servers'] = $array['total_servers'];
		if(!isset($array['path_to_mysqldump'])){
			$this->config['path_to_mysqldump']  = $this->askQuestion("Is mysqldump in your PATH environment? Y/N: ",array('Y','N','y','n'));
			if(strtolower($this->config['path_to_mysqldump']) == 'n'){
				$this->config['path_to_mysqldump'] = $this->askQuestion("Please enter the full path to mysqldump ex: /usr/bin/mysqldump: ");
			}else{
				$this->config['path_to_mysqldump'] = 'mysqldump';
			}
		}else{
			$this->config['path_to_mysqldump'] = $array['path_to_mysqldump'];
		}
		if(!isset($array['base_save_directory']))		
			$this->config['base_save_directory'] = $this->askQuestion("Enter the full directory path you would like to save all the files: ");
		else
			$this->config['base_save_directory'] = $array['base_save_directory'];
			
		if(!isset($array['databases_only']))		
			$this->config['databases_only'] = $this->askQuestion("Do you want to back up a databases tables individually? Y/N: ",array('Y','N','y','n'));
		else
			$this->config['databases_only'] = $array['databases_only'];
		
		if(!isset($array['compress_files']))		
			$this->config['compress_files'] = $this->askQuestion("Would you like to bzip2 each .sql file? Y/N: ",array('Y','N','y','n'));
		else
			$this->config['compress_files'] = $array['compress_files'];
		echo "\n\nOk.....\nServer Setup:\n";
		$s = 1;
		while($s <= $this->config['total_servers']){
			echo "Server Number: $s\n";
			if(!isset($array['server'][$s]['host']))
				$this->servers[$s]['host'] = $this->askQuestion("Enter the hostname for server #$s: ");
			else
				$this->servers[$s]['host'] = $array['server'][$s]['host'];
			if(!isset($array['server'][$s]['username']))
				$this->servers[$s]['username'] = $this->askQuestion("Enter the username for server #$s: ");
			else
				$this->servers[$s]['username'] = $array['server'][$s]['username'];
			if(!isset($array['server'][$s]['password']))
				$this->servers[$s]['password'] = $this->askQuestion("Enter the password for server #$s: ");
			else
				$this->servers[$s]['password'] = $array['server'][$s]['password'];
			echo "\n\nTesting Server Connection:\n";
			$this->servers[$s]['connection'] = $this->connectToMySQL($this->servers[$s]['host'],$this->servers[$s]['username'],$this->servers[$s]['password']);
			if($this->servers[$s]['connection']){
				echo "MySQL connection to server $s is GOOD!\nClosing Connection for now..\nProceeding.\n";
				++$s;
			}else{
				unset($array['server'][$s]['host']);
				unset($array['server'][$s]['username']);
				unset($array['server'][$s]['password']);
				echo "MySQL connection to server $s is NOT GOOD\n.Please re-enter information to try again\n";
			}
		}
		$this->letsRun();
	}
	function letsRun(){
		echo "\n\nStarting Back Up...\n";
		foreach($this->servers as $k=>$v){
			echo "Opening Connection to Server $k\n";
			$this->servers[$k]['connection'] = $this->connectToMySQL($this->servers[$k]['host'],$this->servers[$k]['username'],$this->servers[$k]['password']);
			echo "Gathering All Databases on server...\n\n";
			$this->servers[$k]['databases'] = $this->getAllDatabases($this->servers[$k]['connection']);
			echo "Found ".count($this->servers[$k]['databases'])." Databases on this server.\n\n";
			echo "Looking through each database and backing up each table:\n";
			
			foreach($this->servers[$k]['databases'] as $key=>$value){
				echo "- Dumping Database: `$value`\n";
				$this->servers[$k]['connection']->select_db($value);
				if(strtolower($this->config['databases_only']) == 'y'){
					$filename = $this->config['base_save_directory']."/$value.sql";
					if(!file_exists($filename)){
						$ex = $this->config['path_to_mysqldump'].' --lock-tables=false --user '.$this->servers[$k]['username'].' --password='.$this->servers[$k]['password'].' --host='.$this->servers[$k]['host'].' '.$value.'> '.$filename.' 2> /dev/null';// &';
						
						exec($ex);
						$this->fileSizeChange($filename); //this is insanely DANGEROUS and unreliable. but I put it in. lol
					}else{
						echo "---File Exists for $value.sql! WILL NOT overwrite.\n";
					}
				}else{
					if(!is_dir($this->config['base_save_directory']."/$value"))
						mkdir($this->config['base_save_directory']."/$value/");
					$this->servers[$k]['tables'] = $this->getAllTables($this->servers[$k]['connection'],$value);
					foreach($this->servers[$k]['tables'] as $tableKey =>$tableValue){
						echo "-- Dumping table: `$value`.`$tableValue`...\n";
						$filename = $this->config['base_save_directory']."/$value/$tableValue".'.sql';
						if(!file_exists($filename)){
							$ex = $this->config['path_to_mysqldump'].' --user '.$this->servers[$k]['username'].' --password='.$this->servers[$k]['password'].' --host='.$this->servers[$k]['host'].' '.$value.' '.$tableValue.'> '.$filename.' 2> /dev/null';
							exec($ex);
						}else{
							echo "---File Exists for $tableValue.sql! WILL NOT overwrite!!!\n";
						}
					}
				}
			}
						
		}
	}	
	function fileSizeChange($filename,$size=0,$last_check=false){
		//TBD
	}
	function askQuestion($question,$acceptable_answers=false){
		$response = readline($question);
		if($response == '')
			return  $this->askQuestion($question,$acceptable_answers);
		if($acceptable_answers == false)
			return $response;
		$accepted = 0;
		foreach($acceptable_answers as $k=>$v){
			if($response == $v)
				$accepted++;
		}
		if($accepted == 0)
			return  $this->askQuestion($question,$acceptable_answers);
		else
			return $response;
	}
		
	function connectToMySQL($host,$user,$password){
		$mysqli = @new mysqli($host,$user,$password); // @ is bad practice...but just this once ;P
		if($mysqli->connect_errno)
			return false;
		else
		return $mysqli;
	}
	function getAllDatabases($conn){
		$allDB = array();
		$sql = "SHOW DATABASES";
		if ($result = $conn->query($sql)) { 
			while($obj = $result->fetch_object()){
				$allDB[] = $obj->Database;
			} 
		}
		return $allDB;
	}
	function getAllTables($conn,$database){
		$allTables = array();
		$sql = "SELECT table_name AS table_name FROM information_schema.tables WHERE table_schema = DATABASE();";
		if ($result = $conn->query($sql)) { 
			while($obj = $result->fetch_object()){
				$allTables[] = $obj->table_name;
			} 
		}
		return $allTables;
	}
	function letsDumpIt(){
		foreach($this->allDatabases as $k => $v){ //yes this will break if there is only one database.
			if($this->typeOfBackUp != 0){
				$this->getTablesFromDB($v);
				mkdir($this->pathToSaveDirectory."/".$v);
				foreach($this->currentTables as $key => $value){
				echo $v." ".$value."\n";
					exec($this->pathTomysqldump.' --user '.$this->mysqlUser.' --password='.$this->mysqPassword.' --host='.$this->server.' '.$v.' '.$value.'> '.$this->pathToSaveDirectory."/$v/$value".'.sql');
					$this->zipEmUp($this->pathToSaveDirectory."/$v/$value".'.sql');
				}
			}
			echo $v."\n";	
					exec($this->pathTomysqldump.' --user '.$this->mysqlUser.' --password='.$this->mysqPassword.' --host='.$this->server.' '.$v.'> '.$this->pathToSaveDirectory."/$v".'.sql');
			$this->zipEmUp($this->pathToSaveDirectory."/$v".'.sql');
		}
	}
}