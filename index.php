<?php
$cfg['GMAIL_ACCOUNT'] = '';//Gmailのメールアドレス
$cfg['GMAIL_PASSWORD'] = '';//Gmailのパスワード
//Gmailの接続情報
$cfg['MAILBOX'] = '{imap.gmail.com:993/imap/ssl}';
?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Gmail接続</title>
</head>
<body>
<h1>GmailへIMAP接続して特定のメールアドレスからの本文を取得</h1>

<?php
$mbox = imap_open($cfg['MAILBOX'], $cfg['GMAIL_ACCOUNT'], $cfg['GMAIL_PASSWORD']);

if ($mbox == false) {
    echo 'Gmailへの接続に失敗しました';
} else {
    $mailIds = imap_search($mbox, 'FROM "...@kcg.ac.jp"');//送信者メールアドレス

    foreach ($mailIds as $mailId) {
        $header = imap_headerinfo($mbox, $mailId);
        $subject = getSubject($header);
        echo $mailId . ' : ' . $subject . '<br>';

        $body = getBody($mbox, $mailId);
        $trimedBody = strstr($body, 'From: Samuraki Yuki', true);//返信履歴の切り落とし
        echo '<br>' . $trimedBody . '<br>';
    }
    imap_close($mbox);
}

function getSubject($header)
{
    if (!isset($header->subject)) {
        return '';
    }
    // タイトルをデコード
    $mhead = imap_mime_header_decode($header->subject);
    $subject = '';
    foreach ($mhead as $key => $value) {
        if ($value->charset == 'default') {
            $subject .= $value->text;
        } else {
            $subject .= mb_convert_encoding($value->text, 'UTF-8', $value->charset);
        }
    }
    return $subject;
}

//本文エンコーディングここから
function getBody($mbox, $mailId)
{
    $body = imap_fetchbody($mbox, $mailId, 1, FT_INTERNAL);
    $s = imap_fetchstructure($mbox, $mailId);

    //マルチパートのメールかどうか確認しつつ、文字コードとエンコード方式を確認
    if (isset($s->parts)) {
        //マルチパートの場合
        //$charset = $s->parts[0]->parameters[0]->value;
        $encoding = $s->parts[0]->encoding;
    } else {
        //マルチパートではない場合
        //$charset = $s->parameters[0]->value;
        $encoding = $s->encoding;
    }

    //エンコード方式に従いデコード
    switch ($encoding) {
        case 1://8bit
            $body = imap_8bit($body);
            $body = imap_qprint($body);
            break;
        case 3://Base64
            $body = imap_base64($body);
            break;
        case 4://Quoted-Printable
            $body = imap_qprint($body);
            break;
        case 0://7bit
        case 2://Binary
        case 5://other
        default:
            //7bitやBinaryは何もしない
    }
    $body = mb_convert_encoding($body, 'utf-8', 'auto');
    return $body;
}

?>
</body>
</html>
