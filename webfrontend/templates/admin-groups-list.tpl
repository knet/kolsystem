<h1>Group administration - Choose group</h1>
<!--{include file="show_messages.tpl"}-->

<table class="listing">
<tr>
	<th>Group name</th>
	<th>Description</th>
	<th>Mailing list</th>
</tr>

<!--{foreach from=$grupper item=gruppe}-->
<tr>
	<td><a href="./admin-groups.php?groupname=<!--{$gruppe.gruppenavn}-->"><!--{$gruppe.gruppenavn}--></a></td>
	<td><!--{$gruppe.beskrivelse}--></td>
	<td><!--{$gruppe.mail_liste_navn}--><!--{if $gruppe.mail_liste_navn}-->@nybro.dk<!--{/if}--></td>
</tr>
<!--{/foreach}-->
</table>
