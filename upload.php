<?php
    move_uploaded_file($_FILES["upfile"]["tmp_name"],"../spider_NBSA/".$_FILES["upfile"]["name"]);
    $servername = "localhost";
    $username = "root";
    $password = "1LCpfvccGRaJ8630";
    $null ="";
    try{

      $db = new PDO('mysql:host=localhost;dbname=library', $username, $password,
      #編碼
      array(pdo::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
    //   #偵測錯誤訊息
      // $db ->setAttribute(pdo::ATTR_ERRMODE,pdo::ERRMODE_EXCEPTION);
      // Read the data 利用fopen功能讀取檔案
      $file = fopen($_FILES["upfile"]["name"],"r");
        while(! feof($file))
          {
          $data = fgetcsv($file);          
            if($data[0] != null){
              $sql = 'INSERT INTO `mbsa`(`ID`, `Name`, `Title`, `Attribute:Name`, `BulletinID`, `KBID`, `IsInstalled`, `Severity`, `name_sapider`) VALUES ("'.$null . '", "' . $data[0] . '", "' . $data[1].'", "' . $data[2].'", "' . $data[3].'", "' . $data[4].'", "' . $data[5].'", "' . $data[6].'", "' . $null.'")';
              $result = $db ->query($sql); 
              $result->fetch();
            }
          }
      fclose($file);
      }catch(PDOEXCEPTION $ex){ //例外管理錯誤偵測
          echo $ex;
      }
    header("location:http://localhost/www/spider_NBSA/MBSA_Download.php");
?>