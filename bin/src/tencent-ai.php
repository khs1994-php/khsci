#!/usr/bin/env php

<?php

use App\Console\TencentAI\ChatCommand;
use App\Console\TencentAI\OCRCommand;
use App\Console\TencentAI\TranslateCommand;
use Symfony\Component\Console\Application;

require __DIR__.'/../../public/bootstrap/app.php';

$cli = new Application('Tencent AI CLI', 'v18.06');

$cli->add(new TranslateCommand());

$cli->add(new ChatCommand());

$cli->add(new OCRCommand());

$cli->run();
