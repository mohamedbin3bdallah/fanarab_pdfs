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
		$myselect = 'joborders.joiid as joiid,joborders.joitem as joitem,joborders.joprice as joprice,joborders.joquantity as joquantity,joborders.addtobill as addtobill,users.uname as employee,users.uphone as uphone,customers.cname as customer,customers.cphone as cphone,items.iname as item,items.icode as icode,branches.bcid as branch,bills.bid as bid,bills.btotal as total,bills.bnewtotal as newtotal,bills.bdiscount as discount,bills.bpay as pay,bills.brest as rest,bills.bcode as code,bills.bpaytype as paytype,bills.btype as type,bills.boid as oid,bills.btime as time,bills.notes as notes';
		$join = 'LEFT OUTER JOIN users on bills.beid = users.uid LEFT OUTER JOIN joborders on bills.boid = joborders.jooid LEFT OUTER JOIN items on joborders.joiid = items.iid LEFT OUTER JOIN orders on bills.boid = orders.oid LEFT OUTER JOIN customers on orders.ocid = customers.cid LEFT OUTER JOIN branches on orders.obcid = branches.bcid';
		if(isset($_GET['from'],$_GET['to']) && $_GET['from'] != 0 && $_GET['to'] != 0 && $_GET['from'] <= $_GET['to'])
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = $myselect.',bills.b'.$calender[$system['calendar']].' as date'; $where .= $join.' where bills.b'.$calender[$system['calendar']].' between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; /*$group = 'bills.b'.$calender[$system['calendar']].''; */}
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = $myselect.',month(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') = '.$currentyear.' and month(bills.b'.$calender[$system['calendar']].') between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; /*$group = 'month(bills.b'.$calender[$system['calendar']].')'; */}
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = $myselect.',year(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') between "'.$_GET['from'].'" and "'.$_GET['to'].'"'.$branch; /*$group = 'year(bills.b'.$calender[$system['calendar']].')'; */}
		}
		elseif(isset($_GET['from']) && $_GET['from'] != 0)
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = $myselect.',bills.b'.$calender[$system['calendar']].' as date'; $where .= $join.' where bills.b'.$calender[$system['calendar']].' >= "'.$_GET['from'].'"'.$branch; /*$group = 'bills.b'.$calender[$system['calendar']].''; */}
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = $myselect.',month(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') = '.$currentyear.' and month(bills.b'.$calender[$system['calendar']].') >= "'.$_GET['from'].'"'.$branch; /*$group = 'month(bills.b'.$calender[$system['calendar']].')'; */}
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = $myselect.',year(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') >= "'.$_GET['from'].'"'.$branch; /*$group = 'year(bills.b'.$calender[$system['calendar']].')'; */}
		}
		elseif(isset($_GET['to']) && $_GET['to'] != 0)
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = $myselect.',bills.b'.$calender[$system['calendar']].' as date'; $where .= $join.' where bills.b'.$calender[$system['calendar']].' <= "'.$_GET['to'].'"'.$branch; /*$group = 'bills.b'.$calender[$system['calendar']].''; */}
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = $myselect.',month(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') = '.$currentyear.' and month(bills.b'.$calender[$system['calendar']].') <= "'.$_GET['to'].'"'.$branch; /*$group = 'month(bills.b'.$calender[$system['calendar']].')'; */}
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = $myselect.',year(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') <= "'.$_GET['to'].'"'.$branch; /*$group = 'year(bills.b'.$calender[$system['calendar']].')'; */}
		}
		else
		{
			if($_GET['type'] == 'D') { $type = 'daily'; $select = $myselect.',bills.b'.$calender[$system['calendar']].' as date'; $where .= $join.' where bills.b'.$calender[$system['calendar']].' <> "0000-00-00"'.$branch; /*$group = 'bills.b'.$calender[$system['calendar']].''; */}
			elseif($_GET['type'] == 'M') { $type = 'monthly'; $select = $myselect.',month(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') = '.$currentyear.' and month(bills.b'.$calender[$system['calendar']].') <> "00"'.$branch; /*$group = 'month(bills.b'.$calender[$system['calendar']].')'; */}
			elseif($_GET['type'] == 'Y') { $type = 'yearly'; $select = $myselect.',year(bills.b'.$calender[$system['calendar']].') as date'; $where .= $join.' where year(bills.b'.$calender[$system['calendar']].') <> "00"'.$branch; /*$group = 'year(bills.b'.$calender[$system['calendar']].')'; */}
		}
		//$where .= ' group by '.$group.' order by '.$group.' ASC';
		$bills = getAllDataFromTable($select,'bills',$where,'order by bills.btime DESC');
		if(!empty($bills))
		{
			for($i=0;$i<count($bills);$i++)
			{
				$data[$bills[$i]['bid']]['bid'] = $bills[$i]['bid'];
				$data[$bills[$i]['bid']]['date'] = $bills[$i]['date'];
				$data[$bills[$i]['bid']]['employee'] = $bills[$i]['employee'];
				$data[$bills[$i]['bid']]['uphone'] = $bills[$i]['uphone'];
				$data[$bills[$i]['bid']]['customer'] = $bills[$i]['customer'];
				$data[$bills[$i]['bid']]['cphone'] = $bills[$i]['cphone'];
				$data[$bills[$i]['bid']]['oid'] = $bills[$i]['oid'];
				$data[$bills[$i]['bid']]['code'] = $bills[$i]['code'];
				$data[$bills[$i]['bid']]['time'] = $bills[$i]['time'];
				$data[$bills[$i]['bid']]['notes'] = $bills[$i]['notes'];
				$data[$bills[$i]['bid']]['total'] = $bills[$i]['total'];
				$data[$bills[$i]['bid']]['newtotal'] = $bills[$i]['newtotal'];
				$data[$bills[$i]['bid']]['discount'] = $bills[$i]['discount'];
				$data[$bills[$i]['bid']]['pay'] = $bills[$i]['pay'];
				$data[$bills[$i]['bid']]['rest'] = $bills[$i]['rest'];
				$data[$bills[$i]['bid']]['paytype'] = $bills[$i]['paytype'];
				$data[$bills[$i]['bid']]['type'] = $bills[$i]['type'];
				if($bills[$i]['addtobill'] == '1')
				{
					$data[$bills[$i]['bid']]['items'][$i]['item'] = $bills[$i]['item'];
					$data[$bills[$i]['bid']]['items'][$i]['joitem'] = $bills[$i]['joitem'];
					$data[$bills[$i]['bid']]['items'][$i]['icode'] = $bills[$i]['icode'];
					$data[$bills[$i]['bid']]['items'][$i]['price'] = $bills[$i]['joprice'];
					$data[$bills[$i]['bid']]['items'][$i]['quantity'] = $bills[$i]['joquantity'];
				}
			}

		$paymentmethod = array(1=>lang('atm'),2=>lang('banktransfer'),3=>lang('cash'));
		$billtype = array(1=>lang('normal'),2=>lang('vip'));

		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s');
		
		
		foreach($data as $order)
		{
		if($order['discount'] == '') $order['discount'] = '00.00';
		if($order['rest'] == '') $order['rest'] = '00.00';
		
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
					<title>'.lang('bills').'</title>
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
                                <th>'.lang('number').' '.lang('bill').'</th>
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
                                <th>'.lang('paymentmethod').'</th>
                                <td>'.$paymentmethod[$order['paytype']].'</td>
								<th>'.lang('billtype').'</th>
                                <td>'.$billtype[$order['type']].'</td>
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
                                <td>'.$order['total'].' '.$system['currency'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('mustpay').'</th>
                                <td>'.number_format(($order['newtotal']-$order['discount']),2).' '.$system['currency'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('discount').'</th>
                                <td>'.$order['discount'].' '.$system['currency'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('payed').'</th>
                                <td>'.$order['pay'].' '.$system['currency'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('rest').'</th>
                                <td>'.$order['rest'].' '.$system['currency'].'</td>
                              </tr>
                          </table>
		';
		$html .= '
                          <table>
                              <tr>
                                <th>'.lang('employee').'</th>
                                <td>'.$order['employee'].'</td>
                              </tr>
							  <tr>
                                <th>'.lang('mobile').'</th>
                                <td>'.$order['uphone'].'</td>
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
		$mpdf->Output('bills_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
		}
		else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/bills');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/reports/bills');
?>