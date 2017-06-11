<?php

/**
 * Created by PhpStorm.
 * User: haris
 * Date: 1.6.17
 * Time: 01:32
 */

namespace Ibuntu\Middleware;

use Ibuntu\Application;
use Ibuntu\Clients\GoogleClient;
use Ibuntu\Clients\FacebookClient;
use Ibuntu\Models\User;
use Ibuntu\Services\LoginService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class Authentication
{
    /** @var  GoogleClient $googleClient */
    public $googleClient;

    /** @var  FacebookClient $facebookClient */
    public $facebookClient;

    /** @var  LoginService $loginService */
    public $loginService;

    /** @var Session $session */
    public $session;

    public function __construct($googleClient, $facebookClient, $loginService)
    {
        $this->googleClient = $googleClient;
        $this->facebookClient = $facebookClient;
        $this->loginService = $loginService;
        $this->session = new Session();
        $this->session->start();

    }
    public function authorizeUser(Application $app, Request $request, $type){
        // get user information from Third party
        $user = !empty($request->get('state')) ? $this->getFacebookUser($request->get('state')) : $this->getGoogleUser($request->get('code'));
        if($this->userExists($user['email'])){
            /** @var User $user */
            $user = $this->loginService->createUser($user, $type);
        }
        else{
            $user = $this->loginService->getUserAsArray($user['email']);
        }

        $this->session->set("user", $user);
        $request->request->set('user', $user);
    }

    public function checkCredentials(Application $app, Request $request)
    {
        $email = $request->get('email');
        $user = $this->loginService->getUserAsArray($email);
        if(gettype($user) == "array"){
            $this->session->set("user", $user);
        }
        $request->request->set('user', $user);

    }
    public function isLoggedIn(Application $app, Request $request){
        if(!isset($_SESSION['_sf2_attributes']['user'])){

            $this->checkCredentials($app,$request);
        }
        else{
            $user = $_SESSION['_sf2_attributes']['user'];
            $request->request->set('user', $user);
        }
    }

    public function validateUser(Application $app, Request $request){
        if(!isset($_SESSION['_sf2_attributes']['user'])){
            $request->request->set('user',"User not Authorised to make changes please login as a Professor");
        }
        else{
            $user = $_SESSION['_sf2_attributes']['user'];
            var_dump($user);
        }


    }

    public function getFacebookUser($state){
        $user = $this->facebookClient->getUserData($state);

        return $user;
    }

    public function getGoogleUser($code){
        $user = $this->googleClient->getUserData($code);

        return $user;
    }

    public function userExists($email){
         $user = $this->loginService->findUser($email);

         return empty($user);
    }

}