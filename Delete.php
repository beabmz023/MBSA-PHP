<meta charset="utf-8">

<?php
	//資料庫登入功能頁面
	$servername = "localhost";
    $username = "root";
    $password = "1LCpfvccGRaJ8630";
    try{

     $db = new PDO('mysql:host=localhost;dbname=library', $username, $password,
      #編碼
      array(pdo::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
     #偵測錯誤訊息
     $db ->setAttribute(pdo::ATTR_ERRMODE,pdo::ERRMODE_EXCEPTION);
     $sql = "TRUNCATE `library`.`mbsa`";
     // print_r($sql);
     $point = $db ->query($sql); 
     while($information=$point->fetch()){
      // print_r($information);
        return $information;
     }
    }catch(PDOEXCEPTION $ex){ //例外管理錯誤偵測
      echo $ex;
    }
    header("location:http://localhost/www/spider_NBSA/MBSA_Download.php");
			
?>