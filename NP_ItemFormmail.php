<?php
/*

 * NP_ItemFormmail for only Japanese
 *
 * Copyright (C) 2005-2006 kosugi@kips.gr.jp
 * Licence : GPL
 * 0.2 form element update + bugfix
 * 0.3.1 security reason update
 * 0.3.2 fix mymbmime
 * 0.4.0 refactoring all
 * 0.4.1 <% -> <!%  this is html comment
 * 0.5.0 abort a email field . and more option and compatible with NP_Captcha
 * 0.5.1 delete Admin Area
 * 0.5.2 bug fix Captcha relation and updatescripts
 * 0.5.3 bug fix Captcha error in logged in
 * 0.5.4 bug fix duplicate mail sending problem in some environment
 * 0.5.5 bug fix checkbox problem and ename and preview
 * 0.5.6 lang file tuning etc
 *
 */
/* plugin needs to work on Nucleus versions <=2.3 as well */
if (!defined('_IFORM_COMMON_INCLUDED')) {
    include_once("itemformmail/common.php");
}
// mime function choice
if (!defined('_IFORM_MAILSEND_OPTIONS_DEFINED')) {
    define('_IFORM_MAILSEND_OPTIONS_DEFINED', 1);
    // mymbmime choice.
    define('_IFORM_MAILSEND_ENCODEMIME',        'mbstring');
    //define('_IFORM_MAILSEND_ENCODEMIME',        'buggy-mb');
}

class NP_ItemFormmail extends NucleusPlugin
{
    // formdata
    var $form;
    // receipt for posted data
    var $receipt;
    // replydata
    var $replydata;
    // currentItem
    var $currentItem;
    // for add comment
    var $commentdata;
    // server inner code
    var $base_inner_code;
    // server language
    var $base_language;
    // procedure conditon
    var $mode;
    // form parse counts
    var $parseCount;
    // post var name
    var $postvarname;
    // for parse acition
    var $if_currentlevel;
    // for only once send processing
    var $mailcount;
    var $captcha;

