<?php

include_once('OK.php');
include_once('sortBy.php');
include_once('sortDirection.php');
include_once('recordScopeFilter.php');
include_once('recordScopeUpdate.php');
include_once('ExceptionCustom.php');
include_once('GeneralFault.php');
include_once('AccessViolationFault.php');
include_once('Record.php');
include_once('RecordsResponse.php');
include_once('RecordsSearchResponse.php');
include_once('RecordsSearchRequest.php');
include_once('RecordsByIdRequest.php');
include_once('UpdateRecordRequest.php');
include_once('DeleteRecordRequest.php');
include_once('UpdateRecordResponse.php');
include_once('DeleteRecordResponse.php');


/**
 * 
 */
class VidyoReplayContentManagementService extends SoapClient
{

  /**
   * 
   * @var array $classmap The defined classes
   * @access private
   */
  private static $classmap = array(
    'Exception' => 'ExceptionCustom',
    'GeneralFault' => 'GeneralFault',
    'AccessViolationFault' => 'AccessViolationFault',
    'Record' => 'Record',
    'RecordsResponse' => 'RecordsResponse',
    'RecordsSearchResponse' => 'RecordsSearchResponse',
    'RecordsSearchRequest' => 'RecordsSearchRequest',
    'RecordsByIdRequest' => 'RecordsByIdRequest',
    'UpdateRecordRequest' => 'UpdateRecordRequest',
    'DeleteRecordRequest' => 'DeleteRecordRequest',
    'UpdateRecordResponse' => 'UpdateRecordResponse',
    'DeleteRecordResponse' => 'DeleteRecordResponse');

  /**
   * 
   * @param array $config A array of config values
   * @param string $wsdl The wsdl file to use
   * @access public
   */
  public function __construct(array $options = array(), $wsdl = 'vidyomanage.wsdl')
  {
    foreach(self::$classmap as $key => $value)
    {
      if(!isset($options['classmap'][$key]))
      {
        $options['classmap'][$key] = $value;
      }
    }
    
    parent::__construct($wsdl, $options);
  }

  /**
   * 
   * @param RecordsSearchRequest $parameter
   * @access public
   */
  public function RecordsSearch(RecordsSearchRequest $parameter)
  {
    return $this->__soapCall('RecordsSearch', array($parameter));
  }

  /**
   * 
   * @param RecordsByIdRequest $parameter
   * @access public
   */
  public function RecordsById(RecordsByIdRequest $parameter)
  {
    return $this->__soapCall('RecordsById', array($parameter));
  }

  /**
   * 
   * @param UpdateRecordRequest $parameter
   * @access public
   */
  public function UpdateRecord(UpdateRecordRequest $parameter)
  {
    return $this->__soapCall('UpdateRecord', array($parameter));
  }

  /**
   * 
   * @param DeleteRecordRequest $parameter
   * @access public
   */
  public function DeleteRecord(DeleteRecordRequest $parameter)
  {
    return $this->__soapCall('DeleteRecord', array($parameter));
  }

}
