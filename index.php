<?php
    define('FILENAME', './message.txt');
    date_default_timezone_set('Asia/Tokyo');
    // 変数の初期化(不具合防止)
    $curren_date=null;
    $data=null;
    $file_handle=null;
    $split_data=null;
    $message=array();
    $message_array=array();
    $success_message=null;
    $error_message=null;
    $sanitize=array();
    $pdo=null;
    $stmt=null;
    $res=null;
    $opt=null;

    // DBに接続
    try{
        $opt = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
        );
        $pdo = new PDO('mysql:charset=UTF8;dbname=board;port=8889','root','root',$opt);
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
        }

        if(empty($message)){
            $error_message[]='内容を入力してください';
        }

        if(empty($error_message)){
            /*
            //var_dump($_POST);
            //FILENAMEに設定したtxtファイルを開く
            if($file_handle=fopen(FILENAME,"a")){
                // 書き込み時間取得
                $current_date=date("Y-m-d H:i:s");
                // 書き込むデータ作成
                // '名前','書き込み内容','書き込み時間'
                $data = "'".$sanitize['user_name']."','".$sanitize['message']."','".$current_date."'\n";
                // 書き込み
                fwrite($file_handle,$data);
                // ファイルを閉じる
                fclose($file_handle);
                // 書きこんだら表示される文章
                $success_message = '書き込み完了(´ ∀ `)';
            }
            */
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
                // エラー発生時にはロールバックする
                $pdo->rollBack();
            }

            if($res){
                $success_message = '書き込み完了(´ ∀ `)';
            } else {
                $error_message[] = '書き込み失敗(´・ω・`)';
            }
            // プリペアードステートメントを削除
            $stmt = null;
        }
    }
    // DBとの接続を閉じる
    $pdo = null;

    if($file_handle=fopen(FILENAME,'r')){
        while($data=fgets($file_handle)){
            // echo $data."<br>";
            $split_data=preg_split('/\'/',$data);
            $message=array(
                'user_name'=>$split_data[1],
                'message'=>$split_data[3],
                'post_date'=>$split_data[5]
            );
            array_unshift($message_array,$message);
        }
        //ファイル閉じる
        fclose($file_handle);
    }
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
        <?php if(!empty($success_message)): ?>
            <p class="success_message"><?php echo $success_message; ?></p>
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
                <input type="text" id="user_name" name="user_name" placeholder="葵屋太郎">
            </div>
            <div>
                <label for="message">内容</label>
                <textarea name="message" id="message"></textarea>
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
                        <h2><?php echo $value['user_name']; ?></h2>
                        <!-- [strtotime]で文字列になってる時刻をタイムスタンプ形式に変換 -->
                        <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
                    </div>
                    <p><?php echo $value['message']; ?></p>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>
        </section>
    </body>
</html>