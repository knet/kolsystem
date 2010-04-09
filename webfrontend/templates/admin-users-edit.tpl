<h1>User administration - Edit user: <!--{$brugerdata.brugernavn}--></h1>
<!--{include file="show_messages.tpl"}-->

<form method="post" class="edituser">
<input type="hidden" name="username" value="<!--{$brugerdata.brugernavn}-->">
<table class="formtable">
	
	<tr>
		<td colspan="2"><h3>Account</h3></td>
	</tr>
	<tr>
		<td colspan="2">
		<!--{if $brugerdata.net_tilmeldt_dato}-->
		<p><span style="color: green;"><b>This user is already subscribed to the Internet.</b></span></p>
		<input type="radio" name="action" value="nothing" <!--{if $formdata.action=='nothing' || $formdata.action==''}-->checked<!--{/if}-->> Just update user info<br/>
		<input type="radio" name="action" value="resetpassword" <!--{if $formdata.action=='resetpassword'}-->checked<!--{/if}-->> Reset the password and print new login paper<br/>
		<input type="radio" name="action" value="netsignoff" <!--{if $formdata.action=='netsignoff'}-->checked<!--{/if}-->> Sign off (remove internet account!)<br/>
		<!--{else}-->
		<p><span style="color: red;"><b>This user is not subscribed to the Internet yet.</b></span></p>
		<input type="radio" name="action" value="nothing" <!--{if $formdata.action=='nothing'}-->checked<!--{/if}-->> Just update user info (don't sign up)<br/>
		<input type="radio" name="action" value="netsignup" <!--{if $formdata.action=='netsignup' || $formdata.action==''}-->checked<!--{/if}-->> Sign up, reset password and print login paper<br/>
		<!--{/if}-->
		</td>
	</tr>
		
	<tr>
		<td colspan="2"><h3>User info</h3></td>
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
		<td colspan="2"><h3>Privacy</h3></td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" name="skjult_navn" value="1" <!--{if $formdata.skjult_navn}-->checked<!--{/if}-->/> Hide my name on the resident list</td>
	</tr>
	<tr>
		<td colspan="2"><input type="checkbox" name="skjult_email" value="1" <!--{if $formdata.skjult_email}-->checked<!--{/if}-->/> Hide my email on the resident list from the domentorys network</td>
	</tr>

	<tr>
		<td colspan="2" style="padding-top: 25px;"><button type="submit">Send</button></td>
	</tr>

</table>
</form>