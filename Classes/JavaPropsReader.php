<?php
class JavaPropsReader {

  /* Special chars */
  const ESCAPE='\\';

  const NEWLINE = "\n";
  const CARRIAGE = "\r";
  const WINDOWS_NEWLINE = "\r\n";
  const LINE_ENDS = array(self::NEWLINE, self::CARRIAGE, self::WINDOWS_NEWLINE);

  const WHITESPACE = ' ';
  const TAB = '\t';
  const FORM_FEED = '\f';
  const WHITESPACES = array(self::WHITESPACE, self::TAB, self::FORM_FEED, self::NEWLINE, self::CARRIAGE, self::WINDOWS_NEWLINE);

  const LINE_EXTENDER = '\\';

  const HASH = "#";
  const BANG = "!";
  const COMMENTS = array(self::HASH, self::BANG);

  private $propsFile;
  private $content;

  private $cursor;

  public function __construct($propsFile){
    $this->propsFile = $propsFile;
    $this->content = file_get_contents($this->propsFile);
    $this->cursor = 0;
  }

  private function readChar(){
    if($this->cursor < count($this->content))
      return $this->content[$cursor];
    else
      return null;
  }

  public function read(){
    do{
      $nextChar = $this->readChar();
      switch($nextChar){
        // TODO:
      }
    }while($nexChar !== null);
  }
}
