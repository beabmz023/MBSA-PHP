<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php
 session_start();
 // error_reporting(0);
function harvest_links($url){
    $kBID = "";
    $kBID = str_replace("https://www.catalog.update.microsoft.com/Search.aspx?q=KB", "", $url);
    // print_r($kBID);
    # Initialize 初始化
    global $FETCH_DELAY;
    $link_array = array();
    
    # Get page base for $url  獲取頁面的基礎：
    $page_base = get_base_page_address($url);

    # Download webpage 下載網頁
    //sleep($FETCH_DELAY);
    $downloaded_page = http_get($url, "");
    // print_r($downloaded_page['FILE']);
    $test = return_between($downloaded_page['FILE'], "<div id=\"tableContainer\" class=\"resultsBackGround\">", "</div>", EXCL);
      // echo $test;
    $anchor_tags = parse_array($test, "<tr id=","</tr>",EXCL);                   //在框架<a> </a>的範圍內解析
    
    # Put http attributes for each tag into an array  把http屬性到一個數組中的每個標籤.
    
   
    $spider_update_array = array();
    $string_update_array = array();
    for($xx=0; $xx<count($anchor_tags); $xx++){

      $title_inc = return_between($anchor_tags[$xx], ">","</a>",EXCL);
      $strip_string_tages =  strip_tags($title_inc);

      $NBSA_name = insertmysql($kBID);
      if(trim($strip_string_tages) == $NBSA_name['Title']){
        $title_inc2 = return_between($anchor_tags[$xx], "<input"," value='Download' />",EXCL);
        $input = get_attribute($title_inc2,"id");
        $page_base2 = http_get("https://www.catalog.update.microsoft.com/ScopedViewInline.aspx?updateid=".$input, "");
        $page_href = get_base_page_address("https://www.catalog.update.microsoft.com/ScopedViewInline.aspx?updateid=".$input);
        $followwing_update = return_between($page_base2['FILE'],"<div style=\"padding-bottom: 0.3em;\">","</div>",EXCL);
 
        $supersededbyInfo = return_between($page_base2['FILE'],"<div id=\"supersededbyInfo\" TABINDEX=\"1\" >","</div>",EXCL);
        $supersededbyInfo = strip_tags($supersededbyInfo);
        $string_update_first = strip_tags($followwing_update);
        
        $followwing_updatewqew = return_between($followwing_update,"'ScopedViewInline.aspx?updateid=","'>",EXCL);

        #深度搜尋判斷機制，如取代無連結則回傳n/a
        if(preg_replace('/\s(?=)/', '', $supersededbyInfo) == "n/a"){
          // echo "n/a";
          update_mysql($kBID,"n/a");
          // break;
        }else{
          // echo $string_update_first."<br>";
          update_mysql($kBID,explode_string($string_update_first));
          if($followwing_updatewqew != null){
          array_push($spider_update_array,$followwing_updatewqew);
          // array_push($string_update_array,$string_update_first);          
          for ($ix=0; $ix <count($spider_update_array) ; $ix++) {
              // $leve_update=$xi;
              sleep(2);
              $followwing_updatewqew_page = http_get("https://www.catalog.update.microsoft.com/ScopedViewInline.aspx?updateid=".$spider_update_array[$ix], "");
              $followwing_update_two = return_between($followwing_updatewqew_page['FILE'],"<div style=\"padding-bottom: 0.3em;\">","</div>",EXCL);

              $string_update = strip_tags($followwing_update_two);

              $followwing_updatewqew_two = return_between($followwing_update_two,"'ScopedViewInline.aspx?updateid=","'>",EXCL);

              if($followwing_updatewqew_two !=null){
                array_push($spider_update_array,$followwing_updatewqew_two);
                // array_push($string_update_array,$string_update);
                $SELECT_name = insertmysql($kBID);
                if ($SELECT_name['name_sapider'] == null) {
                  update_mysql($kBID,explode_string($string_update));
                }else{
                  update_mysql($kBID,$SELECT_name['name_sapider']."->".explode_string($string_update));
                }
                
                explode_string($string_update)."<br>";

              }else{
                // echo "N/A";
              }          
              if($ix == 40){
                break ;
              }
            }
          }
        } 
      }
    }
}
function insertmysql($id){

    $servername = "localhost";
    $username = "root";
    $password = "1LCpfvccGRaJ8630";
    try{

     $db = new PDO('mysql:host=localhost;dbname=library', $username, $password,
      #編碼
      array(pdo::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
     #偵測錯誤訊息
     $db ->setAttribute(pdo::ATTR_ERRMODE,pdo::ERRMODE_EXCEPTION);
     $sql = "SELECT * FROM `mbsa` WHERE `KBID` LIKE '$id'";
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

function update_mysql($kBID,$name){

    $servername = "localhost";
    $username = "root";
    $password = "1LCpfvccGRaJ8630";
    try{

     $db = new PDO('mysql:host=localhost;dbname=library', $username, $password,
      #編碼
      array(pdo::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
     #偵測錯誤訊息
     $db ->setAttribute(pdo::ATTR_ERRMODE,pdo::ERRMODE_EXCEPTION);
     $sql = "UPDATE `mbsa` SET `name_sapider` = '$name' WHERE `mbsa`.`KBID` = '$kBID';";
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
function explode_string($data){
  $output = explode(")", $data);
  $output = explode("(", $output[0]);
  return $output[1];
}
function archive_links($spider_array, $penetration_level, $temp_link_array)
    {
    for($xx=0; $xx<count($temp_link_array); $xx++)
        {
        # Don't add exlcuded links to $spider_array   不要排除的鏈接添   加到$spider_array蜘蛛陣列
        if(!excluded_link($spider_array, $temp_link_array[$xx]))
            {
            $spider_array[$penetration_level][] = $temp_link_array[$xx];
            }
        }
    return $spider_array;
    }
function get_domain($url)
    {
    // Remove protocol from $url                      //移除$url中的協定部分
    $url = str_replace("http://", "", $url);
    $url = str_replace("https://", "", $url);
    // Remove page and directory references         //移除頁面與目錄摻照
    if(stristr($url, "/"))
        $url = substr($url, 0, strpos($url, "/"));
    return $url;
    }
 /* __________________________________________________________________________________________________*/     
function excluded_link($spider_array, $link)
    {        
    # Initialization                                #初始化
    global $SEED_URL, $exclusion_array, $ALLOW_OFFISTE;
    $exclude = false;
 /* __________________________________________________________________________________________________*/     
    //Exclude links that are JavaScript commands   //排除JavaScript指令中的鏈結
    if(stristr($link, "javascript"))
        {
         /*echo*/"Ignored JavaScript fuction: $link\n";
        $exclude=true;
        }
    
    // Exclude redundant links                      //排除重複鏈結
    for($xx=0; $xx<count($spider_array); $xx++)
        {
        $saved_link="";
        while(isset($saved_link))
            {
            $saved_link=array_pop($spider_array[$xx]);
            if($link == array_pop($spider_array[$xx]))
                {
                /*echo*/ "Ignored redundant link: $link\n";
                $exclude=true;
                break;
                }
            }
        }  
    // Exclude links found in $exclusion_array      //排除$exclusion_array中可以找到鏈結
    for($xx=0; $xx<count($exclusion_array); $xx++)
        {
        if(stristr($link, $exclusion_array[$xx]))
            {
            /*echo*/ "Ignored excluded link: $link\n";
            $exclude=true;
            }
        }        
    // Exclude offsite links if requested           //如果有要求的話，就排除網外的鏈結
    if($ALLOW_OFFISTE==false)
        {
        if(get_domain($link)!=get_domain($SEED_URL))
            {
            /*echo*/ "Ignored offsite link: $link\n";
            $exclude=true;

            }
        }
    return $exclude;
    }
?>