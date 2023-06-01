<?
	session_start();

	session_register("db_userid");
	if ( $db_userid == "" ) {
		header("location:index.php");
		exit;
	};
	
	include "db_conf.inc";
	session_register("login_dbid");
	$login_dbid = $login_dbid;
	include $DB_CONF[$login_dbid]["include"];
	include "db.inc";
	$db = new DB;
	$db->exec_func("update_session(".$db_code.", ".$db_userid.", '".gethostbyaddr(getenv("REMOTE_ADDR"))."', "."'',"." 1".")");
	@session_destroy();
	@header("location:index.php");
?>