    function getName()
    {
        return 'ItemFormmail';
    }
    function getAuthor()
    {
        return 'Tomoaki Kosugi';
    }
    function getURL()
    {
        return 'http://japan.nucleuscms.org/wiki/plugins:np_itemformmail';
    }
    function getVersion()
    {
        return '0.6.1';
    }
    function getMinNucleusVersion()
    {
        return 350;
    }
    function getDescription()
    {
        return _IFORM_DESCRIPTION;
    }
    function hasAdminArea()
    {
        return 0;
    }
    function getEventList()
    {
        return array('QuickMenu', 'PreItem', 'FormExtra', 'ValidateForm', 'PrePluginOptionsEdit');
    }
    function supportsFeature($what)
    {
        switch ($what) {
            case 'SqlTablePrefix':
            case 'SqlApi':
                return 1;
            case 'HelpPage':
                return 0;
            default:
                return 0;
        }
    }
    //
    // Installation
    //
    function install()
    {
        global $CONF;
        //$this->createOption("erase", _IFORM_OPTION_ERASEDB, 'yesno', 'no');
        //$this->createOption("qmenu", _IFORM_OPTION_QMENU, 'yesno', 'no');
        $this->createOption('subject',         _IFORM_OPTION_SUBJECT,            'text', 'From:ItemFormmail');
        $this->createOption('sendto',          _IFORM_OPTION_SENDTO,             'text', $CONF['AdminEmail'] . '/JIS/SiteAdmin');
        $this->createOption('successmessage',  _IFORM_OPTION_THANKS,             'textarea', _IFORM_OPTION_THANKS_DEF);
        $this->createOption('usepreview',      _IFORM_OPTION_USEPREVIEW,         'yesno', 'yes');
        $this->createOption('autoreply',       _IFORM_OPTION_AUTOREPLY,          'yesno', 'no');
        $this->createOption('autoreplyfrom',   _IFORM_OPTION_AUTOREPLY_FROM,     'text', $CONF['AdminEmail']);
        $this->createOption('autoreplyfromjp', _IFORM_OPTION_AUTOREPLY_FROMJP,   'text', $CONF['SiteName']);
        $this->createOption('autoreplysubject', _IFORM_OPTION_AUTOREPLY_SUBJECT,  'text', _IFORM_OPTION_AUTOREPLY_SUBJECT_DEF);
        $this->createOption('autoreplybody',   _IFORM_OPTION_AUTOREPLY_BODY,     'textarea');
        $this->createOption('registcomment',   _IFORM_OPTION_COMMENT,            'yesno', 'no');
        $this->createOption('usecaptcha',      _IFORM_OPTION_CAPTCHA,            'yesno', 'yes');

        $this->createOption('version', 'Itemformmail-installed options-Version', 'text', $this->getVersion(), "access=readonly");

        $this->createBlogOption('subject',         _IFORM_OPTION_SUBJECT,           'text', '');
        $this->createBlogOption('sendto',          _IFORM_OPTION_SENDTO,            'text', ''); // this default value needs '' because override check
        $this->createBlogOption('successmessage',  _IFORM_OPTION_THANKS,            'textarea', _IFORM_OPTION_THANKS_DEF);
        $this->createBlogOption('usepreview',      _IFORM_OPTION_USEPREVIEW,        'yesno', 'yes');
        $this->createBlogOption('autoreply',       _IFORM_OPTION_AUTOREPLY,         'yesno', 'no');
        $this->createBlogOption('autoreplyfrom',   _IFORM_OPTION_AUTOREPLY_FROM,    'text', $CONF['AdminEmail']);
        $this->createBlogOption('autoreplyfromjp', _IFORM_OPTION_AUTOREPLY_FROMJP,  'text', $CONF['SiteName']);
        $this->createBlogOption('autoreplysubject', _IFORM_OPTION_AUTOREPLY_SUBJECT, 'text', _IFORM_OPTION_AUTOREPLY_SUBJECT_DEF);
        $this->createBlogOption('autoreplybody',   _IFORM_OPTION_AUTOREPLY_BODY,    'textarea');
        $this->createBlogOption('registcomment',   _IFORM_OPTION_COMMENT,           'yesno', 'no');
        $this->createBlogOption('usecaptcha',      _IFORM_OPTION_CAPTCHA,           'yesno', 'yes');
    }
    function _update_this()
    {
        @include 'itemformmail/update.php';
    }
    function uninstall()
    {
    }
    function init()
    {
        mb_internal_encoding(_CHARSET);
        global $manager, $blog;
        // if captcha no use comment out below
        if ($manager->pluginInstalled('NP_Captcha'))
            $this->captcha = $manager->getPlugin('NP_Captcha');
        $language = strtolower(str_replace(array('\\', '/'), '', getLanguageName()));
        $this->base_inner_code = mb_internal_encoding();
        $this->base_language = mb_language();
        if (is_null($this->base_inner_code)) {
            switch ($language) {
                case "utf-8":
                case "utf8":
                default:
                    mb_internal_encoding("UTF-8");
                    if (!mb_language()) mb_language("uni");
                    $this->base_inner_code = mb_internal_encoding();
                    $this->base_language = mb_language();
                    break;
                case "euc-jp":
                case "euc":
                    mb_internal_encoding('EUC-JP');
                    if (!mb_language()) mb_language('Japanese');
                    $this->base_inner_code = mb_internal_encoding();
                    $this->base_language = mb_language();
            }
        }
        $this->postvarname = "f_body";
        // set defaults

        $this->form['successmessage']   = $this->getOption('successmessage');
        $this->form['title']            = $this->getOption('subject');
        $this->form['usepreview']       = $this->getOption('usepreview');
        $this->form['autoreply']        = $this->getOption('autoreply');
        $this->form['autoreplysubject'] = $this->getOption('autoreplysubject');
        $this->form['autoreplybody']    = $this->getOption('autoreplybody');
        $this->form['autoreplyfrom']    = $this->getOption('autoreplyfrom');
        $this->form['autoreplyfromjp']  = $this->getOption('autoreplyfromjp');
        $this->form['sendto']           = $this->getOption('sendto');
        $this->form['usecaptcha']       = $this->getOption('usecaptcha');
        if ($blog) {
            $blogid = $blog->getID();
            if ($this->getBlogOption($blogid, "sendto")) {
                $this->form['successmessage']   = $this->getBlogOption($blogid, 'successmessage');
                $this->form['title']            = $this->getBlogOption($blogid, 'subject');
                $this->form['usepreview']       = $this->getBlogOption($blogid, 'usepreview');
                $this->form['autoreply']        = $this->getBlogOption($blogid, 'autoreply');
                $this->form['autoreplysubject'] = $this->getBlogOption($blogid, 'autoreplysubject');
                $this->form['autoreplybody']    = $this->getBlogOption($blogid, 'autoreplybody');
                $this->form['autoreplyfrom']    = $this->getBlogOption($blogid, 'autoreplyfrom');
                $this->form['autoreplyfromjp']  = $this->getBlogOption($blogid, 'autoreplyfromjp');
                $this->form['sendto']           = $this->getBlogOption($blogid, 'sendto');
                $this->form['usecaptcha']       = $this->getBlogOption($blogid, 'usecaptcha');
            }
        }
        // this form's options
        $this->options = array();
        $this->mode = "normal";
        $this->actions = array("uform", "captcha");

        $this->parser = new PARSER($this->actions, $this, '(<!%|%!>)');
        $this->if_currentlevel = true;
    }
    function event_PrePluginOptionsEdit($data)
    {
        if (!isset($data['contextid'])) {
            return;
        }

        if ($this->captcha) {
            return;
        }
        $options = &$data['options'];
        if ($data['context'] === 'global') {
            foreach ($options as $key => $option) {
                if ($option['name'] !== 'usecaptcha') {
                    continue;
                }
                unset($options[$key]);
            }
        } elseif ($data['context'] === 'blog') {
            foreach ($options as $key => $option) {
                if ($option['name'] !== 'usecaptcha') {
                    continue;
                }
                if ($this->getID() <> $option['pid']) {
                    continue;
                }
                unset($options[$key]);
            }
        }
    }
    function event_PreItem($data)
    {
        if (!isset($data['item'])) {
            return;
        }

        $this->currentItem = &$data['item'];
        $this->readOptionsByItem();
        $this->parseCount = 0;
        $this->currentItem->body = preg_replace_callback(
            '#<!%itemformmail%!>(.*?)<!%/itemformmail%!>#s',
            array(&$this, 'form_handler'),
            $this->currentItem->body
        );
    }

