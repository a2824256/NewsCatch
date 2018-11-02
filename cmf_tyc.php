<?php

class cmf_tyc
{
    private $replaced = array();
    public $synonyms = array();

    //construct
    function __construct()
    {
        $this->synonyms = require(dirname(__FILE__) . '/cmf_ciku.php');
    }

    //replace
    function replace($text)
    {
        foreach ($this->synonyms as $key => $val) {
            if (preg_match("/" . $key . "/", $text) && !in_array($key, $this->replaced)) {
                $text = str_replace($key, (is_array($val) ? $val[array_rand($val)] : $val), $text);
                array_push($this->replaced, $val);
            }
        }

        return $text;
    }
}
