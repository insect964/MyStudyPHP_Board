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

    if(!empty($_GET['logout'])){
        unset($_SESSION['admin_login']);
    }

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

    if(!empty($_POST['name_change'])){
        $pdo->beginTransaction();
        try{
            // 処理
            header("Location: ./change_name.php");
            } catch(Exception $e) {
                // エラー発生時にはロールバック(データが来る前に戻す)する
                $pdo->rollBack();
            }
        } else {
        if(!empty($_POST['password_change'])){
            try{
                // 処理
                
            } catch(Exception $e) {
                // エラー発生時にはロールバック(データが来る前に戻す)する
                $pdo->rollBack();
            }
        }
    }
    $pdo = null;
    
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>マイページ</title>
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
    </head>
    <body>
        <header>
            <h1>マイページ</h1>
            <ul class="board_header">
                <li class="board_header_item">
                    <a href="./index.php">掲示板に戻る</a>
                </li>
                <li class="board_header_item">
                    <a href="./login.php">ログイン</a>
                </li>
            </ul>
        </header>
        <hr>
        <section>
        <form method="post">
            <table>
                <div>
                    <th><h2>ユーザー名変更</h2></th>
                    <td><input type="submit" name="name_change" value="変更"></td>
                </div>
            </table>
            <table>
                <div>
                    <th><h2>パスワード変更</h2></th>
                    <td><input type="submit" name="password_change" value="変更"></td>
                </div>
            </table>
        </form>
        </section>
    </body>
</html>