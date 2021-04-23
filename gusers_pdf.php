<?php
		include('libs/database.php');
		include('libs/lang.php');
		include('libs/calenderdate.php');
		
		$activearr = array(lang('notactive'),lang('active'));
		
		$system = getRowFromTable('*','system','where id = 1','');
		$data = getAllDataFromTable('users.*,usertypes.utname as usertype','users','LEFT OUTER JOIN usertypes ON users.uutid = usertypes.utid ORDER BY users.uctime DESC','');
		
		$mybranches = getAllDataFromTable('bcid,bcname','branches','','');
		$branches = array(); foreach($mybranches as $branch) { $branches[$branch['bcid']] = $branch['bcname']; }
		$mystores = getAllDataFromTable('itid,itname','itemtypes','','');
		$stores = array(); foreach($mystores as $store) { $stores[$store['itid']] = $store['itname']; }

		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s', 'A4-L');
		
		$count = 0;
		$limit_per_page = 15;
		
		foreach($data as $item)
		{
		$item['branches'] = '';
		$item['stores'] = '';
		
		if($item['uctime'] != '') { if(date('H', $item['uctime']) < 12) $time = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', $item['uctime']).' '.lang('am'); else $time = ArabicTools::arabicDate($system['calendar'].' Y-m-d h-i-s', $item['uctime']).' '.lang('pm'); }
		else $time = '';
		
		$item['branches'] = implode(' , ',array_intersect_key($branches, array_flip(explode(',',substr($item['ubcid'],1,-1)))));
		$item['stores'] = implode(' , ',array_intersect_key($stores, array_flip(explode(',',substr($item['uitid'],1,-1)))));
		
		if($item['privileges'] != '')
		{
			$prvs = array(); $prvs = explode(',',substr($item['privileges'],1,-1));
			$myprvs = array(); foreach($prvs as $prv) { $myprvs[] = lang($prv); }
			$item['userprivileges'] = implode(' , ', $myprvs);
		}
		else $item['userprivileges'] = '';
		
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
					<title>'.lang('users').'</title>
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
								<th>'.lang('username').'</th>
								<th>'.lang('usertype').'</th>
								<th>'.lang('branch').'</th>
								<th>'.lang('store').'</th>
								<th>'.lang('privileges').'</th>
								<th>'.lang('email').'</th>
								<th>'.lang('address').'</th>
								<th>'.lang('mobile').'</th>							
								<th>'.lang('time').'</th>
								<th></th>
                              </tr>
		';
		}
		$html .= '
							  <tr>
                                <td>'.($count+1).'</td>
                                <td>'.$item['uname'].'</td>
								<td>'.$item['username'].'</td>
								<td>'.$item['usertype'].'</td>
								<td>'.$item['branches'].'</td>
								<td>'.$item['stores'].'</td>
								<td style="text-align:justify;">'.$item['userprivileges'].'</td>
								<td>'.$item['uemail'].'</td>
								<td>'.$item['uaddress'].'</td>
								<td>'.$item['uphone'].'</td>
								<td>'.$time.'</td>
                                <td>'.$activearr[$item['active']].'</td>
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
		$mpdf->Output('users_report_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
?>