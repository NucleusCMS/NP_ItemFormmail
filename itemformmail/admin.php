<?php
/**
  *
  * Licence : GPL
  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License
  * as published by the Free Software Foundation; either version 2
  * of the License, or (at your option) any later version.
  * (see nucleus/documentation/index.html#license for more info)
  *
  */
/**
 * ItemFormmail
 * Usage include this page
 *
 * @category   mail
 * @package    Itemformmail
 * @author     T. KOSUGI
 * @copyright  2006 T.KOSUGI
 * @license    GNU General Public License version 2
 * @link       http://www.kips.gr.jp
 */
/**
  * NP_itemformmail Admin class
  *
  * Copyright (C) 2006 kosugi@kips.gr.jp
  */
if ( !defined('_IFORM_COMMON_INCLUDED') ) {
	include_once("common.php");
}
class itemformmailPluginAdmin {
	function itemformmailPluginAdmin() { $this->init(); }
	function __construct() { $this->init(); }
	function init() {
		global $oPluginAdmin,$config;
		$this->plug =& $oPluginAdmin->plugin;
		$this->plugname = $this->plug->getName();
		$this->url = $this->plug->getAdminURL();
		$this->directory = $this->plug->getDirectory();
		$this->mode = 'frontpage';
		//$this->forms =     new itemformmail_forms_admin();
		$this->receipts =  new itemformmail_receipts();
		
		// manually handled inherit property 
		//$this->forms->url = $this->url;
		$this->receipts->url = $this->url;

		//$this->forms->plug =& $this->plug;
		$this->receipts->plug =& $this->plug;
	}
	function action($action) {
		$buf .= $this->_viewTitle();
		echo $buf;
		$methodName = 'action_' . $action;
		if (method_exists($this, $methodName)) {
			call_user_func(array(&$this, $methodName));
		}
		//$this->forms->action($action);
		$this->receipts->action($action);
	}
	function error($type,$msg) {
		switch ($type) {
			case "warn":
			case "info":
				echo $msg;
				return;
			case "fatal":
			default:
				echo '<pre>'.$msg.'</pre>';
				unset($this);
				break;
		}
	}
	function _viewTitle($msg=''){
		global $member;
		$buf = '<h2>'._IFORM_DESCRIPTION_LINE."</h2>\n<a href='".$this->url ."'>[reload]</a>";
		if ($member->isAdmin()) {
		$buf .= '<p>[<a href="index.php?action=pluginoptions&amp;plugid='.$this->plug->getID().'">'._IFORM_EDIT_PLUGIN_OPTIONS.'</a>]</p>';
		}
		return $buf;
	}
}

class itemformmail_receipts {
	function itemformmail_receipts() { $this->init(); }
	function __construct() { $this->init(); }
	function init() {
		$this->mode = 'frontpage';
	}

	function action($action) {
		$methodName = 'action_' . $action;
		if (method_exists($this, $methodName)) {
			call_user_func(array(&$this, $methodName));
		} else {
			return;
		}
	}
	function action_frontpage() {
		$buf =  "<h3>受信データ</h3>";
		$buf .= '<ul>';
		$buf .= '<li>';
		$buf .= $this->actionLink('receiptsDeleteAll','ログ一括削除').'<br />';
		$buf .= '</li>';
		$buf .= '</ul>';
		$db = new itemformmail_db();
		$receipts = $db->read_receipts($this->log_ammounts);
		
		if ($receipts == false) {
			$buf .= '<blockquote>';
			$buf .= "受信データが見つかりませんでした";
			$buf .= '</blockquote>';
		} else {
			$buf .= '<blockquote>'.count($receipts)."件を表示中<br />".'<table>';
			$buf .= "<tr><th>ID</th><th>送信者のアドレス</th><th>コンテンツ抜粋</th><th>見る</th><th>削除</th></tr>";
			foreach ($receipts as $receipt ) {
				$buf .= "<tr>";
				$buf .= "<td>".$this->suni($receipt['receiptID'])."</td>";
				$buf .= "<td><a href=mailto:".$this->suni($receipt['mailaddress']).">".$this->suni($receipt['mailaddress'])."</a></td>";
				$buf .= "<td><pre>".$this->suni(mb_strcut($receipt['contents'],0,100)).'</pre></td>';
				$buf .= '<td>'
					. $this->actionLink('receiptView','詳しく見る','receiptID='.$receipt['receiptID'])
					. '</td>'
					. '<td>'
					. $this->actionLink('receiptDelete','削除','receiptID='.$receipt['receiptID'])
					. '</td>';
				$buf .=	'</tr>';
			}
			$buf .= "</table></blockquote>";
		}
		echo $buf;
	}
	function receipt_post_admin() {
		$this->inputcheck = true;
		// mode check
		if (isset($_POST['sendmail']) && $_POST['sendmail'] == true) {
			if ($this->form['usepreview'] == "yes") {
				if (isset($_POST['rewrite']) && $_POST['rewrite'] == true) {
					$this->mode = 'rewrite';
					return;
				} elseif (isset($_POST['previewed']) && $_POST['previewed'] == true) {
					$this->mode = 'send';
				} else {
					$this->mode = 'preview';
				}
			} else {
				$this->mode = 'send';
			}
		} else {
			$this->mode = 'none';
		}
	}
}
?>