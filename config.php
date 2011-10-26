<?php
return array(
	'backups'=>array(
		array(
			'group'=>'YmdG', // Grouping expression to use with date() function
			'offset'=>0, // Offset in seconds
			'count'=>12, // Count of backups preserved
			'dir'=>dirname(__FILE__).'/backups/hourly', // Directory of the backup
		),
		array(
			'group'=>'Ymd',
			'offset'=>0,
			'count'=>14,
			'dir'=>dirname(__FILE__).'/backups/daily',
		),
		array(
			'group'=>'YW',
			'offset'=>0,
			'count'=>10,
			'dir'=>dirname(__FILE__).'/backups/weekly',
		),
		array(
			'group'=>'Ym',
			'offset'=>0,
			'count'=>12,
			'dir'=>dirname(__FILE__).'/backups/monthly',
		),
		array(
			'group'=>'Y',
			'offset'=>0,
			'count'=>2,
			'dir'=>dirname(__FILE__).'/backups/yearly',
		),
	),
	'pickupDir'=> dirname(__FILE__).'/backups/pickup',
);
