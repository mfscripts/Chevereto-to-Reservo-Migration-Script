<?php
/*
 * Chevereto v3.6+ => Reservo Migration Script.
 * 
 * Mirgration script for converting users, images and stats data
 * from Chevereto v3.6+. Set the config values below, upload this
 * script to the base of your Reservo install and load it within
 * a browser.
 * 
 * REQUIREMENTS:
 * MySQL PDO
 * Reservo installed
 *
 * This has been tested with Chevereto v3.6 although it may also
 * work with other versions.
 */

// Chevereto v3.6 - database settings
define('CHEVERETO_DB_HOST', 'localhost');
define('CHEVERETO_DB_NAME', '');
define('CHEVERETO_DB_USER', '');
define('CHEVERETO_DB_PASS', '');

// Chevereto v3.6 - database table prefix
define('CHEVERETO_DB_TABLE_PREFIX', 'chv_');

// Reservo - config file path
define('RESERVO_CONFIG_FILE_PATH', '_config.inc.php');

/*
 * ******************************************************************
 * END OF CONFIG SECTION, YOU SHOULDN'T NEED TO CHANGE ANYTHING ELSE
 * ******************************************************************
 */
 
// allow up to 24 hours for it to run
set_time_limit(60*60*24);

// make sure we are in the root and can find the config file
if (!file_exists(RESERVO_CONFIG_FILE_PATH))
{
    die('ERROR: Could not load Reservo config file. Ensure you\'re running this script from the root of your Reservo install.');
}

// include Reservo config
require_once(RESERVO_CONFIG_FILE_PATH);

// test database connectivity, Reservo
try
{
    $ysDBH = new PDO("mysql:host=" . _CONFIG_DB_HOST . ";dbname=" . _CONFIG_DB_NAME, _CONFIG_DB_USER, _CONFIG_DB_PASS);
    $ysDBH->exec("set names utf8");
}
catch (PDOException $e)
{
    die('ERROR: Could not connect to Reservo database. ' . $e->getMessage());
}

// test database connectivity, Chevereto
try
{
    $chevDBH = new PDO("mysql:host=" . CHEVERETO_DB_HOST . ";dbname=" . CHEVERETO_DB_NAME, CHEVERETO_DB_USER, CHEVERETO_DB_PASS);
    $chevDBH->exec("set names utf8");
}
catch (PDOException $e)
{
    die('ERROR: Could not connect to Chevereto database. ' . $e->getMessage());
}

// initial checks passed, load stats for converting and get user confirmation
$chevStats = array();

// files
$getFiles               = $chevDBH->query('SELECT COUNT(image_id) AS total FROM '.CHEVERETO_DB_TABLE_PREFIX.'images');
$row                    = $getFiles->fetchObject();
$chevStats['totalFiles'] = (int) $row->total;

// users
$getUsers               = $chevDBH->query('SELECT COUNT(user_id) AS total FROM '.CHEVERETO_DB_TABLE_PREFIX.'users');
$row                    = $getUsers->fetchObject();
$chevStats['totalUsers'] = (int) $row->total;

// folders
$getFolders               = $chevDBH->query('SELECT COUNT(album_id) AS total FROM '.CHEVERETO_DB_TABLE_PREFIX.'albums');
$row                      = $getFolders->fetchObject();
$chevStats['totalFolders'] = (int) $row->total;

// banned ips
$getIpBans               = $chevDBH->query('SELECT COUNT(ip_ban_id) AS total FROM '.CHEVERETO_DB_TABLE_PREFIX.'ip_bans');
$row                     = $getIpBans->fetchObject();
$chevStats['totalIpBans'] = (int) $row->total;

// categories
$getCategories               = $chevDBH->query('SELECT COUNT(category_id) AS total FROM '.CHEVERETO_DB_TABLE_PREFIX.'categories');
$row                     = $getCategories->fetchObject();
$chevStats['totalCategories'] = (int) $row->total;

$getSetting              = $chevDBH->query('SELECT setting_value FROM '.CHEVERETO_DB_TABLE_PREFIX.'settings WHERE setting_name = \'crypt_salt\' LIMIT 1');
$row                     = $getSetting->fetchObject();
define("CHEVERETO_CRYPT_SALT", $row->setting_value);

