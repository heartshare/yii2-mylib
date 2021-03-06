<?php
namespace modules\apc\models;


use yii\base\Model;
use yii\db\QueryInterface;
use yii\base\InvalidCallException;


class APCu extends Model
{
	public $apcu_version, $php_version, $memory_type, $start_time, $uptime, $variables_size,
	$file_upload_progress,
	$hits, $misses, $inserts, $entries,
	$memory_segment_size, $memory_segments, $memory_total_size, $memory_available, $memory_used;
	
	
	public function attributeLabels()
	{
		return [
			'apcu_version' => 'APCu Version',
			'php_version' => 'PHP Version',
			'memory_type' => 'Memory Type',
			'start_time' => 'Start Time',
			'uptime' => 'Uptime',
			'file_upload_progress' => 'File Upload Support',
			'hits' => 'Hits',
			'misses' => 'Misses',
			'inserts' => 'Inserts',
			'entries' => 'Cached Variables',
			'memory_total_size' => 'Total Memory Size',	
		];
	}
	
	//cache_list
	//deleted_list
	public function __construct()
	{
		$duration = function ($ts) {
			$time = time();
			$years = (int)((($time - $ts)/(7*86400))/52.177457);
			$rem = (int)(($time-$ts)-($years * 52.177457 * 7 * 86400));
			$weeks = (int)(($rem)/(7*86400));
			$days = (int)(($rem)/86400) - $weeks*7;
			$hours = (int)(($rem)/3600) - $days*24 - $weeks*7*24;
			$mins = (int)(($rem)/60) - $hours*60 - $days*24*60 - $weeks*7*24*60;
			$str = '';
			if($years==1) $str .= "$years year, ";
			if($years>1) $str .= "$years years, ";
			if($weeks==1) $str .= "$weeks week, ";
			if($weeks>1) $str .= "$weeks weeks, ";
			if($days==1) $str .= "$days day,";
			if($days>1) $str .= "$days days,";
			if($hours == 1) $str .= " $hours hour and";
			if($hours>1) $str .= " $hours hours and";
			if($mins == 1) $str .= " 1 minute";
			else $str .= " $mins minutes";
			return $str;
		};
		
		$c = apcu_cache_info();
		$s = apcu_sma_info();
	
		$this->apcu_version = phpversion('apcu');
		$this->php_version = phpversion();
		$this->memory_type = $c['memory_type'];
		$this->variables_size = $c['mem_size'];
		$this->start_time = date('Y-m-d H:i:s', $c['start_time']);
		$this->uptime = $duration($c['start_time']);
		$this->file_upload_progress = $c['file_upload_progress'] == 1 ? 'Yes' : 'No';
		$this->hits = $c['num_hits'];
		$this->misses = $c['num_misses'];
		$this->inserts = $c['num_inserts'];
		$this->entries = $c['num_entries'];
	//	var_dump($c['cache_list']);
		$this->memory_available = $s['avail_mem'];
		$this->memory_segments = $s['num_seg'];
		$this->memory_segment_size = $s['seg_size'];
		$this->memory_total_size = $this->memory_segments * $this->memory_segment_size;
		$this->memory_used = $this->memory_total_size - $this->memory_available;
	}
	
	//for google charts
	static public function getVariables($cols = [])
	{
		return apcu_cache_info()['cache_list'];
		// 'info' => string 'mylib963454f612a8b5fb4a63ba1e97f028a1' (length=37)
		// 'ttl' => int 0
		// 'num_hits' => float 10
		// 'modification_time' => int 1423844226
		// 'creation_time' => int 1423844226
		// 'deletion_time' => int 0
		// 'access_time' => int 1423844515
		// 'ref_count' => int 0
		// 'mem_size' => int 3248
		
		//$cols = ['info','ttl'];
			/*$list = [];
		$list[] = $cols;
	
		foreach (apcu_cache_info()['cache_list'] as $i) {
			$ar = [];
			foreach ($cols as $c) {
				$ar[] = $i[$c];
			}
			$list[] = $ar;
		}
		
		return $list;*/
	}
	
	/**
	 * pretty printer for byte values
	 * copy-pasted from original apc file
	 * @param int $s bytes
	 * @return string
	 */
	static public function bsize($s)
	{
		foreach (array('','K','M','G') as $i => $k) {
			if ($s < 1024) break;
			$s/=1024;
		}
		return sprintf("%0.1f %sbytes", $s, $k);
	}
}

