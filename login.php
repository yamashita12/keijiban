<?php

session_start();
mb_internal_encoding("utf8");

// 1.ログインに状態にあれば、board.phpにリダイレクト
if (isset($_SESSION['id'])) {
    header("Location:board.php");
}

// 2.エラーメッセージを扱う変数の初期化
$errors = "";

// 3.POSTアクセス時の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 3.1.エスケープ処理
    $input["mail"] = htmlentities($_POST["mail"] ?? "", ENT_QUOTES);
    $input["password"] = htmlentities($_POST["password"] ?? "", ENT_QUOTES);

    // 3.2.バリデーションチェック
    if (!filter_input(INPUT_POST, "mail", FILTER_VALIDATE_EMAIL)) { // メールの形式確認
        $errors = "メールアドレスとパスワードを正しく入力してください。";
    }
    if (strlen(trim($_POST["password"] ?? "")) == 0) {  //入力されているかの確認
        $errors = "メールアドレスとパスワードを正しく入力してください。";
    }

    // 3.3.ログイン認証
    if (empty($errors)) {
        // 3.3.1 DBに接続し、入力されたメールアドレスを元にユーザー情報を取り出す
        try {
            $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;", "root", "root"); // DBに接続
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // エラーモードを「警告」に設定
            $stmt = $pdo->prepare(" SELECT * FROM user WHERE mail = ? "); // 入力されたメールアドレスを元にユーザー情報を取り出す
            $stmt->execute(array($input["mail"]));
            $user = $stmt->fetch(PDO::FETCH_ASSOC); // 文字列キーによる配列としてテーブル取得
            $pdo = NULL; // DB切断
        } catch (PDOException $e) {
            $e->getMessage(); // 例外発生時にエラーメッセージを出力
        }

        // 3.3.2 ユーザー情報が取り出せた　かつ　パスワードが一致すれば、セッションに値を代入し、マイページへ遷移
        if ($user && password_verify($input["password"], $user["password"])) {
            // 3.3.2.1 SESSIONに値を格納
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['mail'] = $user['mail'];
            $_SESSION['password'] = $input['password'];

            // 3.3.2.2 「ログイン情報を保持する」にチェックがあれば、セッションにセットする
            if ($_POST['login_keep'] == 1) {
                $_SESSION['login_keep'] = $_POST['login_keep'];
            }

            // 3.3.2.3 「ログイン情報を保持する」にチェックがあればクッキーをセット、なければ削除する。
            if (!empty($_SESSION['id']) && !empty($_SESSION['login_keep'])) {
                setcookie('mail', $_SESSION['mail'], time() + 60 * 60 * 24 * 7);
                setcookie('password', $_SESSION['password'], time() + 60 * 60 * 24 * 7);
                setcookie('login_keep', $_SESSION['login_keep'], time() + 60 * 60 * 24 * 7);
            } else if (empty($_SESSION['login_keep'])) {
                setcookie('mail', '', time() - 1);
                setcookie('password', '', time() - 1);
                setcookie('login_keep', '', time() - 1);
            }

            // 3.3.2.4 board.phpにリダイレクト
            header("Location:board.php");
        } else {
            $errors = "メールアドレスとパスワードを正しく入力してください。";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="style1.css">
        <title>ログインページ</title>
    </head>
    <body>
        <h1 class= "login_title">ログインページ</h1>
        <form action="" method="POST">
            <div class="item">
                <label>メールアドレス</label>
                <input type="text" class="text" size="35" name="mail" value="<?php
                                                                                if($_COOKIE['login_keep'] ?? ""){
                                                                                    echo $_COOKIE['mail'];
                                                                                }
                                                                                ?>">
            </div>
            <div class="item">
                <label>パスワード</label>
                <input type="password" class="text" size="35" name="password" value="<?php
                                                                                    if($_COOKIE['login_keep'] ?? ""){
                                                                                        echo $_COOKIE['password'];
                                                                                    }
                                                                                    ?>">
               
            <?php if (!empty($errors)):?>
              <p class="err_message"><?php echo $errors; ?></p>
            <?php endif; ?>
            </div>
            <div class="login_check">
                <label>
                <input type="checkbox" class="checkbox" name="login_keep" value="1"
                <?php
                    if($_COOKIE['login_keep'] ?? ""){
                        echo "checked='checked'";
                        }
                ?>>ログイン状態を保持する
                </label>
            </div>
            <div class="item">
                <input type="submit" class="submit" value="ログイン">
            </div>
        </form>
        
        
    </body>
</html>

