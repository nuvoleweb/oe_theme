<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\PageHeaderMetadata;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Page header metadata for the OpenEuropa Event content entity.
 *
 * @PageHeaderMetadata(
 *   id = "event_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Event content type"),
 *   weight = -1
 * )
 */
class EventContentType extends NodeViewRoutesBase {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Creates a new NodeViewRouteBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher);

    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_event';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $node = $this->getNode();
    $translated_value = $this->entityRepository->getTranslationFromContext($node->get('oe_event_type')->entity);
    $metadata['metas'] = [
      $translated_value->label(),
    ];

    if ($node->get('oe_summary')->isEmpty()) {
      return $metadata;
    }

    $summary = $node->get('oe_summary')->first();
    $metadata['introduction'] = [
      // We strip the tags because the component expects only one paragraph of
      // text and the field is using a text format which adds paragraph tags.
      '#type' => 'inline_template',
      '#template' => '{{ summary|render|striptags("<strong><a><em>")|raw }}',
      '#context' => [
        'summary' => [
          '#type' => 'processed_text',
          '#text' => $summary->value,
          '#format' => $summary->format,
          '#langcode' => $summary->getLangcode(),
        ],
      ],
    ];

    return $metadata;
  }

}
