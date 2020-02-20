<?php

final class PhabricatorExternalAccountIdentifier
  extends PhabricatorUserDAO
  implements PhabricatorPolicyInterface {

  protected $externalAccountPHID;
  protected $providerConfigPHID;
  protected $identifierHash;
  protected $identifierRaw;

  public function getPHIDType() {
    return PhabricatorPeopleExternalIdentifierPHIDType::TYPECONST;
  }

  protected function getConfiguration() {
    return array(
      self::CONFIG_AUX_PHID => true,
      self::CONFIG_COLUMN_SCHEMA => array(
        'identifierHash' => 'bytes12',
        'identifierRaw' => 'text',
      ),
      self::CONFIG_KEY_SCHEMA => array(
        'key_identifier' => array(
          'columns' => array('providerConfigPHID', 'identifierHash'),
          'unique' => true,
        ),
        'key_account' => array(
          'columns' => array('externalAccountPHID'),
        ),
      ),
    ) + parent::getConfiguration();
  }

  public function save() {
    $identifier_raw = $this->getIdentifierRaw();
    $this->identiferHash = PhabricatorHash::digestForIndex($identifier_raw);
    return parent::save();
  }


/* -(  PhabricatorPolicyInterface  )----------------------------------------- */

  // TODO: These permissions aren't very good. They should just be the same
  // as the associated ExternalAccount. See T13381.

  public function getCapabilities() {
    return array(
      PhabricatorPolicyCapability::CAN_VIEW,
      PhabricatorPolicyCapability::CAN_EDIT,
    );
  }

  public function getPolicy($capability) {
    switch ($capability) {
      case PhabricatorPolicyCapability::CAN_VIEW:
        return PhabricatorPolicies::getMostOpenPolicy();
      case PhabricatorPolicyCapability::CAN_EDIT:
        return PhabricatorPolicies::POLICY_NOONE;
    }
  }

  public function hasAutomaticCapability($capability, PhabricatorUser $viewer) {
    return false;
  }

}
