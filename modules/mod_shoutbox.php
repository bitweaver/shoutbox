<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_shoutbox/modules/mod_shoutbox.php,v 1.6 2007/01/01 10:00:33 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_shoutbox.php,v 1.6 2007/01/01 10:00:33 squareing Exp $
 * @package shoutbox
 * @subpackage functions
 */
global $shoutboxlib, $gQueryUser;

/**
 * required setup
 */
include_once( SHOUTBOX_PKG_PATH.'shoutbox_lib.php' );
$gBitUser->hasPermission( 'p_shoutbox_view' );
if( $gQueryUser && $gQueryUser->isRegistered() ) {
	$shoutUserId = $gQueryUser->mUserId;
	$gBitSmarty->assign( 'moduleTitle', $gQueryUser->getDisplayName().'\'s '.tra( 'shoutbox' ) );
} else {
	$gBitSmarty->assign( 'moduleTitle', tra( 'Shoutbox' ) );
	$shoutUserId = ROOT_USER_ID;
}

$gBitSmarty->assign( 'toUserId', $shoutUserId );
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
			$shout_father .= ( ( $sht_first++ == 1 ) ? "?" : "&amp;" )."$sht_name=$sht_val";
		}
		$shout_father .= '&amp;';
	} else {
		$shout_father .= '?';
	}

	global $gBitSmarty;
	$gBitSmarty->assign( 'shout_ownurl', $shout_father );
	if( isset( $_REQUEST["shout_remove"] ) ) {
		if( $shoutboxlib->expunge( $_REQUEST["shout_remove"] ) ) {
			$shoutFeedback['success'] = tra( "Message removed" );
		} else {
			$shoutFeedback['error'] = $shoutboxlib->mErrors['expunge'];
		}
	}

	if( $gBitUser->hasPermission( 'p_shoutbox_post' ) ) {
		if( isset( $_REQUEST["shout_send"] ) ) {
			if( $shoutboxlib->store( $_REQUEST ) ) {
				$shoutFeedback['success'] = tra( "Message posted" );
			} else {
				$shoutFeedback['error'] = $shoutboxlib->mErrors['store'];
			}
		}
	}

	$getList = array(
		'max_records' => $module_rows,
		'sort_mode' => 'shout_time_desc',
		'to_user_id' => $shoutUserId
	);
	$shout_msgs = $shoutboxlib->getList( $getList );
	$gBitSmarty->assign( 'shout_msgs', $shout_msgs["data"] );
	$gBitSmarty->assign( 'shoutFeedback', $shoutFeedback );
}

?>
