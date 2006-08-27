{* $Header: /cvsroot/bitweaver/_bit_shoutbox/templates/shoutbox.tpl,v 1.9 2006/08/27 06:32:18 jht001 Exp $ *}
{strip}

<div class="display shoutbox">
	<div class="header">
		<h1>{tr}Shoutbox{/tr}</h1>
	</div>

	{if $feedback}{formfeedback hash=$feedback}{/if}
	<div class="body">
		{jstabs}
			{if $gBitUser->hasPermission( 'p_shoutbox_admin' )}
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
			{/if}

			{if $gBitUser->hasPermission( 'p_shoutbox_admin' )}
				{jstab title="Shoutbox Settings"}
					{form legend="Shoutbox Settings"}
						<input type="hidden" name="tab" value="settings" />
						<div class="row">
							{formlabel label="Auto-link URLs" for="shoutbox_autolink"}
							{forminput}
								{html_radios name="shoutbox_autolink" values="m" checked=$shoutbox_autolink labels=false id="shoutbox_autolink"}{tr}URLs for this server only{/tr}<br/>
								{html_radios name="shoutbox_autolink" values="y" checked=$shoutbox_autolink labels=false id="shoutbox_autolink"}{tr}URLs for any server on the internet{/tr}<br/>
								{html_radios name="shoutbox_autolink" values="" checked=$shoutbox_autolink labels=false id="shoutbox_autolink"}{tr}None{/tr}<br/>
								{formhelp note="This will convert any posted URL into an easily readable and clickable link"}
							{/forminput}
						</div>
						<div class="row">
							{formlabel label="Email Settings" for="shoutbox_autolink"}
							{forminput}
								{html_checkboxes name="shoutbox_email_notice" values="y" checked=$gBitSystem->getConfig('shoutbox_email_notice') labels=false id="shoutbox_autolink"}{tr}Auto-email Shouts{/tr}<br/>
								{formhelp note="This will privately email any new shoutbox posts to the user being shouted."}
								{formhelp page="Shoutbox"}
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
					{if $gBitUser->hasPermission('p_shoutbox_admin')}
						<strong>{$channels[user].shout_ip}</strong>
					{/if}
					{tr}To{/tr}: {displayname user_id=`$channels[user].to_user_id`} {tr}From{/tr}: {displayname hash=`$channels[user]`}, {$channels[user].shout_time|bit_long_datetime}
					{if $channels[user].is_editable}
						&nbsp;&nbsp;{smartlink ititle="Edit" ibiticon="liberty/edit_small" offset=$offset shout_id=$channels[user].shout_id to_user_id=$toUserId}
					{/if}
					{if $channels[user].is_deletable}
						&nbsp;{smartlink ititle="Remove" ibiticon="liberty/delete_small" offset=$offset remove=$channels[user].shout_id to_user_id=$toUserId}
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
