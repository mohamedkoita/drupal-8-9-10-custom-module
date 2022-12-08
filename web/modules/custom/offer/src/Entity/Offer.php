<?php
/**
 * @file
 * Contains \Drupal\offer\Entity\Offer.
 */

namespace Drupal\offer\Entity;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\bid\Entity\Bid;
use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Component\Serialization\Json;


/**
 * Defines the offer entity.
 *
 * @ingroup offer
 *
 * @ContentEntityType(
 *   id = "offer",
 *   label = @Translation("Offer"),
 *   base_table = "offer",
 *   data_table = "offer_field_data",
 *   revision_table = "offer_revision",
 *   revision_data_table = "offer_field_revision",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "label" = "title",
 *     "revision" = "vid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   handlers = {
 *     "access" = "Drupal\offer\OfferAccessControlHandler",
 *     "views_data" = "Drupal\offer\OfferViewsData",
 *     "form" = {
 *      "add" = "Drupal\offer\Form\OfferForm",
 *      "step_1" = "Drupal\offer\Form\OfferAddFormStep1",
 *      "step_2" = "Drupal\offer\Form\OfferAddFormStep2",
 *      "step_3" = "Drupal\offer\Form\OfferAddFormStep3",
 *      "edit" = "Drupal\offer\Form\OfferForm",
 *      "delete" = "Drupal\offer\Form\OfferDeleteForm",
 *     }
 *   },
 *   links = {
 *     "canonical" = "/offers/{offer}",
 *     "delete-form" = "/offer/{offer}/delete",
 *     "edit-form" = "/offer/{offer}/edit",
 *     "create" = "/offer/create"
 *   },
 *   field_ui_base_route = "entity.offer.settings"
 * )
 */

