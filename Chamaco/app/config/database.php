<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'sistema';
$active_record = false;

//base de datos del sistema
$db['sistema']['hostname'] = 'localhost';
$db['sistema']['username'] = 'postgres';
$db['sistema']['password'] = 'postgres';
$db['sistema']['database'] = 'chamaco';
$db['sistema']['dbdriver'] = 'postgres';
$db['sistema']['dbprefix'] = '';
$db['sistema']['pconnect'] = false;

$db['sistema']['db_debug'] = FALSE;

$db['sistema']['cache_on'] = FALSE;
$db['sistema']['cachedir'] = '';
$db['sistema']['char_set'] = 'utf8';
$db['sistema']['dbcollat'] = 'utf8_general_ci';
$db['sistema']['swap_pre'] = '';
$db['sistema']['autoinit'] = FALSE;
$db['sistema']['stricton'] = FALSE;

//base de datos del sistema
$db['sac']['hostname'] = 'localhost';
$db['sac']['username'] = 'postgres';
$db['sac']['password'] = 'postgres';
$db['sac']['database'] = 'sac';
$db['sac']['dbdriver'] = 'postgres';
$db['sac']['dbprefix'] = '';
$db['sac']['pconnect'] = false;

$db['sac']['db_debug'] = FALSE;

$db['sac']['cache_on'] = FALSE;
$db['sac']['cachedir'] = '';
$db['sac']['char_set'] = 'utf8';
$db['sac']['dbcollat'] = 'utf8_general_ci';
$db['sac']['swap_pre'] = '';
$db['sac']['autoinit'] = FALSE;
$db['sac']['stricton'] = FALSE;


//base de datos del directorio activo
$db['ldap']['hostname'] = 'gebdc02.gebolivar.com.ve';
$db['ldap']['username'] = 'cdaw';
$db['ldap']['password'] = '';
$db['ldap']['database'] = 'dc=gebolivar,dc=com,dc=ve';
$db['ldap']['dbdriver'] = 'ldap';
$db['ldap']['dbprefix'] = '';
$db['ldap']['pconnect'] = FALSE;

$db['ldap']['db_debug'] = FALSE;

$db['ldap']['cache_on'] = FALSE;
$db['ldap']['cachedir'] = '';
$db['ldap']['char_set'] = 'utf8';
$db['ldap']['dbcollat'] = 'utf8_general_ci';
$db['ldap']['swap_pre'] = '';
$db['ldap']['autoinit'] = FALSE;
$db['ldap']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */
