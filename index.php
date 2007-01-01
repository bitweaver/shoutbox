<?php

// $Header: /cvsroot/bitweaver/_bit_shoutbox/index.php,v 1.8 2007/01/01 10:45:12 squareing Exp $

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once( '../bit_setup_inc.php' );

include_once( SHOUTBOX_PKG_PATH.'shoutbox_lib.php' );

$gBitSystem->verifyPackage( 'shoutbox' );
$gBitSystem->verifyPermission( 'p_shoutbox_view' );

$feedback = NULL;
$edit = NULL;

// Permissioning is handled in the class, where it should be...
if( isset( $_REQUEST["remove"] )) {
	if( $shoutboxlib->expunge( $_REQUEST["remove"] )) {
		$feedback['success'] = tra( "Message removed" );
	} else {
		$feedback['error'] = $shoutboxlib->mErrors['expunge'];
	}
} elseif( isset( $_REQUEST["shoutbox_admin"] )) {
	$shoutbox_autolink = ( isset( $_REQUEST["shoutbox_autolink"] )) ? $_REQUEST["shoutbox_autolink"] : NULL;
	$gBitSystem->storeConfig( 'shoutbox_autolink', $shoutbox_autolink, SHOUTBOX_PKG_NAME );
	$gBitSystem->storeConfig( 'shoutbox_email_notice', (isset($_REQUEST["shoutbox_email_notice"][0])) ? $_REQUEST["shoutbox_email_notice"][0] : NULL, SHOUTBOX_PKG_NAME );
	$gBitSmarty->assign('shoutbox_autolink',$shoutbox_autolink);
}

if( !empty( $_REQUEST["save"] ) && $gBitUser->hasPermission( 'p_shoutbox_post' ) ) {
	if( $shoutboxlib->store( $_REQUEST ) ) {
		$feedback['success'] = tra( "Message saved" );
	} else {
		$feedback['error'] = $shoutboxlib->mErrors;
	}
}

if( !empty( $_REQUEST["shout_id"] ) ) {
	$edit = $shoutboxlib->get_shoutbox($_REQUEST["shout_id"]);
	$gBitSmarty->assign( "shout_id", $_REQUEST["shout_id"] );
}

$listHash = $_REQUEST;
$channels = $shoutboxlib->getList( $listHash );
$gBitSmarty->assign( 'channels', $channels );
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );
$gBitSmarty->assign( 'shout', $edit );
$gBitSmarty->assign( 'feedback', $feedback);
// Display the template
$gBitSystem->display( 'bitpackage:shoutbox/shoutbox.tpl', tra('Shoutbox') );
?>
