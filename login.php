<?php
class Pgsql
{
    var $server = "";
    var $db = "";
    var $user = "";
    var $password = "";
    var $conn;
    var $query_result;
    var $RECORD;
    
    /*
     * constructor of class
     */
    function Pgsql($server1, $db1, $user1, $password1)
    {
        $this->server = $server1;
        $this->db = $db1;
        $this->user = $user1;
        $this->password = $password1;   
		//echo "server=$server1\n";
        $this->open();
    }
    
    /*
     * query excecution function 
     */
    function query($sql)
    {
        if (!$this->conn)
            return false;
        
        $this->query_result = pg_query($this->conn, $sql);
        if (!$this->query_result)
        {
            echo "ERROR Pgsql.query: ".$sql;
            return false;
        }
        return $this->query_result;
    }
    
    /*
     * go to next record in result of query method
     */
    function nextRow()
    {
        $this->RECORD = pg_fetch_assoc($this->query_result);
        if ($this->RECORD!=false)
            return true;
        else
            return false;
    }
    
    
    /*
     * opens a connection to sql server
     */
    function open()
    {
        $rez = true;
        $this->conn = pg_connect("host=".$this->server." port=5432 dbname=".$this->db." user=".$this->user." password=".$this->password);
        if (!$this->conn)
        {
            echo "No conn :".$this->server;
            return false;
        }

        //echo "PGSQL ".$this->server." ok\n";        
        //echo "PGSQL ".$this->conn." ok\n";
    }
    