// page setup
define('PAGE_TITLE', 'Chevereto 3.6+ => Reservo Migration Tool');
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title><?php echo PAGE_TITLE; ?></title>
        <meta name="distribution" content="global" />
        <style>
            body
            {
                margin: 0px;
                padding: 0;
                font: 100%/1.0 helvetica, arial, sans-serif;
                color: #444;
                background: #ccc;
            }

            h1, h2, h3, h4, h5, h6
            {
                margin: 0 0 1em;
                line-height: 1.1;
            }

            h2, h3 { color: #003d5d; }
            h2 { font-size: 218.75%; }
            h3 { font-size: 137.5%; }
            h4 { font-size: 118.75%; }
            h5 { font-size: 112.5%; }
            p { margin: 0 0 1em; }
            img { border: none; }
            a:link { color: #035389; }
            a:visited { color: #09619C; }

            a:focus
            {
                color: #fff;
                background: #000;
            }

            a:hover { color: #000; }

            a:active
            {
                color: #cc0000;
                background: #fff;
            }

            table
            {
                margin: 1em 0;
                border-collapse: collapse;
                width: 100%;
            }

            table caption
            {
                text-align: left;
                font-weight: bold;
                padding: 0 0 5px;
                text-transform: uppercase;
                color: #236271;
            }

            table td, table th
            {
                text-align: left;
                border: 1px solid #b1d2e4;
                padding: 5px 10px;
                vertical-align: top;
            }

            table th { background: #ecf7fd; }

            blockquote
            {
                background: #ecf7fd;
                margin: 1em 0;
                padding: 1.5em;
            }

            code
            {
                background: #ecf7fd;
                font: 115% courier, monaco, monospace;
                margin: 0 .3em;
            }

            abbr, acronym
            {
                border-bottom: .1em dotted;
                cursor: help;
            }
            #container
            {
                margin: 0 0px;
                background: #fff;
            }

            #header
            {
                background: #ccc;
                padding: 20px;
            }

            #header h1 { margin: 0; }

            #navigation
            {
                float: left;
                width: 100%;
                background: #333;
            }

            #navigation ul
            {
                margin: 0;
                padding: 0;
            }

            #navigation ul li
            {
                list-style-type: none;
                display: inline;
            }

            #navigation li a
            {
                display: block;
                float: left;
                padding: 5px 10px;
                color: #fff;
                text-decoration: none;
                border-right: 1px solid #fff;
            }

            #navigation li a:hover { background: #383; }

            #content
            {
                clear: left;
                padding: 20px;
            }

            #content h2
            {
                color: #000;
                font-size: 160%;
                margin: 0 0 .5em;
            }

            #footer
            {
                background: #ccc;
                text-align: right;
                padding: 20px;
                height: 1%;
                font-size: 12px;
            }

            .important, .error
            {
                color: red;
                font-weight: bold;
            }

            .success
            {
                color: green;
                font-weight: bold;
            }

            .button
            {
                border:1px solid #4b546a;-webkit-box-shadow: #B7B8B8 0px 1px 0px inset;-moz-box-shadow: #B7B8B8 0px 1px 0px inset; box-shadow: #B7B8B8 0px 1px 0px inset;-webkit-border-radius: br_rightpx br_leftpx -1px -1px;-moz-border-radius: br_rightpx br_leftpx -1px -1px;border-radius: br_rightpx br_leftpx -1px -1px; padding: 10px 10px 10px 10px; text-decoration:none; display:inline-block;text-shadow: -1px -1px 0 rgba(0,0,0,0.3);font-weight:bold; color: #FFFFFF;
                background-color: #606c88; background-image: -webkit-gradient(linear, left top, left bottom, from(#606c88), to(#3f4c6b));
                background-image: -webkit-linear-gradient(top, #606c88, #3f4c6b);
                background-image: -moz-linear-gradient(top, #606c88, #3f4c6b);
                background-image: -ms-linear-gradient(top, #606c88, #3f4c6b);
                background-image: -o-linear-gradient(top, #606c88, #3f4c6b);
                background-image: linear-gradient(to bottom, #606c88, #3f4c6b);filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#606c88, endColorstr=#3f4c6b);
                cursor: pointer;
            }

            .button:hover
            {
                border:1px solid #4b546a;
                background-color: #4b546a; background-image: -webkit-gradient(linear, left top, left bottom, from(#4b546a), to(#2c354b));
                background-image: -webkit-linear-gradient(top, #4b546a, #2c354b);
                background-image: -moz-linear-gradient(top, #4b546a, #2c354b);
                background-image: -ms-linear-gradient(top, #4b546a, #2c354b);
                background-image: -o-linear-gradient(top, #4b546a, #2c354b);
                background-image: linear-gradient(to bottom, #4b546a, #2c354b);filter:progid:DXImageTransform.Microsoft.gradient(GradientType=0,startColorstr=#4b546a, endColorstr=#2c354b);
            }
			
			label
			{
				margin-bottom: 2px;
				font-weight: bold;
			}
			
			label, input
			{
				clear: both;
				display: block;
			}
			
			input[type=password]
			{
				padding: 5px;
				border:1px solid #4b546a;-webkit-box-shadow: #B7B8B8 0px 1px 0px inset;-moz-box-shadow: #B7B8B8 0px 1px 0px inset; box-shadow: #B7B8B8 0px 1px 0px inset;-webkit-border-radius: br_rightpx br_leftpx -1px -1px;-moz-border-radius: br_rightpx br_leftpx -1px -1px;border-radius: br_rightpx br_leftpx -1px -1px; padding: 10px 10px 10px 10px; text-decoration:none;
				margin-bottom: 18px;
				width: 300px;
			}
        </style>
    </head>
    <body>
        <div id="container">
            <div id="header">
                <h1>
                    <?php echo PAGE_TITLE; ?>
                </h1>
            </div>
            <div id="content">
                <?php if (!isset($_REQUEST['submitted'])): ?>
                    <h2>
                        Confirm Migration
                    </h2>
                    <p>
                        Use this tool to migrate your users, images, albums and other data from Chevereto into a Reservo install.
                    </p>
                    <p>
                        To start, upload this file to the root of your Reservo install, ensure you've set your configuration at the top of this php script, then click 'start migration' below. To confirm, we've loaded your existing Chevereto table sizes below.
                    </p>
                    <p style='padding-top: 4px; padding-bottom: 4px;'>
                        <table style='width: auto;'>
                            <tr>
                                <th style='width: 150px;'>CHV Table:</th>
                                <th style='width: 150px;'>Total Rows:</th>
                            </tr>
                            <tr>
                                <td>Images:</td>
                                <td><?php echo $chevStats['totalFiles']; ?></td>
                            </tr>
                            <tr>
                                <td>Users:</td>
                                <td><?php echo $chevStats['totalUsers']; ?></td>
                            </tr>
                            <tr>
                                <td>Albums:</td>
                                <td><?php echo $chevStats['totalFolders']; ?></td>
                            </tr>
                            <tr>
                                <td>Banned IPs:</td>
                                <td><?php echo $chevStats['totalIpBans']; ?></td>
                            </tr>
							<tr>
                                <td>Total Categories:</td>
                                <td><?php echo $chevStats['totalCategories']; ?></td>
                            </tr>
                        </table>
                    </p>
                    <p class="important">
                        IMPORTANT: When you start this process, any existing data in your Reservo database will be cleared. Please ensure you've backed up both databases beforehand so you can easily revert if you need to.
                    </p>
					<p class="important">
                        This process wont actually migrate your images, it converts all the data in your Chevereto database for Reservo. Although it does keep the same file names for your stored images. So after this is completed you should move all your images into the Reservo /files/ folder on each server. More details supplied after the data conversion.
                    </p>
                    <p style="padding-top: 4px;">
						<p>
							<strong>Users Passwords:</strong> These can not be migrated as they are one-way encoded. Your users will be set a long random password on their migrated account, they should use the password reset form via the Reservo script to re-gain access to their account. If you enter your exiting Chevereto admin password below, we'll setup the admin account password for you.
						</p>
                        <form method="POST" action="migrate.php">
							<label>Chevereto Admin Password:</label>
							<input name="admin_account_password" type="password" value="" placeholder="Your admin area password..."/>
							
                            <input type="hidden" name="submitted" value="1"/>
                            <input type="submit" value="Start Migration" name="submit" class="button" onClick="return confirm('Are you sure you want to delete all the data from your Reservo database and import from the Chevereto database?\n\nYour users will need to request a password reset via Reservo once their data is migrated.');"/>
                        </form>
                    </p>
                <?php else: ?>
                    <h2>
                        Importing Data
                    </h2>
                    <p>
                        Clearing existing Reservo data... 
                        <?php
                        // delete Reservo data
                        $ysDBH->query('DELETE FROM download_tracker');
                        $ysDBH->query('DELETE FROM file');
                        $ysDBH->query('DELETE FROM file_folder');
                        $ysDBH->query('DELETE FROM payment_log');
                        $ysDBH->query('DELETE FROM sessions');
                        $ysDBH->query('DELETE FROM session_transfer');
                        $ysDBH->query('DELETE FROM stats');
                        $ysDBH->query('DELETE FROM users');
						$ysDBH->query('DELETE FROM banned_ips');
						$ysDBH->query('DELETE FROM plugin_imageviewer_category');
						$ysDBH->query('DELETE FROM plugin_imageviewer_category_file');
						$ysDBH->query('DELETE FROM plugin_imageviewer_meta');
						$ysDBH->query('DELETE FROM file_server');
						
						// create local server entry
						$sql   = "INSERT INTO `file_server` (`id`, `serverLabel`, `serverType`, `ipAddress`, `ftpPort`, `ftpUsername`, `ftpPassword`, `statusId`, `storagePath`, `fileServerDomainName`, `scriptPath`, `totalSpaceUsed`, `maximumStorageBytes`, `priority`, `routeViaMainSite`, `lastFileActionQueueProcess`, `serverConfig`) VALUES (1, 'Local Default', 'local', '', 0, '', NULL, 2, NULL, NULL, NULL, 0, 0, 0, 0, '0000-00-00 00:00:00', NULL);";
						$q     = $ysDBH->prepare($sql);
						$count = $q->execute();

                        echo 'done.';
                        ?>
                        <?php updateScreen(); ?>
                    </p>
                    <p style='padding-top: 4px; padding-bottom: 4px;'>
                        <table style='width: auto;'>
                            <tr>
                                <th style='width: 150px;'>CHV Table:</th>
                                <th style='width: 150px;'>Total Rows:</th>
                                <th style='width: 150px;'>Reservo Table:</th>
                                <th style='width: 150px;'>Successful Rows:</th>
                                <th style='width: 150px;'>Failed Rows:</th>
                            </tr>

                            <?php
                            // do images
                            $getFiles       = $chevDBH->query('SELECT image_id, image_user_id, image_original_filename, image_extension, image_album_id, image_md5, image_views, image_size, image_uploader_ip, image_date, image_storage_id, image_name, image_storage_mode, image_original_exifdata, image_width, image_height, image_category_id FROM '.CHEVERETO_DB_TABLE_PREFIX.'images');
                            $success        = 0;
                            $error          = 0;
							while($row = $getFiles->fetch())
                            {
								$image_storage_mode = $row['image_storage_mode'];
								$filePrefix = '';
								switch($image_storage_mode)
								{
									case 'datefolder':
										$filePrefix = preg_replace('/(.*)(\s.*)/', '$1', str_replace('-', '/', $row['image_date'])).'/';
										break;
									case 'old':
										$filePrefix = 'old/';
										break;
								}
								$localFilePath = $filePrefix.$row['image_name'].'.'.$row['image_extension'];

                                // insert into Reservo db
                                $sql   = "INSERT INTO file (id, originalFilename, shortUrl, fileType, extension, fileSize, localFilePath, userId, totalDownload, uploadedIP, uploadedDate, statusId, visits, lastAccessed, deleteHash, folderId, serverId, accessPassword) VALUES (:id, :originalFilename, :shortUrl, :fileType, :extension, :fileSize, :localFilePath, :userId, :totalDownload, :uploadedIP, :uploadedDate, :statusId, :visits, :lastAccessed, :deleteHash, :folderId, :serverId, :accessPassword)";
                                $q     = $ysDBH->prepare($sql);
                                $count = $q->execute(array(
                                    ':id'               => $row['image_id'],
                                    ':originalFilename' => $row['image_original_filename'],
                                    ':shortUrl'         => createShortUrl($row['image_id']),
                                    ':fileType'         => guess_mime_type($row['image_original_filename']),
                                    ':extension'        => strtolower($row['image_extension']),
                                    ':fileSize'         => $row['image_size'],
                                    ':localFilePath'    => $localFilePath,
                                    ':userId'           => ((int)$row['image_user_id']==0?'null':$row['image_user_id']),
                                    ':totalDownload'    => $row['image_views'],
                                    ':uploadedIP'       => $row['image_uploader_ip'],
                                    ':uploadedDate'     => $row['image_date'],
                                    ':statusId'         => 1,
                                    ':visits'           => $row['image_views'],
                                    ':lastAccessed'     => date('Y-m-d H:i:s', time()),
                                    ':deleteHash'       => MD5($row['image_md5'].$row['image_id'].rand(1000000,9999999)),
                                    ':folderId'         => $row['image_album_id'],
                                    ':serverId'         => $row['image_storage_id']==null?1:$row['image_storage_id'],
                                    ':accessPassword'   => null,
                                ));
								
								// add category record
								if((int)$row['image_category_id'])
								{
									$sql   = "INSERT INTO plugin_imageviewer_category_file (file_id, category_id) VALUES (:file_id, :category_id)";
									$q     = $ysDBH->prepare($sql);
									$q->execute(array(
										':file_id'          => $row['image_id'],
										':category_id'      => (int)$row['image_category_id'],
									));
								}

								// add meta record
								$sql   = "INSERT INTO plugin_imageviewer_meta (file_id, width, height, raw_data, date_taken) VALUES (:file_id, :width, :height, :raw_data, :date_taken)";
								$q     = $ysDBH->prepare($sql);
								$q->execute(array(
									':file_id'    => $row['image_id'],
									':width'      => (int)$row['image_width'],
									':height'     => (int)$row['image_height'],
									':raw_data'   => $row['image_original_exifdata'],
									':date_taken' => $row['image_date'],
								));

                                if ($count)
                                {
                                    $success++;
                                }
                                else
                                {
                                    $error++;
                                }
                            }
                            ?>
                            <tr>
                                <td>Images:</td>
                                <td><?php echo $chevStats['totalFiles']; ?></td>
                                <td>file:</td>
                                <td><?php echo $success; ?></td>
                                <td><?php echo $error; ?></td>
                            </tr>
                            <?php updateScreen(); ?>

                            <?php
                            // do users
                            $getUsers = $chevDBH->query('SELECT user_id, user_is_admin, user_username, user_email, user_status, user_date FROM '.CHEVERETO_DB_TABLE_PREFIX.'users ORDER BY user_id DESC');
                            $success  = 0;
                            $error    = 0;
                            while($row = $getUsers->fetch())
                            {
                                // insert into Reservo db
                                $sql       = "INSERT INTO users (id, username, password, level_id, email, lastlogindate, lastloginip, status, datecreated, createdip, identifier) VALUES (:id, :username, :password, :level_id, :email, :lastlogindate, :lastloginip, :status, :datecreated, :createdip, :identifier)";
                                $q         = $ysDBH->prepare($sql);
                                $userLevel = 1;
								$randomPassword = MD5(MD5(microtime().rand(10000,99999).microtime()));
                                if ($row['user_is_admin'] == 1)
                                {
                                    $userLevel = 20;
									if(isset($_REQUEST['admin_account_password']) && (strlen($_REQUEST['admin_account_password'])))
									{
										$randomPassword = MD5($_REQUEST['admin_account_password']);
									}								
                                }

                                $status = 'active';
                                if ($row['user_status'] == 'banned')
                                {
                                    $status = 'suspended';
                                }
								elseif ($row['user_status'] == 'awaiting-confirmation')
                                {
                                    $status = 'pending';
                                }
								elseif ($row['user_status'] == 'awaiting-email')
                                {
                                    $status = 'pending';
                                }
								
								// get password information
								$loginDate = null;
								$loginIp = null;
								$getUserPassword = $chevDBH->query('SELECT login_date, login_ip FROM '.CHEVERETO_DB_TABLE_PREFIX.'logins WHERE login_user_id = '.$row['user_id'].' AND login_type = \'password\' LIMIT 1');
								$passwordRow = $getUserPassword->fetch();
								if($passwordRow)
								{
									$loginDate = $passwordRow['login_date'];
									$loginIp = $passwordRow['login_ip'];
								}

                                $count = $q->execute(array(
                                    ':id'             => $row['user_id'],
                                    ':username'       => $row['user_username'],
                                    ':password'       => $randomPassword,
                                    ':level_id'       => $userLevel,
                                    ':email'          => $row['user_email'],
                                    ':lastlogindate'  => $loginDate,
                                    ':lastloginip'    => $loginIp,
                                    ':status'         => $status,
                                    ':datecreated'    => $row['user_date'],
                                    ':createdip'      => long2Ip32bit($row['usr_lastip']),
                                    ':identifier'     => MD5(microtime() . $row['user_id'] . microtime()),
                                ));

								if($q->errorCode() == 0)
								{
                                    $success++;
                                }
                                else
                                {
									if($error < 100)
									{
										$errorLocal = $q->errorInfo();
										echo 'Skipped Row: '.$errorLocal[2]."<br/>";
									}
									if($error == 100)
									{
										echo "<strong>... [truncated insert errors to first 100]</strong><br/>";
									}
                                    $error++;
                                }
                            }
                            ?>
                            <tr>
                                <td>Users:</td>
                                <td><?php echo $chevStats['totalUsers']; ?></td>
                                <td>users:</td>
                                <td><?php echo $success; ?></td>
                                <td><?php echo $error; ?></td>
                            </tr>
                            <?php updateScreen(); ?>

                            <?php
                            // do albums
                            $getFolders = $chevDBH->query('SELECT album_id, album_name, album_user_id, album_privacy, album_date FROM '.CHEVERETO_DB_TABLE_PREFIX.'albums');
                            $success    = 0;
                            $error      = 0;
                            while($row = $getFolders->fetch())
                            {
								$isPublic = 2;
								if($row['album_privacy'] != 'public')
								{
									$isPublic = 0;
								}
								
                                // insert into Reservo db
                                $sql   = "INSERT INTO file_folder (id, userId, folderName, isPublic, date_added) VALUES (:id, :userId, :folderName, :isPublic, :date_added)";
                                $q     = $ysDBH->prepare($sql);
                                $count = $q->execute(array(
                                    ':id'         => $row['album_id'],
                                    ':userId'     => $row['album_user_id'],
                                    ':folderName' => $row['album_name'],
                                    ':isPublic'   => $isPublic,
									':date_added' => $row['album_date'],
                                ));

                                if ($count)
                                {
                                    $success++;
                                }
                                else
                                {
                                    $error++;
                                }
                            }
                            ?>
                            <tr>
                                <td>Albums:</td>
                                <td><?php echo $chevStats['totalFolders']; ?></td>
                                <td>file_folder:</td>
                                <td><?php echo $success; ?></td>
                                <td><?php echo $error; ?></td>
                            </tr>
                            <?php updateScreen();?>

                            <?php
                            // do ip bans
                            $getIpBans = $chevDBH->query('SELECT ip_ban_id, ip_ban_date, ip_ban_expires, ip_ban_ip, ip_ban_message FROM '.CHEVERETO_DB_TABLE_PREFIX.'ip_bans');
                            $success     = 0;
                            $error       = 0;
                            while($row = $getIpBans->fetch())
                            {
								// insert row
								$sql   = "INSERT INTO banned_ips (id, ipAddress, dateBanned, banType, banNotes, banExpiry) VALUES (:id, :ipAddress, :dateBanned, :banType, :banNotes, :banExpiry)";
                                $q     = $ysDBH->prepare($sql);
                                $count = $q->execute(array(
                                    ':id'            => $row['ip_ban_id'],
                                    ':ipAddress'       => $row['ip_ban_ip'],
                                    ':dateBanned'  => $row['ip_ban_date'],
                                    ':banType'        => 'Whole Site',
									':banNotes' => $row['ip_ban_message'],
									':banExpiry' => $row['ip_ban_expires'],
                                ));

                                if ($count)
                                {
                                    $success++;
                                }
                                else
                                {
                                    $error++;
                                }
                            }
                            ?>
                            <tr>
                                <td>Ip Bans:</td>
                                <td><?php echo $chevStats['totalIpBans']; ?></td>
                                <td>banned_ips:</td>
                                <td><?php echo $success; ?></td>
                                <td><?php echo $error; ?></td>
                            </tr>
                            <?php updateScreen(); ?>
							
							<?php
                            // do categories
                            $getIpBans = $chevDBH->query('SELECT category_id, category_name, category_url_key FROM '.CHEVERETO_DB_TABLE_PREFIX.'categories');
                            $success     = 0;
                            $error       = 0;
                            while($row = $getIpBans->fetch())
                            {
								// insert row
								$sql   = "INSERT INTO plugin_imageviewer_category (id, key, label) VALUES (:id, :key, :label)";
                                $q     = $ysDBH->prepare($sql);
                                $count = $q->execute(array(
                                    ':id'            => $row['category_id'],
                                    ':key'       => $row['category_url_key'],
                                    ':label'  => $row['category_name'],
                                ));

                                if ($count)
                                {
                                    $success++;
                                }
                                else
                                {
                                    $error++;
                                }
                            }
                            ?>
                            <tr>
                                <td>Categories:</td>
                                <td><?php echo $chevStats['totalCategories']; ?></td>
                                <td>plugin_imageviewer_category:</td>
                                <td><?php echo $success; ?></td>
                                <td><?php echo $error; ?></td>
                            </tr>
                            <?php updateScreen(); ?>

                        </table>
                    </p>

                    <p>
                        <strong>Import finished.</strong> Now copy your Chevereto images on your server into the /files/ folder within Reservo, retaining any directory structure. Note that your admin login to Reservo will be updated to the one you set at the start of this process.
                    </p>
					<p class="important">
                        IMPORTANT: When you are finished, ensure you remove this file from your server.
                    </p>
                    <p style="padding-top: 4px;">
                        <form method="POST" action="migrate.php">
                            <input type="submit" value="Restart" name="submit" class="button"/>
                        </form>
                    </p>
                <?php endif; ?>
            </div>
            <div id="footer">
                Copyright &copy; <?php echo date('Y'); ?> <a href="https://reservo.co" target="_blank">Reservo.co</a>
            </div>
        </div>
    </body>
</html>

<?php

// local functions
function updateScreen()
{
    flush();
    ob_flush();
}

function get_file_extension($file_name)
{
    return substr(strrchr($file_name, '.'), 1);
}

function long2Ip32bit($ip)
{
    return long2ip((float) $ip);
}

function guess_mime_type($filename)
{
    $mime_types = array(
        'txt'  => 'text/plain',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'php'  => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'swf'  => 'application/x-shockwave-flash',
        'flv'  => 'video/x-flv',
        // images
        'png'  => 'image/png',
        'jpe'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif'  => 'image/tiff',
        'svg'  => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip'  => 'application/zip',
        'rar'  => 'application/x-rar-compressed',
        'exe'  => 'application/x-msdownload',
        'msi'  => 'application/x-msdownload',
        'cab'  => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3'  => 'audio/mpeg',
        'qt'   => 'video/quicktime',
        'mov'  => 'video/quicktime',
        // adobe
        'pdf'  => 'application/pdf',
        'psd'  => 'image/vnd.adobe.photoshop',
        'ai'   => 'application/postscript',
        'eps'  => 'application/postscript',
        'ps'   => 'application/postscript',
        // ms office
        'doc'  => 'application/msword',
        'rtf'  => 'application/rtf',
        'xls'  => 'application/vnd.ms-excel',
        'ppt'  => 'application/vnd.ms-powerpoint',
        // open office
        'odt'  => 'application/vnd.oasis.opendocument.text',
        'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        'avi'  => 'video/avi',
    );

	$exp = explode('.', $filename);
    $ext = strtolower(array_pop($exp));
    if (array_key_exists($ext, $mime_types))
    {
        return $mime_types[$ext];
    }
    else
    {
        return 'application/octet-stream';
    }
}

function createShortUrl($id)
{
	global $localTracker;
	$selectionList = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	
	if(isset($localTracker))
	{
		$scanHash = $localTracker['scanHash'];
		$p = $localTracker['p'];
		$i = $localTracker['i'];
	}
	else
	{
		for($n = 0; $n<strlen($selectionList); $n++)
		{
			$i[] = substr($selectionList,$n ,1);
		}

		$scanHash = hash('sha256', CHEVERETO_CRYPT_SALT);
		$scanHash = (strlen($scanHash) < strlen($selectionList)) ? hash('sha512', CHEVERETO_CRYPT_SALT) : $scanHash;

		for($n=0; $n < strlen($selectionList); $n++)
		{
			$p[] =  substr($scanHash, $n ,1);
		}

		$localTracker = [
			'scanHash'	=> $scanHash,
			'p'			=> $p,
			'i'			=> $i
		];
	}
	
	array_multisort($p, SORT_DESC, $i);
	$selectionList = implode($i);
	$base  = strlen($selectionList);

	$out = "";
	for ($t = floor(log((float)$id, $base)); $t >= 0; $t--)
	{
		$bcp = bcpow($base, $t);
		$a   = floor($id / $bcp) % $base;
		$out = $out . substr($selectionList, $a, 1);
		$id  = $id - ($a * $bcp);
	}

	return $out;
}
?>