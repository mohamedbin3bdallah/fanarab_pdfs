<?php
	if(isset($_GET['orderby']) && in_array($_GET['orderby'],array('ASC','DESC')))
	{
		include('libs/database.php');
		include('libs/lang.php');
		include('libs/calenderdate.php');

		if(isset($_GET['no']) && is_numeric($_GET['no']) && $_GET['no'] != '0') $limit = 'limit '.$_GET['no'];
		else $limit = '';
		
		$system = getRowFromTable('*','system','where id = 1','');
		$data = getAllDataFromTable('customers.*,COUNT(orders.oid) as count','orders','LEFT OUTER JOIN customers ON orders.ocid = customers.cid GROUP BY orders.ocid ORDER BY count '.$_GET['orderby'],$limit);
		//$data = getAllDataFromTable('COUNT(oid) as count','orders','GROUP BY ocid ORDER BY count(oid) '.$_GET['orderby'],$limit);
		
		if(!empty($data))
		{
		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s');
		
		$count = 0;
		$limit_per_page = 15;
		
		foreach($data as $item)
		{

		if(($count%$limit_per_page) == 0)
		{
		$html = '';
		$html .= '
			<!DOCTYPE html>
			<html lang="en" dir="rtl">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
					<meta charset="utf-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<title>'.lang('customers').'</title>
					<style>
						@import url("http://fonts.googleapis.com/earlyaccess/droidarabickufi.css");
						body { 
							font-family: "Droid Arabic Kufi", sans-serif;
							border: 1px solid #ccc;
						}
						
						.logo-div, .company-div {
							text-align: center;
						}
						
						.logo-img {
							width: 25%;
						}
						
						table {
							border-collapse: collapse;
							width: 100%;
							margin-bottom: 25px;
						}

						td, th {
							text-align: right;
							border: 1px solid #dddddd;
							padding: 8px;
						}

						tr:nth-child(even) {
							background-color: #dddddd;
						}
						
						.total {
							margin-right: 50%;
						}
					</style>
				</head>
				<body>
		';
		$html .= '
			<br>
			<div class="logo-div">
				<img src="../account/imgs/'.$system['logo'].'" class="logo-img" />
			</div>
			<div class="company-div">
				<h3>'.$system['website'].'</h3>
			</div>
			<br>
		';
		$html .= '
                          <table>
							  <tr>
                                <th>'.lang('number').'</th>
								<th>'.lang('name').'</th>
								<th>'.lang('mobile').'</th>
								<th>'.lang('desc').'</th>
								<th>'.lang('ordersno').'</th>
                              </tr>
		';
		}
		$html .= '
							  <tr>
                                <td>'.($count+1).'</td>
                                <td>'.$item['cname'].'</td>
								<td>'.$item['cphone'].'</td>
								<td style="text-align:justify;">'.$item['cdesc'].'</td>
								<td>'.$item['count'].'</td>
                              </tr>
		';
		if((($count%$limit_per_page) == ($limit_per_page-1)) || ($count == (count($data)-1)))
		{
		$html .= '
					</table>
				</body>
			</html>
		';
		$mpdf->AddPage();
		$mpdf->WriteHTML($html);
		//print_r($html);
		}
		$count++;
		}
		$mpdf->Output('customers_report_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
		}
		else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/statistics');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/statistics');
?>