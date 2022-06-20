<?php
    // 管理ページのパスワード
    define( 'PASSWORD', 'AdPass');

    include 'db_access.php';

    // タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');
    // 変数の初期化(不具合防止)
    $curren_date=null;
    $message=array();
    $message_array=array();
    $success_message=null;
    $error_message=null;
    $pdo=null;
    $stmt=null;
    $res=null;
    $opt=null;

    session_start();

    if(!empty($_GET['logout'])){
        unset($_SESSION['admin_login']);
    }

    // DBに接続
    try{
        $opt = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
        );
        $pdo = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';port='.DB_HOST , DB_USER, DB_PASS, $opt);
    } catch(PDOException $e){
        //接続エラー時にエラー内容を取得
        $error_message[] = $e->getMessage();
    }
    if(!empty($_POST['submit'])){
        //ログイン判定
        if(!empty($_POST['admin_password']) && $_POST['admin_password'] === PASSWORD ) {
            $_SESSION['admin_login'] = true;
        } else {
            $error_message[] = 'ログインに失敗しました。';
        }
    }

    if( empty($error_message)){
        // メッセージを新しい順に取得する
        $sql = "SELECT * FROM message ORDER BY post_date DESC";
        $message_array = $pdo->query($sql);
    }
    // DBとの接続を閉じる
    $pdo = null;

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>掲示板 管理ページ</title>
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
    </head>
    <body>
        <h1>掲示板 管理ページ</h1>
        <?php if(!empty($error_message)): ?>
            <ul class="error_message">
                <?php foreach($error_message as $value): ?>
                    <li>・<?php echo $value; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <section>
            <!-- ログインセッション確認 -->
            <?php if(!empty($_SESSION['admin_login']) && $_SESSION['admin_login'] === true): ?>
    
            <form method="get" action="./download.php">
                <select name="limit">
                    <option value="">全て</option>
                    <option value="10">10件</option>
                    <option value="30">30件</option>
                </select>
                <input type="submit" name="btn_download" value="CSVログダウンロード">
            </form>

            <?php if(!empty($message_array)): ?>
            <?php foreach($message_array as $value): ?>
                <article>
                    <div class="info">
                        <!-- 実際に掲示板に表示される部分 -->
                        <h2><?php echo htmlspecialchars( $value['user_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <!-- [strtotime]で文字列になってる時刻をタイムスタンプ形式に変換 -->
                        <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
                        <p><a href="edit.php?message_id=<?php echo $value['id']; ?>">編集</a>
                        <a href="delete.php?message_id=<?php echo $value['id']; ?>">削除</a></p>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars( $value['message'], ENT_QUOTES, 'UTF-8')); ?></p>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>

                <form method="get" action="">
                    <input type="submit" name="logout" value="ログアウト">
                </form>

                <?php else: ?>

                <form method="post">
                    <div>
                        <label for="admin_password">ログインパスワード</label>
                        <input id="admin_password" type="password" name="admin_password" value="">
                    </div>
                    <input type="submit" name="submit" value="ログイン">
                </form>

                <?php endif; ?>
        </section>
    </body>
</html>