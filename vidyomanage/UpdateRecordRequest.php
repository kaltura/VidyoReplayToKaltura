<?php

class UpdateRecordRequest
{

  /**
   * 
   * @var int $id
   * @access public
   */
  public $id;

  /**
   * 
   * @var string $title
   * @access public
   */
  public $title;

  /**
   * 
   * @var string $comments
   * @access public
   */
  public $comments;

  /**
   * 
   * @var string $tags
   * @access public
   */
  public $tags;

  /**
   * 
   * @var recordScopeUpdate $recordScope
   * @access public
   */
  public $recordScope;

  /**
   * 
   * @var string $pin
   * @access public
   */
  public $pin;

  /**
   * 
   * @param int $id
   * @param string $title
   * @param string $comments
   * @param string $tags
   * @param recordScopeUpdate $recordScope
   * @param string $pin
   * @access public
   */
  public function __construct($id, $title, $comments, $tags, $recordScope, $pin)
  {
    $this->id = $id;
    $this->title = $title;
    $this->comments = $comments;
    $this->tags = $tags;
    $this->recordScope = $recordScope;
    $this->pin = $pin;
  }

}
