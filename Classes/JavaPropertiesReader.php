<?php
/**
* A reader compatible with java.util.Properties, as per specification:
* @link http://docs.oracle.com/javase/7/docs/api/java/util/Properties.html#load%28java.io.Reader%29
*/
class JavaPropertiesReader {

  /* Special chars */
  const ESCAPE='\\';

  const NEWLINE = "\n";
  const CARRIAGE = "\r";
  const WINDOWS_NEWLINE = "\r\n";
  private $VALUE_TERMINALS = array(self::NEWLINE, self::CARRIAGE, self::WINDOWS_NEWLINE);
  private $LINE_ENDINGS = array(self::NEWLINE, self::CARRIAGE, self::WINDOWS_NEWLINE);

  const WHITESPACE = ' ';
  const TAB = '\t';
  const FORM_FEED = '\f';
  private $BLANKS = array(self::WHITESPACE, self::TAB, self::FORM_FEED);

  const LINE_EXTENDER = '\\';

  const HASH = "#";
  const BANG = "!";
  private $COMMENTS = array(self::HASH, self::BANG);

  const EQUALS_SEPARATOR = '=';
  const COLON_SEPARATOR = ':';
  private $KEY_TERMINALS = array(self::EQUALS_SEPARATOR, self::COLON_SEPARATOR, self::WHITESPACE);

  private $ALLOWED_ESCAPES = array(self::TAB, self::NEWLINE, self::CARRIAGE, self::FORM_FEED);

  private $propsFile;

  private $cursor;
  private $escaped = false;

  public function __construct($propsFile){
    $this->propsFile = $propsFile;
    $this->cursor = 0;
  }

  /**
   * Return a boolean indicating whether this
   * is a beginning of a multiline property entry
   */
  private function isMultiline($line){

    if(strlen($line) === 0 )
      return false;

    if(strlen($line) === 1) {
      if($line===self::ESCAPE)
        return true;
      else
        return false;
    }

    $charIndex = strlen($line) - 2;
    $escapeCount=0;
    while($line[$charIndex] === self::ESCAPE && $charIndex>=0){
      $escapeCount++;
      $charIndex--;
    }

    if($escapeCount % 2 !== 0)
      return true;
    else
      return false;
  }

  private static function isUnicodeEscape($string){
    return strlen($string) === 6
    && substr($string, 0, 2) === '\u'
    && ctype_xdigit(substr($string, 2));
  }

  private function escapeValue($val){
    $escapedValue="";

    for($index = 0 ; $index < strlen($val); $index ++){
      $currentChar = $val[$index];

      if($currentChar === self::ESCAPE){
        $nextChar = ($index + 1) !== strlen($val) ? $val[$index + 1] : null;
        $isASeparatorEscape = $nextChar !== null && ($nextChar === self::EQUALS_SEPARATOR || $nextChar === self::COLON_SEPARATOR);
        $notAUnicodeEscape = !self::isUnicodeEscape(substr($val, $index, 6));
        $notAnAllowedEscape = !in_array($currentChar.$val[$index + 1], $this->ALLOWED_ESCAPES);
        if($isASeparatorEscape && $notAUnicodeEscape && $notAnAllowedEscape)
        {
          /* Ignore the ESCAPE character */
          continue;
        }
      }

      $escapedValue.=$currentChar;
    }
    return $escapedValue;
  }

  /**
   * Expects a non-empty ltrimmed property line and parses the key.
   */
  private function readKeyAndStripIt(&$line){
    $key="";
    $escaped = FALSE;
    $index=0;

    while($index !== strlen($line) && (!in_array($line[$index], $this->KEY_TERMINALS) || $escaped)){
      $escaped = FALSE;
      $currentChar = $line[$index];
      if($currentChar === self::ESCAPE){
        $escaped = TRUE;
        $nextChar = ($index + 1) !== strlen($line) ? $line[$index + 1] : null;

        $isASeparatorEscape = $nextChar !== null && ($nextChar === self::EQUALS_SEPARATOR || $nextChar === self::COLON_SEPARATOR);
        $notAUnicodeEscape = !self::isUnicodeEscape(substr($line, $index, 6));
        $notAnAllowedEscape = !in_array($currentChar.$line[$index + 1], $this->ALLOWED_ESCAPES);

        if($isASeparatorEscape && $notAUnicodeEscape && $notAnAllowedEscape)
        {
          /* Ignore the ESCAPE character */
          $index += 1;
          continue;
        }
      }
      $key .= $currentChar;
      $index += 1;
    }

    /* Now that we have the key, trim the line on the index: */
    $line = ltrim(substr($line, $index), " =:");

    return $key;
  }

  /**
   * Reads the properties file line by line, and
   * tries to parse the properties.
   */
  public function read(){
    $file = fopen($this->propsFile, "r");

    while(!feof($file)){
      $line = fgets($file);

      /* Concatenate multiline props */
      while($this->isMultiline($line))
        $line = substr($line, 0, strlen($line)-2 ) . fgets($file);

      /* Ignore beginning whitespaces */
      $line = ltrim($line);

      /* If the line is empty, or we've anihilated it */
      if(strlen($line) === 0) continue;

      /* If the line is a comment, it is of no importance to us */
      if(in_array($line[0], $this->COMMENTS)) continue;

      /* Strip trailing newlines */
      $line = rtrim($line, "\r\n");

      /* Now we may parse the line */
      $key = $this->readKeyAndStripIt($line);

      /* After strip, all that's left should be the property value */
      $value = $this->escapeValue($line);

      $properties[$key] = $value;
    }

    fclose($file);

    return $properties;
  }


}
