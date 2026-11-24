<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
// セッションにあるログインIDから、ログインしている対象の会員情報を引く
$select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$select_sth->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $select_sth->fetch();

$error = null;
if (isset($_POST['website_url'])) {
  // フォームから website_url が送信されてきた場合の処理

  // 空文字なら NULL(未設定に戻す)
  $website_url = trim($_POST['website_url']);
  if ($website_url === '') {
    $website_url = null;
  } elseif (
    !filter_var($website_url, FILTER_VALIDATE_URL)
    || !in_array(parse_url($website_url, PHP_URL_SCHEME), ['http', 'https'], true)
  ) {
    // URLとして不正、または http / https 以外(javascript: など)は受け付けない
    $error = 'http:// または https:// から始まる正しいURLを入力してください。';
  }

  if ($error === null) {
    // ログインしている会員情報のwebsite_urlカラムを更新する
    $update_sth = $dbh->prepare("UPDATE users SET website_url = :website_url WHERE id = :id");
    $update_sth->execute([
        ':id' => $user['id'],
        ':website_url' => $website_url,
    ]);
    // 成功したら成功したことを示すクエリパラメータつきのURLにリダイレクト
    header("HTTP/1.1 302 Found");
    header("Location: ./website.php?success=1");
    return;
  }
}
// エラー時は入力した値をそのままフォームに戻す
$form_value = $error !== null ? $_POST['website_url'] : ($user['website_url'] ?? '');
?>
<a href="./index.php">設定一覧に戻る</a>

<h1>外部Webサイトリンク設定</h1>

<form method="POST">
  <input type="url" name="website_url" size="50" placeholder="https://example.com/"
    value="<?= htmlspecialchars($form_value) ?>">
  <button type="submit">決定</button>
</form>

<?php if($error !== null): ?>
<div style="color: red;">
  <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if(!empty($_GET['success'])): ?>
<div>
  外部Webサイトリンクの設定処理が完了しました。
</div>
<?php endif; ?>