    function event_FormExtra($data)
    {
        //echo "test";
    }

    function event_ValidateForm($data)
    {
        //$data['error'] = htmlentities('<h1>test</h1>');
    }

    /*
        main procedure handling form
    */
    /**
     * call back handler , parse contents in item
     *
     * @param array $matches preg_replace_callback matches
     * @return string
     */
    var $result_message;
    function form_handler($matches)
    {
        ++$this->parseCount;
        if ($this->parseCount > 1) {
            return '';
        }
        $this->form['formtext'] = removeBreaks($matches[1]);
        $this->form['formtext'] = str_replace('<br />', PHP_EOL, $this->form['formtext']);
        $item = $this->currentItem;
        // post data interpretation
        // and set mode
        $this->receipt_post($item->title);
        // go for action
        switch ($this->mode) {
            case "send":
                // mail send
                $result = $this->mailsend($this->receipt, $this->replydata);
                $result = ($this->result_message) ? $this->result_message : $this->result_message($result);
                return $this->result_message;
                break;
            case "preview":
                // show preview
                return $this->_parse_preview();
                break;
            case "rewrite":
            default:
                // show form
                return $this->createForm();
                break;
        }
    }
    /**
     * in mode show form
     * form create
     *
     * @return string HTML FORM
     */
    function createForm()
    {
        ob_start();
        $this->parser->parse($this->form['formtext']);
        return ob_get_clean();
    }
    /*
        form parse callback actions for PARSER
    */
    /**
     * Enter description here...
     *
     * @param integer $idx identify and order
     * @param string $type form element type
     * @param string $colname identify name using dynamic form
     * @param string $jpname for viewname
     * @param string $option option
     * @param string $appendix selection or appendix in textfield
     */
    function parse_uform($idx, $type, $param3 = '', $option = '', $appendix = '')
    {
        $p = func_get_args();
        if (5 < count($p)) {
            foreach ($p as $i => $v) {
                if ($i < 4) unset($p[$i]);
            }
            $appendix = join(',', $p);
        }
        if ($idx === 'option') {
            $this->options[] = array($type => $param3);
        } else {
            echo $this->_gene_formElement($idx, $type, $param3, $option, $appendix);
        }
    }
    /**
     * Using NP_Captcha
     * show captcha
     *
     */
    function parse_captcha()
    {
        // compatible for NP_Captcha
        if ($this->form['usecaptcha'] === 'yes' && $this->captcha) {
            global $member;
            if (!$member->isLoggedIn()) {
                $data['type'] = 'commentform-notloggedin';
                //ob_start();
                $this->captcha->event_FormExtra($data);
                //$form = ob_get_contents();
                //ob_end_clean();
            }
        }
        //echo $form;
    }
    /*
        read options
    */
    function readOptionsByItem()
    {
        preg_match_all(
            '#<!%itemformmail%!>(.*?)<!%/itemformmail%!>#s',
            $this->currentItem->body,
            $matches,
            PREG_PATTERN_ORDER
        );
        // read options from every blocks
        foreach ($matches[1] as $match) {
            ob_start();
            $this->parser->parse($match);
            ob_end_clean();
        }
        foreach ($this->options as $option) {
            foreach ($option as $opname => $opvalue) {
                $this->form[$opname] = $opvalue;
            }
        }
    }
    //
    // mail sender
    //
    function mailsend($senddata, $replydata)
    {
        ++$this->mailcount;
        if ($this->mailcount > 1) {
            return;
        }
        // ex: hoge@example.com/UTF-8/admin,hogehoge@example.co.jp/JIS/bisehead
        //     to array()
        // compatible NP_Captcha
        $captchaerror = $this->_validate_captcha();
        if ($captchaerror) {
            return $captchaerror;
        }
        $sendtolist = explode(',', $this->form['sendto']);
        foreach ($sendtolist as $value) {
            $sendto = explode("/", $value);
            // hoge@example.com/UTF-8/admin
            // mailaddress => hoge@example.com
            // sendchara => UTF-8
            // sendtoname => admin
            $mailaddress = $sendto[0];
            $sendchara = isset($sendto[1]) ? $sendto[1] : 'JIS';
            $mailaddressjp = isset($sendto[2]) ? $sendto[2] : '';

            // normal mailsend block
            $this->set_sendchara($sendchara);
            if (isset($senddata['name']) && (strlen($senddata['name']) > 0)) {
                $senddata['mailfrom'] = sprintf(
                    'From:%s<%s>',
                    $this->_mymbmime($senddata['name']),
                    $senddata['mailaddress']
                );
            } else {
                $senddata['mailfrom'] = "From:" . $senddata['mailaddress'];
            }
            $mailheaders = $senddata['mailfrom'];
            if (mb_send_mail($mailaddress, $senddata['title'], $senddata['contents'], $mailheaders)) {
                $result[$sendto['mailaddress']] = "ok";
            } else {
                $result[$sendto['mailaddress']] = _IFORM_ERROR_MAILSEND;
            }
            // end normal mailsend block

        }
        if ($this->form['autoreply'] === 'yes') {
            // start autoreply procedure
            if (strlen($this->form['autoreplyfromjp']) > 0)
                $returnFrom = sprintf(
                    'From: %s<%s>',
                    $this->_mymbmime($this->form['autoreplyfromjp']),
                    $this->form['autoreplyfrom']
                );
            else
                $returnFrom = "From: " . $this->form['autoreplyfrom'];
            if (mb_send_mail($replydata['mailaddress'], $replydata['title'], $replydata['body'], $returnFrom)) {
                $result['autoReply'] = 'ok';
            } else {
                $result['autoReply'] = _IFORM_ERROR_AUTOREPLY;
            }
            // end autoreply
        }
        $this->restore_language();
        return $result;
    }
    //
    // send language setting
    //
    function set_sendchara($chara)
    {
        $this->sendchara = $chara;
        switch ($chara) {
            case 'UTF-8':
                mb_language('uni');
                mb_internal_encoding('UTF-8');
                break;
            case 'JIS':
                mb_language('Japanese');
                break;
        }
    }
    //
    // restore based language
    //
    function restore_language()
    {
        mb_internal_encoding($this->base_inner_code);
        mb_language($this->base_language);
    }

