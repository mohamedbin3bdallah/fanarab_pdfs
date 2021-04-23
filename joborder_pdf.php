<?php
	if(isset($_GET['id']) && is_numeric($_GET['id']))
	{
		include('libs/database.php');
		include('libs/lang.php');
		include('libs/calenderdate.php');

		$system = getRowFromTable('*','system','where id = 1','');

		$preporders = getRowFromTable('joborders.*,orders.oid as oid,orders.ocode as ocode,orders.endtime as endtime,items.iname as item','joborders',' LEFT OUTER JOIN orders on orders.oid = joborders.jooid LEFT OUTER JOIN items on items.iid = joborders.joiid where joborders.joid = '.$_GET['id'],'');

		if(!empty($preporders))
		{
			$data['joid'] = $preporders['joid'];
			$data['oid'] = $preporders['oid'];
			$data['follower'] = $preporders['jouid'];
			$data['employee'] = $preporders['joeid'];
			$data['ocode'] = $preporders['ocode'];
			$data['endtime'] = $preporders['endtime'];
			$data['notes'] = $preporders['notes'];
			$data['accept'] = $preporders['accept'];
			$data['item'] = $preporders['item'];
			$data['joitem'] = $preporders['joitem'];
			$data['price'] = $preporders['joprice'];
			$data['quantity'] = $preporders['joquantity'];

		$myusers = getAllDataFromTable('uid,uname','users','','');
		$users = array();
		foreach($myusers as $user) { $users[$user['uid']] = $user['uname']; }

		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s');
		
		if($data['endtime'] != '') { if(date('H', $data['endtime']) < 12) $endtime = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', $data['endtime']).' '.lang('am'); else $endtime = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', $data['endtime']).' '.lang('pm'); }
		else $endtime = '';
		
		if(date('H', time()) < 12) $time = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', time()).' '.lang('am');
		else $time = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', time()).' '.lang('pm');
		
		$html = '';
		$html .= '
			<!DOCTYPE html>
			<html lang="en" dir="rtl">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
					<meta charset="utf-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<title>'.lang('order').'</title>
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
			<h3 style="text-align:center;">'.lang('order').'</h3>
		';
		$html .= '
                          <table>
							  <tr>
                                <th>'.lang('number').' '.lang('joborder').'</th>
                                <td>'.$_GET['id'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('number').' '.lang('order').'</th>
                                <td>'.$data['oid'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('code').'</th>
                                <td>'.$data['ocode'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('time').'</th>
                                <td>'.$time.'</td>
                              </tr>
							  <tr>
                                <th>'.lang('item').'</th>
                                <td>'.$data['joitem'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('joquantity').'</th>
                                <td>'.$data['quantity'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('follower').'</th>
                                <td>'.$users[$data['follower']].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('joemployee').'</th>
                                <td>'.$users[$data['employee']].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('endtime').'</th>
                                <td>'.$endtime.'</td>
                              </tr>
							  <tr>
                                <th>'.lang('notes').'</th>
                                <td style="text-align:justify;">'.$data['notes'].'</td>
                              </tr>
                          </table>
		';
		$html .= '
				</body>
			</html>
		';

		$mpdf->AddPage();
		$mpdf->WriteHTML($html);
		$mpdf->Output('order_'.$_GET['id'].'_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
		}
		else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/joborders/user');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/joborders/user');
?>