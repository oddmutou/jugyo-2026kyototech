<?php
$user = null;
if (!empty($_GET['user_id'])) {
  $user_id = $_GET['user_id'];
  // DBに接続
  $dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
  // 対象の会員情報を引く
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
  $select_sth->execute([
    ':id' => $user_id,
  ]);
  $user = $select_sth->fetch();
}
if (empty($user)) {
  header("HTTP/1.1 404 Not Found");
  print("そのようなユーザーIDの会員情報は存在しません");
  return;
}
?>

<h1><?= htmlspecialchars($user['name']) ?> さん のプロフィール</h1>
<div>
  <?php if(empty($user['icon_filename'])): ?>
  現在アイコン未設定
  <?php else: ?>
  <img src="/image/<?= htmlspecialchars($user['icon_filename']) ?>"
    style="height: 5em; width: 5em; border-radius: 50%; object-fit: cover;">
  <?php endif; ?>
</div>

<div>
  <?php if(!empty($user['birthday'])): ?>
  <?php
    $birthday = DateTime::createFromFormat('Y-m-d', $user['birthday']);
    $today = new DateTime('now');
  ?>
    <?= $today->diff($birthday)->y ?>歳
  <?php else: ?>
    生年月日未設定
  <?php endif; ?>
</div>

<div>
  <?php if(!empty($user['prefecture'])): ?>
    居住地: <?= htmlspecialchars($user['prefecture']) ?>
  <?php else: ?>
    居住地未設定
  <?php endif; ?>
</div>

<div>
  <?= nl2br(htmlspecialchars($user['introduction'] ?? '')) ?>
</div>
