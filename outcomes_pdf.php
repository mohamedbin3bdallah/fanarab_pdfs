<?php
	if(isset($_GET['type']) && in_array($_GET['type'],array('D','M','Y')))
	{
		include('libs/database.php');
		include('libs/lang.php');
		include('libs/calenderdate.php');

		$system = getRowFromTable('*','system','where id = 1','');
		$calender = array('ar'=>'date','hj'=>'hdate');
		$currentyear = ArabicTools::arabicDate($system['calendar'].' Y', time());
		if(substr($_GET['branch'],0,1) == 'S') $tables = array('o'=>'orders');
		else $tables = array('o'=>'orders','apv'=>'accpaymentvouchers');

		$data = array();
		foreach($tables as $tkey => $table)
		{
		$where = '';
		if(isset($_GET['branch']))
		{
			if(substr($_GET['branch'],0,1) == 'S')
			{
				$myselect = 'sum(joborders.joprice) as total,';
				$branch = ' and items.iitid = '.substr($_GET['branch'],1);
				$join = ' inner join joborders on orders.oid = joborders.jooid inner join items on joborders.joiid = items.iid';
			}
			elseif(substr($_GET['branch'],0,1) == 'B')
			{
				$myselect = 'sum('.$table.'.total) as total,';
				$branch = ' and '.$table.'.'.$tkey.'bcid = '.substr($_GET['branch'],1);
				$join = '';
			}
			else 
			{
				$myselect = 'sum('.$table.'.total) as total,';
				$branch = '';
				$join = '';
			}
		}
		else 
		{
			$myselect = 'sum('.$table.'.total) as total,';
			$branch = '';
			$join = '';
		}

		if(isset($_GET['from'],$_GET['to']) && $_GET['from'] != 0 && $_GET['to'] != 0 && $_GET['from'] <= $_GET['to']) $between = ' between "'.$_GET['from'].'" and "'.$_GET['to'].'"';
		elseif(isset($_GET['from']) && $_GET['from'] != 0) $between = ' >= "'.$_GET['from'].'"';
		elseif(isset($_GET['to']) && $_GET['to'] != 0) $between = ' <= "'.$_GET['to'].'"';
		else $between = ' <> "0000-00-00"';

		if($_GET['type'] == 'D') { $type = 'daily'; $format = ' Y-m-d'; $select = $myselect.$table.'.'.$calender[$system['calendar']].' as date'; $where .= $join.' where '.$table.'.'.$calender[$system['calendar']].''.$between.$branch; $group = ''.$table.'.'.$calender[$system['calendar']].''; }
		elseif($_GET['type'] == 'M') { $type = 'monthly'; $format = ' m'; $select = $myselect.'month('.$table.'.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year('.$table.'.'.$calender[$system['calendar']].') = '.$currentyear.' and month('.$table.'.'.$calender[$system['calendar']].')'.$between.$branch; $group = 'month('.$table.'.'.$calender[$system['calendar']].')'; }
		elseif($_GET['type'] == 'Y') { $type = 'yearly'; $format = ' Y'; $select = $myselect.'year('.$table.'.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year('.$table.'.'.$calender[$system['calendar']].')'.$between.$branch; $group = 'year('.$table.'.'.$calender[$system['calendar']].')'; }

		$where .= ' group by '.$group.' order by '.$group.' ASC';
		$newdata = getAllOutcomeDataFromTable($select,$table,$where,'');
		foreach ($newdata as $key => $value)
		{
			if(isset($data[$key])) $data[$key] += $value;
			else $data[$key] = $value;
		}
		}

		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s');

		$total = 0;
		
		$html = '';
		$html .= '
			<!DOCTYPE html>
			<html lang="en" dir="rtl">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
					<meta charset="utf-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<title>'.lang('outcomes').'</title>
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
                                <th width="50%">'.lang($type).'</th>
								<th width="50%">'.lang('outcomes').'</th>
                              </tr>
                            </thead>
                            <tbody>
			';

			foreach($data as $date => $item)
			{
				$total = $total + $item;
				
				$html .= '
							<tr>
                                <td width="50%">'.$date.'</td>
								<td width="50%">'.number_format($item, 2).' '.$system['currency'].'</td>
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
                                <th width="50%">'.lang('total').'</th>
                                <td width="50%">'.number_format($total, 2).' '.$system['currency'].'</td>
                              </tr>
                          </table>
		';

		$html .= '
				</body>
			</html>
		';

		$mpdf->AddPage();
		$mpdf->WriteHTML($html);
		$mpdf->Output('outcomes_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/outcomes');
?>