{* $Header$ *}
{strip}

{capture name=shoutform}
	{form legend="Post or edit a message"}
		<input type="hidden" name="shout_id" value="{$shout.shout_id}" />

		{if $shout.to_user_id and $shout.to_user_id ne 1}
			<div class="control-group">
				{formlabel label="To"}
				{forminput}
					{displayname user_id=$shout.to_user_id}
				{/forminput}
			</div>
		{/if}

		<div class="control-group">
			{formlabel label="Message" for="message"}
			{forminput}
				<textarea rows="4" cols="60" name="shout_message" id="message">{$shout.shout_message|escape:html}</textarea>
			{/forminput}
		</div>

		<div class="control-group submit">
			<input type="submit" class="btn" name="save" value="{tr}Post{/tr}" />
			{if $shout_id}&nbsp;{smartlink ititle="Post new message"}{/if}
		</div>
	{/form}
{/capture}

<div class="display shoutbox">
	<div class="header">
		<h1>{tr}Shoutbox{/tr}</h1>
	</div>

	<div class="body">
		{formfeedback hash=$feedback}
		{if $gBitUser->hasPermission( 'p_shoutbox_admin' )}
			{jstabs}
				{jstab title="Post or edit a message"}
					{$smarty.capture.shoutform}
				{/jstab}

				{jstab title="Shoutbox Settings"}
					{form legend="Shoutbox Settings"}
						<input type="hidden" name="tab" value="settings" />
						<div class="control-group">
							{formlabel label="Auto-link URLs" for="shoutbox_autolink"}
							{forminput}
								<label><input type="radio" name="shoutbox_autolink" value="m" {if $gBitSystem->getConfig('shoutbox_autolink') == 'm'}checked="checked"{/if} /> {tr}URLs for this server only{/tr}</label><br />
								<label><input type="radio" name="shoutbox_autolink" value="y" {if $gBitSystem->getConfig('shoutbox_autolink') == 'y'}checked="checked"{/if} /> {tr}URLs for any server on the internet{/tr}</label><br />
								<label><input type="radio" name="shoutbox_autolink" value=""  {if !$gBitSystem->isFeatureActive('shoutbox_autolink')}checked="checked"{/if} /> {tr}None{/tr}</label><br />
								{formhelp note="This will convert any posted URL into an easily readable and clickable link"}
							{/forminput}
						</div>

						{if $gBitSystem->isPackageActive( 'smileys' ) && $gLibertySystem->isPluginActive( 'filtersmileys' )}
							<div class="control-group">
								{formlabel label="Enable Smileys" for="shoutbox_smileys"}
								{forminput}
									<input type="checkbox" name="shoutbox_smileys" id="shoutbox_smileys" value="y" {if $gBitSystem->isFeatureActive('shoutbox_smileys')}checked="checked"{/if} />
									{formhelp note="When a user inserts things like: <strong>;-)</strong> or <strong>:-)</strong> they will be replaced with appropriate smiley images."}
								{/forminput}
							</div>
						{/if}

						<div class="control-group">
							{formlabel label="Auto-email Shouts" for="shoutbox_email_notice"}
							{forminput}
								<input type="checkbox" name="shoutbox_email_notice" id="shoutbox_email_notice" value="y" {if $gBitSystem->isFeatureActive('shoutbox_email_notice')}checked="checked"{/if} />
								{formhelp note="This will privately email any new shoutbox posts to the user being shouted." page=Shoutbox}
							{/forminput}
						</div>

						<div class="control-group submit">
							<input name="shoutbox_admin" type="submit" value="{tr}Submit{/tr}" />
						</div>
					{/form}
				{/jstab}
			{/jstabs}
		{elseif $gBitUser->hasPermission('p_shoutbox_post')}
			{$smarty.capture.shoutform}
		{/if}

		{minifind}

		<ul class="data">
			{section name=user loop=$channels}
				<li class="{cycle values="odd,even"} item">
					<div class="floaticon">
						{if $channels[user].is_editable}
							&nbsp;&nbsp;{smartlink ititle="Edit" booticon="icon-edit" offset=$offset shout_id=$channels[user].shout_id to_user_id=$smarty.request.to_user_id}
						{/if}
						{if $channels[user].is_deletable}
							&nbsp;{smartlink ititle="Remove" booticon="icon-trash" offset=$offset remove=$channels[user].shout_id to_user_id=$smarty.request.to_user_id}
						{/if}
					</div>
					{if $gBitUser->hasPermission('p_shoutbox_admin')}
						<strong>{$channels[user].shout_ip}</strong> &bull;&nbsp;
					{/if}
					{if $channels[user].to_user_id != 1}{tr}To{/tr}: {displayname user_id=$channels[user].to_user_id} {/if}{tr}From{/tr}: {displayname hash=$channels[user]}, <small>{$channels[user].shout_time|bit_long_datetime}</small>
					<br />
					{$channels[user].shout_message}
				</li>
			{/section}
		</ul>

		{pagination to_user_id=$smarty.request.to_user_id}
	</div><!-- end .body -->
</div><!-- end .shoutbox -->

{/strip}
