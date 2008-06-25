<?php

// $Header: /cvsroot/bitweaver/_bit_shoutbox/index.php,v 1.11 2008/06/25 22:21:23 spiderr Exp $

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once( '../bit_setup_inc.php' );

include_once( SHOUTBOX_PKG_PATH.'Shoutbox.php' );
$gShout = new Shoutbox();

$gBitSystem->verifyPackage( 'shoutbox' );
$gBitSystem->verifyPermission( 'p_shoutbox_view' );

$feedback = NULL;
$edit = NULL;

// Permissioning is handled in the class, where it should be...
if( isset( $_REQUEST["remove"] )) {
	if( $gShout->expunge( $_REQUEST["remove"] )) {
		$feedback['success'] = tra( "Message removed" );
	} else {
		$feedback['error'] = $gShout->mErrors['expunge'];
	}
} elseif( isset( $_REQUEST["shoutbox_admin"] ) && $gBitUser->isAdmin() ) {
	$gBitSystem->storeConfig( 'shoutbox_autolink', ( isset( $_REQUEST["shoutbox_autolink"] )) ? $_REQUEST["shoutbox_autolink"] : NULL, SHOUTBOX_PKG_NAME );
	$gBitSystem->storeConfig( 'shoutbox_email_notice', ( isset( $_REQUEST["shoutbox_email_notice"] )) ? 'y' : NULL, SHOUTBOX_PKG_NAME );
	$gBitSystem->storeConfig( 'shoutbox_smileys', ( isset( $_REQUEST["shoutbox_smileys"] )) ? 'y' : NULL, SHOUTBOX_PKG_NAME );
	// to be on the safe side, we will simply nuke the entire shoutbox cache
	$gShout->mCache->expungeCache();
}

if( !empty( $_REQUEST["save"] ) && $gBitUser->hasPermission( 'p_shoutbox_post' ) ) {
	if( $gShout->store( $_REQUEST ) ) {
		$feedback['success'] = tra( "Message saved" );
	} else {
		$feedback['error'] = $gShout->mErrors;
	}
}

if( !empty( $_REQUEST["shout_id"] ) ) {
	$edit = $gShout->getShout($_REQUEST["shout_id"]);
	$gBitSmarty->assign( "shout_id", $_REQUEST["shout_id"] );
}

$listHash = $_REQUEST;
$channels = $gShout->getList( $listHash );
$gBitSmarty->assign( 'channels', $channels );
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );
$gBitSmarty->assign( 'shout', $edit );
$gBitSmarty->assign( 'feedback', $feedback);
// Display the template
$gBitSystem->display( 'bitpackage:shoutbox/shoutbox.tpl', tra('Shoutbox') , array( 'display_mode' => 'display' ));
?>
