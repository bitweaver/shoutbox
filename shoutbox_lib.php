<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_shoutbox/Attic/shoutbox_lib.php,v 1.1.1.1.2.7 2005/09/24 18:28:54 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: shoutbox_lib.php,v 1.1.1.1.2.7 2005/09/24 18:28:54 spiderr Exp $
 * @package shoutbox
 */

/**
 * @package shoutbox
 * @subpackage ShoutBoxLib
 */
class ShoutboxLib extends BitBase {
	function ShoutboxLib() {
		BitBase::BitBase();
	}

	// $offset, $maxRecords, $sort_mode, $find
	function getList( &$pListHash ) {
		global $gBitUser, $gBitSystem;
		if ( empty( $_REQUEST["sort_mode"] ) ) {
			$pListHash['sort_mode'] = 'shout_time_desc';
		}
		$this->prepGetList( $pListHash );
		$bindvars = array();
		$mid = '';
		if( !empty( $pListHash['find'] ) ) {
			$mid = " WHERE (UPPER(`shout_message`) like ?)";
			$bindvars = array('%'.strtoupper( $pListHash['find'] ).'%');
		}

		if( !empty( $pListHash['user_id'] ) ) {
			$mid .= empty( $mid ) ? ' WHERE ' : ' AND ';
			$mid .= " `shout_user_id` = ?";
			array_push( $bindvars, $pListHash['user_id'] );
		}

		if( !empty( $pListHash['to_user_id'] ) ) {
			$mid .= empty( $mid ) ? ' WHERE ' : ' AND ';
			$mid .= " `to_user_id` = ?";
			array_push( $bindvars, $pListHash['to_user_id'] );
		}

		$query = "SELECT * FROM `".BIT_DB_PREFIX."tiki_shoutbox` sh INNER JOIN `".BIT_DB_PREFIX."users_users` uus ON (sh.`shout_user_id`=uus.`user_id`) $mid order by ".$this->mDb->convert_sortmode( $pListHash['sort_mode'] );
		$query_cant = "select count(*) from `".BIT_DB_PREFIX."tiki_shoutbox` $mid";
		$result = $this->mDb->query($query,$bindvars,$pListHash['max_records'],$pListHash['offset']);
		$cant = $this->mDb->getOne($query_cant,$bindvars);
		$ret = array();

		while ($res = $result->fetchRow()) {
			if (!$res["shout_user_id"]) {
				$res["shout_user_id"] = tra('Anonymous');
			}
			// convert ampersands and other stuff to xhtml compliant entities
			$res["shout_message"] = htmlspecialchars($res["shout_message"]);

			if( $gBitSystem->isFeatureActive( 'shoutbox_autolink' ) ) {
				$hostname = '';
				if( $gBitSystem->getPreference( 'shoutbox_autolink' ) == 'm' ) {
					//moderated URL's
					$hostname = $gBitSystem->getPreference( 'feature_server_name' ) ? $gBitSystem->getPreference( 'feature_server_name' ) : $_SERVER['HTTP_HOST'];
				}
				// we replace urls starting with http(s)|ftp(s) to active links
				$res["shout_message"] = preg_replace("/((http|ftp)+(s)?:\/\/[^<>\s]*".$hostname."[^<>\s]*)/i", "<a href=\"\\0\">\\0</a>", $res["shout_message"]);
				// we replace also urls starting with www. only to active links
				$res["shout_message"] = preg_replace("/(?<!http|ftp)(?<!s)(?<!:\/\/)(www\.".$hostname."[^ )\s\r\n]*)/i","<a href=\"http://\\0\">\\0</a>",$res["shout_message"]);
				// we replace also urls longer than 30 chars with translantable string as link description instead the URL itself to prevent breaking the layout in some browsers (e.g. Konqueror)
				$res["shout_message"] = preg_replace("/(<a href=\")((http|ftp)+(s)?:\/\/[^\"]+)(\">)([^<]){30,}<\/a>/i", "<a href=\"\\2\">[".tra('Link')."]</a>", $res["shout_message"]);
			}

			// if not in html tag (e.g. autolink), place after every '*;' the empty span too to prevent e.g. '&amp;&amp;...'
			$res["shout_message"] = preg_replace('/(\s*)([^>]+)(<|$)/e', "'\\1'.str_replace(';', ';<span></span>','\\2').'\\3'", $res["shout_message"]);
			// if not in tag or on a space or doesn't contain a html entity we split all plain text strings longer than 25 chars using the empty span tag again
			$wrap_at = 25;
			$res['is_editable'] = $gBitUser->isRegistered() && ($gBitUser->hasPermission( 'bit_p_admin_shoutbox' ) || $gBitUser->getUserId() == $res['shout_user_id'] );
			$res['is_deletable'] = $gBitUser->isRegistered() && ($gBitUser->hasPermission( 'bit_p_admin_shoutbox' ) || $gBitUser->getUserId() == $res['shout_user_id'] || $gBitUser->getUserId() == $res['to_user_id'] );
			$res["shout_message"] = preg_replace('/(\s*)([^\;>\s]{'.$wrap_at.',})([^&]<|$)/e', "'\\1'.wordwrap('\\2', '".$wrap_at."', '<span></span>', 1).'\\3'", $res["shout_message"]);

			$ret[] = $res;
		}
		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

	function verify( &$pParamHash ) {
		global $gBitUser;
		if( empty( $pParamHash['shout_user_id'] ) ) {
			$pParamHash['shout_user_id'] = $gBitUser->getUserId();
		}
		if( empty( $pParamHash['to_user_id'] ) ) {
			$pParamHash['to_user_id'] = ROOT_USER_ID;
		}
		if( !empty( $pParamHash['shout_message'] ) ) {
			$pParamHash['shout_message'] = substr( strip_tags( $pParamHash['shout_message'] ), 0, 255 );
			$shout_sum = md5($pParamHash['shout_message']);
			$cant = $this->mDb->getOne("SELECT `shout_id` from `".BIT_DB_PREFIX."tiki_shoutbox` WHERE `shout_sum`=? AND `shout_user_id`=? AND `to_user_id`=?", array( $shout_sum, $pParamHash['shout_user_id'], $pParamHash['to_user_id'] ) );
			if ($cant) {
				$this->mErrors['store'] = tra( 'Duplicate message' );
			}
		}

		if( !empty( $pParamHash['shout_id'] ) ) {
			// we are editing an existing shout, let's make sure we have permission
			if( !$gBitUser->hasPermission( 'bit_p_admin_shoutbox' ) ) {
				$shout = $this->mDb->getRow( "SELECT * FROM `".BIT_DB_PREFIX."tiki_shoutbox` WHERE `shout_id`=?", array( $pParamHash['shout_id']));
				if( !$gBitUser->isRegistered() || (!empty( $shout ) && $shout['shout_user_id'] != $gBitUser->getUserId()) ) {
					$this->mErrors['store'] = tra( 'You do not have permission to edit the message' );
				}
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	function store( $pParamHash ) {
		if( $this->verify( $pParamHash ) ) {
			global $gBitSystem, $gBitSmarty;
			$now = $gBitSystem->getUTCTime();
			$shoutSum = md5( $pParamHash['shout_message'] );
			if( !empty( $pParamHash['shout_id'] ) ) {
				$userSql = '';
				$bindvars = array( $pParamHash['shout_message'], $shoutSum, (int)$pParamHash['shout_id'] );
				$query = "UPDATE `".BIT_DB_PREFIX."tiki_shoutbox` SET `shout_message`=?, `shout_sum`=?
						  WHERE `shout_id`=? $userSql";
			} else {
				$query = "delete from `".BIT_DB_PREFIX."tiki_shoutbox` where `shout_user_id`=? and `shout_time`=? and `shout_sum`=?";
				$bindvars = array( $pParamHash['shout_user_id'], (int)$now, $shoutSum );
				$this->mDb->query($query,$bindvars);
				$query = "insert into `".BIT_DB_PREFIX."tiki_shoutbox`( `shout_message`, `shout_user_id`, `to_user_id`, `shout_time`,`shout_sum`) values(?,?,?,?,?)";
				$bindvars = array( $pParamHash['shout_message'], $pParamHash['shout_user_id'], $pParamHash['to_user_id'], (int)$now, $shoutSum );
				if( $gBitSystem->isFeatureActive( 'shoutbox_email_notice' ) ) {
					$gToUser = new BitPermUser( $pParamHash['to_user_id'] );
					$gToUser->load();
					$gFromUser = new BitPermUser( $pParamHash['shout_user_id'] );
					$gFromUser->load();
					$gBitSmarty->assign( 'sendShoutUser', $gFromUser->getDisplayName( TRUE ) );
					$gBitSmarty->assign( 'sendShoutMessage', $pParamHash['shout_message'] );
					$gBitSmarty->assign( 'sendShoutUri', $_SERVER['SCRIPT_URI'] );
					$mail_data = $gBitSmarty->fetch('bitpackage:shoutbox/shoutbox_send_notice.tpl');
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
					$headers .= "From: ".$gBitSystem->getPreference( 'sender_email' )."\r\n";
					mail($gToUser->mInfo['email'], tra('A new shoutbox message for you at'). ' ' . $_SERVER["SERVER_NAME"].' '.date( 'Y-m-d' ), $mail_data, $headers);
				}
			}

			$result = $this->mDb->query($query,$bindvars);
		}

		return( count( $this->mErrors ) == 0 );
	}

	function expunge( $pShoutId ) {
		global $gBitUser;
		$hasPerm = $gBitUser->hasPermission( 'bit_p_admin_shoutbox' );
		if( !$hasPerm && $gBitUser->isRegistered() ) {
			$shout = $this->mDb->getRow( 'SELECT * FROM `'.BIT_DB_PREFIX.'tiki_shoutbox` WHERE `shout_id`=?', array($pShoutId) );
			if( $shout ) {
				$hasPerm = ($shout['to_user_id'] == $gBitUser->mUserId) || ($shout['shout_user_id'] == $gBitUser->mUserId);
			} else {
				$this->mErrors['expunge'] = tra( 'Unkown message' );
			}
		}
		if( $hasPerm ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."tiki_shoutbox` where `shout_id`=?";
			$result = $this->mDb->query($query,array((int)$pShoutId));
		} elseif( empty( $this->mErrors['expunge'] ) ) {
			$this->mErrors['expunge'] = tra( 'You do not have permission to delete the message' );
		}
		return( count( $this->mErrors ) == 0 );
	}

	function get_shoutbox($pShoutId) {
		$query = "select * from `".BIT_DB_PREFIX."tiki_shoutbox` where `shout_id`=?";
		$result = $this->mDb->query($query,array((int)$pShoutId));
		if (!$result->numRows()) {
			return false;
		}
		$res = $result->fetchRow();
		return $res;
	}
}

global $shoutboxlib;
$shoutboxlib = new ShoutboxLib();

?>
