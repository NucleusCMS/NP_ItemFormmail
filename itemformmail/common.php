<?php
if ( !defined('_IFORM_COMMON_INCLUDED') ) {
	define('_IFORM_COMMON_INCLUDED',1);
	if (!function_exists('sql_table')){
		function sql_table($name) {
			return 'nucleus_' . $name;
		}
	}
	if ( !defined('_IFORM_LANGUAGE_DEFINED') ) {
		$language = str_replace( array('\\','/'), '', getLanguageName());
		if (file_exists(dirname(__FILE__).'/'.$language.'.php')) {
			include_once(dirname(__FILE__).'/'.$language.'.php');
			define('_IFORM_LANGUAGE_DEFINED',1);
		}
	}
	if ( !defined('_IFORM_FORMS_DEFINED') ) {
		define('_IFORM_FORMS_DEFINED',1);
		define('_IFORM_FORMS_ROW',
		'<tr>
			<td><%jpname%></td>
			<td>
				<%iformRowData%>
			</td>
		</tr>');

	}
	include_once("user-config.php");
}
?>