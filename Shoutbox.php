<?php
/**
 * $Header$
 * @package shoutbox
 */

/**
 * required setup
 */
require_once( KERNEL_PKG_PATH.'BitCache.php' );
/**
 * @package shoutbox
 */
class Shoutbox extends BitBase {
	function __construct() {
		parent::__construct();
		$this->mCache = new BitCache( 'shoutbox' );
	}

	// $offset, $max_records, $sort_mode, $find
	function getList( &$pListHash ) {
		global $gBitUser, $gBitSystem, $gBitSmarty, $gLibertySystem;
		if ( empty( $_REQUEST["sort_mode"] ) ) {
			$pListHash['sort_mode'] = 'shout_time_desc';
		}
		LibertyBase::prepGetList( $pListHash );
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

		$query = "
			SELECT * FROM `".BIT_DB_PREFIX."shoutbox` sh
			INNER JOIN `".BIT_DB_PREFIX."users_users` uus ON (sh.`shout_user_id`=uus.`user_id`) $mid
			ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] );
		$result = $this->mDb->query( $query, $bindvars, $pListHash['max_records'], $pListHash['offset'] );
		$ret = array();

		while( $res = $result->fetchRow() ) {
			if( !$res["shout_user_id"] ) {
				$res["shout_user_id"] = tra('Anonymous');
			}

			// get cached version if we have it
			if( !$this->mCache->isCached( $res['shout_id'], filemtime( __FILE__ ))) {
				// convert ampersands and other stuff to xhtml compliant entities
				$res["shout_message"] = htmlspecialchars( $res["shout_message"] );

				if( $gBitSystem->isFeatureActive( 'shoutbox_autolink' ) ) {
					$hostname = '';
					if( $gBitSystem->getConfig( 'shoutbox_autolink' ) == 'm' ) {
						//moderated URL's
						$hostname = $gBitSystem->getConfig( 'kernel_server_name' ) ? $gBitSystem->getConfig( 'kernel_server_name' ) : $_SERVER['HTTP_HOST'];
					}
					// we replace urls starting with http(s)|ftp(s) to active links
					$res["shout_message"] = preg_replace("/((http|ftp)+(s)?:\/\/[^<>\s]*".$hostname."[^<>\s]*)/i", "<a href=\"\\0\">\\0</a>", $res["shout_message"]);
					// we replace also urls starting with www. only to active links
					$res["shout_message"] = preg_replace("/(?<!http|ftp)(?<!s)(?<!:\/\/)(www\.".$hostname."[^ )\s\r\n]*)/i","<a href=\"http://\\0\">\\0</a>",$res["shout_message"]);
					// we replace also urls longer than 30 chars with translantable string as link description instead the URL itself to prevent breaking the layout in some browsers (e.g. Konqueror)
					$res["shout_message"] = preg_replace("/(<a href=\")((http|ftp)+(s)?:\/\/[^\"]+)(\">)([^<]){30,}<\/a>/i", "<a href=\"\\2\">[".tra('Link')."]</a>", $res["shout_message"]);
				}

				if( $gBitSystem->isFeatureActive( 'shoutbox_smileys' ) && $gBitSystem->isPackageActive( 'smileys' ) && $gLibertySystem->isPluginActive( 'filtersmileys' )) {
					if( $filterfunc = $gLibertySystem->getPluginFunction( 'filtersmileys', 'postparse_function' )) {
						// note that we've already done the htmlspecialchars thing
						// things like :-)) need to preceed :-)
						$smileys = array(
							'---&gt;' => 'arrow',
							'--&gt;'  => 'arrow',
							':-O'     => 'surprised',
							'8-)'     => 'cool',
							':-|'     => 'neutral',
							':-/'     => 'confused',
							':-\\'    => 'confused',
							':-S'     => 'confused',
							';-)'     => 'wink',
							':-))))'  => 'mrgreen',
							':-)))'   => 'lol',
							':-))'    => 'biggrin',
							':-)'     => 'smile',
							':-(('    => 'cry',
							':-('     => 'sad',
							':-P'     => 'razz',
							'>:->'    => 'twisted',
							'>:-('    => 'evil',
							'>:-|'    => 'mad',
							'(?)'     => 'question',
							'(!)'     => 'exclaim',
						);
						foreach( $smileys as $str => $smiley ) {
							$res['shout_message'] = str_replace( $str, "(:$smiley:)", $res['shout_message'] );
						}

						$filterfunc( $res['shout_message'], $res );
					}
				}

				// if not in html tag (e.g. autolink), place after every '*;' the empty span too to prevent e.g. '&amp;&amp;...'
				$res["shout_message"] = preg_replace_callback( '/(\s*)([^>]+)(<|$)/', 
					function ($m) { return  $m[1].str_replace(';', ';<span></span>',$m[2]).$m[3]; },
					$res["shout_message"] );
				// if not in tag or on a space or doesn't contain a html entity we split all plain text strings longer than 25 chars using the empty span tag again
				$wrap_at = 25;
				$res["shout_message"] = preg_replace_callback( '/(\s*)([^\;>\s]{'.$wrap_at.',})([^&]<|$)/', 
					function ($m) { return $m[1].wordwrap($m[2], '".$wrap_at."', '<span></span>', 1).$m[3]; },
					$res["shout_message"] );

				$this->mCache->writeCacheFile( $res['shout_id'], $res['shout_message'] );
			}
			$res['shout_message'] = $this->mCache->readCacheFile( $res['shout_id'] );

			// work out permissions
			$res['is_editable'] = $gBitUser->isRegistered() && ( $gBitUser->hasPermission( 'p_shoutbox_admin' ) || $gBitUser->getUserId() == $res['shout_user_id'] );
			$res['is_deletable'] = $gBitUser->isRegistered() && ( $gBitUser->hasPermission( 'p_shoutbox_admin' ) || $gBitUser->getUserId() == $res['shout_user_id'] || $gBitUser->getUserId() == $res['to_user_id'] );

			$ret[] = $res;
		}

		$query_cant = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."shoutbox` $mid";
		$pListHash["cant"] = $this->mDb->getOne( $query_cant, $bindvars );

		LibertyBase::postGetList( $pListHash );
		return $ret;
	}

	function verify( &$pParamHash ) {
		global $gBitUser;

		if( empty( $pParamHash['shout_user_id'] ) ) {
			$pParamHash['shout_user_id'] = $gBitUser->getUserId();
		}

		if( empty( $pParamHash['to_user_id'] ) ) {
			$pParamHash['to_user_id'] = ROOT_USER_ID;
		} elseif( !is_numeric( $pParamHash['to_user_id'] ) ) {
			$this->mErrors['store'] = 'Invalid user';
		}

		if( !$gBitUser->verifyCaptcha( !empty( $pParamHash['captcha'] ) ? $pParamHash['captcha'] : NULL ) ) {
			$this->mErrors['store'] = tra( 'Incorrect validation code' );
		}

		if( !empty( $pParamHash['shout_message'] ) ) {
			$pParamHash['shout_message'] = trim( substr( strip_tags( $pParamHash['shout_message'] ), 0, 255 ));
			$shout_sum = md5( $pParamHash['shout_message'] );
			$cant = $this->mDb->getOne( "
				SELECT `shout_id`
				FROM `".BIT_DB_PREFIX."shoutbox`
				WHERE `shout_sum`=? AND `shout_user_id`=? AND `to_user_id`=?",
				array( $shout_sum, $pParamHash['shout_user_id'], $pParamHash['to_user_id'] )
			);
			if( $cant ) {
				$this->mErrors['store'] = tra( 'Duplicate message' );
			} elseif( empty( $pParamHash['shout_message'] ) ) {
				// check for empty after strip and trim.
				$this->mErrors['store'] = tra( 'Empty message' );
			}
		} else {
			$this->mErrors['store'] = tra( 'Empty message' );
		}

		if( !empty( $pParamHash['shout_id'] ) ) {
			// we are editing an existing shout, let's make sure we have permission
			if( !$gBitUser->hasPermission( 'p_shoutbox_admin' ) ) {
				$shout = $this->mDb->getRow( "SELECT * FROM `".BIT_DB_PREFIX."shoutbox` WHERE `shout_id`=?", array( $pParamHash['shout_id']));
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
				// since this is an update, we need to make sure the cache file is removed
				$this->mCache->expungeCacheFile( $pParamHash['shout_id'] );
				$bindvars = array( $pParamHash['shout_message'], $shoutSum, (int)$pParamHash['shout_id'] );
				$query = "UPDATE `".BIT_DB_PREFIX."shoutbox` SET `shout_message`=?, `shout_sum`=?
						  WHERE `shout_id`=?";
			} else {
				$query = "DELETE FROM `".BIT_DB_PREFIX."shoutbox` where `shout_user_id`=? and `shout_time`=? and `shout_sum`=?";
				$bindvars = array( $pParamHash['shout_user_id'], (int)$now, $shoutSum );
				$this->mDb->query($query,$bindvars);
				$query = "INSERT INTO `".BIT_DB_PREFIX."shoutbox`( `shout_message`, `shout_user_id`, `to_user_id`, `shout_time`, `shout_sum`, `shout_ip`) VALUES (?,?,?,?,?,?)";
				$bindvars = array( $pParamHash['shout_message'], $pParamHash['shout_user_id'], $pParamHash['to_user_id'], (int)$now, $shoutSum, $_SERVER['REMOTE_ADDR'] );

				// inform the user user that a message has been posted
				if( $pParamHash['to_user_id'] != ROOT_USER_ID && $pParamHash['to_user_id'] != ANONYMOUS_USER_ID && $gBitSystem->isFeatureActive( 'shoutbox_email_notice' ) ) {
					$gToUser = new BitPermUser( $pParamHash['to_user_id'] );
					$gToUser->load();
					$gFromUser = new BitPermUser( $pParamHash['shout_user_id'] );
					$gFromUser->load();
					$gBitSmarty->assign( 'fromUser', $gFromUser->getDisplayName( TRUE ) );
					$gBitSmarty->assign( 'sendShoutMessage', $pParamHash['shout_message'] );
					$mail_data = $gBitSmarty->fetch( 'bitpackage:shoutbox/shoutbox_send_notice.tpl' );
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
					$headers .= "From: ".$gBitSystem->getConfig( 'site_sender_email' )."\r\n";
					mail(
						$gToUser->mInfo['email'],
						tra('A new shoutbox message for you at').' '.$_SERVER["SERVER_NAME"].' '.date( 'Y-m-d' ),
						$mail_data,
						$headers
					);
				}
			}

			$result = $this->mDb->query($query,$bindvars);
		}

		return( count( $this->mErrors ) == 0 );
	}

	function expunge( $pShoutId ) {
		global $gBitUser;
		$hasPerm = $gBitUser->hasPermission( 'p_shoutbox_admin' );
		if( !$hasPerm && $gBitUser->isRegistered() ) {
			$shout = $this->mDb->getRow( 'SELECT * FROM `'.BIT_DB_PREFIX.'shoutbox` WHERE `shout_id`=?', array($pShoutId) );
			if( $shout ) {
				$hasPerm = ($shout['to_user_id'] == $gBitUser->mUserId) || ($shout['shout_user_id'] == $gBitUser->mUserId);
			} else {
				$this->mErrors['expunge'] = tra( 'Unkown message' );
			}
		}
		if( $hasPerm ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."shoutbox` where `shout_id`=?";
			$result = $this->mDb->query($query,array((int)$pShoutId));
		} elseif( empty( $this->mErrors['expunge'] ) ) {
			$this->mErrors['expunge'] = tra( 'You do not have permission to delete the message' );
		}
		return( count( $this->mErrors ) == 0 );
	}

	function getShout($pShoutId) {
		$query = "select * from `".BIT_DB_PREFIX."shoutbox` where `shout_id`=?";
		$result = $this->mDb->query($query,array((int)$pShoutId));
		if (!$result->numRows()) {
			return false;
		}
		$res = $result->fetchRow();
		return $res;
	}
}
?>
