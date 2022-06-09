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
        // 空白除去
        $user_name = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['user_name']);
        $permission = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['permission']);
        $hash_before = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['hash_before']);
        // 表示名の入力チェック
        if(empty($user_name)){
            $error_message[]='投稿者名を入力してください';
        }

        // メッセージの入力チェック
        if(empty($permission)){
            $error_message[]='権限を選択してください';
        }
        // パスワードのハッシュ化
        if(empty($hash_before)){
            $error_message[]='パスワードが入力されていません';
        }else{
            $password = password_hash($hash_before,PASSWORD_DEFAULT);
        }

        if(empty($error_message)){

            // ファイルのアップロード
            // トランザクション開始
            $pdo->beginTransaction();
            try{
            // SQL作成
            $stmt = $pdo->prepare("INSERT INTO user_list ( user_name, permission, password, hash) VALUES ( :user_name, :permission, :password, :hash)");
            // 値をセット
            $stmt->bindParam(':user_name',$user_name, PDO::PARAM_STR);
            $stmt->bindParam(':permission',$permission, PDO::PARAM_STR);
            $stmt->bindParam(':password',$hash_before, PDO::PARAM_STR);
            $stmt->bindParam(':hash',$password,PDO::PARAM_STR);
            // SQLクエリの実行
            $res = $stmt->execute();
            // コミット,PDOで登録したデータをDBに反映
            $res = $pdo->commit();
            } catch(Exception $e) {
                // エラー発生時にはロールバック(データが来る前に戻す)する
                $pdo->rollBack();
            }

            if($res){
                $_SESSION['success_message'] = 'ユーザー作成完了(´ ∀ `)';
            } else {
                $error_message[] = 'ユーザー作成失敗(´・ω・`)';
            }
            // プリペアードステートメントを削除
            $stmt = null;

            header('Location: ./');
            exit;
        }
    }

    if( empty($error_message)){
        // メッセージを新しい順に取得する
        $sql = "SELECT user_name,permission,password,hash FROM user_list ORDER BY id ASC";
        $message_array = $pdo->query($sql);
    }
    // DBとの接続を閉じる
    $pdo = null;

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ユーザー一覧</title>
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
    </head>
    <body>
        <h1>ユーザー認証</h1>
        <?php if( empty($_POST['submit']) && !empty($_SESSION['success_message'])): ?>
            <p class="success_message"><?php echo htmlspecialchars( $_SESSION['success_message'],
            ENT_QUOTES,'UTF-8'); ?></p>
                <?php unset($_SESSION['success_message']) ?>
        <?php endif; ?>
        <?php if(!empty($error_message)): ?>
            <ul class="error_message">
                <?php foreach($error_message as $value): ?>
                    <li>・<?php echo $value; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="post">
            <div>
                <Label for="user_name">ユーザー名</Label>
                <input type="text" id="user_name" name="user_name" value="" placeholder="葵屋太郎">
            </div>
            <div>
                <label for="permission">内容</label>
                <select name="permission" id="permission">
                    <option value="admin">管理者</option>
                    <option value="general">一般</option>
                </select>
            </div>
            <div>
                <label for="password">パスワード</label>
                <input type="password" id="password" name="hash_before" minlength="8" required>
            </div>
            <input type="submit" name="submit" value="送信">
        </form>
        <hr>
        <section>
            <?php if(!empty($message_array)): ?>
            <?php foreach($message_array as $value): ?>
                <article>
                    <div class="info">
                        <!-- 実際に掲示板に表示される部分 -->
                        <?php echo htmlspecialchars( $value['user_name'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo htmlspecialchars( $value['permission'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo htmlspecialchars( $value['password'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo htmlspecialchars( $value['hash'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php if(password_verify($value['password'],$value['hash'])){
                            echo 'Password is True';
                        } else {
                            echo 'Password is False';  
                        }
                        ?>
                    </div>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>
        </section>
    </body>
</html>