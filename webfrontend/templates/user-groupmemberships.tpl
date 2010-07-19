<h1>My groups</h1>
<!--{include file="show_messages.tpl"}-->

<form method="post">
<input type="hidden" name="action" value="save"/>

<table class="listing">
<tr>
	<th>Description</th>
	<th>Mailing list</th>
	<th>Recieve mails</th>
</tr>

<!--{foreach from=$grupper item=gruppe}-->
<tr>
	<td><!--{$gruppe.beskrivelse}--></td>
	<td><!--{$gruppe.mail_liste_navn}--><!--{if $gruppe.mail_liste_navn}-->@<!--{$dorm_domain}--><!--{/if}--></td>
	<td><input type="checkbox" name="modtag_mail[<!--{$gruppe.gruppenavn}-->]" value="1" <!--{if $gruppe.mail_modtag}-->checked<!--{/if}--> <!--{if $gruppe.mail_obligatorisk}-->disabled<!--{/if}-->></td>
</tr>
<!--{/foreach}-->
</table>
<button type="submit">Save</button>
</form>