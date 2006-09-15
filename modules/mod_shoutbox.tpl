{* $Header: /cvsroot/bitweaver/_bit_shoutbox/modules/mod_shoutbox.tpl,v 1.9 2006/09/15 21:16:56 spiderr Exp $ *}

{strip}

{if $gBitSystem->isPackageActive( 'shoutbox' ) and $gBitUser->hasPermission( 'p_shoutbox_view' )}
	{bitmodule title="$moduleTitle" name="shoutbox"}
		{if $shoutFeedback}{formfeedback hash=$shoutFeedback}{/if}

		{if $gBitUser->hasPermission( 'p_shoutbox_post' )}
			<form action="{$shout_ownurl}" method="post">
				<div style="text-align:center">
					<textarea rows="3" cols="20" name="shout_message"></textarea><br />
					{captcha width=100 height=24}
					<input type="hidden" name="to_user_id" value="{$toUserId}" />
					<input type="submit" name="shout_send" value="{tr}send{/tr}" />
				</div>
			</form>
		{/if}

		<ul>
			{section loop=$shout_msgs name=ix}
				<li class="{cycle values="even,odd"}">
					{displayname hash=$shout_msgs[ix]}, {$shout_msgs[ix].shout_time|bit_short_datetime}: {$shout_msgs[ix].shout_message}
					{if $shout_msgs[ix].is_editable}
						&nbsp;<a href="{$smarty.const.SHOUTBOX_PKG_URL}index.php?shout_id={$shout_msgs[ix].shout_id}">{biticon ipackage="icons" iname="accessories-text-editor" iexplain="edit"}</a>
					{/if}

					{if $shout_msgs[ix].is_deletable}
						&nbsp;<a href="{$shout_ownurl}shout_remove={$shout_msgs[ix].shout_id}">{biticon ipackage="icons" iname="edit-delete" iexplain="remove"}</a>
					{/if}
				</li>
			{/section}
		</ul>

		{if $shout_msgs}
			<div style="text-align: center">
				<a href="{$smarty.const.SHOUTBOX_PKG_URL}index.php?to_user_id={$toUserId}">{tr}Read More{/tr}&hellip;</a>
			</div>
		{/if}
	{/bitmodule}
{/if}

{/strip}
