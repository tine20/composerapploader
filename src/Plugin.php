<?php

namespace Tine20\ComposerAppLoader;

use Composer\Composer;
use Composer\IO\IOInterface;

class Plugin implements \Composer\Plugin\PluginInterface
{
    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $installationManager = $composer->getInstallationManager();

        $installationManager->addInstaller(new Tine20Installer($io, $composer, 'tine20application'));
    }
}