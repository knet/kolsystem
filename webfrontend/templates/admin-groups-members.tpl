<script type="text/javascript">
function remove_member(member) {
	$("input#removemember").attr('value', member);
	$("form#groupmembersform").submit();

}
</script>
<h1>Group administration: <!--{$gruppe.gruppenavn}--></h1>
<!--{include file="show_messages.tpl"}-->
<form method="post" id="groupmembersform">

<h2>Add member</h2>
<input type="text" class="brugersoeg" name="addmember" /><br/>
<button type="submit">Add and save</button>

<input type="hidden" name="action" value="save"/>
<input type="hidden" name="removemember" id="removemember" value=""/>
<input type="hidden" name="removemember_ekstern" id="removemember_ekstern" value=""/>
<table style="width: 100%;">
<tr>
	<td colspan="<!--{if $gruppe.mail_skriverettigheder=='udvalgte_medlemmer'}-->6<!--{else}-->5<!--{/if}-->"><h2>Members</h2>
</tr>
<tr>
	<th>Room</th>
	<th>Name</th>
	<th>Email</th>
	<th>Recieve mails</th>
	<!--{if $gruppe.mail_skriverettigheder=='udvalgte_medlemmer'}--><th>Write access</th><!--{/if}-->
	<th>Remove member</th>
</tr>

<!--{foreach from=$gruppemedlemmer item=medlem}-->
<tr>
	<td><!--{$medlem.vaerelse}--></td>
	<td><!--{$medlem.navn}--></td>
	<td><!--{$medlem.email}--></td>
	<td><input type="checkbox" name="mail_modtag[<!--{$medlem.brugernavn}-->]" value="1" <!--{if $medlem.mail_modtag}-->checked<!--{/if}-->/></td>
	<!--{if $gruppe.mail_skriverettigheder=='udvalgte_medlemmer'}--><td><input type="checkbox" name="mail_forfatter[<!--{$medlem.brugernavn}-->]" value="1" <!--{if $medlem.mail_forfatter}-->checked<!--{/if}-->/></td><!--{/if}-->
	<td><a onclick="remove_member('<!--{$medlem.brugernavn}-->');" href="#">Remove</a></td>
	
</tr>
<!--{/foreach}-->

<!--{if $gruppemedlemmer_eksterne}-->
<tr>
	<td colspan="<!--{if $gruppe.mail_skriverettigheder=='udvalgte_medlemmer'}-->6<!--{else}-->5<!--{/if}-->"><h2>External members</h2>
</tr>
<tr>
	<th colspan="2">Name / description</th>
	<th>Email</th>
	<th>Recieve mails</th>
	<!--{if $gruppe.mail_skriverettigheder=='udvalgte_medlemmer'}--><th>Write access</th><!--{/if}-->
	<th></th>
</tr>

<!--{foreach from=$gruppemedlemmer_eksterne item=medlem}-->
<tr>
	<td colspan="2"><!--{$medlem.beskrivelse}--></td>
	<td><!--{$medlem.email}--></td>
	<td><!--{if $medlem.mail_modtag==1}-->Yes<!--{else}-->No<!--{/if}--></td>
	<!--{if $gruppe.mail_skriverettigheder=='udvalgte_medlemmer'}--><td><!--{if $medlem.mail_forfatter==1}-->Yes<!--{else}-->No<!--{/if}--></td><!--{/if}-->
	<td></td>
</tr>
<!--{/foreach}-->
<!--{/if}-->

</table>
<br/><br/>
<button type="submit">Save</button>
</form>