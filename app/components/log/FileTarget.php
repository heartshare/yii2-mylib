<?php
namespace app\components\log;

use yii\helpers\VarDumper;

 class FileTarget extends \yii\log\FileTarget 
{
	public $with_microtime = false;
	
	/**
	 * (non-PHPdoc)
	 * @see \yii\log\Target::formatMessage()
	 */
	public function formatMessage($message)
	{
		list($text, $level, $category, $timestamp) = $message;
		$level = \yii\log\Logger::getLevelName($level);
		if (!is_string($text)) {
			$text = VarDumper::export($text);
		}
		$traces = [];
		if (isset($message[4])) {
			foreach($message[4] as $trace) {
				$traces[] = "in {$trace['file']}:{$trace['line']}";
			}
		}

		$prefix = $this->getMessagePrefix($message);
		
		return (date('Y-m-d H:i:s', $timestamp) . 
			($this->with_microtime ? ':' . round(($timestamp - floor($timestamp))*1000000) : ''))
			. " {$prefix}[$level][$category] $text"
			. (empty($traces) ? '' : "\n    " . implode("\n    ", $traces));
	}
	
}