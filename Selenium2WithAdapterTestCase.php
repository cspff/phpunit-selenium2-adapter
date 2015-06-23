<?php
/*
 * Copyright 2013 Roman Nix
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace AppDevl\Selenium2Adapter;

/**
 * Implements adapter for migration from PHPUnit_Extensions_SeleniumTestCase
 * to PHPUnit_Extensions_Selenium2TestCase.
 *
 * If user's TestCase class is implemented with old format (with commands
 * like open, type, waitForPageToLoad), it should extend MigrationToSelenium2
 * for Selenium 2 WebDriver support.
 */
class Selenium2WithAdapterTestCase extends \PHPUnit_Extensions_Selenium2TestCase{

    public function open($url){
        $this->url($url);
    }

    public function type($selector, $value){
        $input = $this->byQuery($selector);
        $input->value($value);
    }

    protected function byQuery($selector){
        if (preg_match('/^\/\/(.+)/', $selector)){
            /* "//a[contains(@href, '?logout')]" */
            return $this->byXPath($selector);
        } else if (preg_match('/^([a-z]+)=(.+)/', $selector, $match)){
            /* "id=login_name" */
            switch ($match[1]){
                case 'id':
                    return $this->byId($match[2]);
                    break;
                case 'name':
                    return $this->byName($match[2]);
                    break;
                case 'link':
                    return $this->byPartialLinkText($match[2]);
                    break;
                case 'xpath':
                    return $this->byXPath($match[2]);
                    break;
                case 'css':
                    $cssSelector = str_replace('..', '.', $match[2]);
                    return $this->byCssSelector($cssSelector);
                    break;

            }
        } else {
            return $this->byName($selector);
        }
    }

    protected function waitForPageToLoad($timeout){
        $this->timeouts()->implicitWait($timeout);
    }

    public function click($selector){
        $input = $this->byQuery($selector);
        $input->click();
    }

    public function select($selectSelector, $optionSelector){
        $selectElement = parent::select($this->byQuery($selectSelector));
        if (preg_match('/label=(.+)/', $optionSelector, $match)){
            $selectElement->selectOptionByLabel($match[1]);
        } else if (preg_match('/value=(.+)/', $optionSelector, $match)){
            $selectElement->selectOptionByValue($match[1]);
        } else {
            throw new Exception("Unknown option selector '$optionSelector'");
        }

    }

    public function isTextPresent($text){
        if (strpos($this->byCssSelector('body')->text(), $text) !== false){
            return true;
        } else {
            return false;
        }
    }

    public function isElementPresent($selector){
        $element = $this->byQuery($selector);
        if ($element->name()){
            return true;
        } else {
            return false;
        }
    }

    public function getText($selector){
        $element = $this->byQuery($selector);
        return $element->text();
    }

    public function clickAndWait($selector) {
        $element = $this->byQuery($selector);
        $element->click();
    }
}


