<?php
$id=$_GET["id"];
$action=$_GET["action"];
if (strlen($id) >0) {$flag=TRUE;}
	else $flag=FALSE;

if ($action == "edit") {$edit=TRUE;}
	else $edit=FALSE;
	

if($flag){
	$con = mysql_connect("localhost","root","root");
	if (!$con)
	  {
	  die('Could not connect: ' . mysql_error());
	  }

	mysql_select_db("ram_db", $con);
	$result = mysql_query("SELECT * FROM products
	WHERE productid=\"".$id."\"");

	while($row = mysql_fetch_array($result))
	  {
	  $prodid = $row['ProductID'];
	  $name = $row['Name'];
	  $type = $row['Type'];
	  $pivot = $row['Pivot'];
	  $material = $row['Material'];
	  $price = $row['Price'];
	  $notes = $row['Notes'];
	  }
}
if( $flag && (strlen($prodid) > 0) ){
	  
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link rel="stylesheet" href="style.css" type="text/css"/>
	<script type="text/javascript">
	function editProduct(){
		window.location="getproduct.php?<?php echo "id=".$id."&action=edit\""?>;
	}
	function updateProduct(){
		alert("updated!");
	}
	</script>
	
	
</head>
<body>
  <div class="tblReg">
	<form onsubmit="return false;">
	<table>
	<tr><td>קוד מוצר:</td><td> <input type="text" name="productID" id="prodid" value="<?php echo $prodid; ?>" <?php (!$edit) ? print "readonly" :print "";?>/></td></tr>
	<tr><td>שם מוצר:</td><td><input type="text" name="name" id="name" value="<?php echo $name; ?>" <?php (!$edit) ? print "readonly" :print "";?>/></td></tr>
	<tr><td>סוג:</td><td><input type="text" name="type" id="type" value="<?php echo $type; ?>" <?php (!$edit) ? print "readonly" :print "";?>/></td></tr>
	<tr><td>ציר:</td><td><input type="text" name="pivot" id="pivot" value="<?php echo $pivot; ?>" <?php (!$edit) ? print "readonly" :print "";?>/></td></tr>
	<tr><td>חומר:</td><td><input type="text" name="material" id="material" value="<?php echo $material; ?>" <?php (!$edit) ? print "readonly" :print "";?>/></td></tr>
	<tr><td>מחיר:</td><td><input type="text" name="price" id="price" value="<?php echo $price; ?>" <?php (!$edit) ? print "readonly" :print "";?>/></td></tr>
	<tr><td>הערות:</td><td><Textarea name="notes" id="notes" cols=40 rows=6 <?php (!$edit) ? print "readonly" :print "";?>> <?php print str_replace("\"","&quot;",$notes); ?></textarea></td></tr>
	</table>
	<?php if(!$edit) { ?>
		<input type="submit" value="ערוך" onclick="editProduct()"> 
		<?php }
		  elseif ($edit) { ?>
			<input type="submit" value="שמור" onclick="updateProduct()"> 
		<?php } ?>
	
</form>
</div>
</body>
</html>
<?php } ?>