    //
    // interpret post data
    //
    function receipt_post($itemtitle)
    {
        $postvarname = $this->postvarname;
        $this->inputcheck = true;
        // mode check
        if (!isset($_POST['sendmail']) || $_POST['sendmail'] != true) {
            $this->mode = 'none';
            return '';
        }
        if ($this->form['usepreview'] === 'yes') {
            if (isset($_POST['rewrite']) && $_POST['rewrite'] == true) {
                $this->mode = 'rewrite';
                return '';
            }
            if (isset($_POST['previewed']) && $_POST['previewed'] == true) {
                $this->mode = 'send';
            } else {
                $this->mode = 'preview';
            }
        } else {
            $this->mode = 'send';
        }
        switch ($this->mode) {
            case 'send':
                // title
                $this->receipt['contents'] = '';
                // mail check
                if (isset($_POST[$postvarname]['email'])) {
                    $this->receipt['contents'] .= _IFORM_MES_USERADDRESS;
                    if (is_array($_POST[$postvarname]['email'])) {
                        $this->receipt['contents'] .=
                            $_POST[$postvarname]['email']['name'] . "<" . $_POST[$postvarname]['email']['value'] . ">\n\n";
                        $this->receipt['mailaddress']      = $_POST[$postvarname]['email']['value'];
                        if (isset($_POST[$postvarname]['email']['name']) && strlen($_POST[$postvarname]['email']['name']) > 2) {
                            $this->receipt['name']         = $_POST[$postvarname]['email']['name'];
                        }
                    }
                }
                // mailname and mailaddress for add_comment
                $this->commentdata['mailname']    = $_POST[$postvarname]['email']['name'];
                $this->commentdata['mailaddress'] = $this->receipt['mailaddress'];

                // body check
                if (isset($_POST[$postvarname])) {
                    $body_tmp = $_POST[$postvarname];
                    ksort($body_tmp);
                    foreach ($body_tmp as $key => $val) {
                        if (is_array($val) && $key !== 'email') {
                            if (preg_match_all('/[\n\r]/', $val['value'], $matches) > 1) {
                                $val['value'] = "\n" . $val['value'];
                            }
                            $this->receipt['contents'] .= $val['name'] . " = " . $val['value'] . "\n";
                            $this->commentdata['contents'] .= $val['name'] . " = " . $val['value'] . "\n";
                        } elseif ($key !== 'email') {
                            $this->receipt['contents'] .= "$key = $val" . "\n";
                            $this->commentdata['contents'] .= "$key = $val" . "\n";
                        }
                    }
                }
                // append access infomation
                $this->receipt['contents'] .=  "\n\n\n-----access infomation-----\n\n";
                $this->receipt['contents'] .=  "Access time = " . date("Ymd-his", time()) . "\n";
                $this->receipt['contents'] .=  "Remote address = " . getenv('REMOTE_ADDR') . "\n";
                $this->receipt['contents'] .=  "Send from = " . $itemtitle . "\n";
                $this->receipt['contents'] .=  "Browser = " . getenv('HTTP_USER_AGENT') . "\n\n";
                // set custom subject
                if (isset($_POST['subject'])) {
                    $this->receipt['title'] = $_POST['subject'];
                } else {
                    $this->receipt['title'] = $this->form['title'];
                }
                // regist receipt to receipt table
                //itemformmail_db::insert_receipts($this->receipt);
                if ($this->getOption('registcomment') === 'yes') $this->_add_comment();
                //autoReply Check
                if ($this->form['autoreply'] === 'yes') {
                    $this->replydata['mailaddress'] = $this->receipt['mailaddress'];
                    $this->replydata['body']        = $this->form['autoreplybody'];
                    $this->replydata['title']       = $this->form['autoreplysubject'];
                    // set normal mail info
                    $this->receipt['contents']     .= sprintf(
                        "autoReply = _\n%s\n-------\n",
                        $this->replydata['body']
                    );
                    // custom autoReply Subject
                    if (isset($_POST['autoReply']['subject'])) {
                        $this->replydata['title'] = $_POST['autoReply']['subject'];
                    } else {
                        $this->replydata['title'] = $this->form['autoreplysubject']; // added yama
                    }
                }
                break;
            case 'preview':
                //make previewdata
                $this->_parse_previewbody();
                break;
        }
        return true;
    }

