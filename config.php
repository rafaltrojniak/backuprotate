<?php
return array(
	dirname(__FILE__).'/backups/hourly'=>array(
		'group'=>'YmdG', // Grouping expression to use with date() function
		'offset'=>0, // Offset in seconds
		'count'=>12, // Count of backups preserved
	),
	dirname(__FILE__).'/backups/daily'=>array(
		'group'=>'Ymd',
		'offset'=>0,
		'count'=>14,
	),
	dirname(__FILE__).'/backups/weekly'=>array(
		'group'=>'YW',
		'offset'=>0,
		'count'=>10,
	),
	dirname(__FILE__).'/backups/monthly'=>array(
		'group'=>'Ym',
		'offset'=>0,
		'count'=>12,
	),
	dirname(__FILE__).'/backups/yearly'=>array(
		'group'=>'Y',
		'offset'=>0,
		'count'=>2,
	),
);
