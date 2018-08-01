<?php
if (defined('_IFORM_COMMON_INCLUDED') ) return;

define('_IFORM_COMMON_INCLUDED',1);

if ( !defined('_IFORM_LANGUAGE_DEFINED') ) {
	$language = str_replace( array('\\','/'), '', getLanguageName());
	if (is_file(dirname(__FILE__).'/'.$language.'.php')) {
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
