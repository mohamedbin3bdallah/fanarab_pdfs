<?php
	if(isset($_GET['orderby']) && in_array($_GET['orderby'],array('ASC','DESC')))
	{
		include('libs/database.php');
		include('libs/lang.php');
		include('libs/calenderdate.php');

		if(isset($_GET['no']) && is_numeric($_GET['no']) && $_GET['no'] != '0') $limit = 'limit '.$_GET['no'];
		else $limit = '';
		
		if(isset($_GET['store']) && $_GET['store'] != '0') $where = 'where paymentvouchers.accept = "1" and items.iitid IN ('.$_GET['store'].')';
		else $where = 'where paymentvouchers.accept = "1"';
		
		$system = getRowFromTable('*','system','where id = 1','');
		$data = getAllDataFromTable('items.iname as item,items.iquantity as quantity,itemtypes.itname as store,COUNT(paymentvouchers.pvid) as count,SUM(paymentvouchers.pvquantity) as sum','paymentvouchers','LEFT OUTER JOIN items ON paymentvouchers.pviid = items.iid LEFT OUTER JOIN itemtypes ON items.iitid = itemtypes.itid '.$where.' GROUP BY paymentvouchers.pviid ORDER BY sum '.$_GET['orderby'],$limit);

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
					<title>'.lang('statistics').' '.lang('items').'</title>
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
								<th>'.lang('item').'</th>
								<th>'.lang('store').'</th>
								<th>'.lang('sordersno').'</th>
								<th>'.lang('sqordersno').'</th>
								<th>'.lang('quantity').'</th>
                              </tr>
		';
		}
		$html .= '
							  <tr>
                                <td>'.($count+1).'</td>
                                <td>'.$item['item'].'</td>
								<td>'.$item['store'].'</td>
								<td>'.$item['count'].'</td>
								<td>'.$item['sum'].'</td>
								<td>'.$item['quantity'].'</td>
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
		$mpdf->Output('items_statistics_report_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
		}
		else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/statistics');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/statistics');
?>