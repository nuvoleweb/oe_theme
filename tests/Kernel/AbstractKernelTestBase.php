<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel;

use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\oe_theme\Traits\RenderTrait;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractKernelTestBase.
 *
 * @package Drupal\Tests\oe_theme\Kernel
 */
abstract class AbstractKernelTestBase extends KernelTestBase {

  use RenderTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'ui_patterns',
    'ui_patterns_library',
    'oe_theme_helper',
    'breakpoint',
    'responsive_image',
    'image',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->installConfig(['user']);
    $this->installConfig(['system', 'image', 'responsive_image']);

    $this->container->get('theme_installer')->install(['oe_theme']);
    $this->container->get('theme_handler')->setDefault('oe_theme');
    $this->container->set('theme.registry', NULL);

    // Call the install hook of the User module which creates the Anonymous user
    // and User 1. This is needed because the Anonymous user is loaded to
    // provide the current User context which is needed in places like route
    // enhancers.
    // @see CurrentUserContext::getRuntimeContexts().
    // @see EntityConverter::convert().
    module_load_include('install', 'user');
    user_install();
  }

  /**
   * Get fixture content.
   *
   * @param string $filepath
   *   File path.
   *
   * @return array
   *   A set of test data.
   */
  protected function getFixtureContent(string $filepath): array {
    return Yaml::parse(file_get_contents(__DIR__ . "/fixtures/{$filepath}"));
  }

  /**
   * Builds and returns the renderable array for a block.
   *
   * @param string $block_id
   *   The ID of the block.
   * @param array $config
   *   An array of configuration.
   *
   * @return array
   *   A renderable array representing the content of the block.
   */
  protected function buildBlock(string $block_id, array $config): array {
    /** @var \Drupal\Core\Block\BlockBase $plugin */
    $plugin = $this->container->get('plugin.manager.block')->createInstance($block_id, $config);

    // Inject runtime contexts.
    if ($plugin instanceof ContextAwarePluginInterface) {
      $contexts = $this->container->get('context.repository')->getRuntimeContexts($plugin->getContextMapping());
      $this->container->get('context.handler')->applyContextMapping($plugin, $contexts);
    }

    return $plugin->build();
  }

}
