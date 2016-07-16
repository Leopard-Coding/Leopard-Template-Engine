<?php
/**
 * Leopard Template Engine
 *
 * @version 3.0.0-alpha
 * @copyright ©Leopard
 * @license http://creativecommons.org/licenses/by-nd/4.0/ CC BY-ND 4.0
 *
 * @author Julian Pfeil
 */
namespace Leopard;

class TemplateEngine
{
	private $TemplateVars = [];
	public $Config = [
		'plugins' => [],
		'pluginPath' => 'plugins/',
		'pluginExtension' => '.inc.php',
		'templatePath' => 'templates/',
		'templateExtension' => '.tpl',
		'cachePath' => 'cache/',
		'cacheExtension' => '.cache.tpl',
		'caching' => false,
		'cacheLifetime' => 3600
	];
	
	public function __construct($ConfigFile = null)
	{
		if ($ConfigFile !== null) {
			require_once($ConfigFile);
			$this->Config = array_merge($this->Config, $Config);
		}
		foreach($this->Config['plugins'] as $Plugin) {
			include($this->Config['pluginPath'].$Plugin.$this->Config['pluginExtension']);
		}
		
		return;
	}
	
	public function __get($TemplateVarName)
	{
		return $this->TemplateVars[$TemplateVarName];
	}
	
	public function __set($TemplateVarName, $TemplateVarValue)
	{
		$this->TemplateVars[$TemplateVarName] = $TemplateVarValue;
		
		return;
	}
	
	public function __call($Method, $Arguments)
	{
		return call_user_func_array(Closure::bind($this->$Method, $this, get_called_class()), $Arguments);
	}
	
	private function checkCaching($TemplateName)
	{
		switch (true) {
			case ($this->Config['caching'] === false):
				return false;
				break;
			case (!file_exists($this->Config['cachePath'].$TemplateName.$this->Config['cacheExtension']) || time() - filemtime($this->Config['cachePath'].$TemplateName.$this->Config['cacheExtension']) < $this->Config['cacheLifetime']):
				return true;
				break;
			case ($this->Config['cachingCheck'] === true && filemtime($this->Config['cachePath'].$TemplateName.$this->Config['cacheExtension']) < filemtime($this->Config['templatePath'].$TemplateName.$this->Config['templateExtension'])):
				return true;
				break;
			default:
				return false;
		}
	}
	
	public function display($TemplateName) {
		if($this->checkCaching($TemplateName)) {
			ob_start();
			include($this->Config['templatePath'].$TemplateName.$this->Config['templateExtension']);
			$templateContent = ob_get_contents();
			ob_end_clean();
			file_put_contents($this->Config['cachePath'].$TemplateName.$this->Config['cacheExtension'], $templateContent);
			include($this->Config['cachePath'].$TemplateName.$this->Config['cacheExtension']);
		} elseif($this->Config['caching'] === false) {
			include($this->Config['templatePath'].$TemplateName.$this->Config['templateExtension']);
		} else {
			include($this->Config['cachePath'].$TemplateName.$this->Config['cacheExtension']);
		}
		
		return;
	}
}
