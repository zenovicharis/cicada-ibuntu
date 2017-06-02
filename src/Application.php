<?php
/**
 * Created by PhpStorm.
 * User: haris
 * Date: 31.5.17
 * Time: 00:00
 */

namespace Ibuntu;


use Ibuntu\Clients\FacebookClient;
use Ibuntu\Clients\GoogleClient;
use Ibuntu\Middleware\Authentication;
use Ibuntu\Services\LoginService;
use Twig_Environment;
use Twig_SimpleFunction;
use Twig_Loader_Filesystem;

class Application extends \Cicada\Application
{
    public function __construct($configPath, $domain, $protocol)
    {
        parent::__construct();
        $this->configure($configPath);
        $this->configureDatabase();
        $this->setupServices();
        $this->createClients();
        $this->setupMiddleware();
        $this->setupTwig();

    }

    protected function configure($configPath) {
        $this['config'] = function () use ($configPath) {
            return new Configuration($configPath);
        };
    }

    protected function setupServices(){
        $this['loginService'] = function (){
            return new LoginService();
        };
    }

    protected function configureDatabase()
    {
        $dbConfig = $this['config']->getDbConfig();
        \ActiveRecord\Config::initialize(function (\ActiveRecord\Config $cfg) use ($dbConfig) {
            $cfg->set_model_directory('src/Models');
            $cfg->set_connections([
                'main' => sprintf('mysql://%s:%s@%s/%s',
                    $dbConfig['user'], $dbConfig['password'], $dbConfig['host'], $dbConfig['name']
                )
            ]);
            $cfg->set_default_connection('main');
        });
    }

    protected function createClients(){
        $googleCredentials = $this['config']->getGoogleCredentials();
        $this['googleClient'] = function() use ($googleCredentials) {
            return new GoogleClient($googleCredentials);
        };

        $facebookCredentials = $this['config']->getFacebookCredentials();
        $facebookRedirectLoginUrl = $this['config']->getFacebookRedirectUrl();
        $this['facebookClient'] = function() use ($facebookCredentials, $facebookRedirectLoginUrl) {
            return new FacebookClient($facebookCredentials, $facebookRedirectLoginUrl);
        };
    }

    private function setupTwig() {
        $this['twig'] = function() {
            $loader = new \Twig_Loader_Filesystem('front-end/templates');
            $twig = new  \Twig_Environment($loader, array(//
//                'cache' => 'cache',
            ));

            $pathFunction = function ($name, $params = []) {
                /** @var Route $route */
                $route = $this['router']->getRoute($name);
                return $route->getRealPath($params);
            };
            $twig->addFunction(new Twig_SimpleFunction('path', $pathFunction));

            return $twig;
        };
    }

    private function setupMiddleware(){
        $googleClient = $this['googleClient'];
        $facebookClient = $this['facebookClient'];
        $this['authentication'] = function() use ($googleClient, $facebookClient){
            new authentication($googleClient, $facebookClient);
        };
    }
}