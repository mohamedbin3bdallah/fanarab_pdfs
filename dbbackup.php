<?php
include('libs/database.php');
$tables = '*';
$fileName = '../account/backups/Backup-fanarab-'.date('Ymd-His').'.sql';
backup_tables($tables,$fileName);
?>