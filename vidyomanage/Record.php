<?php

class Record
{

  /**
   * 
   * @var int $id
   * @access public
   */
  public $id;

  /**
   * 
   * @var string $guid
   * @access public
   */
  public $guid;

  /**
   * 
   * @var string $tenantName
   * @access public
   */
  public $tenantName;

  /**
   * 
   * @var string $userName
   * @access public
   */
  public $userName;

  /**
   * 
   * @var string $userFullName
   * @access public
   */
  public $userFullName;

  /**
   * 
   * @var dateTime $dateCreated
   * @access public
   */
  public $dateCreated;

  /**
   * 
   * @var string $dateCreatedString
   * @access public
   */
  public $dateCreatedString;

  /**
   * 
   * @var dateTime $endTime
   * @access public
   */
  public $endTime;

  /**
   * 
   * @var string $duration
   * @access public
   */
  public $duration;

  /**
   * 
   * @var string $resolution
   * @access public
   */
  public $resolution;

  /**
   * 
   * @var int $framerate
   * @access public
   */
  public $framerate;

  /**
   * 
   * @var string $pin
   * @access public
   */
  public $pin;

  /**
   * 
   * @var string $recordScope
   * @access public
   */
  public $recordScope;

  /**
   * 
   * @var string $title
   * @access public
   */
  public $title;

  /**
   * 
   * @var string $roomName
   * @access public
   */
  public $roomName;

  /**
   * 
   * @var string $fileLink
   * @access public
   */
  public $fileLink;

  /**
   * 
   * @var string $recorderId
   * @access public
   */
  public $recorderId;

  /**
   * 
   * @var boolean $webcast
   * @access public
   */
  public $webcast;

  /**
   * 
   * @var string $tags
   * @access public
   */
  public $tags;

  /**
   * 
   * @var string $comments
   * @access public
   */
  public $comments;

  /**
   * 
   * @var boolean $locked
   * @access public
   */
  public $locked;

  /**
   * 
   * @param int $id
   * @param string $guid
   * @param string $tenantName
   * @param string $userName
   * @param string $userFullName
   * @param dateTime $dateCreated
   * @param string $dateCreatedString
   * @param dateTime $endTime
   * @param string $duration
   * @param string $resolution
   * @param int $framerate
   * @param string $pin
   * @param string $recordScope
   * @param string $title
   * @param string $roomName
   * @param string $fileLink
   * @param string $recorderId
   * @param boolean $webcast
   * @param string $tags
   * @param string $comments
   * @param boolean $locked
   * @access public
   */
  public function __construct($id, $guid, $tenantName, $userName, $userFullName, $dateCreated, $dateCreatedString, $endTime, $duration, $resolution, $framerate, $pin, $recordScope, $title, $roomName, $fileLink, $recorderId, $webcast, $tags, $comments, $locked)
  {
    $this->id = $id;
    $this->guid = $guid;
    $this->tenantName = $tenantName;
    $this->userName = $userName;
    $this->userFullName = $userFullName;
    $this->dateCreated = $dateCreated;
    $this->dateCreatedString = $dateCreatedString;
    $this->endTime = $endTime;
    $this->duration = $duration;
    $this->resolution = $resolution;
    $this->framerate = $framerate;
    $this->pin = $pin;
    $this->recordScope = $recordScope;
    $this->title = $title;
    $this->roomName = $roomName;
    $this->fileLink = $fileLink;
    $this->recorderId = $recorderId;
    $this->webcast = $webcast;
    $this->tags = $tags;
    $this->comments = $comments;
    $this->locked = $locked;
  }

}
