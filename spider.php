<?php
#初始化
include("LIB_http.php");                        // http 庫
include("LIB_parse.php");                       // 解析庫
include("LIB_resolve_addresses.php");           // 地址分析庫	
include("LIB_exclusion_list.php");              // 排除的關鍵字
include("LIB_simple_spider.php");               // spider routines used by this app.
include("LIB_download_images.php");      				//蜘蛛程序都會用到這個應用程序
#讀取資料庫項目數量
$KBID_count = numberKBID();
for ($i=1; $i <= $KBID_count[0]; $i++){
    #讀取KBID進行資料網路收尋
    $ID = KBIDdata($i);
    set_time_limit(360000);                          // 不讓php超時
    $SEED_URL        = "https://www.catalog.update.microsoft.com/Search.aspx?q=KB".$ID[0];    // First URL spider downloads首先URL蜘蛛下載
    
    $MAX_PENETRATION = 1;                           // Set spider penetration depth設置珠珠的穿透深度
    $FETCH_DELAY     = 2;                           // 等待一秒之間頁面獲取
    $ALLOW_OFFISTE   = false;                        // 不要讓蜘蛛漫遊
    $spider_array    = array();
    # Get links from $SEED_URL獲得鏈結

    $temp_link_array = harvest_links($SEED_URL);
    $spider_array = archive_links($spider_array, 0 , $temp_link_array);
    // Spider links in remaining penetration levels蜘蛛鏈結在剩餘的普及水平
    for($penetration_level=1; $penetration_level<=$MAX_PENETRATION; $penetration_level++){
        $previous_level = $penetration_level - 1;
        for($xx=0; $xx<count($spider_array[$previous_level]); $xx++){
            unset($temp_link_array);
            $temp_link_array = harvest_links($spider_array[$previous_level][$xx]);
            count($spider_array[$previous_level]); 
            $spider_array = archive_links($spider_array, $penetration_level, $temp_link_array);
        }
    }
} 
header("location:http://localhost/www/spider_NBSA/MBSA_Download.php");
function numberKBID(){
    $servername = "localhost";
    $username = "root";
    $password = "1LCpfvccGRaJ8630";

    try{

     $db = new PDO('mysql:host=localhost;dbname=library', $username, $password,
      #編碼
      array(pdo::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
     #偵測錯誤訊息
     $db ->setAttribute(pdo::ATTR_ERRMODE,pdo::ERRMODE_EXCEPTION);
     $sql = "SELECT count(`ID`) FROM `mbsa`";
     // print_r($sql);
     $point = $db ->query($sql); 
     while($information=$point->fetch()){
      // print_r($information);
        return $information;
     }
    }catch(PDOEXCEPTION $ex){ //例外管理錯誤偵測
      echo $ex;
    }
}
function KBIDdata($data){
    $servername = "localhost";
    $username = "root";
    $password = "1LCpfvccGRaJ8630";

    try{

     $db = new PDO('mysql:host=localhost;dbname=library', $username, $password,
      #編碼
      array(pdo::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
     #偵測錯誤訊息
     $db ->setAttribute(pdo::ATTR_ERRMODE,pdo::ERRMODE_EXCEPTION);
     $sql = "SELECT `KBID` FROM `mbsa` WHERE `ID` LIKE $data";
     // print_r($sql);
     $point = $db ->query($sql); 
     while($information=$point->fetch()){
      // print_r($information);
        return $information;
     }
    }catch(PDOEXCEPTION $ex){ //例外管理錯誤偵測
      echo $ex;
    }
}
?>