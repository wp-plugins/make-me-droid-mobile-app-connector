<?php

/*
 * Converts raw JSON output to a readable output including line jumps and indentation.
 */
function pretty_json($json)
{
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }

    return $result;
}

/*
 * Returns the right short language ("en", "fr", etc), according to current locale, that can match a destination url on Make me Droid.
 * Ex: http://www.makemedroid.com/en/guides/firstapptutorial/
 *		=> Must be "en" for a blog in english, or in another untranslated locale (default).
 *		=> Will be "fr" for a blog in french.
 */
function getLanguageSlugForMakeMeDroidURL()
{
	$shortLang = explode("_", get_locale());
	
	$lang = $shortLang[0];
	
	if ($lang == "fr")
		return "fr";
	else
		return "en";
}

/*
 * Helper to use i18n gettext call with a predefined text domain.
 */
function _tran($str)
{
	return __($str, MMD_WP_SLUG);
}

?>
