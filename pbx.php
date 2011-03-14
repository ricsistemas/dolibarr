<?php
//require_once("main.inc.php");
//require("filefunc.inc.php");
require_once(DOL_DOCUMENT_ROOT.'/mpdf/mpdf.php');
?>
<?php
	function makeDraft($id) // $id = order's id
	{
		//$con = mysql_connect("localhost","root","root");
		$con = mysql_connect("localhost","eyalevin_doli","dolibarr");
		//echo $dolibarr_main_db_host.', '.$dolibarr_main_db_user.', '.$dolibarr_main_db_pass);
		if (!$con)
		  {
		  die('Could not connect: ' . mysql_error());
		  }
		mysql_set_charset('utf8',$con); 
		mysql_select_db("eyalevin_dolibarr", $con);
		$result = mysql_query("SELECT rowid, tms, fk_soc, fk_projet, ref, total_ht FROM llx_commande Where rowid=\"".$id."\"");
		
		while($row = mysql_fetch_array($result))
		  {
		  $rowid = $row['rowid'];
		  $tms = $row['tms'];
		  $fk_soc = $row['fk_soc'];
		  $fk_projet = $row['fk_projet'];
		  $ref = str_replace("(","",$row['ref']);
		  $ref = str_replace(")","",$ref);
		  $total_ht_order = $row['total_ht']; // total amount of money without tax for current order
		  //print 'Order id='.$rowid .'('. $ref .') ,'. $tms .', Company'. $fk_soc .','. $fk_projet;
		  //print '<br>';
		  }
		  
		  
		  $result = mysql_query("SELECT fk_product,tva_tx,qty,price,total_ht,total_ttc, description FROM llx_commandedet Where fk_commande=\"".$rowid."\"");
		  //$num_rows = mysql_num_rows($result);
		  $i=0;
		  $info="";
		  while($row = mysql_fetch_array($result))
		  {
				$i++;
				$product = $row['fk_product']; // if fk_product = NULL it means that the product does not exist ("free text")

				if($product != null){  //get product name
					$prodResult = mysql_query("SELECT ref,label FROM llx_product Where rowid=\"".$product."\"");
					while($productRow = mysql_fetch_array($prodResult)){
						$prodLabel = $productRow['ref'];
						$prodDesc = $productRow['label'];
			//			print 'Product #'.$i.': ';
			//			echo $prodLabel;
			//			print ' - ';
			//			echo $prodDesc;
			//			print '<br>';

					}	
				}
				else {
			//		print 'Product #'.$i.': '.$row['description'].'<br>';
				}
				$tax = $row['tva_tx']; // Tax for current product (usually 16 or 0)
				$qty = $row['qty']; // Quantity of product in this order
				$price = $row['price']; // Price of 1 quantity of this product
				$total_ht = $row['total_ht']; // Total price (not inculding tax) of product (qty*price)
				$total_ht = substr($total_ht,0,strpos($total_ht,'.')+3);
				$total_ttc = $row['total_ttc']; // Total price including tax
			//	print 'id = '.$product.'<br> tax = '.$tax.'<br> qty='.$qty.'<br> price='.$price.'<br> total price = '.$total_ttc.'<br>';
				$info.="<tr><td>".$qty."</td>"; //Adding quantity to pdf
				if($product != null) {
					$info.="<td>".$prodLabel ." - ". $prodDesc."</td>"; //Adding product to pdf
				}
				else {
					$info.="<td>".$row['description']."</td>"; // Adding Free-Text product to pdf
				}
				$info.="<td>".$price."</td>"; // Adding price to pdf
				$info.="<td>".$total_ht."</td></tr>"; // Adding total price (without tax) to pdf
		  }
		  $total_ht_order = substr($total_ht_order,0,strpos($total_ht_order,'.')+3);
		  $info.="<tr></tr><tr></tr><tr></tr><tr><td><td><td>סהכ ללא מעמ</td><td>".$total_ht_order."</td></tr>";

		  
	

	
	// Get company's name
	$compResult = mysql_query("SELECT nom FROM llx_societe Where rowid=\"".$fk_soc."\"");
					while($compRow = mysql_fetch_array($compResult)){
						$compName = $compRow['nom']; // Saving company name for pdf
					}
	$mpdf=new mPDF('utf-8'); 
	$mpdf->SetDirectionality('rtl');

	$html = '
	<table>
	<tr>
	<td><img src="'.DOL_DOCUMENT_ROOT.'/title_right.jpg" height="300px" width="400px"></td>
	<td valign="top"><img src="'.DOL_DOCUMENT_ROOT.'/title_left.jpg" height="200px" width="300px"></td>
	</tr>
	</table>
	<table>
	<tr> <td>עבור:'.$compName.' </td> </tr> </table> 
	<br>
	<table>
	<tr> <td>לידי:</td> </tr>
	</table>
	<h2><center>

	<p style="text-align: center;font-size:20px;"> &nbsp;&nbsp;הצעת מחיר מספר'.$ref.'&nbsp;&nbsp;</p>
	</table>
	<table class="main" border=1 width=90% height=80%>
	<tr>
		<td width="10%"> כמות</td>
		<td align="center" width="75%">פרטים</td>
		<td align="center" width="10%">מחיר יח</td>
		<td align="center" width="15%">סה"כ בש"ח</td>
	</tr>
	';
	$html.=$info;
	$html.= '</table>';

	$mpdf->SetAutoFont();
	$mpdf->SetDisplayMode('fullpage');
	// LOAD a stylesheet
	$stylesheet = file_get_contents(DOL_DOCUMENT_ROOT.'/style.css');
	$mpdf->WriteHTML($stylesheet,1); // The parameter 1 tells that this is css/style only and no
	$mpdf->WriteHTML($html);

	//$mpdf->Output($ref.'.pdf','I'); //Show pdf in browser
	//$filename='('.$ref.').pdf';
	$fullpath=DOL_DATA_ROOT.'/commande/('.$ref.')';
	if(!file_exists($fullpath)) 
	{ 
		mkdir($fullpath); 
	} 
	//$mpdf->Output($fullpath.'/('.$ref.').pdf','I'); //Save pdf in dir
	$mpdf->Output($fullpath.'/('.$ref.').pdf','F'); //Save pdf in dir
	// $mpdf->Output('draft.pdf','D'); //Make the browser download pdf
	}
	
		function makeOrder($id) // $id = order's id
	{
		$con = mysql_connect("localhost","root","root");
		mysql_set_charset('utf8',$con); 
		if (!$con)
		  {
		  die('Could not connect: ' . mysql_error());
		  }

		mysql_select_db(dolibarr, $con);
		$result = mysql_query("SELECT rowid, tms, fk_soc, fk_projet, ref, total_ht FROM llx_commande Where rowid=\"".$id."\"");
		
		while($row = mysql_fetch_array($result))
		  {
		  $rowid = $row['rowid'];
		  $tms = $row['tms'];
		  $fk_soc = $row['fk_soc'];
		  $fk_projet = $row['fk_projet'];
		  $ref = str_replace("(","",$row['ref']);
		  $ref = str_replace(")","",$ref);
		  $total_ht_order = $row['total_ht']; // total amount of money without tax for current order
		  //print 'Order id='.$rowid .'('. $ref .') ,'. $tms .', Company'. $fk_soc .','. $fk_projet;
		  //print '<br>';
		  }
		  
		  
		  $result = mysql_query("SELECT fk_product,tva_tx,qty,price,total_ht,total_ttc, description FROM llx_commandedet Where fk_commande=\"".$rowid."\"");
		  //$num_rows = mysql_num_rows($result);
		  $i=0;
		  $info="";
		  while($row = mysql_fetch_array($result))
		  {
				$i++;
				$product = $row['fk_product']; // if fk_product = NULL it means that the product does not exist ("free text")

				if($product != null){  //get product name
					$prodResult = mysql_query("SELECT ref,label FROM llx_product Where rowid=\"".$product."\"");
					while($productRow = mysql_fetch_array($prodResult)){
						$prodLabel = $productRow['ref'];
						$prodDesc = $productRow['label'];
			//			print 'Product #'.$i.': ';
			//			echo $prodLabel;
			//			print ' - ';
			//			echo $prodDesc;
			//			print '<br>';

					}	
				}
				else {
			//		print 'Product #'.$i.': '.$row['description'].'<br>';
				}
				$tax = $row['tva_tx']; // Tax for current product (usually 16 or 0)
				$qty = $row['qty']; // Quantity of product in this order
				$price = $row['price']; // Price of 1 quantity of this product
				$total_ht = $row['total_ht']; // Total price (not inculding tax) of product (qty*price)
				$total_ttc = $row['total_ttc']; // Total price including tax
			//	print 'id = '.$product.'<br> tax = '.$tax.'<br> qty='.$qty.'<br> price='.$price.'<br> total price = '.$total_ttc.'<br>';
				$info.="<tr><td>".$qty."</td>"; //Adding quantity to pdf
				if($product != null) {
					$info.="<td>".$prodLabel ." - ". $prodDesc."</td>"; //Adding product to pdf
				}
				else {
					$info.="<td>".$row['description']."</td>"; // Adding Free-Text product to pdf
				}
				$info.="<td>".$price."</td>"; // Adding price to pdf
				$info.="<td>".$total_ht."</td></tr>"; // Adding total price (without tax) to pdf
		  }
		  $info.="<tr></tr><tr></tr><tr></tr><tr><td><td><td>סהכ ללא מעמ</td><td>".$total_ht_order."</td></tr>";

		  
	?>

	<?php
	// Get company's name
	$compResult = mysql_query("SELECT nom FROM llx_societe Where rowid=\"".$fk_soc."\"");
					while($compRow = mysql_fetch_array($compResult)){
						$compName = $compRow['nom']; // Saving company name for pdf
					}
	$mpdf=new mPDF('utf-8'); 
	$mpdf->SetDirectionality('rtl');

	$html = '
	<table>
	<tr>
	<td><img src="'.DOL_DOCUMENT_ROOT.'/title_right.jpg" height="300px" width="400px"></td>
	<td valign="top"><img src="'.DOL_DOCUMENT_ROOT.'/title_left.jpg" height="200px" width="300px"></td>
	</tr>
	</table>
	<table>
	<tr> <td>עבור:'.$compName.' </td> </tr> </table> 
	<br>
	<table>
	<tr> <td>לידי:</td> </tr>
	</table>
	<h2><center>

	<p style="text-align: center;font-size:20px;"> &nbsp;&nbsp;הצעת מחיר מספר'.$ref.'&nbsp;&nbsp;</p>
	</table>
	<table class="main" border=1 width=90% height=80%>
	<tr>
		<td width="10%"> כמות</td>
		<td align="center" width="75%">פרטים</td>
		<td align="center" width="10%">מחיר יח</td>
		<td align="center" width="15%">סה"כ בש"ח</td>
	</tr>
	';
	$html.=$info;
	$html.= '</table>';

	$mpdf->SetAutoFont();
	$mpdf->SetDisplayMode('fullpage');
	// LOAD a stylesheet
	$stylesheet = file_get_contents(DOL_DOCUMENT_ROOT.'/style.css');
	$mpdf->WriteHTML($stylesheet,1); // The parameter 1 tells that this is css/style only and no
	$mpdf->WriteHTML($html);

	//$mpdf->Output($ref.'.pdf','I'); //Show pdf in browser
	$mpdf->Output(DOL_DATA_ROOT.'/commande/'.$ref.'/'.$ref.'.pdf','F'); //Save pdf in dir
	// $mpdf->Output('draft.pdf','D'); //Make the browser download pdf
	}
?>