    //
    // generate previewdata
    //
    function _parse_preview()
    {
        $bodydata = $this->preview['body'];
        // start preview table
        $previewedtable[] = _IFORM_RECEIPTS_PREVIEW_BLOCK_HEAD;
        $previewedtable[] = $bodydata['visible'];
        $previewedtable[] = _IFORM_RECEIPTS_PREVIEW_BLOCK_FOOT;
        // end table
        // for NP_Captcha compatible
        $ver_key = postVar('ver_key');
        $ver_sol = postVar('ver_sol');
        $captchaerror = $this->_captcha_check($ver_key, $ver_sol);
        // start contents data form
        $previewedform[] = '<form name="form1" method="post" action="">';
        // submit button  or REWRITE WARNING

        if ($this->inputcheck) {
            $previewedform[] =
                '<input type="submit" name="Submit" value="' . _IFORM_SUBMIT . '"  class="formbutton"/>';
        } else {
            $previewedform[] = _IFORM_WARNING_REWRITE;
            if ($captchaerror) $previewedform[] = $captchaerror;
        }
        // f_body contents
        $previewedform[] = $bodydata['hidden'];
        // email contents
        // $previewedform[] = $emaildata['hidden'];
        // flags
        $previewedform[] = '<input name="previewed" type="hidden" value="true"/>';
        $previewedform[] = '<input name="sendmail" type="hidden"  value="true"/>';
        $previewedform[] = '</form>';
        // end contents data form

        //
        // start rewrite form
        $rewriteform[] = _IFORM_RECEIPTS_PREVIEW_HORIZON;
        $rewriteform[] = '<form  name="rewrite"  method="post" action="">';
        $rewriteform[] = '<input name="rewrite"  type="hidden" value="true" />';
        $rewriteform[] = '<input name="sendmail" type="hidden" value="true"/>';
        $rewriteform[] = '<input type="submit" name="Submit" value="' . _IFORM_REWRITE . '" class="formbutton"/>';
        $rewriteform[] = $bodydata['hidden'];
        // $rewriteform[] = $emaildata['hidden'];
        $rewriteform[] = '</form>';
        // end rewrite form
        //
        // append preview
        return join("\n", $previewedtable) . join("\n", $previewedform) . join("\n", $rewriteform);
    }
    function _parse_previewbody()
    {
        $postvarname  = $this->postvarname;
        $bodydata     = array();
        $bodyVisible  = '';
        $hiddenfields = '';
        $linefeed     = "\n";

        if (isset($_POST[$postvarname])) {
            $postbody = $_POST[$postvarname];

            foreach ($postbody as $element) {
                if (isset($element['option']) && $element['option'] === 'ename') {
                    $ename = $element['value'];
                }
            }
            ksort($postbody);
            reset($postbody);
            foreach ($postbody as $key => $val) {
                if (is_array($val)) {
                    if (!isset($bodydata[$key]['name'])) {
                        $bodydata[$key]['name'] = '';
                    }
                    if (!isset($bodydata[$key]['value'])) {
                        $bodydata[$key]['value'] = '';
                    }
                    if (!isset($val['name'])) {
                        $val['name'] = '';
                    }
                    if (!isset($val['value'])) {
                        $val['value'] = '';
                    }

                    $bodydata[$key]['name'] .= $this->_suniview($val['name']);
                    if (isset($val['option'])) {
                        switch ($val['option']) {
                            case 'need':
                                $bodydata[$key]['value'] .= $this->_formvalues($val['value']);
                                break;
                            case 'email':
                                $email = $this->_mailvalues($val['value'], $ename, true);
                                $bodydata[$key]['value'] .= $email;
                                $hiddenfields .= sprintf(
                                    '<input name="%s[email][value]" type="hidden" value="%s"/>%s',
                                    $postvarname,
                                    $this->_suni($val['value']),
                                    $linefeed
                                );
                                break;
                            case 'confirm':
                                //$bodydata[$key]['value'] .= $this->_mailvalues($val['value'],'',true);
                                // if you use confirm need , put confirmfield after emailfield
                                $confirmEmail = $this->_mailvalues($val['value'], $ename, true);
                                if (!($confirmEmail == $email)) {
                                    //confirm ERROR
                                    $this->inputcheck = false;
                                    $bodydata[$key]['value'] .= _IFORM_RECEIPTS_PREVIEW_ERR_HEAD .
                                        _IFORM_ERROR_MAIL_CONFIRM .
                                        _IFORM_RECEIPTS_PREVIEW_ERR_FOOT;
                                } else {
                                    $bodydata[$key]['name'] = 'confirm';
                                }
                                break;
                                /* edit start yamamoto*/
                            case 'ename':
                                if (strlen($val['value']) > 0) {
                                    $bodydata[$key]['value'] .= $this->_suniview($val['value'] . _IFORM_ENAME_SAMA);
                                    $hiddenfields .= sprintf(
                                        '<input name="%s[email][name]" type="hidden" value="%s%s"/>%s',
                                        $postvarname,
                                        $this->_suni($val['value']),
                                        _IFORM_ENAME_SAMA,
                                        $linefeed
                                    );
                                } else {
                                    $this->inputcheck = false;
                                    $bodydata[$key]['value'] .= _IFORM_ERROR_NO_CONTENT;
                                }
                                break;
                            case 'ename2':
                                if (strlen($val['value']) > 0) {
                                    $bodydata[$key]['value'] .= $this->_suniview($val['value']);
                                    $hiddenfields .= sprintf(
                                        '<input name="%s[email][name]" type="hidden" value="%s"/>%s',
                                        $postvarname,
                                        $this->_suni($val['value']),
                                        $linefeed
                                    );
                                } else {
                                    $this->inputcheck = false;
                                    $bodydata[$key]['value'] .= _IFORM_ERROR_NO_CONTENT;
                                }
                                //$ename = true;
                                break;
                                /* edit end yamamoto*/
                            default:
                                $bodydata[$key]['value'] .= $this->_suniview($val['value']);
                                break;
                        }
                    } else {
                        $bodydata[$key]['value'] .= $this->_suniview($val['value']);
                    }
                    if ($bodydata[$key]['name'] !== 'confirm') {
                        $hiddenfields .= sprintf(
                            '<input name="%s[%s][name]" type="hidden" value="%s"/>%s',
                            $postvarname,
                            $this->_suni($key),
                            $this->_suni($val['name']),
                            $linefeed
                        );
                        $hiddenfields .= sprintf(
                            '<input name="%s[%s][value]" type="hidden" value="%s"/>%s',
                            $postvarname,
                            $this->_suni($key),
                            $this->_suni($val['value']),
                            $linefeed
                        );
                    }
                } else {
                    $bodydata[$key]['name']  .= $this->_suniview($val['key']);
                    $bodydata[$key]['value'] .= $this->_suniview($val['value']);
                    $hiddenfields .= sprintf(
                        '<input name="%s[%s]" type="hidden" value="%s"/>%s',
                        $this->postvarname,
                        $this->_suni($key),
                        $this->_suni($val),
                        $linefeed
                    );
                }
            }
            // f_body data renderrer
            ksort($bodydata);
            foreach ($bodydata as $key => $val) {
                if (($val['name'] || $val['value']) && ($bodydata[$key]['name'] !== 'confirm')) {
                    $bodyVisible .=
                        _IFORM_RECEIPTS_PREVIEW_ROW_HEAD . $linefeed .
                        _IFORM_RECEIPTS_PREVIEW_CELL_HEAD_NAME . $linefeed .
                        $val['name'] . $linefeed .
                        _IFORM_RECEIPTS_PREVIEW_CELL_FOOT . $linefeed .
                        _IFORM_RECEIPTS_PREVIEW_CELL_HEAD_VALUE . $linefeed .
                        $val['value'] . $linefeed .
                        _IFORM_RECEIPTS_PREVIEW_CELL_FOOT . $linefeed .
                        _IFORM_RECEIPTS_PREVIEW_ROW_FOOT . $linefeed;
                }
            }
        }
        // for NP_Captcha compatible
        $hiddenfields .= sprintf(
            '<input name="ver_key" type="hidden" value="%s"/>%s',
            $this->_suni(postVar('ver_key')),
            $linefeed
        );
        $hiddenfields .= sprintf(
            '<input name="ver_sol" type="hidden" value="%s"/>%s',
            $this->_suni(postVar('ver_sol')),
            $linefeed
        );

        $this->preview['body']['visible'] = $bodyVisible;
        $this->preview['body']['hidden'] = $hiddenfields;
    }

