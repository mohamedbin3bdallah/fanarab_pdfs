<?php
	if(isset($_GET['type']) && in_array($_GET['type'],array('D','M','Y')))
	{
		include('libs/database.php');
		include('libs/lang.php');
		include('libs/calenderdate.php');

		$system = getRowFromTable('*','system','where id = 1','');
		$calender = array('ar'=>'date','hj'=>'hdate');
		$currentyear = ArabicTools::arabicDate($system['calendar'].' Y', time());
		
		$where = '';
		if(isset($_GET['branch']) && $_GET['branch'] != 0) $branch = ' and orders.obcid = '.$_GET['branch']; else $branch = '';
		$myselect = 'sum(bills.btotal) as total,sum(bills.bdiscount) as discount';
		$join = 'LEFT OUTER JOIN orders on bills.boid = orders.oid LEFT OUTER JOIN branches on orders.obcid = branches.bcid';
		if(isset($_GET['from'],$_GET['to']) && $_GET['from'] != 0 && $_GET['to'] != 0 && $_GET['from'] <= $_GET['to'])
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $format = ' Y-m-d'; $select = $myselect.',bills.b'.$calender[$system['calendar']].' as date'; $where .= $join.' where bills.b'.$calender[$system['calendar']].' between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; $group = 'bills.b'.$calender[$system['calendar']].''; }
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $format = ' m'; $select = $myselect.',month(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') = '.$currentyear.' and month(bills.b'.$calender[$system['calendar']].') between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; $group = 'month(bills.b'.$calender[$system['calendar']].')'; }
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $format = ' Y'; $select = $myselect.',year(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; $group = 'year(bills.b'.$calender[$system['calendar']].')'; }
		}
		elseif(isset($_GET['from']) && $_GET['from'] != 0)
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $format = ' Y-m-d'; $select = $myselect.',bills.b'.$calender[$system['calendar']].' as date'; $where .= $join.' where bills.b'.$calender[$system['calendar']].' >= "'.$_GET['from'].'"'.$branch; $group = 'bills.b'.$calender[$system['calendar']].''; }
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $format = ' m'; $select = $myselect.',month(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') = '.$currentyear.' and month(bills.b'.$calender[$system['calendar']].') >= "'.$_GET['from'].'"'.$branch; $group = 'month(bills.b'.$calender[$system['calendar']].')'; }
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $format = ' Y'; $select = $myselect.',year(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') >= "'.$_GET['from'].'"'.$branch; $group = 'year(bills.b'.$calender[$system['calendar']].')'; }
		}
		elseif(isset($_GET['to']) && $_GET['to'] != 0)
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $format = ' Y-m-d'; $select = $myselect.',bills.b'.$calender[$system['calendar']].' as date'; $where .= $join.' where bills.b'.$calender[$system['calendar']].' <= "'.$_GET['to'].'"'.$branch; $group = 'bills.b'.$calender[$system['calendar']].''; }
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $format = ' m'; $select = $myselect.',month(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') = '.$currentyear.' and month(bills.b'.$calender[$system['calendar']].') <= "'.$_GET['to'].'"'.$branch; $group = 'month(bills.b'.$calender[$system['calendar']].')'; }
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $format = ' Y'; $select = $myselect.',year(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') <= "'.$_GET['to'].'"'.$branch; $group = 'year(bills.b'.$calender[$system['calendar']].')'; }
		}
		else
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $format = ' Y-m-d'; $select = $myselect.',bills.b'.$calender[$system['calendar']].' as date'; $where .= $join.' where bills.b'.$calender[$system['calendar']].' <> "0000-00-00"'.$branch; $group = 'bills.b'.$calender[$system['calendar']].''; }
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $format = ' m'; $select = $myselect.',month(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') = '.$currentyear.' and month(bills.b'.$calender[$system['calendar']].') <> "00"'.$branch; $group = 'month(bills.b'.$calender[$system['calendar']].')'; }
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $format = ' Y'; $select = $myselect.',year(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') <> "00"'.$branch; $group = 'year(bills.b'.$calender[$system['calendar']].')'; }
		}
		$where .= ' group by '.$group.' order by '.$group.' ASC';
		$data = getAllDataFromTable($select,'bills',$where,'');
		
		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s');

		$total = 0;
		$discount = 0;
		
		$html = '';
		$html .= '
			<!DOCTYPE html>
			<html lang="en" dir="rtl">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
					<meta charset="utf-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<title>'.lang('incomes').'</title>
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
							//margin-right: 50%;
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

		if(!empty($data))
		{
			$html .= '
                          <table>
                            <thead>
                              <tr>
                                <th width="25%">'.lang($type).'</th>
                                <th width="25%">'.lang('total').'</th>
								<th width="25%">'.lang('discount').'</th>
								<th width="25%">'.lang('incomes').'</th>
                              </tr>
                            </thead>
                            <tbody>
			';

			for($i=0;$i<count($data);$i++)
			{
				$total = number_format($total + $data[$i]['total'], 2);
				$discount = number_format($discount + $data[$i]['discount'], 2);
				
				$html .= '
							<tr>
                                <td width="25%">'.$data[$i]['date'].'</td>
								<td width="25%">'.number_format($data[$i]['total'], 2).' '.$system['currency'].'</td>
								<td width="25%">'.number_format($data[$i]['discount'], 2).' '.$system['currency'].'</td>
                                <td width="25%">'.number_format($data[$i]['total'] - $data[$i]['discount'], 2).' '.$system['currency'].'</td>
                            </tr>
				';
			}
			$html .= '
							</tbody>
                          </table>
			';
		}

		$html .= '
                          <table class="total">
                              <tr>
                                <th>'.lang('total').'</th>
                                <td>'.number_format($total, 2).' '.$system['currency'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('discount').'</th>
                                <td>'.number_format($discount, 2).' '.$system['currency'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('incomes').'</th>
                                <td>'.number_format($total - $discount, 2).' '.$system['currency'].'</td>
                              </tr>
                          </table>
		';

		$html .= '
				</body>
			</html>
		';

		$mpdf->AddPage();
		$mpdf->WriteHTML($html);
		$mpdf->Output('incomes_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/incomes');
?>