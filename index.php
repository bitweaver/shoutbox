<?php

// $Header: /cvsroot/bitweaver/_bit_shoutbox/index.php,v 1.1.1.1.2.3 2005/09/24 18:28:54 spiderr Exp $

// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once( '../bit_setup_inc.php' );

include_once( SHOUTBOX_PKG_PATH.'shoutbox_lib.php' );

$gBitSystem->verifyPackage( 'shoutbox' );
$gBitSystem->verifyPermission( 'bit_p_view_shoutbox' );

$feedback = NULL;
$info = NULL;

// Permissioning is handled in the class, where it should be...
if (isset($_REQUEST["remove"])) {
	if( $shoutboxlib->expunge($_REQUEST["remove"]) ) {
		$feedback['success'] = tra( "Message removed" );
	} else {
		$feedback['error'] = $shoutboxlib->mErrors['expunge'];
	}
} elseif (isset($_REQUEST["shoutbox_admin"])) {
	$shoutbox_autolink = (isset($_REQUEST["shoutbox_autolink"])) ? $_REQUEST["shoutbox_autolink"] : NULL;
	$gBitSystem->storePreference('shoutbox_autolink',$shoutbox_autolink);
	$gBitSystem->storePreference( 'shoutbox_email_notice', (isset($_REQUEST["shoutbox_email_notice"][0])) ? $_REQUEST["shoutbox_email_notice"][0] : NULL );
	$gBitSmarty->assign('shoutbox_autolink',$shoutbox_autolink);
}

if (isset($_REQUEST["save"]) && ($gBitUser->hasPermission( 'bit_p_post_shoutbox' ))) {
	if( $shoutboxlib->store( $_REQUEST ) ) {
		$feedback['success'] = tra( "Message saved" );
		// reload the message
	} else {
		$feedback['error'] = $shoutboxlib->mErrors['store'];
	}
}

if( !empty( $_REQUEST["shout_id"] ) ) {
	$info = $shoutboxlib->get_shoutbox($_REQUEST["shout_id"]);
	if( !$shoutboxlib->verify( $_REQUEST ) ) {
		$feedback['error'] = $shoutboxlib->mErrors['store'];
	}
	$gBitSmarty->assign( "shout_id", $_REQUEST["shout_id"] );
}

//$listHash = array( 'offset' => $offset, 'max_records' => $maxRecords, 'sort_mode' => $sort_mode, 'find' => $find );
$channels = $shoutboxlib->getList( $_REQUEST );
$gBitSmarty->assign_by_ref('offset', $_REQUEST['offset']);
$gBitSmarty->assign('find', $_REQUEST['find']);

$cant_pages = ceil($channels["cant"] / $maxRecords);
$gBitSmarty->assign_by_ref('cant_pages', $cant_pages);
$gBitSmarty->assign('actual_page', 1 + ( $_REQUEST['offset'] / $_REQUEST['max_records']));

if($channels["cant"] > ( $_REQUEST['offset'] + $maxRecords)) {
	$gBitSmarty->assign('next_offset',  $_REQUEST['offset'] + $_REQUEST['max_records']);
} else {
	$gBitSmarty->assign('next_offset', -1);
}

// If offset is > 0 then prev_offset
if( !empty( $_REQUEST['offset'] ) ) {
	$gBitSmarty->assign('prev_offset',  $_REQUEST['offset'] - $_REQUEST['max_records']);
} else {
	$gBitSmarty->assign('prev_offset', -1);
}

if( !empty( $_REQUEST['to_user_id'] ) ) {
	$gBitSmarty->assign('toUserId', $_REQUEST['to_user_id'] );
}

$gBitSmarty->assign_by_ref('channels', $channels["data"]);

$gBitSmarty->assign_by_ref( 'shout', $info );
/*
if(isset($_REQUEST['page'])) {
	$page = &$_REQUEST['page'];
	$offset = ($page - 1) * $_REQUEST['max_records'];
}
*/

$gBitSmarty->assign('feedback', $feedback);
// Display the template
$gBitSystem->display( 'bitpackage:shoutbox/shoutbox.tpl', tra('Shoutbox') );

?>
