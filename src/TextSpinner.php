<?php

namespace Epigrade\TextSpinner;

/**
 * Text spinner class.
 */
class TextSpinner
{

    private $spintax;
    private $placeholders;
    private $syntaxMarkers;

    /**
     * Constructor of the class.
     * 
     * @param string $spintax Optional string containing the spintax. 
     * If not provided here you must set $spintax before running any functions on it.
     */
    public function __construct($spintax = null, $placeholders = [])
    {
        $this->spintax = $spintax;
        $this->placeholders = $placeholders;

        $this->syntaxMarkers = [
            'open' => '{',
            'close' => '}',
            'separator' => '|',
            'placeholder' => '~',
        ];
    }

    /**
     * Get spintax.
     */
    public function getSpintax()
    {
        return $this->spintax;
    }

    /**
     * Set spintax.
     */
    public function setSpintax($spintax)
    {
        $this->spintax = $spintax;
    }

    /**
     * Get placeholders.
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Set placeholders.
     */
    public function setPlaceholders($placeholders)
    {
        $this->placeholders = $placeholders;
    }

    /**
     * Get syntax markers.
     */
    public function getSyntaxMarkers()
    {
        return $this->syntaxMarkers;
    }

    /**
     * Set syntax markers.
     */
    public function setSyntaxMarkers($syntaxMarkers)
    {
        $this->syntaxMarkers = $syntaxMarkers;
    }

    /**
     * Validate spintax if properly formatted (number of open brackets matches close brackets) 
     * and optionally if all placeholders have a value assigned.
     * 
     * @param boolean $validatePlaceholders Also validate placeholders.
     * @return boolean True if spintax is valid, false otherwise.
     */
    public function validate($validatePlaceholders = false)
    {
        $brackets = 0;
        $spintaxLength = strlen($this->spintax);
        for ($i = 0; $i < $spintaxLength; $i++) {
            if ($this->spintax[$i] == $this->syntaxMarkers['open'])
                $brackets++;
            elseif ($this->spintax[$i] == $this->syntaxMarkers['close'])
                $brackets--;
        }
        if ($brackets === 0) {
            if ($validatePlaceholders) {
                if ($this->invalidPlaceholders()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Find invalid placeholders (not found in $placeholders member, not having a string value 
     * or containing the $syntaxMarkers['placeholder'] character).
     * 
     * @return (boolean|array) False if all placeholders were found and have valid values or 
     * an array with invalid placeholders.
     */
    public function invalidPlaceholders()
    {
        $notFound = array();
        preg_match_all(
            '/'
                . preg_quote($this->syntaxMarkers['placeholder'], '/')
                . '[^' . preg_quote($this->syntaxMarkers['placeholder'], '/') . ']+'
                . preg_quote($this->syntaxMarkers['placeholder'], '/')
                . '/',
            $this->spintax,
            $matches
        );
        $matches = array_unique($matches[0]);
        foreach ($matches as $val) {
            $val = trim($val, $this->syntaxMarkers['placeholder']);
            if (
                !isset($this->placeholders[$val]) ||
                !is_string($this->placeholders[$val]) ||
                strpos($this->placeholders[$val], $this->syntaxMarkers['placeholder']) !== false
            ) {
                $notFound[] = $val;
            }
        }
        if (count($notFound) > 0) {
            return $notFound;
        }
        return false;
    }

    /**
     * Spin the spintax and optionally replace placeholders with placeholder values.
     * 
     * @param string $spintax Optional string to set $spintax member to.
     */
    public function spin($replacePlaceholders = true)
    {
        $spunText = $this->spinPass($this->spintax);
        if ($replacePlaceholders)
            $spunText = $this->replacePlaceholders($spunText);
        return $spunText;
    }

    /**
     * Replace placeholders inside a string with actual values.
     * 
     * @param string $str String containing placeholders.
     * @param array $placeholders Associative array with keys being the placeholder name 
     * (without delimiter) and values the strings to replace them with.
     * @param string $delimiter delimiter character(s) for the placeholder. Default is '~'.
     * @return type
     */
    public function replacePlaceholders($str)
    {
        if (!is_array($this->placeholders))
            return $str;
        foreach ($this->placeholders as $key => $val) {
            $str = str_replace(
                $this->syntaxMarkers['placeholder']
                    . $key
                    . $this->syntaxMarkers['placeholder'],
                $this->spinPass($val),
                $str
            );
        }
        return $str;
    }

    private function spinPass($mytext)
    {
        while ($this->inStr($this->syntaxMarkers['close'], $mytext)) {
            $rbracket = strpos($mytext, $this->syntaxMarkers['close'], 0);
            $tString = substr($mytext, 0, $rbracket);
            $tStringToken = explode($this->syntaxMarkers['open'], $tString);
            $tStringCount = count($tStringToken) - 1;
            $tString = $tStringToken[$tStringCount];
            $tStringToken = explode($this->syntaxMarkers['separator'], $tString);
            $tStringCount = count($tStringToken) - 1;
            $i = rand(0, $tStringCount);
            $replace = $tStringToken[$i];
            $tString = $this->syntaxMarkers['open'] . $tString . $this->syntaxMarkers['close'];
            $mytext = $this->strReplaceFirst($tString, $replace, $mytext);
        }
        return $mytext;
    }

    private function strReplaceFirst($s, $r, $str)
    {
        $l = strlen($str);
        $a = strpos($str, $s);
        $b = $a + strlen($s);
        $temp = substr($str, 0, $a) . $r . substr($str, $b, ($l - $b));
        return $temp;
    }

    private function inStr($needle, $haystack)
    {
        return @strpos($haystack, $needle) !== false;
    }
}
