<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // POSTで送られてきた場合のみログアウト処理
  unset($_SESSION["login_user_id"]);

  // ログアウトが完了したらログアウト完了画面にリダイレクト
  header("HTTP/1.1 303 See Other");
  header("Location: ./logout_finish.php");
  return;
}
?>

<h1>ログアウト</h1>
<!-- ログアウトフォーム -->
<form method="POST">
  <button type="submit">ログアウト</button>
</form>
