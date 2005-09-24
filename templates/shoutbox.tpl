{* $Header: /cvsroot/bitweaver/_bit_shoutbox/templates/shoutbox.tpl,v 1.1.1.1.2.2 2005/09/24 17:55:42 spiderr Exp $ *}
{strip}

<div class="display shoutbox">
	<div class="header">
		<h1>{tr}Shoutbox{/tr}</h1>
	</div>

	{if $feedback}{formfeedback hash=$feedback}{/if}
	<div class="body">
		{jstabs}
			{jstab title="Post or edit a message"}
				{form legend="Post or edit a message"}
					<input type="hidden" name="shout_id" value="{$shout.shout_id}" />

					{if $shout.to_user_id and $shout.to_user_id ne 1}
						<div class="row">
							{formlabel label="To"}
							{forminput}
								{displayname user_id=$shout.to_user_id}
							{/forminput}
						</div>
					{/if}

					<div class="row">
						{formlabel label="Message" for="message"}
						{forminput}
							<textarea rows="4" cols="60" name="shout_message" id="message">{$shout.shout_message|escape:html}</textarea>
						{/forminput}
					</div>

					<div class="row submit">
						<input type="submit" name="save" value="{tr}Post{/tr}" />
						{if $shout_id}&nbsp;{smartlink ititle="Post new message"}{/if}
					</div>
				{/form}
			{/jstab}

			{if $gBitUser->hasPermission( 'bit_p_admin_shoutbox' )}
				{jstab title="Shoutbox Settings"}
					{form legend="Shoutbox Settings"}
						<input type="hidden" name="tab" value="settings" />
						<div class="row">
							{formlabel label="Auto-link URLs" for="shoutbox_autolink"}
							{forminput}
								{formhelp note="This will convert any posted URL into an easily readable and clickable link" page="Shoutbox"}
								{html_radios name="shoutbox_autolink" values="m" checked=$shoutbox_autolink labels=false id="shoutbox_autolink"}{tr}URLs for this server only{/tr}<br/>
								{html_radios name="shoutbox_autolink" values="y" checked=$shoutbox_autolink labels=false id="shoutbox_autolink"}{tr}URLs for any server on the internet{/tr}<br/>
								{html_radios name="shoutbox_autolink" values="" checked=$shoutbox_autolink labels=false id="shoutbox_autolink"}{tr}None{/tr}<br/>
							{/forminput}
						</div>

						<div class="row submit">
							<input name="shoutbox_admin" type="submit" value="{tr}Submit{/tr}" />
						</div>
					{/form}
				{/jstab}
			{/if}
		{/jstabs}

		{minifind}

		<ul class="data">
			{section name=user loop=$channels}
				<li class="{cycle values="odd,even"} item">
					{tr}To{/tr}: {displayname user_id=`$channels[user].to_user_id`} {tr}From{/tr}: {displayname hash=`$channels[user]`} {tr}at{/tr} {$channels[user].shout_time|bit_long_datetime}
					{if $channels[user].is_editable}
						&nbsp;&nbsp;{smartlink ititle="Edit" ibiticon="liberty/edit_small" offset=$offset shout_id=$channels[user].shout_id to_user_id=$toUserId}
					{/if}
					{if $channels[user].is_deletable}
						&nbsp;{smartlink ititle="Remove" ibiticon="liberty/delete_small" offset=$offset shout_id=$channels[user].shout_id to_user_id=$toUserId}
					{/if}
					<br />
					{$channels[user].shout_message}
				</li>
			{/section}
		</ul>

		{pagination}
	</div><!-- end .body -->
</div><!-- end .shoutbox -->

{/strip}
