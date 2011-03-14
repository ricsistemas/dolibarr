<?php

include("../mpdf.php");
$mpdf=new mPDF('utf-8'); 
$mpdf->SetDirectionality('rtl');

$html = '
<table>
<tr>
<td><img src="title_right.jpg" height="300px" width="400px"></td>
<td valign="top"><img src="title_left.jpg" height="200px" width="300px"></td>
</tr>
</table>
<table border=1>
<tr> <td>עבור:</td> </tr> </table>
<br>
<table border=1>
<tr> <td>לידי:</td> </tr>
</table>
<h2><center>

<p style="text-align: center;font-size:20px;"> הצעת מחיר מספר 123/321</p>
</table>
<table border=1 width=90% height=80%>
<tr>
	<td width="10%"> כמות </td>
	<td align="center" width="75%">פרטים</td>
	<td align="center" width="10%">מחיר יח</td>
	<td align="center" width="15%">סה"כ בש"ח</td>
</tr>
<tr><td> וואו </td><td> שגעון </td>	</tr>


</table>
';

$mpdf->SetAutoFont();
$mpdf->SetDisplayMode('fullpage');
// LOAD a stylesheet
$stylesheet = file_get_contents('style.css');
$mpdf->WriteHTML($stylesheet,1); // The parameter 1 tells that this is css/style only and no
$mpdf->WriteHTML($html);
$mpdf->Output();


?>