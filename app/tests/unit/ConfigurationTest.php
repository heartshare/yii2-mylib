<?php
namespace tests\components;

use app\components\Configuration;
use org\bovigo\vfs\vfsStream;
use yii\db\ActiveRecord;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
	/* @var $base \org\bovigo\vfs\vfsStreamDirectory   */
	private $base;
	/* @var $config Configuration */
	private $config;
	
	private $cfg_file = 'config/libconfig.json'; //rel to @app
	
	protected function setUp()
	{
		$this->base = vfsStream::setup('base', null, ['config' => ['books' => [] ], ]);
		\Yii::$aliases['@app'] = vfsStream::url('base');
		$this->config = new Configuration(['config_file' => "@app/{$this->cfg_file}" ]);
	}
	
	
	public function test_getVersion()
	{
		$this->assertTrue(is_string($this->config->getVersion()));
	}
	
	
	public function test_save()
	{
		$this->config->save();
		$this->assertTrue($this->base->hasChild($this->cfg_file), 'config file was not saved');
		
		/* @var $default Configuration */
		/* @var $saved Configuration */
		$default = json_decode(file_get_contents(dirname(__DIR__).'/data/default_config.json'));
		$saved = json_decode(file_get_contents($this->base->getChild($this->cfg_file)->url()));
		$this->assertEquals($default, $saved, 'saved config file doesnt match default one');
		
		//check changes are saved
		$this->config->system->language = 'yo-yo';
		$this->config->save();
		$changed = json_decode(file_get_contents($this->base->getChild($this->cfg_file)->url()));
		$this->assertEquals($this->config->system->language, 'yo-yo', 'config object was not changed');
		$this->assertEquals($this->config->system->language, $changed->system->language, 'config change was not saved to file');
	}
	
	// test introduction of new option into default config, via reflection
	public function test_load()
	{
		$default_config = $this->config->getDefaultCfg();
		$default_config->system->new_param = 'some value';
		
		$mock_cfg = $this->getMockBuilder('\app\components\Configuration')
			->disableOriginalConstructor()
			->setMethods(['getDefaultCfg'])
			->getMock();
		
		$mock_cfg->expects($this->any())->method('getDefaultCfg')->willReturn($default_config);
		$mock_cfg->load($this->config->config_file); // must reflect new parameter in hardcoded default config
		$this->assertEquals($mock_cfg->system->new_param, 'some value');
	}
	
	
	public function test_EncodeDecode()
	{
		$filename = 'фівзїхыssAsd.ext'; //utf-8
		$enc = $this->config->Encode($filename); // set codepage
		$dec = $this->config->Decode($enc); // utf-8
		
		$this->assertEquals($filename, $dec, 'filename encode/decode has failed');
	}
	
	// file exists
	public function test_load_in_constructor()
	{
		$config2 = new Configuration(['config_file' => $this->config->config_file]);
	}
	
	/**
	 * @expectedException yii\base\InvalidCallException
	 */
	function test_setVersion_NotAllowed()
	{
		$this->config->version = 'asd';
	}
	
	/**
	 * @expectedException yii\base\InvalidCallException
	 */
	function test_setSystem_NotAllowed()
	{
		$this->config->system = 'asd';
	}
	
	
	/**
	 * @expectedException yii\base\InvalidValueException
	 */
	function test_load_WrongConfigFile()
	{
		$this->config->load('asd/asd/asd');
	}
	
	/**
	 * @expectedException yii\base\InvalidValueException
	 */
	function test_save_WrongConfigFile()
	{
		$this->config->config_file = 'asd/asd/asd';
		$this->config->save();
	}
	
	
	
	
	
	
	
	
	

}