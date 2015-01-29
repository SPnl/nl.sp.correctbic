<?php

/**
 * Collection of upgrade steps
 */
class CRM_Correctbic_Upgrader extends CRM_Correctbic_Upgrader_Base {

  public function enable() {
    CRM_Core_BAO_Setting::setItem('1000', 'Extension', 'nl.sp.correctbic:version');
  }

  public function upgrade_1001() {
    $minId = CRM_Core_DAO::singleValueQuery('SELECT min(id) FROM civicrm_value_iban');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT max(id) FROM civicrm_value_iban');
    for ($startId = $minId; $startId <= $maxId; $startId += 10) {
      $endId = $startId + 10 - 1;
      $title = ts('Correct BIC (%1 / %2)', array(
          1 => $startId,
          2 => $maxId,
      ));
      $this->addTask($title, 'correct', $startId, $endId);
    }
    return true;
  }

  public static function correct($startId, $endId) {
    $config = CRM_Ibanaccounts_Config::singleton();

    $sql = "SELECT * FROM `civicrm_value_iban` WHERE (`id` BETWEEN %1 AND %2)";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
    ));
    while($dao->fetch()) {
      $bic = CRM_Ibanaccounts_Utils_IbanToBic::getBic($dao->iban);
      if (!empty($bic)) {
        $params = array();
        $params['custom_'.$config->getBicCustomFieldValue().'_'.$dao->id] = $bic;
        $params['entityID'] = $dao->entity_id;
        //var_dump($params); exit();
        CRM_Core_BAO_CustomValueTable::setValues($params);
      }
    }
    return true;
  }

}