class Offer extends EditorialContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type); // provides id and uuid fields

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User'))
      ->setDescription(t('The user that created the offer.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the offer'))
      ->setSettings([
        'max_length' => 150,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Offer entity is published.'))
      ->setDefaultValue(TRUE);

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
   *
   * Makes the current user the owner of the entity
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
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

  /**
  * {@inheritdoc}
  */
  public function getBidsNumber() {

    $bidNb = \Drupal::entityQuery('bid')
          ->condition('offer_id', $this->id())
          ->count()
          ->execute();

    return $bidNb;
  }

  /*
  * {@inheritdoc}
  */
  public function getOfferBids() {
    $bidNb = \Drupal::entityQuery('bid')
          ->condition('offer_id', $this->id())
          ->execute();

    return $bidNb;
  }

  /**
  * {@inheritdoc}
  */
  public function getHighestBid() {

    $hgNbQuery = \Drupal::entityQuery('bid')
          ->condition('offer_id', $this->id())
          ->sort('bid', 'DESC')
          ->range(0, 1)
          ->execute();

    //On va utiliser la méthode Node::load pour récupérer les nodes qu'on va pouvoir récupere à partir des ids obtenus lors
    //des requêtes ne renvoient que des ids


  $idf = reset($hgNbQuery);
  $hgNb = Bid::load($idf);
  if ($hgNb) {
    return $hgNb->get('bid')->getString();
  }

  return 0;



    return 2;
  }

  /**
   * Returns a promotext
   * @return string
   */
  public function getPromoText() {
    return 'Be the first!';
  }

  /**
   * Return a price string based on field_price
   * @return string
   */
  public function getPriceAmount() {
    switch($this->get('field_offer_type')->getString()) {
      case 'with_minimum':
        return $this->get('field_price')->getString() . '$';
      case 'no_minimum':
        return 'Start bidding at 0$';
    }
    return '';
  }

  /**
  * Return a rendered table below an offer
  * @return a drupal table render array
  */

  public function getOfferBiddingTable() {
    $bids = $this->getOfferBids();
    $row = [];
    // dump($bids);
    // die();

    foreach ($bids as $bid) {
      $bid = Bid::load($bid);
      $price = $bid->get('bid')->getString();
      $owner = $bid->getOwner();
      $ownerName = $owner->getDisplayName();
      $time = \Drupal::service('date.formatter')->formatTimeDiffSince($bid->created->value);

      $updates = '';
      $link = '';

      if ($bid->hasRevisions()) {
        $revisions = $bid->getRevisionsList();
        //On va d'abord récupérer le tableau de toutes les revisions du bid
        //On va recupérer l'id de la derniere revision en cours puis retirer la derniere revision qui est celle actuelle
        $current_revision_id = $bid->getLoadedRevisionId();
        unset($revisions[$current_revision_id]);
        //Puis on va récuperer l'avant derniere revision pour comparer la derniere et l'avant dernière
        //Afin de pouvoir afficher de combien l'utilisateur a augmenté sa mise initiale, ainsi de suite
        $last_revision_id = max(array_keys($revisions));
        //On recupere maintenant la revision avec le entity manager
        $revisionBid = \Drupal::entityTypeManager()
            ->getStorage('bid')
            ->loadRevision($last_revision_id);

        $revisionAmount = $revisionBid->get('bid')->getString();
        $priceDiff = $price - $revisionAmount;
        $priceDiff = $priceDiff . ' $';

        $updates = ' <svg width="24px" height="18px" viewBox="0 0 24 24"
                    fill="#61f70a" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.1018 16.9814C5.02785 16.9814 4.45387 15.7165
                    5.16108 14.9083L10.6829 8.59762C11.3801 7.80079 12.6197 7.80079
                    13.3169 8.59762L18.8388 14.9083C19.5459 15.7165 18.972 16.9814
                    17.898 16.9814H6.1018Z" fill="#61f70a"/>
                    </svg><small style="color:#0444C4">Last raise was ' .
                    $priceDiff .'</small> ';
      }

      $link = '';

      if ($bid->access('delete')) {
        $url = $bid->toUrl('delete-form'); // On utilise la clé du formulaire ici pour l'avoir
        // $link = Link::fromTextAndUrl('Remove bid', $url)->toString();
        $deleteLink = [
          '#type' => 'link',
          '#title' => 'Remove bid',
          '#url' => $url,
          '#attributes' => [
            'class' => [
              'use-ajax', 'button', 'button-small', 'button-danger'
            ],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode(['title' => t('Remove bid?'), 'width' => 800,]),
          ],
        ];

        $link = \Drupal::service('renderer')->render($deleteLink);

      }

      $row = [
        Markup::create($ownerName . ' - ' . $time .' ago'),
        Markup::create($price . '$' . $updates),
        Markup::create($link),

      ];
      $rows[]= $row;
    }
      $build['table'] = [
        '#type' => 'table',
        '#rows' => $rows,
        '#empty' => t('This offer has no bids yet. Grab your chance!')
      ];

    return [
      '#type' => '#markup',
      '#markup' => \Drupal::service('renderer')->render($build)
    ];
    }

/**
* Checks if the current user has bids on the current offer
* @return bool
*/
public function CurrentUserHasBids() {
  $user_id = \Drupal::currentUser()->id();
  $id = $this->id();

  $query = \Drupal::entityQuery('bid')
        ->condition('offer_id', $id)
        ->condition('user_id', $user_id)
        ->count()
        ->execute();

    if ($query > 0) {
      return true;
    } else {
      return false;
    }
  }

/**
* Get yhe current bid of current user
* @return \Drupal\offer\Entity|Bid
*/
public function CurrentUserBid() {
  $user_id = \Drupal::currentUser()->id();
  $id = $this->id();

  $query = \Drupal::entityQuery('bid')
        ->condition('offer_id', $id)
        ->condition('user_id', $user_id)
        ->sort('created', 'DESC')
        ->execute();

  $bidId = reset($query);
  $bid = Bid::load($bidId);
  return $bid;
}

}
