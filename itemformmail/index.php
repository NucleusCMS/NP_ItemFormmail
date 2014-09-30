<?php

	// if your 'plugin' directory is not in the default location,
	// edit this variable to point to your site directory
	// (where config.php is)
	$strRel = '../../../';

	include($strRel . 'config.php');

	if (!$member->isLoggedIn())
		doError('You are not logged in.');
		
	include($DIR_LIBS . 'PLUGINADMIN.php');
	require_once('admin.php');
	// create the admin area page
	$oPluginAdmin = new PluginAdmin('ItemFormmail');

	if (!$member->isAdmin()) {
		$oPluginAdmin->start();
			echo '<h2>This Area needs admin.</h2>';
		$oPluginAdmin->end();
		exit;
	}
	
	$oPluginAdmin->start();
	//admin handler
	$myAdmin = new itemformmailPluginAdmin();
	if (requestVar('action')) {
		$myAdmin->action(requestVar('action'));
	} else {
		$myAdmin->action('frontpage');
	}

	$oPluginAdmin->end();

?>