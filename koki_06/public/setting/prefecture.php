<?php
session_start();
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: /login.php");
  return;
}

// 選択肢となる都道府県の一覧
$prefectures = [
  '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
  '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '山梨県', '長野県',
  '新潟県', '富山県', '石川県', '福井県',
  '岐阜県', '静岡県', '愛知県', '三重県',
  '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
  '鳥取県', '島根県', '岡山県', '広島県', '山口県',
  '徳島県', '香川県', '愛媛県', '高知県',
  '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県',
];

// DBに接続
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
// セッションにあるログインIDから、ログインしている対象の会員情報を引く
$select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
$select_sth->execute([
    ':id' => $_SESSION['login_user_id'],
]);
$user = $select_sth->fetch();

if (isset($_POST['prefecture'])) {
  // フォームから prefecture が送信されてきた場合の処理

  // 空文字(未設定を選択)なら NULL、それ以外は一覧に含まれる値だけを受け付ける
  // (selectでも、POSTは自由に書き換えて送れるのでサーバー側でも確認する)
  $prefecture = $_POST['prefecture'];
  if ($prefecture === '') {
    $prefecture = null;
  } elseif (!in_array($prefecture, $prefectures, true)) {
    header("HTTP/1.1 400 Bad Request");
    print("不正な都道府県が指定されました");
    return;
  }

  // ログインしている会員情報のprefectureカラムを更新する
  $update_sth = $dbh->prepare("UPDATE users SET prefecture = :prefecture WHERE id = :id");
  $update_sth->execute([
      ':id' => $user['id'],
      ':prefecture' => $prefecture,
  ]);
  // 成功したら成功したことを示すクエリパラメータつきのURLにリダイレクト
  header("HTTP/1.1 302 Found");
  header("Location: ./prefecture.php?success=1");
  return;
}
?>

<h1>居住地(都道府県)設定</h1>
<form method="POST">
  <select name="prefecture">
    <option value="">未設定</option>
    <?php foreach ($prefectures as $name): ?>
    <option value="<?= htmlspecialchars($name) ?>"
      <?= $user['prefecture'] === $name ? 'selected' : '' ?>
    ><?= htmlspecialchars($name) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">決定</button>
</form>

<?php if(!empty($_GET['success'])): ?>
<div>
  居住地の設定処理が完了しました。
</div>
<?php endif; ?>
