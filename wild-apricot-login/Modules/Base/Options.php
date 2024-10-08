<?php

class WA_Modules_Base_Options implements WA_Modules_Interfaces_IOptions
{
    protected $optionName;
    protected $options;
    protected $isOptionsChanged = false;
    protected $isOptionsLoaded = false;
    protected $crypt;
    protected $isEncryptionEnabled = true;
    protected $isAutoSaveEnabled = true;

    public function __construct($optionName, $enableEncryption = true, $enableAutoSave = true)
    {
        $this->optionName = $optionName;
        $this->isEncryptionEnabled = $enableEncryption === true;
        $this->isAutoSaveEnabled = $enableAutoSave === true;

        if ($this->isEncryptionEnabled)
        {
            $this->crypt = new WA_Modules_Base_Crypt_AES();
        }

        if ($this->isAutoSaveEnabled)
        {
            register_shutdown_function(array($this, 'save'));
        }
    }

    public function __destruct()
    {
        if ($this->isAutoSaveEnabled)
        {
            $this->save();
        }
    }

    public function getOption($unit, $key, $forceLoad = false)
    {
        $this->loadOptions($forceLoad);

        $unit = WA_Utils::sanitizeKey($unit);
        $key = WA_Utils::sanitizeKey($key);

        return (isset($this->options[$unit]) && isset($this->options[$unit][$key])) ? $this->options[$unit][$key] : null;
    }

    public function setOption($unit, $key, $value, $forceLoad = false)
    {
        $this->loadOptions($forceLoad);

        $unit = WA_Utils::sanitizeKey($unit);
        $this->createUnitOptions($unit);
        $key = WA_Utils::sanitizeKey($key);

        if (!isset($this->options[$unit][$key]) || $this->options[$unit][$key] !== $value)
        {
            $this->options[$unit][$key] = $value;
            $this->isOptionsChanged = true;
        }
    }

    public function unsetOption($unit, $key, $forceLoad = false)
    {
        $this->loadOptions($forceLoad);

        $unit = WA_Utils::sanitizeKey($unit);
        $key = WA_Utils::sanitizeKey($key);

        if (isset($this->options[$unit]) && isset($this->options[$unit][$key]))
        {
            unset($this->options[$unit][$key]);
            $this->isOptionsChanged = true;
        }
    }

    public function save()
    {
        if ($this->isOptionsChanged)
        {
            $options = maybe_serialize($this->options);

            if ($this->isEncryptionEnabled)
            {
                $options = $this->crypt->encrypt($options);
            }

            if (!$this->update($options))
            {
                throw new Exception('Unable to save options.');
            }
        }

        $this->isOptionsChanged = false;
    }

    protected function loadOptions($forceLoad = false)
    {
        if ($this->isOptionsLoaded && !$forceLoad) { return; }

        $this->isOptionsLoaded = true;
        $options = $this->load();

        if ($this->isEncryptionEnabled)
        {
            $options = $this->crypt->decrypt($options);
        }

        $options = maybe_unserialize($options);

        $this->options = WA_Utils::isNotEmptyArray($options) ? $options : array();
    }

    protected function createUnitOptions($unit)
    {
        if (!isset($this->options[$unit]) || !is_array($this->options[$unit]))
        {
            $this->options[$unit] = array();
        }
    }

    protected function update($options)
    {
        throw new Exception('You must implement "update" method.');
    }

    protected function load()
    {
        throw new Exception('You must implement "load" method.');
    }
}