<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="da-DK">
 
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><!--{$header_title}--> - Network account</title> 
<!--<link rel="stylesheet" href="/wp-content/themes/nybro/style.css" type="text/css" media="screen" />-->
<link rel="stylesheet" href="/netaccount/style.css" type="text/css" media="screen" />
<style type="text/css">
</style>
<script type='text/javascript' src='js/jquery-1.3.2.js'></script>

<!--{if $set_focus}-->
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("input:text:visible:first").focus();
});
</script>
<!--{/if}-->

<!--{if $include_autocomplete}-->
<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<script type='text/javascript' src='users_autocomplete_json.php'></script>
<script type="text/javascript">
jQuery(document).ready(
	function(){
		$(".brugersoeg").autocomplete(users, {
			minChars: 0,
			width: 350,
			matchContains: true,
			autoFill: false,
			formatItem: function(row, i, max) {
				return row.brugernavn + " (" + row.vaerelse + "): " + row.navn;
			},
			formatMatch: function(row, i, max) {
				return row.brugernavn + " (" + row.vaerelse + "): " + row.navn;
			},
			formatResult: function(row) {
				return row.brugernavn;
			}
		});

		$("#brugersoeg").result( function(){ $("#brugersoeg").parents('form:first').submit(); } );
	});
</script>
<!--{/if}-->

<!--{if $printlogin}-->
<script type="text/javascript">
jQuery(document).ready(
	function(){
		window.print();
	});
</script>
<link rel="stylesheet" href="printlogin.css" type="text/css" media="print" />
<!--{/if}-->

<script type="text/javascript">
jQuery(document).ready(
	function(){
		jQuery(".messages").hide().fadeIn(500);
	});
</script>


</head>
 
<body>
 
<!--{if $printlogin}-->
<!--{include file="printlogin.tpl"}-->
<!--{/if}-->

<div class="outer">
 
<div class="titlebanner">
</div>
 
<div class="topbanner"></div>
 
<div class="menu1">
<ul>
<li><a href="http://www.nybro.dk/" title="Back to homepage">Back to homepage</a></li>
</ul>
</div>
 
<div class="menu2">

<h1>My account</h1>
<ul>
<!--{if $logged_in}-->
	<li class="page_item"><a href="logout.php">Log out</a></li>
	<li class="page_item"><a href="user-account.php">Edit account</a></li>
	<li class="page_item"><a href="user-groupmemberships.php">Groups and mailing lists</a></li>
<!--{else}-->	
	<li class="page_item"><a href="login.php">Log in</a></li>
<!--{/if}-->
</ul>

<!--{if $adminmenu}-->
<h1>Administration</h1>
<ul>
	<!--{foreach from=$adminmenu item=menuitem}-->
	<li class="page_item"><a href="<!--{$menuitem.href}-->" title="<!--{$menuitem.title}-->"><!--{$menuitem.title}--></a></li>
	<!--{/foreach}-->
</ul>
<!--{/if}-->

</div>

<div class="content narrowcontentarea">
<!--{include file="$contenttemplate"}-->
</div>

<div style="clear: both; height: 10px;"></div>
 
<div class="footer">&nbsp;</div>
 
</div>
 
</body>

</html>