    /*
        generate form elements
    */
    function _gene_formElement($idx, $type, $jpname = '', $option = '', $appendix = '')
    {
        $postvarname = $this->postvarname;
        if (isset($_POST[$postvarname][$idx]['value'])) {
            $columnValue = hsc($_POST[$postvarname][$idx]['value']);
        } else {
            $columnValue = '';
        }
        $text = '';
        switch ($type) {
            default:
            case 'text':
            case 'textfield':
                if (empty($columnValue) && requestVar($jpname)) {
                    $columnValue = requestVar($jpname);
                }
                $args = func_get_args();
                $args = array_slice($args, 4);
                $appendix = implode(',', $args);
                $text .= sprintf(
                    "<input name='%s[%s][value]' type='text' value='%s' %s />\n",
                    $postvarname,
                    $idx,
                    $columnValue,
                    $appendix
                );
                break;
            case 'textarea':
                $text .= sprintf(
                    "<textarea name='%s[%s][value]' %s>%s</textarea>\n",
                    $postvarname,
                    $idx,
                    $appendix,
                    $columnValue
                );
                break;
            case 'select':
                $options = explode("\n", $appendix);
                $text .= sprintf(
                    "<select name='%s[%s][value]'>\n",
                    $postvarname,
                    $idx
                );
                foreach ($options as $op) {
                    $opt = explode('/', $op);
                    $label = $opt[0];
                    if (isset($opt[1]) && strlen($opt[1]) > 0) {
                        $value = $opt[1];
                    } else {
                        $value = $opt[0];
                    }
                    $value = trim($value);
                    $columnValue = trim($columnValue);
                    if ($columnValue) {
                        if ($value == $columnValue) {
                            $selected = "selected='selected'";
                        } else {
                            $selected = '';
                        }
                    } else {
                        if (isset($opt[2]) && strlen($opt[2]) > 0) {
                            $selected = "selected='selected'";
                        } elseif (requestVar($jpname) == $value) {
                            $selected = "selected='selected'";
                        } else {
                            $selected = '';
                        }
                    }
                    $text .= sprintf(
                        "<option value= '%s' %s >%s</option>\n",
                        $value,
                        $selected,
                        $label
                    );
                }
                $text .= '</select>';
                break;
            case 'radio':
                $options = explode("\n", $appendix);

                $text .= "<p>\n";
                foreach ($options as $op) {
                    $opt = explode("/", $op);
                    $label = $opt[0];
                    if (isset($opt[1]) && strlen($opt[1]) > 0) {
                        $value = $opt[1];
                    } else {
                        $value = $opt[0];
                    }
                    if ($columnValue) {
                        if ($value == $columnValue) {
                            $selected = "checked='checked'";
                        } else {
                            $selected = '';
                        }
                    } else {
                        if (isset($opt[2]) && strlen($opt[2]) > 0) {
                            $selected = "checked='checked'";
                        } else {
                            $selected = '';
                        }
                    }
                    $text .= sprintf(
                        "<input name='%s[%s][value]' type='radio' value= '%s' %s /><label for='%s.[%s][value]' >%s</label>\n",
                        $postvarname,
                        $idx,
                        $value,
                        $selected,
                        $postvarname,
                        $idx,
                        $label
                    );
                }
                $text .= "</p>";
                break;
            case 'checkbox':
                $options = explode("\n", $appendix);
                foreach ($options as $op) {
                    $opt = explode('/', $op);
                    $label = $opt[0];
                    if (isset($opt[1]) && strlen($opt[1]) > 0) {
                        $value = $opt[1];
                    } else {
                        $value = $opt[0];
                    }
                    if ($columnValue) {
                        if ($value == $columnValue) {
                            $selected = "checked='checked'";
                        } else {
                            $selected = '';
                        }
                    } elseif (isset($opt[2]) && strlen($opt[2]) > 0) {
                        $selected = "checked='checked'";
                    } else {
                        $selected = '';
                    }
                    $text .= sprintf("
                    <input name='%s[%s][value]' type='checkbox' value= '%s' %s />
                    <label for='%s[value]' >%s</label>\n",
                        $postvarname, $idx, $value, $selected, $postvarname[$idx], $label
                    );
                }
                break;
        }
        if ($option != '') {
            $text .= sprintf(
                "<input name='%s[%s][option]' type='hidden' value='%s'/>\n",
                $postvarname, $idx, $option
            );
        }
        $text .= sprintf(
            "<input name='%s[%s][name]' type='hidden' value='%s'/>\n",
            $postvarname, $idx, $jpname
        );
        return $text;
    }

