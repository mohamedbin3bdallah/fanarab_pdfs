<?php
		include('libs/database.php');
		include('libs/lang.php');
		include('libs/calenderdate.php');

		$system = getRowFromTable('*','system','where id = 1','');
		
		if($_GET['store'] == '0' || !is_numeric($_GET['store'])) $where = '';
		else $where = 'where items.iitid = '.$_GET['store'];
		$data = getAllDataFromTable('items.*,itemtypes.itname as type,itemmodels.imname as model,delegates.dname as delegate,users.uname as user','items','LEFT OUTER JOIN itemtypes ON items.iitid = itemtypes.itid LEFT OUTER JOIN itemmodels ON items.iimid = itemmodels.imid LEFT OUTER JOIN delegates ON items.idid = delegates.did LEFT OUTER JOIN users ON items.iuid = users.uid '.$where.' ORDER BY items.ictime DESC, items.iitid,items.iimid,items.iname ASC','');

		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s', 'A4-L');
		
		$count = 0;
		$limit_per_page = 10;
		
		foreach($data as $item)
		{

		if($item['ictime'] != '') { if(date('H', $item['ictime']) < 12) $time = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', $item['ictime']).' '.lang('am'); else $time = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', $item['ictime']).' '.lang('pm'); }
		else $time = '';

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
					<title>'.lang('stores').'</title>
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
                                <th>'.lang('bill').'</th>
								<th>'.lang('type').'</th>
								<th>'.lang('model').'</th>
								<th>'.lang('name').'</th>
								<th>'.lang('delegate').'</th>
								<th>'.lang('buyprice').'</th>
								<th>'.lang('wholesaleprice').'</th>
								<th>'.lang('retailprice').'</th>
								<th>'.lang('quantity').'</th>
								<th>'.lang('minrange').'</th>
								<th>'.lang('maxrange').'</th>
								<th>'.lang('user').'</th>
								<th>'.lang('time').'</th>
                              </tr>
		';
		}
		$html .= '
							  <tr>
                                <td>'.$item['bin'].'</td>
                                <td>'.$item['type'].'</td>
								<td>'.$item['model'].'</td>
								<td>'.$item['iname'].'</td>
                                <td>'.$item['delegate'].'</td>
								<td>'.$item['ibuyprice'].' '.$system['currency'].'</td>
								<td>'.$item['iwholesaleprice'].' '.$system['currency'].'</td>
								<td>'.$item['iretailprice'].' '.$system['currency'].'</td>
								<td>'.$item['iquantity'].'</td>
								<td>'.$item['iminrange'].'</td>
								<td>'.$item['imaxrange'].'</td>
								<td>'.$item['user'].'</td>
								<td>'.$time.'</td>
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
		$mpdf->Output('stores_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
?>