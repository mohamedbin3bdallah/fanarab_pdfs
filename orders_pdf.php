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
		$myselect = 'orders.*,joborders.joiid as joiid,joborders.joitem as joitem,joborders.joprice as joprice,joborders.joquantity as joquantity,users.uname as employee,users.uphone as uphone,customers.cname as customer,customers.cphone as cphone,items.iname as item,items.icode as icode,branches.bcid as branch,bills.btotal as total';
		$join = 'LEFT OUTER JOIN users on orders.ouid = users.uid LEFT OUTER JOIN joborders on orders.oid = joborders.jooid LEFT OUTER JOIN items on joborders.joiid = items.iid LEFT OUTER JOIN bills on orders.oid = bills.boid LEFT OUTER JOIN customers on orders.ocid = customers.cid LEFT OUTER JOIN branches on orders.obcid = branches.bcid';
		if(isset($_GET['from'],$_GET['to']) && $_GET['from'] != 0 && $_GET['to'] != 0 && $_GET['from'] <= $_GET['to'])
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = $myselect.',orders.'.$calender[$system['calendar']].' as date'; $where .= $join.' where orders.'.$calender[$system['calendar']].' between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; /*$group = 'orders.'.$calender[$system['calendar']].''; */}
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = $myselect.',month(orders.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(orders.'.$calender[$system['calendar']].') = '.$currentyear.' and month(orders.'.$calender[$system['calendar']].') between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; /*$group = 'month(orders.'.$calender[$system['calendar']].')'; */}
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = $myselect.',year(orders.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(orders.'.$calender[$system['calendar']].') between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; /*$group = 'year(orders.'.$calender[$system['calendar']].')'; */}
		}
		elseif(isset($_GET['from']) && $_GET['from'] != 0)
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = $myselect.',orders.'.$calender[$system['calendar']].' as date'; $where .= $join.' where orders.'.$calender[$system['calendar']].' >= "'.$_GET['from'].'"'.$branch; /*$group = 'orders.'.$calender[$system['calendar']].''; */}
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = $myselect.',month(orders.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(orders.'.$calender[$system['calendar']].') = '.$currentyear.' and month(orders.'.$calender[$system['calendar']].') >= "'.$_GET['from'].'"'.$branch; /*$group = 'month(orders.'.$calender[$system['calendar']].')'; */}
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = $myselect.',year(orders.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(orders.'.$calender[$system['calendar']].') >= "'.$_GET['from'].'"'.$branch; /*$group = 'year(orders.'.$calender[$system['calendar']].')'; */}
		}
		elseif(isset($_GET['to']) && $_GET['to'] != 0)
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = $myselect.',orders.'.$calender[$system['calendar']].' as date'; $where .= $join.' where orders.'.$calender[$system['calendar']].' <= "'.$_GET['to'].'"'.$branch; /*$group = 'orders.'.$calender[$system['calendar']].''; */}
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = $myselect.',month(orders.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(orders.'.$calender[$system['calendar']].') = '.$currentyear.' and month(orders.'.$calender[$system['calendar']].') <= "'.$_GET['to'].'"'.$branch; /*$group = 'month(orders.'.$calender[$system['calendar']].')'; */}
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = $myselect.',year(orders.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(orders.'.$calender[$system['calendar']].') <= "'.$_GET['to'].'"'.$branch; /*$group = 'year(orders.'.$calender[$system['calendar']].')'; */}
		}
		else
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = $myselect.',orders.'.$calender[$system['calendar']].' as date'; $where .= $join.' where orders.'.$calender[$system['calendar']].' <> "0000-00-00"'.$branch; /*$group = 'orders.'.$calender[$system['calendar']].''; */}
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = $myselect.',month(orders.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(orders.'.$calender[$system['calendar']].') = '.$currentyear.' and month(orders.'.$calender[$system['calendar']].') <> "00"'.$branch; /*$group = 'month(orders.'.$calender[$system['calendar']].')'; */}
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = $myselect.',year(orders.'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(orders.'.$calender[$system['calendar']].') <> "00"'.$branch; /*$group = 'year(orders.'.$calender[$system['calendar']].')'; */}
		}
		//$where .= ' group by '.$group.' order by '.$group.' ASC';
		$orders = getAllDataFromTable($select,'orders',$where,'order by orders.otime DESC');
		if(!empty($orders))
		{
			for($i=0;$i<count($orders);$i++)
			{
				$data[$orders[$i]['oid']]['oid'] = $orders[$i]['oid'];
				$data[$orders[$i]['oid']]['date'] = $orders[$i]['date'];
				$data[$orders[$i]['oid']]['employee'] = $orders[$i]['employee'];
				$data[$orders[$i]['oid']]['uphone'] = $orders[$i]['uphone'];
				$data[$orders[$i]['oid']]['customer'] = $orders[$i]['customer'];
				$data[$orders[$i]['oid']]['cphone'] = $orders[$i]['cphone'];
				$data[$orders[$i]['oid']]['code'] = $orders[$i]['ocode'];
				$data[$orders[$i]['oid']]['time'] = $orders[$i]['otime'];
				$data[$orders[$i]['oid']]['notes'] = $orders[$i]['notes'];
				$data[$orders[$i]['oid']]['total'] = $orders[$i]['total'];
				$data[$orders[$i]['oid']]['items'][$i]['item'] = $orders[$i]['item'];
				$data[$orders[$i]['oid']]['items'][$i]['joitem'] = $orders[$i]['joitem'];
				$data[$orders[$i]['oid']]['items'][$i]['icode'] = $orders[$i]['icode'];
				$data[$orders[$i]['oid']]['items'][$i]['price'] = $orders[$i]['joprice'];
				$data[$orders[$i]['oid']]['items'][$i]['quantity'] = $orders[$i]['joquantity'];
			}

		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s');

		foreach($data as $order)
		{
			
		if($order['time'] != '') { if(date('H', $order['time']) < 12) $time = date('h-i-s', $order['time']).' '.lang('am'); else $time = date('h-i-s', $order['time']).' '.lang('pm'); }
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
					<title>'.lang('orders').'</title>
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
                                <td>'.$order['oid'].'</td>
								<th>'.lang('code').'</th>
                                <td><img src="../account/barcode/barcode.php?codetype=Code39&size=55&text='.$order['code'].'" /></td>
                              </tr>
							  <tr>
                                <th>'.lang('day').'</th>
                                <td>'.$order['date'].'</td>
								<th>'.lang('time').'</th>
                                <td>'.$time.'</td>
                              </tr>
							  <tr></tr>
                              <tr>
                                <th>'.lang('customer').'</th>
                                <td>'.$order['customer'].'</td>
								<th>'.lang('mobile').'</th>
                                <td>'.$order['cphone'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('employee').'</th>
                                <td>'.$order['employee'].'</td>
								<th>'.lang('mobile').'</th>
                                <td>'.$order['uphone'].'</td>
                              </tr>
                          </table>
		';
		if(!empty($order['items']))
		{
			$html .= '
                          <table>
                            <thead>
                              <tr>
                                <th width="15%">'.lang('code').'</th>
                                <th width="39%">'.lang('info').'</th>
                                <th width="17%">'.lang('quantity').'</th>
                                <th>'.lang('price').'</th>
                              </tr>
                            </thead>
                            <tbody>
			';
			foreach($order['items'] as $item)
			{
				$html .= '
							<tr>
                                <td width="15%">'.$item['icode'].'</td>
                                <td width="39%">'.$item['item'].$item['joitem'].'</td>
                                <td width="17%">'.$item['quantity'].'</td>
                                <td>'.$item['price'].' '.$system['currency'].'</td>
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
                                <td>'.number_format(($order['total']-$order['discount']),2).' '.$system['currency'].'</td>
                              </tr>
                          </table>
		';
		$html .= '
				</body>
			</html>
		';

		$mpdf->AddPage();
		$mpdf->WriteHTML($html);
		}
		$mpdf->Output('joborders_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
		}
		else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/orders');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/orders');
?>