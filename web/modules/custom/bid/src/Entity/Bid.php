<?php
/**
* @file
* Contains \Drupal\bid\Entity\bid.
*/
namespace Drupal\bid\Entity;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
/**
* Defines the bid entity.
*
* @ingroup bid
*
* @ContentEntityType(
* id = "bid",
* label = @Translation("bid"),
* base_table = "bid",
* data_table = "bid_field_data",
* fieldable = FALSE,
* revision_table = "bid_revision",
* revision_data_table = "bid_field_revision",
* entity_keys = {
* "id" = "id",
* "uuid" = "uuid",
* "label" = "title",
* "owner" = "uid",
* "revision" = "vid",
* "published" = "status",
* "uid" = "uid",
* "owner" = "uid",
* },
* handlers = {
* "access" = "Drupal\bid\BidAccessControlHandler",
* },
* revision_metadata_keys = {
* "revision_user" = "revision_uid",
* "revision_created" = "revision_timestamp",
* "revision_log_message" = "revision_log"
* },
* constraints = {
* "AllFieldsRequired" = {}
* }
* )
*/
class Bid extends EditorialContentEntityBase {
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type); // provides id and uuid fields

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('The user that created the bid.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default');

    $fields['offer_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Offer'))
      ->setDescription(t('The offer the bid is for.'))
      ->setSetting('target_type', 'offer')
      ->setSetting('handler', 'default');

    $fields['bid'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Bid amount'))
      ->setRevisionable(TRUE)
      ->setDescription(t('The bid amount in $.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }
  /**
  * {@inheritdoc}
  */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }
  /**
  * {@inheritdoc}
  */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

}
