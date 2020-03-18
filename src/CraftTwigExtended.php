<?php

namespace crafttwigextended;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use yii\base\Event;

class CraftTwigExtended extends Plugin
{
    public static $plugin;

    public $schemaVersion = '1.0.0';

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new class extends \Twig_Extension
        {
            public function getFunctions()
            {
                return [
                    new \Twig_Function('autoversion', array($this, 'autoversion'))
                ];
            }

            public function autoversion($resource)
            {
                $output = '%s';
                $query  = [];

                if ( preg_match('/^((https?:\/\/)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(\/.*)?$/i', $resource) )
                {
                    $parsed_url = parse_url($resource);

                    $output =
                        (isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '') .
                        rtrim((isset($parsed_url['host']) ? $parsed_url['host'] : ''), '/') .
                        '%s';

                    parse_str(($parsed_url['query'] ?? ''), $query);

                    $resource = $parsed_url['path'];
                }

                if ( file_exists(CRAFT_BASE_PATH . '/web/' . preg_replace('/\/?web(\/.*)/i', '$1', ltrim($resource, '/'))) )
                {
                    $pathinfo = pathinfo($resource);

                    $query['_v'] = filemtime(CRAFT_BASE_PATH . '/web/' . preg_replace('/\/?web(\/.*)/i', '$1', ltrim($resource, '/')));

                    $resource = $pathinfo['dirname'] . ($pathinfo['dirname'] == '/' ? '' : '/' ) . $pathinfo['basename'];
                }

                return sprintf($output, $resource) . ($query ? '?' . http_build_query($query) : '');
            }
        });
    }
}
