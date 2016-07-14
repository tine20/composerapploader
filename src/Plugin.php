<?php
/**
 * Tine 2.0
 *
 * @package     composerapploader
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @copyright   Copyright (c) 2016 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Paul Mehrer <p.mehrer@metaways.de>
 *
 */

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