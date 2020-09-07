<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_policy\Plugin\PageHeaderMetadata;

use Drupal\node\NodeInterface;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;

/**
 * Page header metadata for the OpenEuropa Policy content entity.
 *
 * @PageHeaderMetadata(
 *   id = "policy_content_type",
 *   label = @Translation("Metadata extractor for the OE Content Policy content type"),
 *   weight = -1
 * )
 */
class PolicyContentType extends NodeViewRoutesBase {

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $entity = $this->getNode();

    return $entity instanceof NodeInterface && $entity->bundle() === 'oe_policy';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $entity = $this->getNode();
    if ($entity->get('oe_summary')->isEmpty()) {
      return $metadata;
    }

    $summary = $entity->get('oe_summary')->first();
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
