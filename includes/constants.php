<?php
/**
 * App Constants
 */

include("version.php");

define("APP_NAME","PlayLister");
define("APP_ID", 4);
define("APP_DESCRIPTION","Music Playlister based on iTunes and Youtube API Services");
define("APP_URL","http://spicyscripts.com/playlister");
define("APP_VERSION_URL","http://spicyscripts.com/version-check/?id=".APP_ID."&v=".APP_VERSION);

define("ADMIN_SEO_URL", "admin/");
define("ADMIN_URL", "admin.php");
define("ADMIN_PATH", "admin/");
	  	 
/**
 * Database Table Constants - these constants
 * hold the names of all the database tables used
 * in the script.
 */

define("TBL_USERS", "users");
define("TBL_USERS_ACTIVE",  "users_active");
define("TBL_USERS_BANNED",  "users_banned");
define("TBL_GUESTS_ACTIVE", "guests_active");
define("TBL_COUNTRIES", "countries");
define("TBL_SETTINGS", "settings");
define("TBL_GENRES", "genres");
define("TBL_PLAYLISTS", "playlists");
define("TBL_PLAYLISTS_TRACKS", "playlists_tracks");
define("TBL_ACTIVITY", "activity");

?>