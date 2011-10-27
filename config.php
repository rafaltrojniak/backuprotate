<?php
return array(
	'backups'=>array(
		// 'NameOfBackup'=>array(Config)
		'hourly'=>array(
			'rotate'=>'grouped', // Rotating algorithm
			'rotate_opts'=>array(
				'group'=>'YmdH', // Grouping expression to use with date() function
				//'offset'=>null, // Offset in seconds
				'count'=>12, // Count of backups preserved
			),
			'dir'=>dirname(__FILE__).'/backups/hourly', // Directory of the backup
		),
		'daily'=>array(
			'rotate'=>'grouped',
			'rotate_opts'=>array(
				'group'=>'Ymd',
				'count'=>14,
			),
			'dir'=>dirname(__FILE__).'/backups/daily',
		),
		'weekly'=>array(
			'rotate'=>'grouped',
			'rotate_opts'=>array(
				'group'=>'YW',
				'count'=>10,
			),
			'dir'=>dirname(__FILE__).'/backups/weekly',
		),
		'monthly'=>array(
			'rotate'=>'grouped',
			'rotate_opts'=>array(
				'group'=>'Ym',
				'count'=>12,
			),
			'dir'=>dirname(__FILE__).'/backups/monthly',
		),
		'yearly'=>array(
			'rotate'=>'grouped',
			'rotate_opts'=>array(
				'group'=>'Y',
				'count'=>2,
			),
			'dir'=>dirname(__FILE__).'/backups/yearly',
		),
	),
	'pickupDir'=> dirname(__FILE__).'/backups/pickup',
);
