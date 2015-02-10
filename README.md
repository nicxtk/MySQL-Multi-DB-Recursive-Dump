<h1>Usage</h1>
<p>This works on Mac/Linux based systems. I have not tried for Windows, although I am sure it would be a quick tweak on using the readline() function.</p>

Step #1: run the script. 'php dump.php' from command line.
Step #2: Answer the questions.
Step #3: Success. 

The --lock-tables=false option can be trick with INNODB tables. But I would imagine most people dont have permissions to lock tables on tables like information_schema.

I did not make the zip or bzip part functional yet. Its easy to add, but I needed to get this wrapped up. Ill loop back around in the next day or so.

Output looks like this:

php dump.php 


Ok.....
Server Setup:
Server Number: 1


Testing Server Connection:
MySQL connection to server 1 is GOOD!
Closing Connection for now..
Proceeding.


Starting Back Up...
Opening Connection to Server 1
Gathering All Databases on server...

Found 514 Databases on this server.

Looking through each database and backing up each table:
- Dumping Database: `information_schema`
---File Exists for information_schema.sql! WILL NOT overwrite.
- Dumping Database: `API`
........................................................


<h2>config file</h2>
I went ahead and made the config file :P

	Example:
	```php
	$array = array();
	$array['total_servers'] 		= 3; //the total number of servers.
	$array['path_to_mysqldump']		= '/usr/local/Cellar/mysql/5.6.21/bin/mysqldump'; //location of mysqldump if not in your path.
	$array['base_save_directory'] 	= '/Users/john/Desktop/BACKUPS'; //where to save the data.
	$array['compress_files'] 		= 'Y'; //Use bzip2 to compress sql files. Y/N
	$array['databases_only'] 		= 'Y'; //N = This will make an individual back up of each table in a tables. Easier for data recovery, slower for dumping.
	
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
	
    ```
    
<h1>CHANGLOG</h1>
2015-02-10:<br>
This was always a command line script, but it was modified for GIT to be more friendly and accept user input instead of hardcoding the values.
<br><br>
I am going to add a config file reader, that will not ask questions and run based off the data inside the config file.

2015-02-10.2:<br>
I went a head and made the config file anyway. See above for usage.


