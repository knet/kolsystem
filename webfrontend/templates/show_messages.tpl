<!--{ if $messages }-->
<ul class="messages">
<!--{foreach from=$messages item=message}-->
<li><!--{$message}--></li>
<!--{/foreach}-->
</ul>
<!--{/if}-->

<!--{ if $errors }-->
<span style="color: red"><strong>Errors</strong></span>
<ul class="messages errors">
<!--{foreach from=$errors item=error}-->
<li><!--{$error}--></li>
<!--{/foreach}-->
</ul>
<!--{/if}-->