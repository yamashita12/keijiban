<?php

session_start();
mb_internal_encoding("utf8");

// 1.ログインしていなければ、ログインページにリダイレクト
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
}

// 2.変数の初期化
$errors = array();

// 3.POSTアクセス時の処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 3.1.エスケープ処理
    $input["title"] = htmlentities($_POST["title"] ?? "", ENT_QUOTES);
    $input["comments"] = htmlentities($_POST["comments"] ?? "", ENT_QUOTES);

    // 3.2.バリデーションチェック
    if (strlen(trim($input["title"] ?? "")) == 0) {  //入力されているかの確認
        $errors["title"] = "タイトルを入力してください。";
    }
    if (strlen(trim($input["comments"] ?? "")) == 0) {  //入力されているかの確認
        $errors["comments"] = "コメントを入力してください。";
    }

    // 3.3. エラーが無ければ、DBに接続し投稿内容を格納
    if (empty($errors)) {
        try {
            $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;", "root", "root"); // DBに接続
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // エラーモードを「警告」に設定
            $stmt = $pdo->prepare(" INSERT INTO post(user_id,title,comments) VALUES(?,?,?) ");
            $stmt->execute(array($_SESSION["id"], $input["title"], $input["comments"]));
            $pdo = NULL; // DB切断
        } catch (PDOException $e) {
            $e->getMessage(); // 例外発生時にエラーメッセージを出力
        }
    }
}

// 4.GETアクセス時の処理（DBに接続し、投稿内容を取り出す）
try {
    $pdo = new PDO("mysql:dbname=php_jissen;host=localhost;", "root", "root"); // DBに接続
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // エラーモードを「警告」に設定
    $posts = $pdo->query(" SELECT title,comments,name,posted_at FROM post INNER JOIN user ON post.user_id = user.id ORDER BY posted_at DESC");
    $pdo = NULL; // DB切断
} catch (PDOException $e) {
    $e->getMessage(); // 例外発生時にエラーメッセージを出力
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel = "stylesheet" type="text/css" href="style2.css">
    <title>4eachblog</title>
</head>
<body>
    <div class="title">
        <div class="title_img">
            <img src="4eachblog_logo.jpg" alt="">
        </div>
        <div class="logout">
            <div class="logout_name">こんにちは<?php echo $_SESSION["name"]?>さん</div>
            <form action="logout.php" class="logout_submit">
                <input type="submit" class="submit_title" value="ログアウト">
            </form>
        </div>
    </div>
    <header id="header">
      <ul style="list-style: none;">
        <li>トップ</li>
        <li>プロフィール</li>
        <li>4eachについて</li>
        <li>登録フォーム</li>
        <li>問い合わせ</li>
        <li>その他</li>
      </ul>
    </header>

    <main>
        <div id="left">
            <!--左部分-->
            <div class= "main_title">
                <h1>プログラミングに役立つ掲示板</h1>
            </div>

            <form class="form_wapper" action="" method="POST">
                <div class="form_title">
                    <p>入力フォーム</p>
                </div>
                <div class="form_item">
                    <label>タイトル</label>
                    <input type="text" size="" name="title" class="text">
                    <?php if(!empty($errors)):?>
                        <p class="err_message"><?php echo $errors["title"] ?? ''?></p>
                    <?php endif; ?>

                </div>
                
                <div class="form_item">
                    <label>コメント</label>
                    <textarea name="comments" class="textarea"></textarea>
                    <?php if(!empty($errors)):?>
                        <p class="err_message"><?php echo $errors["comments"] ?? ''?></p>
                    <?php endif; ?>
                </div>

                <div class="submit_item">
                    <input type="submit" class="submit" value="送信する">
                </div>
            </form>

            <?php foreach ($posts as $post) :?>
                <div class="kiji">
                    <h3><?php echo $post["title"] ?></h3>
                    <div class="contens"><?php echo$post["comments"]?></div>
                    <div class="handlename">投稿者：<?php echo$post["name"]?></div>
                    <div class="time">投稿時間：
                        <?php
                            //日付フォーマット
                            $posted_at = new Datetime($post["posted_at"]);
                            echo $posted_at->format('Y年m月d日 H:i')
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="right">
            <!--右部分-->
            <div class="contens">
                <div class="contens_box">
                    <div class="contens_title">
                        <p>人気の記事</p>
                    </div>
                    <div class="contens_list">
                        <ul style="list-style: none;">
                        <li>PHPオススメ</li>
                        <li>PHP myAdminの使い方</li>
                        <li>今人気のエディタTop5</li>
                        <li>HTMLの基礎</li>
                        </ul>
                    </div>
                </div>
                <div class="contens_box">
                    <div class="contens_title">
                        <p>オススメリンク</p>
                    </div>
                    <div class="contens_list">
                        <ul style="list-style: none;">
                        <li>インターノウス株式会社</li>
                        <li>XAMPPのダウンロード</li>
                        <li>Eclipseのダウンロード</li>
                        <li>Bracketsのダウンロード</li>
                        </ul>
                    </div>
                </div>
                <div class="contens_box">
                    <div class="contens_title">
                        <p>カテゴリ</p>
                    </div>
                    <div class="contens_list">
                        <ul style="list-style: none;">
                        <li>HTML</li>
                        <li>PHP</li>
                        <li>MySQL</li>
                        <li>Javascript</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    


</body>
</html>