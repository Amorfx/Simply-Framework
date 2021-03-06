<?php

namespace Simply\Core\Command;

use Symfony\Component\Filesystem\Filesystem;

class ClearCacheCommand extends AbstractWordPressCommand {
    static $commandName = 'simply:cache:clear';
    public function execute($args, $assoc_args) {
        $this->showColorMessage('Remove application cache...', '%b');
        $fs = new Filesystem();
        $fs->remove(SIMPLY_CACHE_DIRECTORY);
        $this->showColorMessage('Done.', '%g');
    }
}
