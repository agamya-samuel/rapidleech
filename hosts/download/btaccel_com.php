<?php

if (!defined('RAPIDLEECH'))
  {
  require_once("index.html");
  exit;
  }
####### Free Account Info. ###########
$btaccel_login = ""; //  Set your username
$btaccel_pass = ""; //  Set your password
##############################
		
	/**
	 * You can use this function to retrieve pages without parsing the link
	 * 
	 * @param string $link The link of the page to retrieve
	 * @param string $cookie The cookie value if you need
	 * @param array $post name=>value of the post data
	 * @param string $referer The referer of the page, it might be the value you are missing if you can't get plugin to work
	 * @param string $auth Page authentication, unneeded in most circumstances
	 */
	function GetPage($link, $cookie = 0, $post = 0, $referer = 0, $auth = 0) 
	{
		global $pauth;
		if (!$referer) {
			global $Referer;
			$referer = $Referer;
		}
		$Url = parse_url(trim($link));
		$page = geturl ( $Url ["host"], $Url ["port"] ? $Url ["port"] : 80, $Url ["path"] . ($Url ["query"] ? "?" . $Url ["query"] : ""), $referer, $cookie, $post, 0, $_GET ["proxy"], $pauth, $auth );
		is_page ( $page );
		return $page;
	}
	
	function loginto( $usr, $pass )
	{	
		if ( empty($usr) || empty($pass) )
		{
			html_error("Login/Pass not inserted",0);
		}
		else
		{
			$Href = "http://www.btaccel.com/login/";
			$referer = "http://www.btaccel.com/";
			
			$post["email"]=$usr;
			$post["password"]=$pass;
			$page = GetPage( $Href, 0, $post, $referer );
			$cookie = GetCookies($page);
						
			$page = GetPage('http://www.btaccel.com/home/', $cookie, 0, $referer);
			is_notpresent($page, 'logout', 'Error logging in - perhaps logins are incorrect');
			
			return $cookie;
		}
	}
	
?>

<table border="1" align="center" cellspacing="5" cellpadding="5">
<form id="tavola"  >
<?php

$pos = strpos($LINK,"step=1");
if( !( $pos === false ) )
{
	$cookie = trim ( cut_str ( $LINK, "cookies=", "&" ) );
	$cookie = urldecode( $cookie );
		
	$Href = $LINK;
	$tmp = $Href ;

	$tmp2=cut_str($tmp,'&file_name=','&');
	$ff=explode("/",$tmp2);
	$FileName=$ff[count($ff)-1];

	$Url = parse_url($Href);
	
	$loc = "$PHP_SELF?filename=".urlencode($FileName)."&host=".$Url["host"]."&path=".urlencode($Url["path"].($Url["query"] ? "?".$Url["query"] : ""))."&referer=".urlencode($LINK)."&cookie=".urlencode($cookie)."&email=".($_GET["domail"] ? $_GET["email"] : "")."&partSize=".($_GET["split"] ? $_GET["partSize"] : "")."&method=".$_GET["method"]."&proxy=".($_GET["useproxy"] ? $_GET["proxy"] : "")."&saveto=".$_GET["path"]."&link=".urlencode($LINK).($_GET["add_comment"] == "on" ? "&comment=".urlencode($_GET["comment"]) : "").($pauth ? "&pauth=$pauth" : "").(isset($_GET["audl"]) ? "&audl=doum" : "");
	insert_location( $loc );
}
else
{
	$GetUser = parse_url( $LINK );
	
	$user = $GetUser['user'];
	$pass = $GetUser['pass'];
			
	if ( isset ( $user ) && isset ( $pass ) )
	{
		$btaccel_login = trim( $user );
		$btaccel_pass = trim( $pass );
		
		$LINK = $GetUser['scheme'] . "://" . $GetUser['host'] .$GetUser['path'];
	}
	
	$cookies = loginto( $btaccel_login, $btaccel_pass );
	
	$page = GetPage( $LINK, $cookies ); 
	
	//$page = geturl($Url["host"], $Url["port"] ? $Url["port"] : 80, $Url["path"].($Url["query"] ? "?".$Url["query"] : ""), 0, $cookies, 0, 0, $_GET["proxy"],$pauth); 
	$frmfiles=cut_str($page,'<table id="files"','</table>');
	preg_match_all('%http://.+/get\?[^\'"]+%i',$frmfiles,$files) ;
	$cc=1 ;
	
	foreach( $files[0] as $tmp )
	{
	    $tmp2=cut_str($tmp,'&file_name=','\n');
	    $ff=explode("/",$tmp2);
	    $fi=$ff[count($ff)-1];
		$fil=urldecode($fi);
		$file=str_replace(":80/get?","/get/?",$tmp);
		$file .= "&step=1&cookies=".urlencode ( $cookies )."&";
		
		$namefile=$fil;
		echo "<tr><td><input type=checkbox id=cs$cc ></td><td><input type=\"hidden\" id=\"lin$cc\" value=\"$file\" ></td><td id=link$cc >$namefile</td></tr>";
		$cc++;
	}
?>
<tr><td><input type='checkbox' name='checkall' id='checkall' onclick='checkedAll();'> all<td><td align=center><input type=button onclick='selt(<?php echo $cc-1 ?>)' value='Step1 select and click'></tr>
</form>
</table>
<script language="javascript" type="text/javascript">
    function selt(cc)
    {
        var pp;
        var lks="";    
        var ck;
        var tmp;
		var tmp2;
		
		var totalCheckedLinks = 0;
        for( pp=1; pp<=cc; pp++ )
		{
			ck = document.getElementById("cs"+ pp );
			if (ck.checked) 
			{
				totalCheckedLinks++;
				tmp = document.getElementById("lin"+ pp );
				tmp2 = tmp.value.replace(/%/g,"%25");
				lks = lks + tmp2 + "\r\n";
			}
        }
		
		if ( totalCheckedLinks == 1 )
		{
			// only one link to download, so rl can download it.
			document.write('<form id="downloadFormId" name="downloadForm" action=index.php method=post >');
			document.write('<Input type=Hidden name=link value= "' + lks + '" >');	
			// document.write('<center><Input type=submit name=submitButton value="Step2 click for download"></center>');	
			document.write('</form>');
		}
		else
		{
			// morethan one link to download, so autodownloader will download it.
			document.write('<form id="downloadFormId" name="downloadForm" action=audl.php?GO=GO method=post >');
			document.write('<Input type=Hidden name=links value= "' + lks + '" >');	
			// document.write('<center><Input type=submit name=submit value="Step2 click for send selected to Autodownloader"></center>');	
			document.write('</form>');
		}
		
		document.getElementById( "downloadFormId" ).submit();
		
    }
	
	checked=false;
	function checkedAll (frm1) 
	{
		var aa= document.getElementById('tavola');
		if (checked == false)
        {
           checked = true
        }
        else
        {
          checked = false
        }
		for (var i =0; i < aa.elements.length; i++) 
		{
			aa.elements[i].checked = checked;
		}
    }
</script>
	
<?php	
}

flush();
/*************************\  
WRITTEN by kaox 21-jul-2009
UPDATED by kaox 04-oct-2009
UPDATED and Fixed by rajmalhotra 19-Dec-2009 
UPDATED by rajmalhotra 17-Jan-2010 If only one link to download then rapidleech will take care of it and removed step 2 button
Fixed by Raj Malhotra on 28 Feb 2010
\*************************/
?>