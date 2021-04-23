<?php

function sms_getbalance($username,$password)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://www.sms-quick.com/api/getbalance.php");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$username&password=$password&return=json");
	return curl_exec($ch);
}
	
function sms_sendmessage($username,$password,$sender,$numbers,$message)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://www.sms-quick.com/api/sendsms.php");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$username&password=$password&message=$message&numbers=$numbers&sender=$sender&unicode=E");
	return curl_exec($ch);
}
	
function randomCode($length=9)
{
   $characters = "23456789ABCDEFHJKLMNPRTVWXYZabcdefghijklmnopqrstuvwxyz";
   $string = '';
   for ($p = 0; $p < $length; $p++) 
   {
       $string .= $characters[mt_rand(0, strlen($characters)-1)];
   }
   return $string;
}

function exist($table,$where)
{
	include("libs/config.php");
	include("libs/opendb.php");
	$result = mysqli_query($con,"select count(*) as count from {$table} $where");
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	include("libs/closedb.php");
	if($row['count'] == 0) return 0;
	else return 1;
}

function getNoOfRowsFromTable($table,$where)
{
	include("libs/config.php");
	include("libs/opendb.php");
	$row['count'] = 0;
	$result = mysqli_query($con,"select count(*) as count from {$table} $where");
	if(!empty($result)) $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	include("libs/closedb.php");
	return $row['count'];
}

function getRowFromTable($select,$table,$where,$limit)
{
	include("libs/config.php");
	include("libs/opendb.php");
	$result = mysqli_query($con,"select $select from {$table} $where $limit");
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	include("libs/closedb.php");
	return $row;
}

function getAllDataFromTable($select,$table,$where,$limit)
{
	include("libs/config.php");
	include("libs/opendb.php");
	$result = mysqli_query($con,"select $select from {$table} $where $limit");
	$allrows = array();	
	if(!empty($result))
	{
		for($i=0; $row = mysqli_fetch_array($result, MYSQLI_ASSOC); $i++)
		{
			$allrows[$i] = $row;
		}
	}
	include("libs/closedb.php");
	return $allrows;
}

function getAllOutcomeDataFromTable($select,$table,$where,$limit)
{
	include("libs/config.php");
	include("libs/opendb.php");
	$result = mysqli_query($con,"select $select from {$table} $where $limit");
	$allrows = array();	
	if(!empty($result))
	{
		for($i=0; $row = mysqli_fetch_array($result, MYSQLI_ASSOC); $i++)
		{
			$allrows[$row['date']] = $row['total'];
		}
	}
	include("libs/closedb.php");
	return $allrows;
}

function insertRow($table,$row)
{
	include("libs/config.php");
	include("libs/opendb.php");
	$fquery = "insert into {$table} (";
	$lquery = " values (";
	foreach ($row as $key => $value)
	{
		$fquery .= $key;
		$lquery .= "'".$value."'";
		$data  = array_keys($row);
		$lastkey = array_pop($data);
		if($key != $lastkey) 
		{	
			$fquery .= ",";
			$lquery .= ",";
		}	
	}
	$fquery .= ")";
	$lquery .= ")";
	$query = $fquery.$lquery;
	$stmt = mysqli_prepare($con,$query);
	if(mysqli_stmt_execute($stmt)) return mysqli_insert_id($con);
	else return 0;
    /*include("libs/closedb.php");
	unset($data,$lastkey,$row,$fquery,$lquery,$query,$result);*/
}

function update($table,$set,$where)
{
	include("libs/config.php");
	include("libs/opendb.php");	
	$stmt = mysqli_prepare($con,"update {$table} $set $where");
	if(mysqli_stmt_execute($stmt)) return 1;
	else return 0;
	//include("libs/closedb.php");
}

function deleterow($table,$where)
{
	include("libs/config.php");
	include("libs/opendb.php");	
	$stmt = mysqli_prepare($con,"delete from {$table} $where");
	if(mysqli_stmt_execute($stmt)) return 1;
	else return 0;
	//include("libs/closedb.php");
}

