<?php

namespace Scam;

class ProfileController extends Controller {
    public function settings() {
        $this->renderTemplate('profile/settings.php');
    }

    public function updatePassword() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['current_password']) && is_string($this->post['current_password']));
        $this->accessDeniedUnless(isset($this->post['password']) && is_string($this->post['password']));
        $this->accessDeniedUnless(isset($this->post['password_confirmation']) && is_string($this->post['password_confirmation']));

        $success = false;

        $user = $this->getModel('User');

        # check that new password & confirmation match
        if($this->post['password'] === $this->post['password_confirmation']) {
            # ... that old password matches
            if($user->checkPassword($this->user->id, $this->post['current_password'])) {
                # verify length
                if(mb_strlen($this->post['password']) >= 8) {
                    # save in database
                    if($user->updatePassword($this->user->id, $this->post['password'])) {
                        $success = true;
                    }
                    else {
                        $errorMessage = 'Could not update password due to unknown error.';
                    }
                }
                else {
                    $errorMessage = 'New password must be at least 8 characters.';
                }
            }
            else {
                $errorMessage = 'Current password was wrong.';
            }
        }
        else {
            $errorMessage = 'New passwords did not match.';
        }

        if($success) {
            $this->setFlash('success', 'Successfully updated password.');
            $this->redirectTo('?c=profile&a=settings');
        }
        else {
            $this->renderTemplate('profile/settings.php', ['error' => $errorMessage]);
        }
    }

    public function updateProfilePin() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['current_profile_pin']) && is_string($this->post['current_profile_pin']));
        $this->accessDeniedUnless(isset($this->post['profile_pin']) && is_string($this->post['profile_pin']));
        $this->accessDeniedUnless(isset($this->post['profile_pin_confirmation']) && is_string($this->post['profile_pin_confirmation']));

        $success = false;

        $user = $this->getModel('User');

        # check that new profile pin & confirmation match
        if($this->post['profile_pin'] === $this->post['profile_pin_confirmation']) {
            # ... that old profile pin matches
            if($user->checkProfilePin($this->user->id, $this->post['current_profile_pin'])) {
                # verify length
                if(mb_strlen($this->post['profile_pin']) >= 8) {
                    # save in database
                    if($user->updateProfilePin($this->user->id, $this->post['profile_pin'])) {
                        $success = true;
                    }
                    else {
                        $errorMessage = 'Could not update profile pin due to unknown error.';
                    }
                }
                else {
                    $errorMessage = 'New profile pin must be at least 8 characters.';
                }
            }
            else {
                $errorMessage = 'Current profile pin was wrong.';
            }
        }
        else {
            $errorMessage = 'New profile pins did not match.';
        }

        if($success) {
            $this->setFlash('success', 'Successfully updated profile pin.');
            $this->redirectTo('?c=profile&a=settings');
        }
        else {
            $this->renderTemplate('profile/settings.php', ['error' => $errorMessage]);
        }
    }

    public function multisig() {
        $this->renderTemplate('profile/multisig.php');
    }

    public function becomeVendor() {
        $this->renderTemplate('profile/becomeVendor.php');
    }
}