    //
    // result messages
    //
    function result_message($result)
    {
        if (is_array($result)) {
            $topresult = array_shift($result);
            switch ($topresult) {
                case 'ok':
                    $this->result_message = $this->form['successmessage'];
                    return $this->form['successmessage'];
                case 'error':
                    $this->result_message = _IFORM_ERROR_MAILSEND;
                    return _IFORM_ERROR_MAILSEND;
                default:
                    //debug print
                    echo "<pre>\n";
                    print_r($result);
                    echo '</pre>';
            }
        } else {
            switch ($result) {
                case 'ok':
                    return $this->form['successmessage'];
                case 'error':
                    return _IFORM_ERROR_MAILSEND;
                default:
                    //debug print
                    return $result;
            }
        }
        return '';
    }

    //
    //otherfunctions
    //
    function _validate_captcha()
    {
        global $member;
        if ($member->isLoggedIn()) {
            return '';
        }
        // compatible for NP_Captcha
        if ($this->form['usecaptcha'] === 'yes' && $this->captcha) {
            $data['type'] = 'comment';
            $this->captcha->event_ValidateForm($data);
            if ($data['error']) {
                $this->inputcheck = false;
                return "\n<span class = 'formerror' >" . $data['error'] . "</span>\n";
            }
        }
        return '';
    }

