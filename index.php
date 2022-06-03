<?php
    // DBの接続情報
    define( 'DB_PORT', '8889');
    define( 'DB_USER', 'root');
    define( 'DB_PASS', 'root');
    define( 'DB_NAME', 'board');

    // タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');
    // 変数の初期化(不具合防止)
    $user_name = null;
    $curren_date=null;
    $message=array();
    $message_array=array();
    $error_message=null;
    $pdo=null;
    $stmt=null;
    $res=null;
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
        $message = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['message']);
        // 表示名の入力チェック
        if(empty($user_name)){
            $error_message[]='投稿者名を入力してください';
        } else {
            // セッションに表示名を保存
            $_SESSION['user_name'] = $user_name;
        }

        // メッセージの入力チェック
        if(empty($message)){
            $error_message[]='内容を入力してください';
        } else {
            // 文字数確認
            if( 500 < mb_strlen($message, 'UTF-8')){
                $error_message[] = '内容は500文字以内にしてください。';
            }
        }

        if(empty($error_message)){

            // 書き込み日時取得
            $current_date = date("Y-m-d H:i:s");
            // トランザクション開始
            $pdo->beginTransaction();
            try{
            // SQL作成
            $stmt = $pdo->prepare("INSERT INTO message ( user_name, message, post_date) VALUES ( :user_name, :message, :current_date)");
            // 値をセット
            $stmt->bindParam(':user_name',$user_name, PDO::PARAM_STR);
            $stmt->bindParam(':message',$message, PDO::PARAM_STR);
            $stmt->bindParam(':current_date',$current_date, PDO::PARAM_STR);
            // SQLクエリの実行
            $res = $stmt->execute();
            /* 勉強備忘録
            * user_nameは元々user-nameで作っていたのだが
            * このexecute()をすると
            * Invalid parameter number: parameter was not defined というエラーが発生した
            * 内容は「パラメータがないよ(意訳)」なのでbindParam周りだろうと予測
            * タイポかな？と思うもタイポ無し、原因がわからなかったが……
            * テーブル名にはハイフンが使えない(バッククォートを使えば使えるが)というのが原因だった
            * このためCtrl+Aからの置換でuser_nameに変更し、phpMyadminのテーブルもuser_nameに変更したところ
            * 無事DBの方に記録させることができた。
            */
            // コミット,PDOで登録したデータをDBに反映
            $res = $pdo->commit();
            } catch(Exception $e) {
                // エラー発生時にはロールバック(データが来る前に戻す)する
                $pdo->rollBack();
            }

            if($res){
                $_SESSION['success_message'] = '書き込み完了(´ ∀ `)';
            } else {
                $error_message[] = '書き込み失敗(´・ω・`)';
            }
            // プリペアードステートメントを削除
            $stmt = null;

            header('Location: ./');
            exit;
        }
    }

    if( empty($error_message)){
        // メッセージを新しい順に取得する
        $sql = "SELECT user_name,message,post_date FROM message ORDER BY post_date DESC";
        $message_array = $pdo->query($sql);
    }
    // DBとの接続を閉じる
    $pdo = null;

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>掲示板</title>
        <link rel="stylesheet" type="text/css" href="stylesheet.css">
    </head>
    <body>
        <h1>社員用雑談掲示板</h1>
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
                <Label for="user_name">投稿者名</Label>
                <input type="text" id="user_name" name="user_name" value="<?php 
                    if( !empty($_SESSION['user_name']) ){echo htmlspecialchars($_SESSION['user_name'],ENT_QUOTES,'UTF-8'); } ?>"
                    placeholder="葵屋太郎">
            </div>
            <div>
                <label for="message">内容</label>
                <textarea name="message" id="message"><?php if(!empty($message)){
                    echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
                } ?></textarea>
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
                        <h2><?php echo htmlspecialchars( $value['user_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <!-- [strtotime]で文字列になってる時刻をタイムスタンプ形式に変換 -->
                        <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars( $value['message'], ENT_QUOTES, 'UTF-8')); ?></p>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>
        </section>
    </body>
</html>