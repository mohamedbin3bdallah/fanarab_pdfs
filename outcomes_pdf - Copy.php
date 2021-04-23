<?php
	if(isset($_GET['type']) && in_array($_GET['type'],array('D','M','Y')))
	{
		include('libs/database.php');
		include('libs/lang.php');

		$system = getRowFromTable('*','system','where id = 1','');

		$where = '';
		if(isset($_GET['branch']))
		{
			if(substr($_GET['branch'],0,1) == 'S')
			{
				$branch = ' and items.iitid = '.substr($_GET['branch'],1);
				$join = ' inner join items on joborders.joiid = items.iid';
			}
			elseif(substr($_GET['branch'],0,1) == 'B')
			{
				$branch = ' and orders.obcid = '.substr($_GET['branch'],1);
				$join = '';
			}
			else 
			{
				$branch = '';
				$join = '';
			}
		}
		else 
		{
			$branch = '';
			$join = '';
		}

		if(isset($_GET['from'],$_GET['to']) && $_GET['from'] != 0 && $_GET['to'] != 0 && $_GET['from'] <= $_GET['to'])
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = 'sum(joborders.joprice) as total,orders.odate as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where orders.odate between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; $group = 'orders.odate'; }
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = 'sum(joborders.joprice) as total,month(orders.odate) as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where year(orders.odate) = '.date('Y').' and month(orders.odate) between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; $group = 'month(orders.odate)'; }
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = 'sum(joborders.joprice) as total,year(orders.odate) as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where year(orders.odate) between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; $group = 'year(orders.odate)'; }
		}
		elseif(isset($_GET['from']) && $_GET['from'] != 0)
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = 'sum(joborders.joprice) as total,orders.odate as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where orders.odate >= "'.$_GET['from'].'"'.$branch; $group = 'orders.odate'; }
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = 'sum(joborders.joprice) as total,month(orders.odate) as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where year(orders.odate) = '.date('Y').' and month(orders.odate) >= "'.$_GET['from'].'"'.$branch; $group = 'month(orders.odate)'; }
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = 'sum(joborders.joprice) as total,year(orders.odate) as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where year(orders.odate) >= "'.$_GET['from'].'"'.$branch; $group = 'year(orders.odate)'; }
		}
		elseif(isset($_GET['to']) && $_GET['to'] != 0)
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = 'sum(joborders.joprice) as total,orders.odate as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where orders.odate <= "'.$_GET['to'].'"'.$branch; $group = 'orders.odate'; }
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = 'sum(joborders.joprice) as total,month(orders.odate) as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where year(orders.odate) = '.date('Y').' and month(orders.odate) <= "'.$_GET['to'].'"'.$branch; $group = 'month(orders.odate)'; }
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = 'sum(joborders.joprice) as total,year(orders.odate) as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where year(orders.odate) <= "'.$_GET['to'].'"'.$branch; $group = 'year(orders.odate)'; }
		}
		else
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = 'sum(joborders.joprice) as total,orders.odate as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where orders.odate <> "0000-00-00"'.$branch; $group = 'orders.odate'; }
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = 'sum(joborders.joprice) as total,month(orders.odate) as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where year(orders.odate) = '.date('Y').' and month(orders.odate) <> "00"'.$branch; $group = 'month(orders.odate)'; }
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = 'sum(joborders.joprice) as total,year(orders.odate) as date'; $where .= 'inner join joborders on orders.oid = joborders.jooid'.$join.' where year(orders.odate) <> "00"'.$branch; $group = 'year(orders.odate)'; }
		}
		$where .= ' group by '.$group.' order by '.$group.' ASC';
		$data = getAllDataFromTable($select,'orders',$where,'');

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

			for($i=0;$i<count($data);$i++)
			{
				$total = number_format($total + $data[$i]['total'], 2);
				
				$html .= '
							<tr>
                                <td width="50%">'.$data[$i]['date'].'</td>
								<td width="50%">'.number_format($data[$i]['total'], 2).' '.$system['currency'].'</td>
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
		$mpdf->Output('outcomes_'.date('Y-m-d-H-i-s', time()).'.pdf', 'I');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/outcomes');
?>