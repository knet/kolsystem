<h1>Login</h1>

<!--{if $error}-->
<ul class="messages errors">
<li><!--{$error}--></li>
</ul>
<!--{/if}-->

<form method="post">
	
<table>
<tr>
	<td>Username:</td>
	<td><input type="text" name="brugernavn" value="<!--{$input.brugernavn}-->"/></td>
</tr>
<tr>
	<td>Password:</td>
	<td><input type="password" name="password" value="<!--{$input.password}-->"/></td>
</tr>
<tr>
	<td></td>
	<td><button type="submit">Login</button></td>
</tr>
</table>
</form>
