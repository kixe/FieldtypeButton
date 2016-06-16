<?php

/**
 * Helper WireData Class to hold a Button object
 *
 */

class Button extends WireData {

    protected $languageSupport = false;

    public function __construct() {
        $this->set('label', '');
        $this->set('target', ''); // expecting page (object, id, path) or any url string
        $this->set('class', '');
        $this->set('html', '<a href="{target}" class="{class}">{label}</a>');
        parent::set('targetPage',null); // will be set if target is detected as instance of page
        if($this->modules->isInstalled('LanguageSupport')) $this->languageSupport = $this->modules->get('LanguageSupport');
    }

    private function setTargetPage(Page $page) {
        return parent::set('targetPage', $page);
    }

    public function set($key, $value) {
        $langLabels = array();
        if($this->languageSupport) {       
            $LanguagePageIDs = $this->languageSupport->otherLanguagePageIDs;
            // $LanguagePageIDs[] = $languageSupport->defaultLanguagePageID;
            foreach($LanguagePageIDs as $languageID) $langLabels[] = "label$languageID";
        }
        if (in_array($key, $langLabels) || $key == 'label' || $key == 'class' || $key == 'target' || $key == 'html') {
            if ($key == 'target') {
                // get page by id
                if (ctype_digit("$value")) {
                    $_target = wire('pages')->get("id=$value");
                    if (!$_target->id) throw new WireException("Expecting Page ID if value is numeric. Page with ID=$value doesn't exist.");
                    else $value = $_target;
                }
                // get page by path
                else {
                    $_target = wire('pages')->get("path=$value");
                    $value = $_target->id? $_target:$this->sanitizer->url($value);
                }                           
                if ($value instanceof Page) {
                    $this->setTargetPage($value);
                    $languageSupportPageNames = $this->modules->isInstalled('LanguageSupportPageNames');
                    $value = $languageSupportPageNames? $value->localUrl(wire('user')->language):$value->url;
                }
            }
            else if (!is_string($value) && $value !== null) {
                throw new WireException("Button property $key only accepts string values");
            }

            else if($key == 'html') $value = $this->sanitizer->textarea($value, array('stripTags' => false));
            else $value = $this->sanitizer->text($value);
        }
        if ($key == 'targetPage') throw new WireException("Modifying of property 'targetPage' not allowed. Use 'target' instead.");
        else return parent::set($key, $value);
    }

    /**
     * get any property of this WireData object
     *
     */
    public function get($key) {
        if (strpos($key,'targetPage') === 0 && strpos($key, '.') !== false) return parent::getDot($key);
        return parent::get($key);
    }

    /**
     * Provide rendering for a button
     * multi language support (label will be translated to user language value)
     *
     */
    public function render() {
        $userLanguageID = wire('user')->language->id;
        if ($this->languageSupport && $this->languages->getDefault()->id != $userLanguageID) {
            $this->label = $this->{"label$userLanguageID"};
        }
        return wirePopulateStringTags($this->html, $this);
    }

    /**
     * Return a string representing this button
     *
     */
    public function __toString() {
        return $this->render();
    }

}