function backup_tables($tables = '*',$fileName)
{
	include('libs/config.php');
    include("libs/opendb.php");

    //get all of the tables
    if($tables == '*')
    {
        $tables = array();
        $result = mysqli_query($con, 'SHOW TABLES');
        while($row = mysqli_fetch_row($result))
        {
            $tables[] = $row[0];
        }
    }
    else
    {
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }

    $return = '';
    //cycle through
    foreach($tables as $table)
    {
        $result = mysqli_query($con, 'SELECT * FROM '.$table);
        $num_fields = mysqli_num_fields($result);
        $num_rows = mysqli_num_rows($result);

        $return.= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = mysqli_fetch_row(mysqli_query($con, 'SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";
        $counter = 1;

        //Over tables
        for ($i = 0; $i < $num_fields; $i++) 
        {   //Over rows
            while($row = mysqli_fetch_row($result))
            {   
                if($counter == 1){
                    $return.= 'INSERT INTO '.$table.' VALUES(';
                } else{
                    $return.= '(';
                }

                //Over fields
                for($j=0; $j<$num_fields; $j++) 
                {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }

                if($num_rows == $counter){
                    $return.= ");\n";
                } else{
                    $return.= "),\n";
                }
                ++$counter;
            }
        }
        $return.="\n\n\n";
    }

    //save file
    //$fileName = '../account/backups/Backup-fanarab-'.date('Ymd-His').'.sql';
    $handle = fopen($fileName,'w+');
    fwrite($handle,$return);
    if(fclose($handle)){
        //echo "Done, the file name is: ".$fileName;
        exit; 
    }
	include("libs/closedb.php");
}

function uploadimgs($folder)
{
	$image_director = "uploads/".$folder;
	if(!is_dir($image_director)) $create_image_director = mkdir($image_director);	
	for($i=0;$i<count($_FILES['file']['name']);$i++)
	{
		if($_FILES['file']['size'][$i] < 1024000)
		{
			$file_extn[$i] = pathinfo($_FILES['file']['name'][$i], PATHINFO_EXTENSION);
			if($_FILES["file"]["type"][$i] == "image/jpg" || $_FILES["file"]["type"][$i] == "image/jpeg" || $_FILES["file"]["type"][$i] == "image/gif" || $_FILES["file"]["type"][$i] == "image/png")
			{
				$_FILES["file"]["name"][$i] = rand().'.'.$file_extn[$i];
				move_uploaded_file($_FILES["file"]["tmp_name"][$i], $image_director.'/'.$_FILES["file"]["name"][$i]);				
			}
		}
	}
}

function uploadimgsreturn($folder)
{
	$image_director = "uploads/".$folder;
	if(!is_dir($image_director)) $create_image_director = mkdir($image_director);	
	if($_FILES['file']['size'] < 1024000)
	{
		$file_extn = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
		if($_FILES["file"]["type"] == "image/jpg" || $_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/png")
		{
			$_FILES["file"]["name"] = rand().'.'.$file_extn;
			move_uploaded_file($_FILES["file"]["tmp_name"], $image_director.'/'.$_FILES["file"]["name"]);				
			return $_FILES["file"]["name"];
		}
		else return 0;
	}
	else return 0;
}

function uploadpic($file,$folder)
{
	$image_director = "uploads/".$folder;
	if(!is_dir($image_director)) $create_image_director = mkdir($image_director);	
	if($_FILES[$file]['size'] < 1024000)
	{
		$file_extn = pathinfo($_FILES[$file]['name'], PATHINFO_EXTENSION);
		if($_FILES[$file]["type"] == "image/jpg" || $_FILES[$file]["type"] == "image/jpeg" || $_FILES[$file]["type"] == "image/png")
		{
			$_FILES[$file]["name"] = rand().'.'.$file_extn;
			move_uploaded_file($_FILES[$file]["tmp_name"], $image_director.'/'.$_FILES[$file]["name"]);				
			return $_FILES[$file]["name"];
		}
		else return 0;
	}
	else return 0;
}

function uploadpicdynamic($file,$folder,$width,$height,$resize)
{
	$image_director = "uploads/".$folder;
	if(!is_dir($image_director)) $create_image_director = mkdir($image_director);	
	if($_FILES[$file]['size'] < 1024000)
	{
		$file_extn = pathinfo($_FILES[$file]['name'], PATHINFO_EXTENSION);
		if($_FILES[$file]["type"] == "image/jpg" || $_FILES[$file]["type"] == "image/jpeg" || $_FILES[$file]["type"] == "image/png")
		{
			$_FILES[$file]["name"] = rand().'.'.$file_extn;
			move_uploaded_file($_FILES[$file]["tmp_name"], $image_director.'/'.$_FILES[$file]["name"]);				
			$resizeObj = new Resize($image_director.'/'.$_FILES["file"]["name"]);
			$resizeObj -> resizeImage($width, $height, $resize);
			$resizeObj -> saveImage($image_director.'/'.$_FILES["file"]["name"], 100);
			return $_FILES[$file]["name"];
		}
		else return 0;
	}
	else return 0;
}

function delTree($dir)
{
	$files = array_diff(scandir($dir), array('.','..')); 
    foreach ($files as $file) { 
    (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
    }
    //rmdir($dir);
}

function sendemail($person)
{
	require_once('../PHPMailer/class.phpmailer.php');
	require_once('../PHPMailer/class.smtp.php');
	require_once('../PHPMailer/PHPMailerAutoload.php');
	$mail             = new PHPMailer(); // defaults to using php "mail()"
	$mail->IsSMTP(); // telling the class to use SMTP
	//$mail->Host       = "smtp.secureserver.net";
	$mail->Host       = "localhost";
	//	$mail->Host       = "smtpout.secureserver.net";      // sets GMAIL as the SMTP server
	//	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	//	$mail->SMTPSecure = 'ssl';
	//	$mail->Port = 465;
	//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
	//$mail->Username   = "";  // GMAIL username
	//$mail->Password   = "";					
	//$mail->AddReplyTo("name@yourdomain.com","First Last");
	$address = "info@elzinyforlaw.com";
	$mail->SetFrom($address, 'INFO');
	$mail->AddAddress($person['email']);
	$mail->Subject    = 'Activation';
	//$mail->AltBody    = "You can active your account on : "; // optional, comment out and test
	$mail->Body    = 'Activation link: http://www.elzinyforlaw.com/active.php?email='.$person['email'].'&code='.$person['code'];
	if($mail->Send()) return 1;
	else return 0;
}


function sendemail_admin($email,$subject,$message)
{
	require_once('../PHPMailer/class.phpmailer.php');
	require_once('../PHPMailer/class.smtp.php');
	require_once('../PHPMailer/PHPMailerAutoload.php');
	$mail             = new PHPMailer(); // defaults to using php "mail()"
	$mail->IsSMTP(); // telling the class to use SMTP
	//$mail->Host       = "smtp.secureserver.net";
	$mail->Host       = "localhost";
	//	$mail->Host       = "smtpout.secureserver.net";      // sets GMAIL as the SMTP server
	//	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	//	$mail->SMTPSecure = 'ssl';
	//	$mail->Port = 465;
	//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
	//$mail->Username   = "";  // GMAIL username
	//$mail->Password   = "";					
	//$mail->AddReplyTo("name@yourdomain.com","First Last");
	$address = "info@elzinyforlaw.com";
	$mail->SetFrom($address, 'INFO');
	$mail->AddAddress($email);
	$mail->Subject    = $subject;
	//$mail->AltBody    = "You can active your account on : "; // optional, comment out and test
	$mail->Body    = $message;
	if($mail->Send()) return 1;
	else return 0;
}

?>