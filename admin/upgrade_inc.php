<?php

global $gBitSystem, $gUpgradeFrom, $gUpgradeTo;

$upgrades = array(

'BONNIE' => array( 
	'CLYDE' => array(
// STEP 1
array( 'DATADICT' => array(
array( 'RENAMECOLUMN' => array( 
	'tiki_shoutbox' => array( '`msgId`' => '`shout_id` I4 AUTO',
							  '`message`' => '`shout_message` C(255)',
							  '`timestamp`' => '`shout_time` I8',
							  '`hash`' => '`shout_sum` C(32)'
	),
)),
array( 'ALTER' => array(
	'tiki_shoutbox' => array(
		'shout_user_id' => array( '`shout_user_id`', 'I4' ), // , 'NOTNULL' ),
		'to_user_id' => array( '`to_user_id`', 'I4' ), // , 'NOTNULL' ),
	),
)),
)),

// STEP 2
array( 'QUERY' => 
	array( 'SQL92' => array( 
	"UPDATE `".BIT_DB_PREFIX."tiki_shoutbox` SET `shout_user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_shoutbox`.`user`)",
	"UPDATE `".BIT_DB_PREFIX."tiki_shoutbox` SET `to_user_id`=".ROOT_USER_ID,
	)),
),

// STEP 3
array( 'DATADICT' => array(
	array( 'DROPCOLUMN' => array(
		'tiki_shoutbox' => array( '`user`' ),
	)),
)),

	)
)

);

if( isset( $upgrades[$gUpgradeFrom][$gUpgradeTo] ) ) {
	$gBitSystem->registerUpgrade( SHOUTBOX_PKG_NAME, $upgrades[$gUpgradeFrom][$gUpgradeTo] );
}


?>
