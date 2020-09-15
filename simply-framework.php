<?php

use SimplyFramework\Container\Extension\Metabox\MetaboxExtension;
use SimplyFramework\Container\Extension\PostType\PostTypeExtension;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

require_once __DIR__ . '/vendor/autoload.php';

define('SIMPLY_CACHE_DIRECTORY', __DIR__ . '/cache');

class Simply {
    private static $container;
    private static function initContainer() {
        $file = __DIR__ .'/cache/container.php';
        $containerConfigCache = new ConfigCache($file, WP_DEBUG);
        $configDirectories = apply_filters('simply_config_directories', array(__DIR__ . '/config'));
        $extensions = apply_filters('simply_container_extensions', array(new PostTypeExtension, new MetaboxExtension));

        if (!$containerConfigCache->isFresh()) {
            $containerBuilder = new ContainerBuilder();
            foreach ($extensions as $anExtension) {
                $containerBuilder->registerExtension($anExtension);
                $containerBuilder->loadFromExtension($anExtension->getAlias());
            }


            foreach ($configDirectories as $aConfigDirectory) {
                $finder = new Finder();
                $finder->files()->in($aConfigDirectory);
                $loader = new YamlFileLoader($containerBuilder, new FileLocator($aConfigDirectory));
                if ($finder->hasResults()) {
                    foreach ($finder as $aFile) {
                        $loader->load($aFile->getRelativePathname());
                    }
                }
            }

            $containerBuilder->compile();

            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(['class' => 'CachedContainer']),
                $containerBuilder->getResources()
            );
        }

        require_once $file;
        self::$container = new CachedContainer();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\Container
     */
    static function getContainer() {
        if (is_null(self::$container)) {
            self::initContainer();
        }
        return self::$container;
    }

    static function bootstrap() {
        self::initContainer();
        self::getContainer()->get('framework.manager')->initialize();
    }
}

// Use after_setup_theme and not init because the command manager use cli_init to register command
add_action('after_setup_theme', function() {
    Simply::bootstrap();
});

add_action('deactivate_plugin', function() {
    $fs = new Filesystem();
    $fs->remove(SIMPLY_CACHE_DIRECTORY);
});

add_action('activate_plugin', function() {
    $fs = new Filesystem();
    $fs->remove(SIMPLY_CACHE_DIRECTORY);
});

