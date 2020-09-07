<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\Kernel\Plugin\Condition;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;

/**
 * Test CurrentComponentLibraryCondition plugin.
 */
class CurrentComponentLibraryConditionTest extends AbstractKernelTestBase {

  /**
   * Tests the current component library condition.
   */
  public function testCondition(): void {
    $manager = \Drupal::service('plugin.manager.condition');
    $condition = $manager->createInstance('oe_theme_helper_current_component_library');
    $condition->setConfiguration(['component_library' => 'ec']);

    $condition_empty = $manager->createInstance('oe_theme_helper_current_component_library');

    $condition_negated = $manager->createInstance('oe_theme_helper_current_component_library');
    $condition_negated->setConfiguration(['component_library' => 'ec', 'negate' => TRUE]);

    $this->assertEqual($condition->summary(), new FormattableMarkup('The current component library is @component_library', ['@component_library' => 'ec']));
    $this->assertEqual($condition_empty->summary(), new FormattableMarkup('The current component library can be set to anything', []));
    $this->assertEqual($condition_negated->summary(), new FormattableMarkup('The current component library is not @component_library', ['@component_library' => 'ec']));

    // Assert condition values, by default the component library set to "ec".
    $this->assertTrue($condition->execute(), 'Condition asserting that component library is "ec" should be true.');
    $this->assertTrue($condition_empty->execute(), 'Condition that has no component library value set should always be true.');
    $this->assertFalse($condition_negated->execute(), 'Condition asserting that component library is not "ec" should be false.');

    // Change component library to "eu" and assert new condition execution.
    $this->config('oe_theme.settings')->set('component_library', 'eu')->save();
    $this->assertFalse($condition->execute(), 'Condition asserting that component library is "ec" should be false.');
    $this->assertTrue($condition_empty->execute(), 'Condition that has no component library value set should always be true.');
    $this->assertTrue($condition_negated->execute(), 'Condition asserting that component library is not "ec" should be true.');
  }

}
