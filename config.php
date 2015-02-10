<?php
/// I am listing this out as neatly as possible.

$array = array();
$array['total_servers'] 		= 1; //the total number of servers.
$array['path_to_mysqldump']		= '/usr/local/Cellar/mysql/5.6.21/bin/mysqldump'; //location of mysqldump if not in your path.
$array['base_save_directory'] 	= '/Users/john/Desktop/BACKUPS'; //where to save the data.
$array['compress_files'] 		= 'Y'; //Use bzip2 to compress sql files. Y/N
$array['databases_only'] 		= 'Y'; //This will make an individual back up of each table in a tables. Easier for data recovery, slower for dumping.
/* 
	SERVER SECTION INFORMATION 
	our $array['total_servers'] as 3 so we need to have 3 server setups/
*/
$array['server'][1]['host'] 	= '127.0.0.1'; 						//hostname for server 1
$array['server'][1]['username'] = 'USERNAME_FOR_SERVER_ONE'; 		//username for server 1
$array['server'][1]['password'] = 'PASSWORD_FOR_SERVER_ONE'; 		//password for server 1


$array['server'][2]['host'] 	= '127.0.0.1'; 						//hostname for server 2
$array['server'][2]['username'] = 'USERNAME_FOR_SERVER_TWO'; 		//username for server 2
$array['server'][2]['password'] = 'PASSWORD_FOR_SERVER_TWO'; 		//password for server 2

$array['server'][3]['host'] 	= '127.0.0.1';						//hostname for server 3
$array['server'][3]['username'] = 'USERNAME_FOR_SERVER_THREE'; 		//username for server 3
$array['server'][3]['password'] = 'PASSWORD_FOR_SERVER_THREE'; 		//password for server 3