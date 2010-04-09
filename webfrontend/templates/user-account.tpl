<h1>Edit account</h1>
<!--{include file="show_messages.tpl"}-->

<form method="post">
<input type="hidden" name="action" value="save">
	
<table>
	<tr>
		<td colspan="2"><h3>User account</h3></td>
	</tr>
	<tr>
		<td>Username:</td>
		<td><!--{$brugerdata.brugernavn}--></td>
	</tr>
	<tr>
		<td>Name:</td>
		<td><!--{$brugerdata.navn}--></td>
	</tr>
	<tr>
		<td>Room:</td>
		<td><!--{$brugerdata.vaerelse}--></td>
	</tr>
	<tr>
		<td>Email:</td>
		<td><input type="text" name="email" class="wide" value="<!--{$formdata.email}-->"/></td>
	</tr>
	<tr>
		<td>Mobile telephone number:</td>
		<td><input type="text" name="mobilnummer" value="<!--{$formdata.mobilnummer}-->"/></td>
	</tr>
	<tr>
		<td>Homepage:</td>
		<td><input type="text" name="hjemmeside" class="wide" value="<!--{$formdata.hjemmeside}-->"/></td>
	</tr>
	<tr>
		<td colspan="2"><h3>Change password</h3><p>Only fill this fields, if you want to change your password</td>
	</tr>
	<tr>
		<td>New password:</td>
		<td><input type="password" name="newpassword" value=""/></td>
	</tr>
	<tr>
		<td>Repeat new password:</td>
		<td><input type="password" name="newpassword2" value=""/></td>
	</tr>
	
	<tr>
		<td colspan="2"><h3>Privacy</h3></td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" name="skjult_navn" value="1" <!--{if $formdata.skjult_navn}-->checked<!--{/if}-->/> Hide my name on the resident list</td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" name="skjult_email" value="1" <!--{if $formdata.skjult_email}-->checked<!--{/if}-->/> Hide my email on the resident list from the dormitory network</td>
	</tr>
	<tr>
		<td colspan="2" style="padding-top: 25px;"><button type="submit">Save</button></td>
	</tr>

</table>
</form>