    /**
     * this function referring NP_Captcha::check
     *  some modified
     *
     * @param string$key
     * @param string $solution
     * @return string errormessage or null
     */
    function _captcha_check($key, $solution)
    {
        global $member;
        if ($member->isLoggedIn()) {
            return true;
        }
        if (!($this->form['usecaptcha'] === 'yes' && $this->captcha)) {
            return true;
        }
        // initialize on first call
        if (!$this->captcha->inited)
            $this->captcha->init_captcha();

        // cleanup old captchas
        //$this->captcha->_removeOldEntries();

        // check if key exists
        if (!$this->captcha->_existsKey($key)) {
            $this->inputcheck = false;
            return $this->captcha->getOption('FailedMsg');
        }

        // get info
        $res = sql_query(
            sprintf(
                "SELECT * FROM %s WHERE ckey='%s'",
                $this->captcha->table,
                addslashes($key)
            )
        );
        if ($res) {
            $o = sql_fetch_object($res);
        }

        // delete captcha key (we've got the info)
        //$this->captcha->_deleteKey($key);

        if (!$res || !$o) {
            $this->inputcheck = false;
            return $this->captcha->getOption('FailedMsg');
        }

        // check if captcha entry is active
        if ($o->active != 1) {
            $this->inputcheck = false;
            return $this->captcha->getOption('FailedMsg');
        }

        // check solution
        if (md5(strtoupper($solution)) != $o->solution) {
            $this->inputcheck = false;
            return $this->captcha->getOption('FailedMsg');
        }
        return true;
        // correct solution for captcha challenge
        //return true;
    }

    function _mymbmime($str)
    {
        switch (_IFORM_MAILSEND_ENCODEMIME) {
            case 'buggy-mb':
                switch ($this->sendchara) {
                    case 'JIS':
                        $convertTo = "ISO-2022-JP";
                        break;
                    case 'UTF-8':
                        $convertTo = "UTF-8";
                        break;
                }
                $data = "=?" . $convertTo . "?B?" .
                    trim(
                        chunk_split(
                            base64_encode(
                                mb_convert_encoding($str, $convertTo, "auto")
                            )
                        )
                    ) . '=?=';
                $data = str_replace("\r\n", "=?==?$convertTo?B?", $data);
                return $data;
            case 'string':
            default:
                $data = mb_encode_mimeheader($str);
                return $data;
        }
    }

    function _suni($str)
    {
        return htmlentities($str, ENT_QUOTES, mb_internal_encoding());
    }

    function _suniview($str)
    {
        return nl2br(htmlentities($str, ENT_QUOTES, mb_internal_encoding()));
    }

    function _mailvalues($mailaddress, $mailname, $check = true)
    {
        $message = '';
        if (!$this->_mailcheck($mailaddress) && $check) {
            $this->inputcheck = false;
            $message .= _IFORM_RECEIPTS_PREVIEW_ERR_HEAD .
                _IFORM_ERROR_NO_MAIL .
                _IFORM_RECEIPTS_PREVIEW_ERR_FOOT;
        } else {
            $message .= sprintf(
                '%s&lt;%s&gt;',
                $this->_suniview($mailname),
                $this->_suniview($mailaddress)
            );
        }
        return $message;
    }

    function _formvalues($str)
    {
        if ($this->_nullcheck($str)) {
            return $this->_suniview($str);
        }
        $this->inputcheck = false;
        return _IFORM_RECEIPTS_PREVIEW_ERR_HEAD . _IFORM_ERROR_NO_CONTENT . _IFORM_RECEIPTS_PREVIEW_ERR_FOOT;
    }

    function _mailcheck($str)
    {
        if (strlen($str) < 4) {
            return false;
        }
        $keywords = preg_split(
            '/[\s,;]+/',
            mb_convert_kana(trim($str), 'as')
        );
        if (preg_match_all('/@/', $keywords[0], $matches) != 1) {
            return false;
        }

        $domain = explode('@', $keywords[0]);
        if (strpos($domain[1], '.') === false) {
            return false;
        }

        return htmlentities($keywords[0]);
    }

    function _nullcheck($str)
    {
        return $str != '';
    }

    function _add_comment()
    {
        global $manager;

        $data['itemid'] = $this->currentItem->itemid;
        $data['user']   = $this->commentdata['mailname'];
        $data['userid'] = $this->commentdata['mailaddress'];
        $data['body']   = $this->commentdata['contents'];

        $comments = new COMMENTS($data['itemid']);

        $blogid = getBlogIDFromItemID($data['itemid']);
        $blog = &$manager->getBlog($blogid);

        // note: PreAddComment and PostAddComment gets called somewhere inside addComment
        //$errormessage =
        $comments->addComment($blog->getCorrectTime(), $data);
    }
}
