<?php

namespace Junges\StackOverflowPTBR;

use Facade\Ignition\Tabs\Tab as BaseTab;

class Tab extends BaseTab
{
    public function name(): string
    {
        return 'Stack Overflow PT-BR';
    }

    public function component(): string
    {
        return 'ignition-stackoverflow-portuguese';
    }

    public function registerAssets()
    {
        $this->script($this->component(), __DIR__.'/../dist/js/tab.js');
    }

    public function meta(): array
    {
        return [
            'title' => $this->name(),
        ];
    }
}
