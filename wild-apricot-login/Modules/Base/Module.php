<?php

class WA_Modules_Base_Module implements WA_Modules_Interfaces_IModule
{
    const JS_ID_PREFIX = 'wa-integration';
    const CSS_ID_PREFIX = 'wa-integration';

    protected $id;
    protected $name;
    protected $plugin;
    protected $core;
    protected $jsPath;
    protected $cssPath;
    protected $imgPath;

    public function __construct($id, $name, WaIntegrationPlugin $plugin)
    {
        $this->id = $id;
        $this->name = $name;
        $this->plugin = $plugin;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function activate(WA_Modules_Interfaces_ICore $core, array $args = null)
    {
        $this->core = $core;
    }

    public function getOption($optionKey, $forceLoad = false)
    {
        return $this->core->getModuleOption($this->id, $optionKey, $forceLoad);
    }

    public function setOption($optionKey, $optionValue)
    {
        return $this->core->setModuleOption($this->id, $optionKey, $optionValue);
    }

    public function unsetOption($optionKey)
    {
        return $this->core->unsetModuleOption($this->id, $optionKey);
    }

    public function getSessionData($key)
    {
        return $this->core->getModuleSessionData($this->id, $key);
    }

    public function setSessionData($key, $value)
    {
        $this->core->setModuleSessionData($this->id, $key, $value);
    }

    public function unsetSessionData($key)
    {
        $this->core->unsetModuleSessionData($this->id, $key);
    }

    public function enqueueScript($scriptName)
    {
        $id = $this->getScriptId($scriptName);
        $url = $this->getScriptUrl($scriptName);

        wp_enqueue_script($id, $url, array('jquery'), $this->plugin->getConfigOption('version'));

        return $id;
    }

    public function getScriptUrl($scriptName)
    {
        $scriptPath = $this->getScriptPath($scriptName);

        return plugins_url($scriptPath, dirname(__FILE__));
    }

    public function getImageUrl($imgName)
    {
        $imgPath = '/' . $this->id . '/' . $this->plugin->getConfigOption('images_dir') . '/' . $imgName;

        return plugins_url($imgPath . '?' . $this->plugin->getConfigOption('version'), dirname(__FILE__));
    }

    public function enqueueStyle($styleName)
    {
        if (!is_string($this->cssPath))
        {
            $this->cssPath = '/' . $this->id . '/' . $this->plugin->getConfigOption('css_dir') . '/';
        }

        $stylePath = $this->cssPath . $styleName;
        $id = self::CSS_ID_PREFIX . preg_replace("/[^a-z]/i", '-', preg_replace("/\.[^.]+$/", '', $stylePath));
        $url = plugins_url($stylePath, dirname(__FILE__));

        wp_enqueue_style($id, $url);

        return $id;
    }

    public function isSettingsValid()
    {
        return true;
    }

    private function getScriptPath($scriptName)
    {
        if (!is_string($this->jsPath))
        {
            $this->jsPath = '/' . $this->id . '/' . $this->plugin->getConfigOption('js_dir') . '/';
        }

        return $this->jsPath . $scriptName;
    }

    private function getScriptId($scriptName)
    {
        $scriptPath = $this->getScriptPath($scriptName);

        return self::JS_ID_PREFIX . preg_replace("/[^a-z]/i", '-', preg_replace("/\.[^.]+$/", '', $scriptPath));
    }
}