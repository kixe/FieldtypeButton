<?php namespace ProcessWire;

/**
 * Helper WireData Class to hold a Button object
 * @version 1.1.2
 *
 * @since 1.0.2 fixed bug in render() if single language mode (2016-12-06)
 * @since 1.0.3 synchronized version numbering (2016-12-06)
 * @since 1.0.4 - fixed repeater issue (2016-12-06)
 * @since 1.0.5 - fixed issue if page id stored in DB doesn't exist (2017-06-16)
 * @since 1.0.6 - fixed issue button label in non default language (2018-07-18)
 * @since 1.0.7 - added lang and langID properties modified output formatting (2018-07-19)
 * @since 1.0.8 - changed properties 'lang' to 'language'. 'lang' is placeholder for the homesegment now (2018-11-13)
 * @since 1.0.9 - fixed bug check LanguageSupport (2019-04-16)
 * @since 1.1.0 - added property 'langNonDefault' which will be replaced by homesegment
 *                only for non default languages and property 'langForEn' which will
 *                be replaced only by one single selected language
 *                NOTE: if 'langNonDefault' and 'langForEn' is set the replacement is always appended by a slash
 *                (2019-11-19)
 * @since 1.1.1 - added property 'httpTarget', and property aliasse 'url' (target) and 'httpUrl' (httpTarget) (2020-06-03)
 * @since 1.1.2 - added ProcessWire namespace, made render() hookable to optionally run textformatters (2022-03-03)
 */

class Button extends WireData {

    protected $languageSupport = false;

    public function __construct() {
        $this->set('label', '');
        $this->set('target', ''); // expecting page (object, id, path) or any url string
        $this->set('class', '');
        $this->set('html', '<a href="{target}" class="{class}">{label}</a>');
        parent::set('targetPage', null); // will be set if target is detected as instance of page
        if($this->modules->isInstalled('LanguageSupportPageNames')) {
            $this->languageSupport = $this->modules->get('LanguageSupport');
            $ul = $this->wire('user')->language;
            $hs = $this->wire('pages')->get(1)->localName($ul);
            $this->set('language', $ul);           
            $this->set('langID', $ul->id);    
            $this->set('lang', $hs);  
            $this->set('langNonDefault', $ul->isDefault()? '' : $hs . '/');
            foreach ($this->wire('languages') as $language) {
                $hs = $this->wire('pages')->get(1)->localName($language);
                $lp = "langFor" . ucfirst($hs);
                $this->set($lp, $ul == $language? $hs . '/' : '');
            }
        }
    }

    private function setTargetPage(Page $page) {
        return parent::set('targetPage', $page);
    }

    private function unsetTargetPage() {
        return parent::set('targetPage', null);
    }

    public function set($key, $value) {
        $langLabels = array();
        if($this->languageSupport) {       
            $LanguagePageIDs = $this->languageSupport->otherLanguagePageIDs;
            // $LanguagePageIDs[] = $this->languageSupport->defaultLanguagePageID;
            foreach($LanguagePageIDs as $languageID) $langLabels[] = "label$languageID";
        }
        if (in_array($key, $langLabels) || $key == 'label' || $key == 'class' || $key == 'target' || $key == 'html') {
            if ($key == 'target') {
                // get page by id. If id doesn't exist reset.
                if (ctype_digit("$value")) {
                    $_target = wire('pages')->get("id=$value");
                    if ($_target->id) $value = $_target;
                    // else throw new WireException("Expecting Page ID if value is numeric. Page with ID=$value doesn't exist.");
                    else $value = null;
                }
                // get page by path
                else {
                    $_target = wire('pages')->get('path='.$this->sanitizer->selectorValue($value));
                    $value = $_target->id? $_target: $this->sanitizer->url($value);
                }                           
                if ($value instanceof Page) {
                    $this->setTargetPage($value);
                    $languageSupportPageNames = $this->modules->isInstalled('LanguageSupportPageNames');
                    $httpValue = $languageSupportPageNames? $value->httpUrl(wire('user')->language): $value->httpUrl;
                    parent::set('httpTarget', $httpValue);
                    $value = $languageSupportPageNames? $value->localUrl(wire('user')->language): $value->url;
                }
                else {
                    $this->unsetTargetPage();
                    parent::set('httpTarget', $value);
                }
            }
            else if (!is_string($value) && $value !== null) {
                throw new WireException("Button property $key only accepts string values");
            }

            else if($key == 'html') $value = $this->sanitizer->textarea($value, array('stripTags' => false));
            else $value = $this->sanitizer->text($value);
        }
        if (in_array($key, array('targetPage','httpTarget','httpUrl','url'))) throw new WireException("Modifying of property $key not allowed. Use 'target' instead.");
        else return parent::set($key, $value);
    }

    /**
     * get any property of this WireData object
     *
     */
    public function get($key) {
        if (strpos($key,'targetPage') === 0 && strpos($key, '.') !== false) return parent::getDot($key);
        if ($key == 'url') return parent::get('target');
        if ($key == 'httpUrl') return parent::get('httpTarget');
        return parent::get($key);
    }

    /**
     * Provide rendering for a button
     * multi language support (label will be translated to user language value)
     *
     */
    public function ___render() {
        if ($this->languageSupport) {
            $userLanguageID = wire('user')->language->id;
            if ($this->languages->getDefault()->id != $userLanguageID) {
                $_label = $this->{"label$userLanguageID"};
                // fallback to default language  if label is empty
                $this->label = $_label? $_label:$this->label;
            }
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