<?php

namespace Habib\Master\Console;

use Illuminate\Foundation\Console\MailMakeCommand as GeneratorCommand;

class MailMakeCommand extends GeneratorCommand
{

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('markdown')
            ? dirname(dirname(__DIR__)) . '/base_stubs/markdown-mail.stub'
            : dirname(dirname(__DIR__)) . '/base_stubs/mail.stub';
    }

}
