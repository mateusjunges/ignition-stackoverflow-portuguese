<?php

namespace Junges\StackOverflowPTBR\Tests;

use Junges\StackOverflowPTBR\Tab;

class TabTest extends TestCase
{
    public function test_if_tab_has_correct_name()
    {
        $tab = new Tab();
        $this->assertEquals('StackOverflow PT-BR', $tab->name());
    }

    public function test_if_tab_has_the_correct_component()
    {
        $tab = new Tab();
        $this->assertEquals('ignition-stackoverflow-portuguese', $tab->component());
    }

    public function test_if_tab_has_the_correct_scripts()
    {
        $tab = new Tab();
        $this->assertArrayHasKey('ignition-stackoverflow-portuguese', $tab->scripts);
        $this->assertStringEndsWith("dist/js/tab.js", $tab->scripts['ignition-stackoverflow-portuguese']);
    }
}
