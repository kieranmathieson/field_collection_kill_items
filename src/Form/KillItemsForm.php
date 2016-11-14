<?php
/**
 * @file
 * Build a form for killing field collection items.
 *
 * @author Kieran Mathieson
 */

namespace Drupal\field_collection_kill_items\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field_collection\Entity\FieldCollectionItem;
use Symfony\Component\DependencyInjection\ContainerInterface;


class KillItemsForm extends FormBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    QueryFactory $entity_query
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity.query')
    );
  }

  /**
   * Build the form.
   *
   * @param array $form
   *   Default form array structure.
   * @param FormStateInterface $form_state
   *   Object containing current form state.
   *
   * @return array
   *   The render array defining the elements of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //Load the field collection item entities.
    $query = $this->entityQuery->get('field_collection_item');
    $result = $query->execute();
    //Exit if none exist.
    $collectionItems = FieldCollectionItem::loadMultiple(array_values($result));
    if ( sizeof($collectionItems) == 0 ) {
      $form = [
        'nothing' =>[
          '#markup' => '<p>' . t('There are no field collection items.') . '</p>',
        ],
      ];
      return $form;
    }
    //Build a table to show the existing entities.
    $tableHeader = array(
      array('data' => t('Item id'), 'field' => 't.numbers'),
      array('data' => t('Field name'), 'field' => 't.alpha'),
      array('data' => t('Host type'), 'field' => 't.alpha'),
    );
    $tableRows = array();
    foreach ($collectionItems as $collectionItem) {
      $tableRows[] = ['data' => [
        $collectionItem->item_id->value,
        $collectionItem->field_name->getValue()[0]['target_id'],
        $collectionItem->host_type->value,
      ]];
    }
    $form = [
      'instructions' =>[
        '#markup' => '<p>' . t('Here are the field collection items you can kill. Be careful!') . '</p>',
      ],
    ];
    $form['collection_items_table'] = array(
      '#theme' => 'table',
      '#header' => $tableHeader,
      '#rows' => $tableRows,
    );
    //A text field the user can type entity ids into.
    $form['items_to_kill'] = [
      '#type' => 'textfield',
      '#size' => 50,
      '#title' => $this->t('Ids of items to kill'),
      '#description' => 'Comma separated list of ids of items to kill',
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'field_collection_kill_items';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nothingToKill = $this->t('Sorry, no ids were given. Nothing to kill.');
    $itemsToKillFieldContents = trim($form_state->getValue('items_to_kill'));
    //Check that the user gave numeric ids.
    if ( ! $itemsToKillFieldContents ) {
      drupal_set_message($nothingToKill);
      return;
    }
    $idsOfItemsToKill = explode(',', $itemsToKillFieldContents);
    if ( sizeof($idsOfItemsToKill) == 0 ) {
      drupal_set_message($nothingToKill);
      return;
    }
    $dataOk = TRUE;
    foreach($idsOfItemsToKill as $idOfItemToKill) {
      if ( ! is_numeric($idOfItemToKill) ) {
        $dataOk = FALSE;
        break;
      }
    }
    if ( ! $dataOk ) {
      drupal_set_message($this->t('Sorry, all of the ids must be numbers.'));
      return;
    }
    //Erase the entities.
    $itemsToKill = $this->entityTypeManager->
        getStorage('field_collection_item')->loadMultiple($idsOfItemsToKill);
    foreach ($itemsToKill as $item) {
      $item->delete();
    }
    drupal_set_message($this->t('The items have been killed.'));
  }

}
