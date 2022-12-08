<?php

namespace Drupal\bid;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

//En gros les nodes dans drupal ont deja en elle un systeme de gestion de permission intégrés et en fonction du type de node qu'on créé, il va falloir
//Implementer le EntityAccessControlhandler pour chaque entité afin de créer des permissions spécifiques.
//Ainsi la fonction pour vérifier si un utilisateur aura le droit de faire une action comportera trois arguments, le node ou l'entité en question,\
//L'utilisateur qui voudra éffectuer cette action ainsi que le nom de l'operation

/**
* Access controller for the bid entity. Controls create/edit/delete access for entity and fields.
*
* @see \Drupal\bid\Entity\Bid.
*/
class BidAccessControlHandler extends EntityAccessControlHandler {

  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    $access = AccessResult::forbidden();
    switch ($operation) {

      case 'view':
      $access = AccessResult::allowed();
      break;

      case 'update':
      $access = AccessResult::allowedIf($account->id() == $entity->getOwnerId())->cachePerUser()->addCacheableDependency($entity);
      break;

      case 'edit':
      $access = AccessResult::allowedIf($account->id() == $entity->getOwnerId())->cachePerUser()->addCacheableDependency($entity);
      break;

      case 'delete':
      $access = AccessResult::allowedIf($account->id() == $entity->getOwnerId())->cachePerUser()->addCacheableDependency($entity);
      break;
    }
    return $access;

  }

}
