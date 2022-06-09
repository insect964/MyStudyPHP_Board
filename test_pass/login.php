<?php
    // DBの接続情報
    define( 'DB_PORT', '8889');
    define( 'DB_USER', 'root');
    define( 'DB_PASS', 'root');
    define( 'DB_NAME', 'test');

    ini_set("display_errors", 1);
    error_reporting(E_ALL);

    // タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');
    // 変数の初期化(不具合防止)
    $user_name=null;
    $permission=null;
    $password=null;
    $hash_before=null;
    $hash_after=null;
    $message=array();
    $message_array=array();
    $filename=array();
    $upload_file=null;
    $error_message=null;
    $pdo=null;
    $stmt=null;
    $res=null;
    $upload_res=null;
    $opt=null;

    // DBに接続
    try{
        $opt = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
        );
    } catch(PDOException $e){
        //接続エラー時にエラー内容を取得
        $error_message[] = $e->getMessage();
    }
    if(!empty($_POST['submit'])){
        $pdo = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';port='.DB_PORT , DB_USER, DB_PASS, $opt);
        // sql
        $stmt = $pdo->prepare('SELECT * FROM user_list WHERE user_name = :user_name');
        // 実行
        $stmt->execute(array(':user_name' => $_POST['user_name']));
        // 実行結果(fetchAllはテーブルのレコードを全取得)
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 認証
        if(password_verify($_POST['password'], $result['hash'])){
            echo "ログイン成功";
        } else {
            echo "ログイン失敗";
            var_dump($result['hash']);
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>ログインテストページ</title>
    </head>
    <body>
        <h1>ログインテスト</h1>
        <form method="post">
        <div>
            <label>ユーザー名</label>
            <input type="text" name="user_name">
        </div>
        <div>
            <label>パスワード</label>
            <input type="password" name="password">
        </div>
        <input type="submit" name="submit" value="ログイン">
        </form>
    </body>
</html>