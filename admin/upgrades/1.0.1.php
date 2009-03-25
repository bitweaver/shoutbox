<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_shoutbox/admin/upgrades/1.0.1.php,v 1.2 2009/03/25 08:28:11 squareing Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => SHOUTBOX_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Minor fix to ip columns to support IPv6",
	'post_upgrade' => NULL,
);

// all we are doing is change the column type of user_id for liberty_content_history.
// postgresql < 8.2 doesn't allow easy column type changing
// and therefore we need to undergo this annoying dance.
$gBitInstaller->registerPackageUpgrade( $infoHash, array(

// copy data into new column
array( 'QUERY' =>
	// postgres > 8.2 needs to have the type cast
	array(
		'PGSQL' => array( "ALTER TABLE `".BIT_DB_PREFIX."shoutbox` ALTER `shout_ip` TYPE VARCHAR(39)" ,),
		'OCI'   => array( "ALTER TABLE `".BIT_DB_PREFIX."shoutbox` MODIFY (`shout_ip` TYPE VARCHAR2(39))" ,),
		'MYSQL' => array( "ALTER TABLE `".BIT_DB_PREFIX."shoutbox` MODIFY `shout_ip` TYPE VARCHAR(39)" ,),
	),
),

));
?>
