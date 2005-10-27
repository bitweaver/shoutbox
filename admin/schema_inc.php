<?php

$tables = array( 
'tiki_shoutbox' => "
  shout_id I4 AUTO NOTNULL PRIMARY,
  shout_user_id I4 NOTNULL,
  to_user_id I4 NOTNULL,
  shout_message C(255) NOTNULL,
  shout_time I8 NOTNULL,
  shout_sum C(32)
" );

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( SHOUTBOX_PKG_NAME, $tableName, $tables[$tableName] );
}


$gBitInstaller->registerPackageInfo( SHOUTBOX_PKG_NAME, array(
	'description' => "The shoutbox is a module that resides in one of the side-columns and allows users to chat with each other, ask questions or post some random comments.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
	'version' => '0.1',
	'state' => 'beta',
	'dependencies' => '',
) );

// ### Default Preferences
$gBitInstaller->registerPreferences( SHOUTBOX_PKG_NAME, array(
	array(SHOUTBOX_PKG_NAME, 'shoutbox_autolink','n')
) );

// ### Default UserPermissions
$gBitInstaller->registerUserPermissions( SHOUTBOX_PKG_NAME, array(
	array('bit_p_view_shoutbox', 'Can view shoutbox', 'basic', SHOUTBOX_PKG_NAME),
	array('bit_p_admin_shoutbox', 'Can admin shoutbox (Edit/remove msgs)', 'editors', SHOUTBOX_PKG_NAME),
	array('bit_p_post_shoutbox', 'Can post messages in shoutbox', 'basic', SHOUTBOX_PKG_NAME)
) );

?>
