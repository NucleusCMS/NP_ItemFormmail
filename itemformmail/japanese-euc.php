<?php
if ( !defined('_IFORM_LANGUAGE_DEFINED') ) {
	define('_IFORM_LANGUAGE_DEFINED',		1);
	//プラグイン説明
	define('_IFORM_DESCRIPTION',			'アイテムをフォームメール化します。<br />固定表示可能なプラグイン(NP_ShowItemなど)やリダイレクトコードを合わせて使うと安定したフォームメールになると思います。');

	//プラグインオプション編集画面用
	define('_IFORM_OPTION_QMENU',	'クイックメニューに表示しますか？');
	define('_IFORM_OPTION_ERASEDB',	'アンインストール時にプラグイン用テーブルを削除しますか？');
	define('_IFORM_OPTION_SENDTO',		'受け取るメールアドレス');
	define('_IFORM_OPTION_SUBJECT',		'メールの件名');
	define('_IFORM_OPTION_THANKS',		'送信完了時のメッセージ');
	define('_IFORM_OPTION_THANKS_DEF',		"<h2>お問い合わせありがとうございました。</h2>\n");
	define('_IFORM_OPTION_ITEMID',		'フォームを表示するアイテムのアイテムID(数字)');
	define('_IFORM_OPTION_COMMENT',		'コメントとして保存しますか？');
	define('_IFORM_OPTION_CAPTCHA',		'画像認証を使用しますか？');

	define('_IFORM_OPTION_QMENU',		'クイックメニューに表示しますか？（必要ないと思います)');
	define('_IFORM_PARSEITEM',		'フォームタグを自動育成しますか？');
	define('_IFORM_OPTION_USEFORM',		'このアイテムをフォームとして使用しますか？');
	define('_IFORM_OPTION_USEPREVIEW',		'プレビューを使いますか？');
	define('_IFORM_OPTION_FORMTEXT',		'フォームタグの記述');
	define('_IFORM_OPTION_AUTOREPLY',		'自動返信を使いますか？');
	define('_IFORM_OPTION_AUTOREPLY_SUBJECT',	'自動返信の件名');
	define('_IFORM_OPTION_AUTOREPLY_SUBJECT_DEF',	'自動返信:ありがとうございました');
	define('_IFORM_OPTION_AUTOREPLY_FROM',	'自動返信の送信元');
	define('_IFORM_OPTION_AUTOREPLY_FROMJP',	'自動返信送信元の日本語名');
	define('_IFORM_OPTION_AUTOREPLY_BODY',	'自動返信の中身');
	define('_IFORM_OPTION_SENDTO',	'フォームの受信アドレス <br />( ex: mailaddress/UTF-8/myname )');
	define('_IFORM_QMENU_TITLE',	'フォームメール');
	define('_IFORM_QMENU_TOOLTIP',	'フォームメール管理');

	//メール内への記述用
	define('_IFORM_MES_USERADDRESS',	'送信元:');
	define('_IFORM_SUBJECT',	'フォームメールの件名');
	define('_IFORM_AUTOREPLY_SUBJECT',	'自動返信の件名');
	define('_IFORM_AUTOREPLY_BODY',		'このメールはシステムによって自動的に送信されています。\n');
// added yamamoto ここから
	define('_IFORM_ENAME_SAMA',	' 様');
// added yamamoto ここまで

	//フォーム等への表示用
	define('_IFORM_THANKS',		"<h2>お問い合わせありがとうございました。</h2>\n");
	define('_IFORM_SUBMIT',		' 送 信 ');
	define('_IFORM_REWRITE',	' 戻 る ');

	define('_IFORM_ERROR_AUTOREPLY',	'自動返信メールの送信に失敗しました。');
	define('_IFORM_ERROR_MAILSEND',		'メールの送信に失敗しました。');
	define('_IFORM_ERROR_MAIL_CONFIRM',	'確認アドレスが一致しません');
	define('_IFORM_ERROR_NO_MAIL',		'メールアドレスを入力してください');
	define('_IFORM_ERROR_NO_CONTENT',	'この項目は必ず入力してください');

	define('_IFORM_FORMS_NOTICE_OPTIONS_NOT_FOUND' ,
	'<span class = "form-notice">このフォームのオプションが見つからないため、メール送信はできません。</span>');
	define('_IFORM_WARNING_REWRITE',	"<span class = 'formerror' >再入力してください</span><br /><br />");

	//管理画面用
	define('_IFORM_DESCRIPTION_LINE',	'<h2>ItemFormmail</h2>');
	define('_IFORM_EDIT_PLUGIN_OPTIONS',	'プラグインオプションの編集');

	define('_IFORM_USE_FORM',		'このアイテムをフォームとして使用しますか？');
	define('_IFORM_USE_PREVIEW',	'プレビューを使いますか？');
	define('_IFORM_USE_AUTOREPLY',	'自動返信を使いますか？');
	define('_IFORM_OPTION_ITEMID',	'フォームを表示するアイテムのアイテムID(数字)');

	//管理画面用フォーム
	define('_IFORM_FORMEDIT_FORM_TEMPLATE','
<h3>フォーム編集</h3>
<form name="formedit" method="POST" action="">
 <table border="0" cellspacing="0" cellpadding="0">
<%iform(1,text,formname,フォーム名)%>
<%iform(2,text,description,フォーム説明,,size="50")%>
<%iform(3,textarea,formtext,フォーム説明,need,cols="35" rows="12")%>
<%iform(4,text,title,送信用件名,,size="40")%>
<%iform(5,textarea,successmessage,送信成功時のメッセージ,,cols="20" rows="6")%>
<%iform(6,select,autoreply,自動返信を使用しますか？,need,使用する/1
使用しない/2/selected)%>
<%iform(7,text,autoreplyfrom,自動返信メール用送信元アドレス,,size="40")%>
<%iform(8,text,autoreplyfromjp,自動返信メールの送信元日本語名,,size="40")%>
<%iform(9,text,autoreplysubject,自動返信メールの件名,,size="40")%>
<%iform(10,textarea,autoreplybody,自動返信メールの本文,need,cols="35" rows="12")%>
<%iform(11,special,sendto,フォームメール受信用アドレス,,sendto/sentoaddress/sentoname)%>
<%iform(12,select,usepreview,プレビューを使用しますか？,need,使用する/1
使用しない/2/selected)%>
 </table>
 <input name="sendmail" type="hidden" value="true"/>
 <input type="submit" name="Submit" value="　送　信　"/>　
 <input type="reset" name="Submit" value=" リセット "/>
</form>');
	//サンプルフォーム

	define('_IFORM_SAMPLE_FORM_DESC','サンプル用のフォームです');
	define('_IFORM_SAMPLE_FORM_DATA','
<form name="form1" method="post" action="">
 <p>メールフォーム用サンプル </p>
 <table border="0" cellspacing="0" cellpadding="0">
  <tr>
   <td>お名前</td>
   <td><%uform(1,text,id-name,名前)%>
   </td>
  </tr>
  <tr>
   <td>おところ</td>
   <td><%uform(2,text,address,住所)%>
   </td>
  </tr>
  <tr>
   <td>コメント</td>
   <td><%uform(3,textarea,comment,コメント,need,cols="20" rows="5")%>
   </td>
  </tr>
  <tr>
    <td>好きな果物</td>
    <td><%uform(6,select,fruit,好きな果物,need,特になし
ラフランス//selected
パイナップル
クランベリー
ドリアン
その他)%></td>
  </tr>
  <tr>
    <td>
      性別</td>
    <td><%uform(7,radio,sex,性別,,男性
女性//selected
不明)%>
</td>
  </tr>
  <tr>
    <td>未成年？</td>
    <td><%uform(8,checkbox,under20,未成年?)%>
   	</td>
  </tr>
  <tr>
   <td>確認メール受信者名</td>
   <td><%uform(9,email,name,確認メール受信者名,need)%>
   </td>
  </tr>
  <tr>
    <td>E-Mail</td>
    <td><%uform(10,email,value,,need)%></td>
  </tr>
  <tr>
   <td>確認用</td>
   <td><%uform(11,email,confirm)%></td>
  </tr>
 </table>
 <input name="sendmail" type="hidden" value="true"/>
 <input type="submit" name="Submit" value="　送　信　"/>　
 <input type="reset" name="Submit" value=" リセット "/>
</form>');
}

?>