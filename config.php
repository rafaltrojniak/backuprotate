<?php
return array(
	'backups'=>array(
		// 'NameOfBackup'=>array(Config)
		'hourly'=>array(
			'rotate'=>'grouped', // Rotating algorithm
			'rotate_opts'=>array(
				'group'=>'YmdH', // Grouping expression to use with date() function
			),
			'clean'=>'count',
			'clean_opts'=>array(
				'count'=>12,
			),
			'dir'=>dirname(__FILE__).'/backups/hourly', // Directory of the backup
			'copier'=>'copier',
		),
		'daily'=>array(
			'rotate'=>'grouped',
			'rotate_opts'=>array(
				'group'=>'Ymd',
			),
			'clean'=>'count',
			'clean_opts'=>array(
				'count'=>14,
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
			'clean'=>'count',
			'clean_opts'=>array(
				'count'=>12,
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
