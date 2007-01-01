{tr}A new shoutbox message has been posted for you at:{/tr} <br>
<a href="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}">http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}</a><br />
<br />
{tr}From{/tr}: {if $fromUser->mInfo.real_name}{$fromUser->mInfo.real_name} [aka {/if}{$fromUser->mInfo.login}{if $fromUser->mInfo.real_name}]{/if}<br />
{tr}Message{/tr}:<br />
<p>{$sendShoutMessage}</p>
