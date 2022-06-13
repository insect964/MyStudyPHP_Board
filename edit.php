<?php
    // DBの接続情報
    define( 'DB_PORT', '8889');
    define( 'DB_USER', 'root');
    define( 'DB_PASS', 'root');
    define( 'DB_NAME', 'board');

    ini_set("display_errors", 1);
    error_reporting(E_ALL);

    // タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');

    // 変数の初期化(不具合防止)
    $user_name = null;
    $message = array();
    $message_data = null;
    $error_message = array();
    $pdo = null;
    $stmt = null;
    $res = null;
    $opt = null;
    
    session_start();

    // 管理者としてログインしているか確認
    if( empty($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
        // ログインページへリダイレクト
        header("Location: ./admin.php");
        exit;
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

    if( !empty($_GET['message_id']) && empty($_POST['message_id'])){
        // SQL作成
        $stmt = $pdo->prepare("SELECT * FROM message WHERE id = :id");

        // 値をセット
        $stmt->bindValue(':id', $_GET['message_id'], PDO::PARAM_INT);

        // SQLクエリの実行
        $stmt->execute();

        // 表示するデータを取得
        $message_data = $stmt->fetch();

        // 投稿データが取得できないときは管理ページに戻る
        if( empty($message_data)){
            header("Location: ./admin.php");
            exit;
        }
    } elseif( !empty($_POST['message_id'])){
        // 空白除去
        $user_name = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '',
        $_POST['user_name']);
        $message = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '',
        $_POST['message']);

        // ユーザー名の入力チェック
        if( empty($user_name)){
            $error_message[] = 'ユーザー名を入力してください';
        }
        // 内容の入力チェック
        if( empty($message)){
            $error_message[] = '内容を入力してください';
        } else {
            if( 500 < mb_strlen($message, 'UTF-8')){
                $error_message[] = '内容は500文字以内にしてください。';
            }
        }

        if( empty($error_message)){
            // トランザクション開始
            $pdo->beginTransaction;

            try{
                //SQL作成
                $stmt = $pdo->prepare("UPDATE message SET user_name = :user_name, 
                message = :message WHERE id = :id");

                //値をセット
                $stmt->bindParam('user_name',$user_name,PDO::PARAM_STR);
                $stmt->bindParam('message',$message,PDO::PARAM_STR);
                $stmt->bindValue(':id',$_POST['message'],PDO::PARAM_INT);

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
                header("Location: ./admin.php");
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
        <title>掲示板(編集)</title>
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
    </head>
    <body>
        <h1>社員用雑談掲示板(編集)</h1>
        <?php if(!empty($error_message)): ?>
            <ul class="error_message">
                <?php foreach($error_message as $value): ?>
                    <li>・<?php echo $value; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="post">
            <div>
                <Label for="user_name">投稿者名</Label>
                <input type="text" id="user_name" name="user_name" value="<?php
                if(!empty($message_data['user_name'])){ echo $message_data['user_name']; } 
                elseif(!empty($user_name)) { echo htmlspecialchars($user_name,ENT_QUOTES,'UTF-8');} ?>" placeholder="葵屋太郎">
            </div>
            <div>
                <label for="message">内容</label>
                <textarea name="message" id="message"><?php if(!empty($message_data['message']))
                { echo $message_data['message']; }
                elseif(!empty($message)) { echo htmlspecialchars($message,ENT_QUOTES,'UTF-8');} ?></textarea>
            </div>
            <a class="btn_cancel" href="admin.php">キャンセル</a>
            <input type="submit" name="submit" value="更新">
            <input type="hidden" name="message_id" value="<?php if(!empty($message_data['id']))
            { echo $message_data['id'];}
            elseif(!empty($_POST['message_id'])) { echo htmlspecialchars($_POST['message_id'],ENT_QUOTES,'UTF-8');} ?>">
        </form>
    </body>
</html>