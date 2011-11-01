<?php
return array(
	'backups'=>array(
		// 'NameOfBackup'=>array(Config)
		'hourly'=>array(
			'rotate'=>'grouped', // Rotating algorithm
			'rotate_opts'=>array(
				'group'=>'YmdH', // Grouping expression to use with date() function
			),
			'clean'=>'age',
			'clean_opts'=>array(
				'age'=>"P1W", // For Age algorithm date interval in format http://pl.php.net/manual/en/dateinterval.construct.php
				'count'=>7, // For Count algorithm the count of the backups keeped
			),
			'dir'=>dirname(__FILE__).'/backups/hourly', // Directory of the backup
			'copier'=>'copier',
		),
		'daily'=>array(
			'rotate'=>'grouped',
			'rotate_opts'=>array(
				'group'=>'Ymd',
			),
			'clean'=>'age',
			'clean_opts'=>array(
				'age'=>"P1M",
			),
			'dir'=>dirname(__FILE__).'/backups/daily',
			'copier'=>'linker',
		),
		'weekly'=>array(
			'rotate'=>'grouped',
			'rotate_opts'=>array(
				'group'=>'YW',
			),
			'clean'=>'count',
			'clean_opts'=>array(
				'count'=>10,
			),
			'dir'=>dirname(__FILE__).'/backups/weekly',
			'copier'=>'linker',
		),
		'monthly'=>array(
			'rotate'=>'grouped',
			'rotate_opts'=>array(
				'group'=>'Ym',
			),
			'clean'=>'age',
			'clean_opts'=>array(
				'age'=>"P1Y",
			),
			'dir'=>dirname(__FILE__).'/backups/monthly',
			'copier'=>'linker',
		),
		'yearly'=>array(
			'rotate'=>'grouped',
			'rotate_opts'=>array(
				'group'=>'Y',
			),
			'clean'=>'count',
			'clean_opts'=>array(
				'count'=>2,
			),
			'dir'=>dirname(__FILE__).'/backups/yearly',
			'copier'=>'linker',
		),
	),
	'pickupDir'=> dirname(__FILE__).'/backups/pickup',
);
