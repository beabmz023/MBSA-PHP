
<!DOCTYPE html>
  <html>
    <head lang="en">
     <meta charset="UTF-8">
     <title>MBSA自動化</title>
     <script language="JavaScript" type="text/javascript">
       
       
      var idTmr;
     //獲取當前瀏覽器型別
      function getExplorer() {
       var explorer = window.navigator.userAgent ;
       //ie
       if (explorer.indexOf("MSIE") >= 0) {
        return 'ie';
       }
       //firefox
       else if (explorer.indexOf("Firefox") >= 0) {
        return 'Firefox';
       }
       //Chrome
       else if(explorer.indexOf("Chrome") >= 0){
        return 'Chrome';
       }
       //Opera
       else if(explorer.indexOf("Opera") >= 0){
        return 'Opera';
       }
       //Safari
       else if(explorer.indexOf("Safari") >= 0){
        return 'Safari';
       }
      }
        
     //獲取到型別需要判斷當前瀏覽器需要呼叫的方法，目前專案中火狐，谷歌沒有問題
      //win10自帶的IE無法匯出
      function exportExcel(tableid) {
       if(getExplorer()=='ie')
       {
        var curTbl = document.getElementById(tableid);
        var oXL = new ActiveXObject("Excel.Application");
        var oWB = oXL.Workbooks.Add();
        var xlsheet = oWB.Worksheets(1);
        var sel = document.body.createTextRange();
        sel.moveToElementText(curTbl);
        sel.select();
        sel.execCommand("Copy");
        xlsheet.Paste();
        oXL.Visible = true;
      
        try {
         var fname = oXL.Application.GetSaveAsFilename("Excel.xls", "Excel Spreadsheets (*.xls), *.xls");
        } catch (e) {
         print("Nested catch caught " + e);
        } finally {
         oWB.SaveAs(fname);
         oWB.Close(savechanges = false);
         oXL.Quit();
         oXL = null;
         idTmr = window.setInterval("Cleanup();", 1);
        }
      
       }
       else
       {
        tableToExcel(tableid)
       }
      }
      function Cleanup() {
       window.clearInterval(idTmr);
       CollectGarbage();
      }
        
     //判斷瀏覽器後呼叫的方法，把table的id傳入即可
      var tableToExcel = (function() {
       var uri = 'data:application/vnd.ms-excel;base64,',
         template = '<html><head><meta charset="UTF-8"></head><body><table>{table}</table></body></html>',
         base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) },
         format = function(s, c) {
          return s.replace(/{(\w+)}/g,
            function(m, p) { return c[p]; }) }
       return function(table, name) {
        if (!table.nodeType) table = document.getElementById(table)
        var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
        window.location.href = uri + base64(format(template, ctx))
       }
      })()
     </script>
    </head>
    <body>
      <Form Action="upload.php" Method="POST" Enctype="multipart/form-data">
      <Input Type="File" Name="upfile" >
      <Input Type="Submit" value=" 開始上傳 ">
      </Form>
      <Form Action="Delete.php" Method="POST">
      <Input Type="Submit" value=" 開始刪除">
      </Form>
      <Form Action="Spider.php" Method="POST">
      <Input Type="Submit" value=" 查詢取代">
      </Form>
      <div>       
      <button type="button" onclick="exportExcel('tableExcel')">匯出Excel</button>
      </div>
      <div id="myDiv">
      <table id="tableExcel" width="100%" border="1" cellspacing="0" cellpadding="0">
        <tr><th scope="col">Source.Name</th><th scope="col">Table.Detail.UpdateData.Title</th><th scope="col">Table.Attribute:Name</th><th scope="col">Table.Detail.UpdateData.Attribute:BulletinID</th><th scope="col">Table.Detail.UpdateData.Attribute:KBID</th><th scope="col">Table.Detail.UpdateData.Attribute:IsInstalled</th><th scope="col">Table.Detail.UpdateData.Attribute:Severity</th><th scope="col">查詢取代</th></tr>
        <?php
        		$Name ="";
        		$Title = "";
        		$Attribute = "";
        		$BulletinID = "";
        		$KBID = "";
        		$IsInstalled = "";
        		$Severity = "";
        		$name_sapider = "";

        		$mbsa_munber =  count_mbsa();
        	for ($xx=1; $xx <= $mbsa_munber[0]; $xx++) { 
        		$mbsa = SELECT_mbsa($xx);
        		// print_r($mbsa);
        		$Name = $mbsa['Name'];
        		$Title = $mbsa['Title'];
        		$Attribute = $mbsa['Attribute:Name'];
        		$BulletinID = $mbsa['BulletinID'];
        		$KBID = $mbsa['KBID'];
        		$IsInstalled = $mbsa['IsInstalled'];
        		$Severity = $mbsa['Severity'];
        		$name_sapider = $mbsa['name_sapider'];

        		echo "<tr><th>".$Name."</th><th>".$Title."</th><th>".$Attribute."</th><th>".$BulletinID."</th><th>".$KBID."</th><th>".$IsInstalled."</th><th>".$Severity."</th><th>".str_replace('->','<br>',$name_sapider)."</th></tr>";
        	}
        	

        	// print_r(SELECT_mbsa($id));``
        	function count_mbsa(){
			    $servername = "localhost";
			    $username = "root";
			    $password = "1LCpfvccGRaJ8630";
			    try{
			     $db = new PDO('mysql:host=localhost;dbname=library', $username, $password,
			      #編碼
			      array(pdo::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
			     #偵測錯誤訊息
			     $db ->setAttribute(pdo::ATTR_ERRMODE,pdo::ERRMODE_EXCEPTION);			     
			     $sql = "SELECT count(*) FROM `mbsa`";
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
			function SELECT_mbsa($id){
			    $servername = "localhost";
			    $username = "root";
			    $password = "1LCpfvccGRaJ8630";
			    try{

			     $db = new PDO('mysql:host=localhost;dbname=library', $username, $password,
			      #編碼
			      array(pdo::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8';"));
			     #偵測錯誤訊息
			     $db ->setAttribute(pdo::ATTR_ERRMODE,pdo::ERRMODE_EXCEPTION);
			     $sql = "SELECT * FROM `mbsa` WHERE `ID` LIKE '$id'";
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
      </table>
      </div>
    </body>
</html>