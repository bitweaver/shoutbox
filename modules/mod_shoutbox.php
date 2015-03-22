<?php
/**
 * $Header$
 * @package shoutbox
 * @subpackage functions
 */
global $gQueryUser;

/**
 * required setup
 */
include_once( SHOUTBOX_PKG_PATH.'Shoutbox.php' );
$gShout = new Shoutbox();

$gBitUser->hasPermission( 'p_shoutbox_view' );
if( $gQueryUser && $gQueryUser->isRegistered() ) {
	$shoutUserId = $gQueryUser->mUserId;
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable( $gQueryUser->getDisplayName());
} else {
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable( tra( 'Shoutbox' ));
	$shoutUserId = ROOT_USER_ID;
}

$_template->tpl_vars['toUserId'] = new Smarty_variable( $shoutUserId );
$shoutFeedback= NULL;

if( $gBitSystem->isPackageActive( 'shoutbox' ) && $gBitUser->hasPermission( 'p_shoutbox_view' ) ) {
	$parsedUrl = parse_url( $_SERVER["REQUEST_URI"] );

	if( isset( $parsedUrl["query"] ) ) {
		parse_str( $parsedUrl["query"], $sht_query );
	}

	$shout_father = $parsedUrl["path"];

	// recreate url parameters and append ? or &amp; that we can add parameters in the tpl
	if( !empty( $sht_query ) ) {
		$sht_first = 1;
		foreach( $sht_query as $sht_name => $sht_val ) {
			# We don't want to copy some values into father url
			# It would be good to have a more general solution to decide which parameters
			# should be copied into the father url
			if ($sht_name == 'shout_remove') {
				continue;
				}
			$shout_father .= ( ( $sht_first++ == 1 ) ? "?" : "&amp;" )."$sht_name=$sht_val";
		}
		$shout_father .= '&amp;';
	} else {
		$shout_father .= '?';
	}

	global $gBitSmarty;
	$_template->tpl_vars['shout_ownurl'] = new Smarty_variable( $shout_father );
	if( isset( $_REQUEST["shout_remove"] ) ) {
		if( $gShout->expunge( $_REQUEST["shout_remove"] ) ) {
			$shoutFeedback['success'] = tra( "Message removed" );
		} else {
			$shoutFeedback['error'] = $gShout->mErrors['expunge'];
		}
	}

	if( $gBitUser->hasPermission( 'p_shoutbox_post' ) ) {
		if( isset( $_REQUEST["shout_send"] ) ) {
			if( $gShout->store( $_REQUEST ) ) {
				$shoutFeedback['success'] = tra( "Message posted" );
			} else {
				$shoutFeedback['error'] = $gShout->mErrors['store'];
			}
		}
	}
	
	// moduleParams contains lots of goodies: extract for easier handling
	extract( $moduleParams );

	$getList = array(
		'max_records' => $module_rows,
		'sort_mode' => 'shout_time_desc',
		'to_user_id' => $shoutUserId
	);
	$shout_msgs = $gShout->getList( $getList );
	$_template->tpl_vars['shout_msgs'] = new Smarty_variable( $shout_msgs );
	$_template->tpl_vars['shoutFeedback'] = new Smarty_variable( $shoutFeedback );
}

?>
