<?php

namespace SimplyFramework\Template;

use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\FormRenderer;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TwigFunction;

class TemplateEngine {
    private $engine;

    public function __construct() {
        // the path to your other templates
        $viewsDirectory = apply_filters('simply_views_directory', []);

        // environnement configuration
        $envConfig = [
            'cache' => SIMPLY_CACHE_DIRECTORY . '/twig'
        ];
        if (WP_DEBUG) {
            $envConfig['cache'] = false;
        }

        $twig = new Environment(new FilesystemLoader($viewsDirectory), $envConfig);

        // adds the FormExtension to Twig
        $twig->addExtension(new FormExtension());
        $twig = $this->addTwigFunctions($twig);

        $this->engine = apply_filters('simply_template_configuration', $twig);;
    }

    public function addTwigFunctions($twig) {
        $twig->addFunction(new TwigFunction('function', [$this, 'execFunction']));
        $twig->addFunction(new TwigFunction('fn', [$this, 'execFunction']));
        return $twig;
    }

    public function execFunction($function_name) {
        $args = func_get_args();
        array_shift($args);
        if ( is_string($function_name) ) {
            $function_name = trim($function_name);
        }
        return call_user_func_array($function_name, ($args));
    }

    public function render($view, array $context) {
        $this->engine->display($view, $context);
    }
}
