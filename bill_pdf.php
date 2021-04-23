<?php
	if(isset($_GET['id']) && is_numeric($_GET['id']))
	{
		include('libs/database.php');
		include('libs/lang.php');
		include('libs/calenderdate.php');

		$system = getRowFromTable('*','system','where id = 1','');

		$preorder = getAllDataFromTable('bills.*,users.uname as employee,users.uphone as phone,customers.cname as customer,customers.cphone as cphone,orders.oid as oid,orders.ocode as ocode,joborders.joitem as joitem,joborders.joprice as joprice,joborders.joquantity as joquantity,joborders.addtobill as addtobill,items.icode as icode,items.iname as item','bills','LEFT OUTER JOIN users ON bills.beid = users.uid LEFT OUTER JOIN orders ON orders.oid = bills.boid LEFT OUTER JOIN customers ON orders.ocid = customers.cid LEFT OUTER JOIN joborders ON joborders.jooid = orders.oid LEFT OUTER JOIN items ON items.iid = joborders.joiid where bills.bid = '.$_GET['id'],'');
		if(!empty($preorder))
		{
			for($i=0;$i<count($preorder);$i++)
			{
				$order['oid'] = $preorder[$i]['oid'];
				$order['customer'] = $preorder[$i]['customer'];
				$order['cphone'] = $preorder[$i]['cphone'];
				$order['employee'] = $preorder[$i]['employee'];
				$order['phone'] = $preorder[$i]['phone'];
				$order['code'] = $preorder[$i]['bcode'];
				$order['btime'] = $preorder[$i]['btime'];
				$order['bnotes'] = $preorder[$i]['notes'];
				$order['total'] = $preorder[$i]['btotal'];
				$order['newtotal'] = $preorder[$i]['bnewtotal'];
				$order['discount'] = $preorder[$i]['bdiscount'];
				$order['pay'] = $preorder[$i]['bpay'];
				$order['rest'] = $preorder[$i]['brest'];
				$order['bpaytype'] = $preorder[$i]['bpaytype'];
				$order['btype'] = $preorder[$i]['btype'];
				$order['accept'] = $preorder[$i]['accept'];
				if($preorder[$i]['addtobill'] == '1')
				{
					$order['items'][$i]['icode'] = $preorder[$i]['icode'];
					$order['items'][$i]['item'] = $preorder[$i]['item'];
					$order['items'][$i]['joitem'] = $preorder[$i]['joitem'];
					$order['items'][$i]['price'] = $preorder[$i]['joprice'];
					$order['items'][$i]['quantity'] = $preorder[$i]['joquantity'];
				}
			}

		$paymentmethod = array(1=>lang('atm'),2=>lang('banktransfer'),3=>lang('cash'));
		$billtype = array(1=>lang('normal'),2=>lang('vip'));

		include_once($_SERVER['DOCUMENT_ROOT'].'/mpdf/mpdf.php');
		$mpdf = new mPDF('ar-s');
		
		if($order['discount'] == '') $order['discount'] = '00.00';
		if($order['rest'] == '') $order['rest'] = '00.00';
		
		if($order['btime'] != '') { if(date('H', $order['btime']) < 12) $time = date('h-i-s', $order['btime']).' '.lang('am'); else $time = date('h-i-s', $order['btime']).' '.lang('pm'); }
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
					<title>'.lang('pdf_bill').'</title>
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
                                <td>'.ArabicTools::arabicDate($system['calendar'].' Y-m-d', $order['btime']).'</td>
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
                                <td>'.$paymentmethod[$order['bpaytype']].'</td>
								<th>'.lang('billtype').'</th>
                                <td>'.$billtype[$order['btype']].'</td>
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
                                <th>'. lang('mobile').'</th>
                                <td>'.$order['phone'].'</td>
                              </tr>
                          </table>
		';
		$html .= '
				</body>
			</html>
		';

		$mpdf->AddPage();
		$mpdf->WriteHTML($html);
		$mpdf->Output('bill_'.$order['code'].'_'.ArabicTools::arabicDate($system['calendar'].' Y-m-d-H-i-s', time()).'.pdf', 'I');
		}
		else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/bills');
	}
	else header('Location: http://'.$_SERVER['HTTP_HOST'].'/account/bills');
?>