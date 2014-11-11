<?php

namespace Scam;

class UsersController extends Controller {

    protected function redirectToLoginIfNotLoggedIn() {
        # no redirect = no check for logged in user for this controller
    }

    public function login() {
        if($this->isUserLoggedIn()) {
            $this->redirectTo('?');
        }

        $this->renderTemplate('users/login.php');
    }

    public function doLogin() {
        if($this->isUserLoggedIn()) {
            $this->redirectTo('?');
        }

        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['name']) && is_string($this->post['name']));
        $this->accessDeniedUnless(isset($this->post['password']) && is_string($this->post['password']));

        # check for username & pw
        $user = $this->getModel('User')->checkLogin($this->post['name'], $this->post['password']);
        if($user) {
            $_SESSION['user_id'] = $user->id;
            session_regenerate_id(true);
            $this->setFlash('success', 'Successfully logged in.');
            $this->redirectTo('?');
        }
        else {
            $this->renderTemplate('users/login.php', ['error' => 'Login failed.']);
        }
    }

    public function logout() {
        # only for logged in users
        parent::redirectToLoginIfNotLoggedIn();

        # clean up session thoroughly (including session cookie)
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
        session_destroy();

        $this->redirectTo('?');
    }

    public function register() {
        if($this->isUserLoggedIn()) {
            $this->redirectTo('?');
        }

        $this->renderTemplate('users/register.php');
    }

    public function doRegister() {
        if($this->isUserLoggedIn()) {
            $this->redirectTo('?');
        }

        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['name']) && is_string($this->post['name']) && mb_strlen($this->post['name']) >= 3);
        $this->accessDeniedUnless(isset($this->post['password']) && is_string($this->post['password']) && mb_strlen($this->post['password']) >= 8);
        $this->accessDeniedUnless(isset($this->post['password_confirmation']) && is_string($this->post['password_confirmation']));
        $this->accessDeniedUnless(isset($this->post['profile_pin']) && is_string($this->post['profile_pin']) && mb_strlen($this->post['profile_pin']) >= 8);
        $this->accessDeniedUnless(isset($this->post['profile_pin_confirmation']) && is_string($this->post['profile_pin_confirmation']));

        $success = false;

        $user = $this->getModel('User');

        # check that name is not emtpy or taken...
        if($user->isNameFree($this->post['name'])) {
            # ... that password match
            if($this->post['password'] === $this->post['password_confirmation']) {
                # ... profile pins match
                if($this->post['profile_pin'] === $this->post['profile_pin_confirmation']) {
                    # save in database
                    if ($user->register($this->post['name'], $this->post['password'], $this->post['profile_pin'])) {
                        $success = true;
                    } else {
                        $errorMessage = 'Could not register due to unknown error.';
                    }
                }
                else {
                    $errorMessage = 'Profile PINs did not match.';
                }
            }
            else {
                $errorMessage = 'Password did not match.';
            }
        }
        else {
            $errorMessage = 'Name already taken.';
        }

        if($success) {
            $this->session['user_id'] = $user;
            session_regenerate_id(true);
            $this->setFlash('success', 'Successfully registered.');
            $this->redirectTo('?c=users&a=login');
        }
        else {
            $this->renderTemplate('users/register.php', ['error' => $errorMessage]);
        }
    }
}