    /*
     * closes the connection
     */
    function close()
    {
        if ($this->conn)
            pg_close($this->conn);
    }
}


	session_start();
	include "db_conf.inc";

	if ( isset($HTTP_POST_VARS["loginas"]) ) { $loginas = $HTTP_POST_VARS["loginas"]; }
	else { $loginas = ""; }
	if ( isset($HTTP_POST_VARS["dbid"]) ) { $dbid = $HTTP_POST_VARS["dbid"]; }
	else { $dbid = ""; }

	$db_userid = "default";
	$db_login = "default";
	$db_password = "default";

	$rz=true;
	
	if ( $loginas != "" ){
		
		session_register("login_dbid");
		$login_dbid = $HTTP_POST_VARS["dbid"];
		$password = $HTTP_POST_VARS["password"];
		include_once $DB_CONF[$login_dbid]["include"];
		include_once "db.inc";

		$qry="select value from params where system='SYNC' and par='EXEC'";
		$dbSYNC = new DB;
		$dbSYNC->query($qry);
		$dbSYNC->next_record();
		if ($dbSYNC->Record["value"]=='1')
		{
			$qry="select case when now() > cast(value as date) then 1 else 0 end as value from params where system='SYNC' and par='DATE_FROM'";
			$dbSYNC = new DB;
			$dbSYNC->query($qry);
			$dbSYNC->next_record();
			if ($dbSYNC->Record["value"] == "1")
			{
				$rz = false;			
				echo "<script>alert('Sistemoje turi bûti ádiegti pakeitimai! Pagrindiniame kompiuteryje Atnaujinimø lange paspauskite mygtukà Pakeitimai. Ádiegus juos galësite tæsti darbà.');</script>";	
			}
		}
		
 		$qry="select case  when now()>='2011.05.08' then 1 else 0 end as dat, coalesce((select value from params where system='SYST' and upper(par)='RECIPEUPD' ),'0') as upd";
		$dbVAT = new DB;
		$dbVAT->query($qry);
		$dbVAT->next_record();
		if ($dbVAT->Record["dat"]==1 && $dbVAT->Record["upd"]==0)
		{
			$rz = false;			
			echo "<script>alert('Programa neparuoðta 2011.05.08 receptø pakeitimams! Paleiskite 2011.05.08 procedûrà atnaujinimo programoje.');</script>";	
		}
		
		$qry="select case when now()>='2015.01.01' then 1 else 0 end as dat, coalesce((select value from params where system='SYST' and par='eur_update' ),'1') as upd";
		$dbEUR = new DB;
		$dbEUR->query($qry);
		$dbEUR->next_record();
		if ($dbEUR->Record["dat"]==1 && $dbEUR->Record["upd"]==0)
		{
			$rz = false;			
			echo "<script>alert('Programa neparuoðta 2015.01.01 Euro pakeitimams! Paleiskite 2015.01.01 procedûrà atnaujinimo programoje!');</script>";	
		}
		
		$db = new DB;
		$qry = "select u.id as userid, u.login, u.name || ' ' || u.surname as user_name, u.password, p.name as user_post, u.email as user_email from users u ,post p where u.locked = 0 and u.postid=p.id and upper(login)=upper('" . $loginas . "')";		
		$db->query($qry);
		$db->next_record();
		$db_userid = $db->Record["userid"];
		$db_login = $db->Record["login"];
		$db_password = $db->Record["password"];
	}
	if ( strtoupper($loginas) == strtoupper($db_login) && $password == $db_password && $rz) {
		$db_user_name = $db->Record["user_name"];
		$db_user_email = $db->Record["user_email"];
		$db_user_post = $db->Record["user_post"];
		session_register("db_userid", "db_login", "db_user_name", "db_user_email", "db_user_post");
		$db->query("select * from search_systemdata");
		$db->next_record();
		$uab_name = $db->Record["name"];
		session_register("uab_name", "uab_address", "uab_postindex", "uab_email", "uab_phone", "uab_fax", "kas_client_id", "uab_ecode", "vat_code");
		$uab_address = $db->Record["address"];
		$uab_postindex = $db->Record["postindex"];
		$uab_ecode = $db->Record["ecode"];
		$uab_email = $db->Record["email"];
		$uab_phone = $db->Record["phone"];
		$uab_fax = $db->Record["fax"];
		$db_code = $db->Record["code"];
        $vat_code = $db->Record["tcode"];
		$kas_client_id = $db->Record["kas_client_id"];

		session_register("db_code", "db_storeid", "db_currencyid", "db_currencycode", "db_currencyname", "db_storename", "db_local", "kas_screen", "programfile");
		$db_storeid = $db->Record["storeid"];
		$db_currencyid = $db->Record["currencyid"];
		$db_currencycode = $db->Record["currencycode"];
		$db_currencyname = $db->Record["currencyname"];
		$db_storename = $db->Record["storename"];
		$db_local = $db->Record["local"];
		$db->query("select screen_size, programfile from devices where computername='".getenv("REMOTE_ADDR")."'");
		$db->next_record();	
		$kas_screen=$db->Record["screen_size"];
		$programfile=$db->Record["programfile"];
		$db->exec_func("update_session(".$db_code.", ".$db_userid.", '".gethostbyaddr(getenv("REMOTE_ADDR"))."', "."'Log in',"." 0".")");
		$URL = (($DB_CONF[$login_dbid]["url"]=="")?"main.php":"./".$DB_CONF[$login_dbid]["url"]."/main.php");
		header("location:".$URL);
	}
	else {
		$db_userid = "";
		$db_storeid = "";
		$db_login = "";
		session_register("db_userid");
		session_register("db_storeid");
		session_register("db_login");

		$login_dbid = "";
		$combobox = "";
		while ( list( $_id, $_db ) = each( $DB_CONF ) ) {
			$combobox .= "<option value=\"$_id\"";
			if ( $_id == $dbid ) {	$combobox .= " selected"; };
			$combobox .= ">".$_db["name"]."</option>";
		}
	if ($login_dbid != "") { $URL = (($DB_CONF[$login_dbid]["url"]=="")?"":$DB_CONF[$login_dbid]["url"]."/"); }
	else { $URL = "centras_devel/"; }
?>
<HTML>
<head>
	<LINK REL=stylesheet href="centras_devel/css/style.css" TYPE="text/css">
	<SCRIPT SRC="./<?=$URL ?>lib/js/md5.js"></SCRIPT>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1257">
	<TITLE>KAS</TITLE>
</head>
<BODY leftmargin=0 topmargin=0 onload="forma.loginas.focus();" onfocus="forma.loginas.focus();" onkeypress="press();" style="background-color:white">
<script>
	function fokusas() {
		if (!forma.loginas.value) { forma.loginas.focus(); forma.loginas.select(); }
		else { forma.pwd.focus(); forma.pwd.select(); }
	}
	function press () {
		switch (event.keyCode) {
			case 13 : // jei ENTER
				if ((!forma.loginas.value) || (!forma.pwd.value)) {fokusas(); event.keyCode = false;}
				else forma.ENTER.click();
				break;
			case 27 :
				window.close();
				break;
		}
	}
</script>
<?
$rz = false;
if ($rz && date("Y.m.d")>="2009.08.31")
{
	$dbVAT = new Pgsql("localhost", "kas", "kas", "llopasss");
	$qry="select count(1) as rez from devices where devicetype in (5,7)";
	$dbVAT->query($qry);
	$dbVAT->nextRow();
	
	if ($dbVAT->RECORD["rez"]=0)
	{
			$rz = true;			
	}
	
}
if ($rz){
?>
<table>
	<tr>
		<td>
			<a href="pvm_tamro.exe">PVM keitimo programa (TIK "DATECS FP550" ir "DATECS FP1000" kasos aparatams!!!)</a>
		</td>
	</tr>
</table>

<?
}
?>
<TABLE CLASS="tableLogoHeader" BORDER="0" CELLSPACING="0" CELLPADDING="0" BGCOLOR="#FF9900" WIDTH="100%">
  <TR>
  <TABLE WIDTH="100%" class="tableLogoHeader" align=center ><TR><TD valign="middle" align="center">
    <TABLE BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
    <TR>
    <TD>
      <TABLE WIDTH="100%" CELLPADDING="0" CELLSPACING="0" BORDER="0">
        <TR>
	<TD WIDTH="100%">
          <TABLE WIDTH="100%" CELLPADDING="0" CELLSPACING="0" BORDER="0">
            <TR>
	    <TD HEIGHT="138px"><IMG SRC="centras_devel/img/top1.jpg" ></TD>
	    <TD nowrap HEIGHT="138px" BACKGROUND="centras_devel/img/top1bg.jpg" WIDTH="100%" ALIGN="CENTER" class="normalText">
	    <SPAN CLASS="titlePageTextBig" >K</SPAN>iekinës<SPAN CLASS="titlePageTextBig" >A</SPAN>pskaitos<SPAN CLASS="titlePageTextBig" >S</SPAN>istema
	    </TD>
	    </TR>
	  </TABLE>
	</TD>
        </TR>
      </TABLE>
      </TD>
      </TR>
    </TABLE>
  </TABLE>
  </TR>
</TABLE>

<FORM NAME="forma" METHOD="POST">
<table align="center" style="border:1px solid black" width="300px">
<tr>
<td>
<table align="center" width="100%">
	<tr>
		<td class="normalText">Prisijungimo vardas</td>
		<td><INPUT TYPE="TEXT" TITLE="Login Name" NAME="loginas" CLASS="inputForm" VALUE="" SIZE=15"></td>
	</tr>
	<tr>
		<td class="normalText">Slaptaþodis</td>
		<td><INPUT TYPE="PASSWORD" TITLE="Password" ID="pwd" CLASS="inputForm" SIZE=15 onchange="forma.password.value=MD5(pwd.value)"><INPUT TYPE="hidden" name="password"></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><SELECT name="dbid" class="inputForm" style="width:100%"><?=$combobox?></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><INPUT TYPE="SUBMIT" NAME="ENTER" VALUE="Prisijungti" CLASS="buttonForm" ></td>
	</tr>
<?
		if ( $loginas != "" ){
?>

	<tr>
		<td colspan="2">Klaida prisijungimo duomenyse!!!</td>
	</tr>

</table>
</form>
</td>
</tr>
</table>
<?
		};
?>
	</BODY>
</HTML>
<?
		};
?>

