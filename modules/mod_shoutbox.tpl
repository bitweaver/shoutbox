{* $Header: /cvsroot/bitweaver/_bit_shoutbox/modules/mod_shoutbox.tpl,v 1.2 2005/08/07 17:46:42 squareing Exp $ *}

{strip}

{if $gBitSystem->isPackageActive( 'shoutbox' ) and $gBitUser->hasPermission( 'bit_p_view_shoutbox' )}
	{bitmodule title="$moduleTitle" name="shoutbox"}
		{if $feedback}{formfeedback hash=$feedback}{/if}

		{if $gBitUser->hasPermission( 'bit_p_post_shoutbox' )}
			<form action="{$shout_ownurl}" method="post">
				<div style="text-align:center">
					<textarea rows="3" cols="20" name="shout_message"></textarea>
					<br />
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
						&nbsp;<a href="{$smarty.const.SHOUTBOX_PKG_URL}index.php?shout_id={$shout_msgs[ix].shout_id}">{biticon ipackage=liberty iname="edit_small" iexplain="remove"}</a>
					{/if}

					{if $shout_msgs[ix].is_deletable}
						&nbsp;<a href="{$shout_ownurl}shout_remove={$shout_msgs[ix].shout_id}">{biticon ipackage=liberty iname="delete_small" iexplain="remove"}</a>
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
