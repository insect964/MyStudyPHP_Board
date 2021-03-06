<?php
    include 'db_access.php';
    /*
    ini_set("display_errors", 1);
    error_reporting(E_ALL);
    */

    // タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');

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

    if( !empty($_GET['list_id']) && empty($_POST['list_id'])){
        // SQL作成
        $stmt = $pdo->prepare("SELECT * FROM user_list WHERE id = :id");

        // 値をセット
        $stmt->bindValue(':id', $_GET['list_id'], PDO::PARAM_INT);

        // SQLクエリの実行
        $stmt->execute();

        // 表示するデータを取得
        $name_data = $stmt->fetch();

        // 投稿データが取得できないときは管理ページに戻る
        if( empty($name_data)){
            header("Location: ./admin.php");
            exit;
        }
    } elseif( !empty($_POST['list_id'])){
        // 空白除去
        $user_name = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '',
        $_POST['user_name']);

        // ユーザー名の入力チェック
        if( empty($user_name)){
            $error_message[] = 'ユーザー名を入力してください';
        }

        if( empty($error_message)){
            // トランザクション開始
            $pdo->beginTransaction;

            try{
                //SQL作成
                $stmt = $pdo->prepare("UPDATE user_list SET user_name = :user_name WHERE id = :id");

                //値をセット
                $stmt->bindParam('user_name',$user_name,PDO::PARAM_STR);
                $stmt->bindValue(':id',$_POST['list_id'],PDO::PARAM_INT);

                //SQLクエリの実行
                $stmt->execute();

                //コミット
                $res = $pdo->commit();

            } catch(Exception $e) {
                // エラー発生時にはロルバ
                $pdo->rollBack();
            }

            //更新に成功したら一覧に
            if($res){
                header("Location: ./mypage.php");
                exit;
            }
        }
    }

    // DBとの接続を閉じる
    $stmt = null;
    $pdo = null;
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>掲示板(パスワード編集)</title>
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
    </head>
    <body>
        <h1>社員用雑談掲示板(パスワード編集)</h1>
        <form method="post">
            <div>
                <Label for="password">新しいパスワード</Label>
                <input type="text" id="password" name="password" value="<?php
                if(!empty($pass_data['password'])){ echo $pass_data['password']; } 
                elseif(!empty($password)) { echo htmlspecialchars($password,ENT_QUOTES,'UTF-8');} ?>" placeholder="葵屋太郎">
            </div>
            <a class="btn_cancel" href="mypage.php">キャンセル</a>
            <input type="submit" name="submit" value="更新">
            <input type="hidden" name="list_id" value="<?php if(!empty($pass_data['id']))
            { echo $pass_data['id'];}
            elseif(!empty($_POST['list_id'])) { echo htmlspecialchars($_POST['list_id'],ENT_QUOTES,'UTF-8');} ?>">
        </form>
    </body>
</html>