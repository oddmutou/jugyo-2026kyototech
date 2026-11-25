<?php
session_start();
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

// この人の投稿データを取得
$select_sth = $dbh->prepare(
  'SELECT bbs_entries.*, users.name AS user_name, users.icon_filename AS user_icon_filename'
  . ' FROM bbs_entries INNER JOIN users ON bbs_entries.user_id = users.id'
  . ' WHERE user_id = :user_id'
  . ' ORDER BY bbs_entries.created_at DESC'
);
$select_sth->execute([
  ':user_id' => $user_id,
]);
$is_self = !empty($_SESSION['login_user_id']) && (int)$user['id'] === (int)$_SESSION['login_user_id'];
?>
<style>
  body {
    margin: 0;
  }

  .profile-cover {
    display: block;
    width: 100%;
    aspect-ratio: 3 / 1;
    max-height: 300px;
    object-fit: cover;
    background: #ccc; /* 画像未設定のときにグレーにする */
  }

  .profile-body {
    padding: 0 1em 1em;
  }

  .profile-icon-wrap {
    display: block;
    position: relative;
    box-sizing: border-box;
    height: 6rem;
    width: 6rem;
    margin-top: -3rem; /* 上半分を、カバー画像と被せてみる */
    border: 4px solid #fff;
    border-radius: 50%;
    background: #eee;
    overflow: hidden; /* 「変更する」の背景が丸からはみ出さないように */
  }

  .profile-icon {
    display: block;
    height: 100%;
    width: 100%;
    object-fit: cover;
  }

  .profile-icon-empty { /* アイコン画像が無いときよう */
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    font-size: 0.7rem;
    text-align: center;
    color: #666; /* カバー画像よりちょっと暗いグレー */
  }

  /* 自分のアイコンにカーソルをあてたときに出る「変更する」 */
  .profile-icon-edit {
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5); /* 半透明のグレー */
    color: #fff;
    font-size: 0.8rem;
    text-decoration: none;
    opacity: 0; /* 普段は隠す */
    transition: opacity 0.15s;
  }

  .profile-icon-edit:hover,
  .profile-icon-edit:focus {
    opacity: 1;
  }
</style>

<div style="padding: 0.5em 1em;">
  <a href="/bbs.php">掲示板に戻る</a>
  <?php if($is_self): // 自分自身の場合 ?>
  このページは、あなたのプロフィールです。
  <a href="/setting/index.php">設定を変更する</a>
  <?php endif; ?>
</div>

<?php if(!empty($user['cover_filename'])): ?>
<img class="profile-cover" src="/image/<?= htmlspecialchars($user['cover_filename']) ?>">
<?php else: ?>
<div class="profile-cover"></div>
<?php endif; ?>

<div class="profile-body">
<div class="profile-icon-wrap">
  <?php if(empty($user['icon_filename'])): ?>
  <div class="profile-icon-empty">アイコン<br>未設定</div>
  <?php else: ?>
  <img class="profile-icon" src="/image/<?= htmlspecialchars($user['icon_filename']) ?>">
  <?php endif; ?>
  <?php if($is_self): ?>
  <a class="profile-icon-edit" href="/setting/icon.php">変更する</a>
  <?php endif; ?>
</div>

<h1><?= htmlspecialchars($user['name']) ?> さん のプロフィール</h1>

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
  <?php if(!empty($user['website_url'])): ?>
    <a href="<?= htmlspecialchars($user['website_url']) ?>" target="_blank" rel="noopener noreferrer">
      <?= htmlspecialchars($user['website_url']) ?>
    </a>
  <?php endif; ?>
</div>

<div>
  <?= nl2br(htmlspecialchars($user['introduction'] ?? '')) ?>
</div>

</div>

<div class="profile-body">
<hr>
<?php foreach($select_sth as $entry): ?>
  <dl style="margin-bottom: 1em; padding-bottom: 1em; border-bottom: 1px solid #ccc;">
    <dt>日時</dt>
    <dd><?= $entry['created_at'] ?></dd>
    <dt>内容</dt>
    <dd>
      <?= htmlspecialchars($entry['body']) ?>
      <?php if(!empty($entry['image_filename'])): ?>
      <div>
        <img src="/image/<?= $entry['image_filename'] ?>" style="max-height: 10em;">
      </div>
      <?php endif; ?>
    </dd>
  </dl>
<?php endforeach ?>
</div>
