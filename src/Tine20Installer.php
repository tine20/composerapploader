<?php
/**
 * Tine 2.0
 *
 * @package     Tine20\ComposerAppLoader
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @copyright   Copyright (c) 2016 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Paul Mehrer <p.mehrer@metaways.de>
 *
 */

namespace Tine20\ComposerAppLoader;

use \Composer\Installer\LibraryInstaller;
use \Composer\Package\PackageInterface;

/**
 * Class Tine20Installer
 *
 * @package Tine20\ComposerAppLoader
 */
class Tine20Installer extends LibraryInstaller
{
    protected $tine20BasePath = null;

    protected function getTine20BasePath()
    {
        if (null === $this->tine20BasePath) {
            $vendorDir = rtrim($this->vendorDir, '/');

            $vendorPart = trim($this->composer->getConfig()->get('vendor-dir', \Composer\Config::RELATIVE_PATHS), '/');
            if (strlen($vendorPart) > 0) {
                $this->tine20BasePath = rtrim(substr($vendorDir, 0, strrpos($vendorDir, $vendorPart)), '/');
            } else {
                $this->tine20BasePath = $vendorDir;
            }
        }
        return $this->tine20BasePath;
    }

    protected function createTine20Links(PackageInterface $package)
    {
        $basePath = $this->getTine20BasePath() . '/';
        $extra = $package->getExtra();

        if (!is_array($extra) || !isset($extra['symlinks']) || !is_array($extra['symlinks']) || count($extra['symlinks']) < 1) {
            return;
        }

        $this->io->writeError('    Creating Links');

        $vendorPart = trim($this->composer->getConfig()->get('vendor-dir', \Composer\Config::RELATIVE_PATHS), '/');
        if (strlen($vendorPart) > 0) {
            $vendorPart .= '/';
        }

        $vendorPart .= $package->getPrettyName();
        $targetDir = $package->getTargetDir();

        $vendorPart .= ($targetDir ? '/'.$targetDir : '');

        if (isset($extra['symlinks'])) {
            foreach ($extra['symlinks'] as $trgt => $src) {

                $dirs = explode('/', $trgt);
                $prefix = '';

                if (($count = count($dirs)) > 1) {
                    $baseDirs = explode('/', trim($basePath, '/'));
                    $start = true;
                    $i = 0;
                    $postfix = '';
                    foreach ($dirs as $dir) {
                        // we ignore last path part
                        if (++$i === $count) {
                            break;
                        }
                        if ($dir === '..') {
                            if (!$start) {
                                $this->io->writeError('     illegal path found: "' . $trgt . '"');
                                continue 2;
                            }
                            $postfix .= array_pop($baseDirs) . '/';
                        } else {
                            $start = false;
                            $prefix .= '../';
                        }
                    }
                    $prefix .= $postfix;
                }

                $this->io->writeError('     ln -s ' . './' . $prefix . $vendorPart . '/' . $src . ' ' . $basePath . $trgt);
                exec('ln -s ' . './' . $prefix . $vendorPart . '/' . $src . ' ' . $basePath . $trgt);
            }
        }

        if (isset($extra['reverseSymlinks'])) {
            foreach ($extra['reverseSymlinks'] as $trgt => $src) {

                $srcPrefix = '';
                $dirs = explode ('/', $vendorPart);
                foreach ($dirs as $dir) {
                    $srcPrefix .= '../';
                }

                $this->io->writeError('     ln -s ./' . $srcPrefix . $src . ' ./' . $vendorPart . '/' . $trgt);
                exec('ln -s ./' . $srcPrefix . $src . ' ./' . $vendorPart . '/' . $trgt);
            }
        }
    }

    protected function removeTine20Links(PackageInterface $package)
    {
        $basePath = $this->getTine20BasePath() . '/';
        $extra = $package->getExtra();

        if (!is_array($extra) || !isset($extra['symlinks']) || !is_array($extra['symlinks']) || count($extra['symlinks']) < 1) {
            return;
        }

        $this->io->writeError('    Removing Links');

        if (isset($extra['symlinks'])) {
            foreach ($extra['symlinks'] as $trgt => $src) {
                $this->io->writeError('    rm ' . $basePath . $trgt);
                exec('rm ' . $basePath . $trgt);
            }
        }

        if (isset($extra['reverseSymlinks'])) {
            $vendorPart = trim($this->composer->getConfig()->get('vendor-dir', \Composer\Config::RELATIVE_PATHS), '/');
            if (strlen($vendorPart) > 0) {
                $vendorPart .= '/';
            }

            $vendorPart .= $package->getPrettyName();
            $targetDir = $package->getTargetDir();

            $vendorPart .= ($targetDir ? '/'.$targetDir : '');

            foreach ($extra['reverseSymlinks'] as $trgt => $src) {

                $this->io->writeError('    rm ./' . $vendorPart . '/' . $trgt);
                exec('rm ./' . $vendorPart . '/' . $trgt);
            }
        }
    }

    protected function installCode(PackageInterface $package)
    {
        parent::installCode($package);

        $this->createTine20Links($package);
    }

    protected function updateCode(PackageInterface $initial, PackageInterface $target)
    {
        $this->removeTine20Links($initial);

        parent::updateCode($initial, $target);

        $this->createTine20Links($target);
    }
}