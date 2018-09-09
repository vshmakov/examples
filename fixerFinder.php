<?php

return $finder = PhpCsFixer\Finder::create()
//    ->exclude('somedir')
//    ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
    ->in(__DIR__."/src")
    ->in(__DIR__."/lib")
;
