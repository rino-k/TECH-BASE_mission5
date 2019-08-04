<?php
/*データベースへの接続*/
$dsn = 'データベース名';
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

/*テーブル（名前：tb）を作成
$sql = "CREATE TABLE IF NOT EXISTS tb"
."("
."id INT AUTO_INCREMENT PRIMARY KEY,"
."name char(32),"
."comment TEXT,"
."date char(50),"
."password char(50)"
.");";
$stmt = $pdo->query($sql);*/

/*入力ミス・未記入があった場合のメッセージ一覧*/
$mistake = "パスワードが正しくありません。";
$empty_name = "名前が未記入です。";
$empty_comment = "コメントが未記入です。";
$empty_password = "パスワードが未記入です。";
$empty_erase = "削除する投稿番号が未記入です。";
$empty_edit = "編集する投稿番号が未記入です。";
$number_nonexistent = "対象となる投稿が存在しません。";

/*投稿機能*/
if(!empty($_POST["name_p"]) && !empty($_POST["comment_p"]) && !empty($_POST["password_post"]) && empty($_POST["edit_a"])){
	$date_p = date("Y/m/d H:i:s");

	/*テーブルに，入力フォームから送信された情報を入力する*/
	$sql = $pdo -> prepare("INSERT INTO tb (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
	$sql -> bindParam(':name', $name, PDO::PARAM_STR);
	$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
	$sql -> bindParam(':date', $date, PDO::PARAM_STR);
	$sql -> bindParam(':password', $password, PDO::PARAM_STR);

	$name = $_POST["name_p"];
	$comment = $_POST["comment_p"];
	$date = $date_p;
	$password = $_POST["password_post"];

	$sql -> execute();
	
}

/*削除機能*/
else if(!empty($_POST["erase"]) && !empty($_POST["password_erase"])){

	/*対象となる投稿が存在する場合プログラムを実行．なければエラーメッセージを表示*/
	$sql = 'SELECT count(id) FROM tb';
	$stmt = $pdo->prepare($sql);
	$stmt -> execute();
	$row = $stmt->fetchColumn();

	if(1 <= $_POST["erase"] && $_POST["erase"] <= $row){
		/*配列results_eraseにテーブルのデータを入れる*/
		$sql = 'SELECT * FROM tb';
		$stmt = $pdo->query($sql);
		$results_erase = $stmt->fetchALL();

		/*削除する投稿の正しいパスワードを取得*/
		foreach($results_erase as $value){
			if($value[0] == $_POST["erase"]){
				$password_a = $value[4];
			}
		}

		/*入力されたパスワードが正しい場合，削除機能を実行
		正しくなければエラーメッセージを表示*/
		if(strcmp($_POST["password_erase"], $password_a) == 0){
			$id_erase = 1;

			/*テーブルのデータを全削除*/
			$sql = 'TRUNCATE TABLE tb';
			$stmt = $pdo->query($sql);

			/*データのidが削除対象番号$_POST["erase"]と異なる場合はテーブルに書き込みをおこなう*/
			$sql = $pdo -> prepare("INSERT INTO tb (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
			$sql -> bindParam(':name', $name, PDO::PARAM_STR);
			$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
			$sql -> bindParam(':date', $date, PDO::PARAM_STR);
			$sql -> bindParam(':password', $password, PDO::PARAM_STR);

			foreach($results_erase as $value){
				if($value[0] != $_POST["erase"]){
					$name = $value[1];
					$comment = $value[2];
					$date = $value[3];
					$password = $value[4];

					$sql -> execute();
				}
			}
		}else{
			echo $mistake;
		}

	}else{
		echo $number_nonexistent;
	}
}

/*編集機能（編集番号取得と，編集対象の投稿の名前・コメントを投稿フォームに表示）*/
else if(!empty($_POST["edit"]) && !empty($_POST["password_edit"])){
	$edit_number = $_POST["edit"];

	/*対象となる投稿が存在する場合プログラムを実行．なければエラーメッセージを表示*/
	$sql = 'SELECT count(id) FROM tb';
	$stmt = $pdo->prepare($sql);
	$stmt -> execute();
	$row = $stmt->fetchColumn();

	if(1 <= $edit_number && $edit_number <= $row){

		/*配列results_editにテーブルのデータを入れる*/
		$sql = 'SELECT * FROM tb';
		$stmt = $pdo->query($sql);
		$results_edit = $stmt->fetchALL();

		/*編集する投稿の正しいパスワードを取得*/
		foreach($results_edit as $value){
			if($value[0] == $edit_number){
				$password_a = $value[4];
			}
		}

		/*入力されたパスワードが正しい場合，編集機能を実行
		正しくなければエラーメッセージを表示*/
		if(strcmp($_POST["password_edit"], $password_a) == 0){

			/*データのidが編集対象番号$_POST["edit"]と等しい場合，
			編集対象の投稿の名前・コメントを変数に代入し，投稿フォームに表示*/
			foreach($results_edit as $value){
				if($value[0] == $edit_number){
					$name_edit = $value[1];
					$comment_edit = $value[2];
				}
			}
		}else{
			echo $mistake;
		}

	}else{
		echo $number_nonexistent;
	}
}

/*編集機能（テーブルのデータを投稿フォームから送信された内容に書き換え）*/
else if(!empty($_POST["name_p"]) && !empty($_POST["comment_p"]) && !empty($_POST["password_post"]) && !empty($_POST["edit_a"])){
	$date_p = date("Y/m/d H:i:s");

	/*配列results_editにテーブルのデータを入れる*/
	$sql = 'SELECT * FROM tb';
	$stmt = $pdo->query($sql);
	$results_edit = $stmt->fetchALL();

	/*テーブルのデータを全削除*/
	$sql = 'TRUNCATE TABLE tb';
	$stmt = $pdo->query($sql);

	/*idが編集対象番号と等しいデータは，
	投稿フォームから送信された内容に書き換え,
	等しくない場合はそのまま書き写す*/
	$sql = $pdo -> prepare("INSERT INTO tb (name, comment, date, password) VALUES (:name, :comment, :date, :password)");
	$sql -> bindParam(':name', $name, PDO::PARAM_STR);
	$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
	$sql -> bindParam(':date', $date, PDO::PARAM_STR);
	$sql -> bindParam(':password', $password, PDO::PARAM_STR);

	foreach($results_edit as $value){
		if($value[0] == $_POST["edit_a"]){
			$name = $_POST["name_p"];
			$comment = $_POST["comment_p"];
			$date = $date_p;
			$password = $_POST["password_post"];

			$sql -> execute();
		}else{
			$name = $value[1];
			$comment = $value[2];
			$date = $value[3];
			$password = $value[4];

			$sql -> execute();
			
		}
	}

}
?>

<html>
<form method = "POST" action = "mission_5-1.php">

【　投稿フォーム　】
<br>
<!--名前-->
<input type = "text" name = "name_p" size = "50" value = "<?php if(!empty($_POST["edit"]) && !empty($_POST["password_edit"])){if(1 <= $edit_number && $edit_number <= $row){if(strcmp($_POST["password_edit"], $password_a) == 0){echo $name_edit;}}} ?>" placeholder = "名前">
<br>
<!--コメント-->
<input type = "text" name = "comment_p" size = "50" value = "<?php if(!empty($_POST["edit"]) && !empty($_POST["password_edit"])){if(1 <= $edit_number && $edit_number <= $row){if(strcmp($_POST["password_edit"], $password_a) == 0){echo $comment_edit;}}} ?>" placeholder = "コメント">
<br>
<!--パスワード-->
<input type = "password" name = "password_post" size = "50" value = "" placeholder = "パスワード">
<br>
<!--編集する番号 表示しない-->
<input type = "hidden" name = "edit_a" size = "50" value = "<?php if(!empty($_POST["edit"])){echo $edit_number;} ?>"/>
<input type = "submit" name = "submit_post" value = "送信"/>
<br>
<br>

【　削除フォーム　】
<br>
<!--削除対象番号-->
<input type = "text" name = "erase" value = "" size = "12" placeholder = "削除対象番号">
<br>
<!--パスワード-->
<input type = "password" name = "password_erase" value = "" size = "50" placeholder = "パスワード">
<br>
<input type = "submit" name = "submit_erase" value = "削除"/>
<br>
<br>

【　編集フォーム　】
<br>
<!--編集対象番号-->
<input type = "text" name = "edit" value = "" size = "12" placeholder = "編集対象番号">
<br>
<!--パスワード-->
<input type = "password" name = "password_edit" value = "" size = "50" placeholder = "パスワード">
<br>
<input type = "submit" name = "submit_edit" value = "編集"/>
</form>
</html>

<?php
/*空欄があった場合メッセージを表示する*/

/*投稿フォームに空欄があった場合*/
if(isset($_POST["submit_post"])){
	if(empty($_POST["name_p"])){
		echo $empty_name."<br>";
	}
	if(empty($_POST["comment_p"])){
		echo $empty_comment."<br>";
	}
	if(empty($_POST["password_post"])){
		echo $empty_password."<br>";
	}
}

/*削除フォームに空欄があった場合*/
if(isset($_POST["submit_erase"])){
	if(empty($_POST["erase"])){
		echo $empty_erase."<br>";
	}
	if(empty($_POST["password_erase"])){
		echo $empty_password."<br>";
	}
}

/*編集フォームに空欄があった場合*/
if(isset($_POST["submit_edit"])){
	if(empty($_POST["edit"])){
		echo $empty_edit."<br>";
	}
	if(empty($_POST["password_edit"])){
		echo $empty_password."<br>";
	}
}
?>

<!-- 投稿一覧を表示する区切り線と見出し -->
<html>
<p>
</p>
<hr>
【　投稿一覧　】
<br>
<br>
</html>

<?php
/*投稿フォーム下にテーブルのデータを表示する*/
$sql = 'SELECT * FROM tb';
$stmt = $pdo->query($sql);
$results = $stmt->fetchALL();

foreach($results as $value){
	for($i = 0 ; $i < 4 ; $i++ ){
		echo $value[$i]." ";
	}
	echo"<br>";
}
?>
