<?php

class WA_Modules_Base_ShortCode implements WA_Modules_Interfaces_IShortCode
{
    const SCRIPT_DATA_NAME = 'WaLoginPluginShortcodesData';

    private static $isEditorDataInitialized = false;
    private static $isQuickTagsScriptEnqueued = false;
    private static $editPages = array('post.php','post-new.php');

    protected $module;
    protected $shortCodeName;
    protected $args;
    protected $attributes = array();
    protected $defaults = array();
    protected $formTitle = '';
    protected $buttonImgUrl = '';
    protected $formFields = array();
    protected $isContentWrapper = false;

    public function __construct(WA_Modules_Interfaces_IModule $module, $shortCodeName, array $args = null)
    {
        $this->module = $module;
        $this->shortCodeName = $shortCodeName;
        $this->args = $args;

        add_shortcode($shortCodeName, array($this, 'render'));
        add_action('admin_init', array($this, 'addEditorButtons'));
    }

    public function render($attributes, $content, $shortCodeName)
    {
        return '';
    }

    public function addEditorButtons()
    {
        if (!current_user_can('edit_posts') || !current_user_can('edit_pages')) { return; }

        if (get_user_option('rich_editing') == 'true')
        {
            foreach (self::$editPages as $hook)
            {
                add_action('admin_head-' . $hook, array($this, 'localizeEditorScript'));
            }

            add_filter('mce_external_plugins', array($this, 'addRichEditorPlugin'));
            add_filter('mce_buttons', array($this, 'registerRichEditorButton'));
            add_action('admin_enqueue_scripts', array($this, 'addQuickTagScript'));
        }
    }

    public function addQuickTagScript($hook)
    {
        if (self::$isQuickTagsScriptEnqueued || !in_array($hook, self::$editPages) || !wp_script_is('quicktags')) { return; }

        self::$isQuickTagsScriptEnqueued = true;
        wp_enqueue_script('WaShortcodeQuickTagButton', plugins_url('/Base/js/WaShortcodeQuickTagButton.js', dirname(__FILE__)), array('jquery'));
    }

    public function addRichEditorPlugin($pluginArr)
    {
        $buttonId = $this->getEditorButtonId();
        $pluginArr[$buttonId] = plugins_url('/Base/js/WaShortcodeEditorButtons.js', dirname(__FILE__));

        return $pluginArr;
    }

    public function registerRichEditorButton($buttons)
    {
        $buttonId = $this->getEditorButtonId();
        array_push($buttons, $buttonId);

        return $buttons;
    }

    public function localizeEditorScript()
    {
        echo '<script type="text/javascript">';

        if (!self::$isEditorDataInitialized)
        {
            echo 'var ' . self::SCRIPT_DATA_NAME . ' = {};';

            self::$isEditorDataInitialized = true;
        }

        echo self::SCRIPT_DATA_NAME . '["' . $this->shortCodeName . '"] = ' . json_encode($this->getEditorButtonData());
        echo '</script>';
    }

    protected function disablePageCache()
    {
        if (!defined('DONOTCACHEPAGE'))
        {
            define('DONOTCACHEPAGE',true);
        }
    }

    private function getEditorButtonId()
    {
        return $this->shortCodeName;
    }

    private function getEditorButtonData()
    {
        return array(
            'shortcodeTag' => $this->shortCodeName,
            'formTitle' => $this->formTitle,
            'buttonImgUrl' => $this->buttonImgUrl,
            'attr' => $this->attributes,
            'defaults' => $this->defaults,
            'formFields' => $this->formFields,
            'isContentWrapper' => $this->isContentWrapper
        );
    }
} 