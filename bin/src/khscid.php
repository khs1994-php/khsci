#!/usr/bin/env php

<?php

// khscid.php is KhsCI Daemon CLI

use Symfony\Component\Console\Application;

require __DIR__.'/../../public/bootstrap/app.php';

spl_autoload_register(function ($class): void {
    $class = str_replace('\\', \DIRECTORY_SEPARATOR, $class);
    $file = __DIR__.\DIRECTORY_SEPARATOR.$class.'.php';

    if (file_exists($file)) {
        require $file;
    }
});

try {
    /**
     * @see https://juejin.im/entry/5a3795a051882572ed55af00
     * @see https://segmentfault.com/a/1190000005084734
     */
    $cli = new Application('KhsCI Daemon CLI', 'v18.06');

    $cli->add(new Migrate());

    $cli->add(new Up());

    $cli->run();
} catch (Exception $e) {
    throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
}
