<?php
	if(isset($_GET['id']) && is_numeric($_GET['id']))
	{
		include('libs/database.php');
		include('libs/lang.php');
		include('libs/calenderdate.php');

		$system = getRowFromTable('*','system','where id = 1','');

		$preporders = getRowFromTable('withdrowvouchers.*,bills.bid as bid,bills.bnewtotal as bnewtotal,users.uname as employee,customers.cname as customer','withdrowvouchers',' LEFT OUTER JOIN bills on withdrowvouchers.wvoid = bills.boid LEFT OUTER JOIN orders on withdrowvouchers.wvoid = orders.oid LEFT OUTER JOIN customers on orders.ocid = customers.cid LEFT OUTER JOIN users on withdrowvouchers.wvuid = users.uid where withdrowvouchers.wvid = '.$_GET['id'],'');
		if(!empty($preporders))
		{
			$data['id'] = $preporders['wvid'];
			$data['bid'] = $preporders['bid'];
			$data['oid'] = $preporders['wvoid'];
			$data['customer'] = $preporders['customer'];
			$data['employee'] = $preporders['employee'];
			$data['total'] = $preporders['wvtotal'];
			$data['time'] = $preporders['wvtime'];
			$data['notes'] = $preporders['notes'];
			$data['newtotal'] = $preporders['bnewtotal'];


		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s');
		
		if($data['time'] != '') { if(date('H', $data['time']) < 12) $time = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', $data['time']).' '.lang('am'); else $time = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', $data['time']).' '.lang('pm'); }
		else $time = '';

		$html = '';
		$html .= '
			<!DOCTYPE html>
			<html lang="en" dir="rtl">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
					<meta charset="utf-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<title>'.lang('withdrowvoucher').'</title>
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
			<h3 style="text-align:center;">'.lang('withdrowvoucher').'</h3>
		';
		$html .= '
                          <table>
							  <tr>
                                <th>'.lang('number').'</th>
                                <td>'.$data['id'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('number').' '.lang('bill').'</th>
                                <td>'.$data['oid'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('customer').'</th>
                                <td>'.$data['customer'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('pay').'</th>
                                <td>'.$data['total'].' '.$system['currency'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('total').'</th>
                                <td>'.$data['newtotal'].' '.$system['currency'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('notes').'</th>
                                <td style="text-align:justify;">'.$data['notes'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('employee').'</th>
                                <td>'.$data['employee'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('time').'</th>
                                <td>'.$time.'</td>
                              </tr>
                          </table>
		';
		$html .= '
				</body>
			</html>
		';

		$mpdf->AddPage();
		$mpdf->WriteHTML($html);
		$mpdf->Output('withdrowvoucher_'.$_GET['id'].'_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
		}
		else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/withdrowvouchers');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/withdrowvouchers');
?>