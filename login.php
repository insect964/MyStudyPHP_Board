<?php
    // DBの接続情報
    define( 'DB_PORT', '8889');
    define( 'DB_USER', 'root');
    define( 'DB_PASS', 'root');
    define( 'DB_NAME', 'board');

    /*
    ini_set("display_errors", 1);
    error_reporting(E_ALL);
    */

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

    session_start();

    // DBに接続
    try{
        $opt = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
        );
        $pdo = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';port='.DB_PORT , DB_USER, DB_PASS, $opt);
    } catch(PDOException $e){
        //接続エラー時にエラー内容を取得
        $error_message[] = $e->getMessage();
    }
    if(!empty($_POST['submit'])){


        // sql
        $stmt = $pdo->prepare('SELECT * FROM user_list WHERE user_name = :user_name');
        // 実行
        $stmt->execute(array(':user_name' => $_POST['user_name']));
        // 実行結果(fetchはテーブルのレコードを取得)
        // fetchだと問題がなく、fetchAllだとエラーになりNULLが出てくる。要勉強。
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // 認証
        if(password_verify($_POST['password'], $result['hash'])){
            // echo "ログイン成功";
            header("Location: ./mypage.php");
        } else {
            echo "ログイン失敗";
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>掲示板ログインページ</title>
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
    </head>
    <body>
        <header>
            <h1>ログイン</h1>
            <ul class="board_header">
                <li class="board_header_item">
                    <a href="./index.php">掲示板に戻る</a>
                </li>
            </ul>
        </header>
        <hr>
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