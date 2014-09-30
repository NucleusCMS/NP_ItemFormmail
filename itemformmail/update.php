<?php
$ainstalled = explode(".", $this->getOption("version"));
$current = explode(".", $this->getVersion());
if ($ainstalled[0] <> $current[0]) echo "mismatch major version num , Please re-install";
switch ($ainstalled[1]) {
	// don't use 'break;'
	case 0:
	case 1:
	case 2:
	case 3:

	case 4:
		// create BlogOption and copy plugoption to blogoption
		@$this->createOption("usecaptcha", _IFORM_OPTION_CAPTCHA,"yesno","yes");
		@$this->createBlogOption("subject", _IFORM_OPTION_SUBJECT, "text", "");
		@$this->createBlogOption("sendto", _IFORM_OPTION_SENDTO, "text", '');// this default value needs '' because override check
		@$this->createBlogOption("successmessage", _IFORM_OPTION_THANKS, "textarea", _IFORM_OPTION_THANKS_DEF);
		@$this->createBlogOption("usepreview", _IFORM_OPTION_USEPREVIEW, "yesno", "yes");
		@$this->createBlogOption("autoreply", _IFORM_OPTION_AUTOREPLY, "yesno", "no");
		@$this->createBlogOption("autoreplyfrom",
		_IFORM_OPTION_AUTOREPLY_FROM, "text", $CONF['AdminEmail']);
		@$this->createBlogOption("autoreplyfromjp",
		_IFORM_OPTION_AUTOREPLY_FROMJP, "text", $CONF['SiteName']);
		@$this->createBlogOption("autoreplysubject",
				_IFORM_OPTION_AUTOREPLY_SUBJECT, "text", _IFORM_OPTION_AUTOREPLY_SUBJECT_DEF);
		@$this->createBlogOption("autoreplybody", _IFORM_OPTION_AUTOREPLY_BODY, "textarea");
		@$this->createBlogOption("registcomment", _IFORM_OPTION_COMMENT, "yesno", "no");
		@$this->createBlogOption("usecaptcha", _IFORM_OPTION_CAPTCHA,"yesno","yes");
	case 5:
		switch ($ainstalled[2]) {
			case 1:
				@$this->deleteOption("qmenu");
		}

}
$this->deleteOption("version");
$this->createOption("version", "Itemformmail-installed options-Version"
				, "text" , $this->getVersion() , "access=readonly" );
?>