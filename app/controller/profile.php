<?php

namespace Scam;

class ProfileController extends Controller {
    public function settings() {
        # get a random sign for challenge/response pgp key verification
        $_SESSION['random_str'] = $this->getModel('User')->getRandomStr();

        $this->renderTemplate('profile/settings.php');
    }

    public function updatePassword() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['current_password']) && is_string($this->post['current_password']));
        $this->accessDeniedUnless(isset($this->post['password']) && is_string($this->post['password']) && mb_strlen($this->post['password']) >= 8);
        $this->accessDeniedUnless(isset($this->post['password_confirmation']) && is_string($this->post['password_confirmation']));

        $success = false;
        $errorMessage = '';

        $user = $this->getModel('User');

        # check that new password & confirmation match
        if($this->post['password'] === $this->post['password_confirmation']) {
            # ... that old password matches
            if($user->checkPassword($this->user->id, $this->post['current_password'])) {
                # save in database
                if($user->updatePassword($this->user->id, $this->post['password'])) {
                    $success = true;
                }
                else {
                    $errorMessage = 'Could not update password due to unknown error.';
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
        $this->accessDeniedUnless(isset($this->post['profile_pin']) && is_string($this->post['profile_pin']) && mb_strlen($this->post['profile_pin']) >= 8);
        $this->accessDeniedUnless(isset($this->post['profile_pin_confirmation']) && is_string($this->post['profile_pin_confirmation']));

        $success = false;
        $errorMessage = '';

        $user = $this->getModel('User');

        # check that new profile pin & confirmation match
        if($this->post['profile_pin'] === $this->post['profile_pin_confirmation']) {
            # ... that old profile pin matches
            if($user->checkProfilePin($this->user->id, $this->post['current_profile_pin'])) {
                # save in database
                if($user->updateProfilePin($this->user->id, $this->post['profile_pin'])) {
                    $success = true;
                }
                else {
                    $errorMessage = 'Could not update profile pin due to unknown error.';
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

    public function setPGP() {
        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['pgp_public_key']) && is_string($this->post['pgp_public_key']) && mb_strlen($this->post['pgp_public_key']) >= 0);
        $this->accessDeniedUnless(isset($this->post['pgp_sign_response']) && is_string($this->post['pgp_sign_response']) && mb_strlen($this->post['pgp_sign_response']) >= 0);
        $this->accessDeniedUnless(isset($this->post['profile_pin']) && is_string($this->post['profile_pin']));

        $success = false;
        $errorMessage = '';

        $user = $this->getModel('User');
        $pubKey = trim($this->post['pgp_public_key']);
        $signature = trim($this->post['pgp_sign_response']);

        # validate pgp public key
        if ($user->isValidPGPPublicKey($pubKey)) {
            # validate signature (challenge response)
            if ($user->isValidPGPSignatureOfMessage($pubKey, $signature, $_SESSION['random_str'])) {
                # check profile pin
                if($user->checkProfilePin($this->user->id, $this->post['profile_pin'])) {
                    if ($user->setPGP($this->user->id, $pubKey)) {
                        $success = true;
                    } else {
                        $errorMessage = 'Could not set PGP due to unknown error.';
                    }
                }
                else {
                    $errorMessage = 'Profile pin wrong.';

                }
            }
            else {
                $errorMessage = 'Signature invalid.';

            }
        }
        else {
            $errorMessage = 'Not a valid PGP public key.';
        }

        if($success) {
            $this->setFlash('success', 'Successfully updated PGP key.');
            $this->redirectTo('?c=profile&a=settings');
        }
        else {
            # new challenge
            $_SESSION['random_str'] = $user->getRandomStr();
            $this->renderTemplate('profile/settings.php', ['error' => $errorMessage]);
        }
    }

    public function resetProfilePin() {
        $this->accessDeniedUnless($this->user->pgp_public_key);

        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['pgp_sign_response']) && is_string($this->post['pgp_sign_response']) && mb_strlen($this->post['pgp_sign_response']) >= 0);
        $this->accessDeniedUnless(isset($this->post['profile_pin']) && is_string($this->post['profile_pin']) && mb_strlen($this->post['profile_pin']) >= 8);
        $this->accessDeniedUnless(isset($this->post['profile_pin_confirmation']) && is_string($this->post['profile_pin_confirmation']));

        $success = false;
        $errorMessage = '';

        $user = $this->getModel('User');

        # check that new profile pin & confirmation match
        if($this->post['profile_pin'] === $this->post['profile_pin_confirmation']) {
            # validate pgp signature (challenge response)
            if ($user->isValidPGPSignatureOfMessage($this->user->pgp_public_key, trim($this->post['pgp_sign_response']), $_SESSION['random_str'])) {
                # save in database
                if($user->updateProfilePin($this->user->id, $this->post['profile_pin'])) {
                    $success = true;
                }
                else {
                    $errorMessage = 'Could not update profile pin due to unknown error.';
                }
            }
            else {
                $errorMessage = 'PGP signature was invalid.';
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
            # new challenge
            $_SESSION['random_str'] = $user->getRandomStr();
            $this->renderTemplate('profile/settings.php', ['error' => $errorMessage]);
        }
    }

    public function bip32() {
        $this->renderTemplate('profile/bip32.php');
    }

    public function setBip32() {
        # refuse if key is already set
        $this->accessDeniedIf($this->user->bip32_extended_public_key);

        # check for existence & format of input params
        $this->accessDeniedUnless(isset($this->post['bip32_extended_public_key']) && is_string($this->post['bip32_extended_public_key']) && mb_strlen($this->post['bip32_extended_public_key']) >= 0);
        $this->accessDeniedUnless(isset($this->post['profile_pin']) && is_string($this->post['profile_pin']));

        $success = false;
        $errorMessage = '';

        $user = $this->getModel('User');

        # validate BIP32 extended public key
        $key = $user->parseBip32ExtendedPK($this->post['bip32_extended_public_key']);
        if ($key) {
            # check that M/0'/0 is provided
            if ($key['depth'] == 2) {
                # check profile pin
                if($user->checkProfilePin($this->user->id, $this->post['profile_pin'])) {
                    if ($user->setBip32ExtendedPublicKey($this->user->id, trim($this->post['bip32_extended_public_key']))) {
                        $success = true;
                    } else {
                        $errorMessage = 'Could not set PK due to unknown error.';
                    }
                }
                else {
                    $errorMessage = 'Profile pin wrong.';

                }
            }
            else {
                $errorMessage = "Key depth is wrong (not M/k'/0 was given)";

            }
        }
        else {
            $errorMessage = 'Not a valid BIP32 extended public key.';
        }

        if($success) {
            $this->setFlash('success', 'Successfully set BIP32 configuration.');
            $this->redirectTo('?c=profile&a=bip32');
        }
        else {
            $this->renderTemplate('profile/bip32.php', ['error' => $errorMessage]);
        }
    }

    public function becomeVendor() {
        $this->accessDeniedIf($this->user->is_vendor);
        $hasOrders = $this->getModel('Order')->hasOrdersAsBuyer($this->user->id);
        $noPGPKey = !$this->user->pgp_public_key;

        $this->renderTemplate('profile/becomeVendor.php', ['hasOrders' => $hasOrders, 'noPGPKey' => $noPGPKey]);
    }

    public function doBecomeVendor() {
        $this->accessDeniedIf($this->user->is_vendor);
        $hasOrders = $this->getModel('Order')->hasOrdersAsBuyer($this->user->id);
        $noPGPKey = !$this->user->pgp_public_key;
        $this->accessDeniedIf($hasOrders || $noPGPKey);

        $user = $this->getModel('User');
        if($user->becomeVendor($this->user->id)) {
            $this->setFlash('success', 'Successfully became vendor.');
            $this->redirectTo('?c=vendor&a=bip32');
        }
        else {
            $this->renderTemplate('profile/becomeVendor.php', ['error' => 'Could not become vendor due to unknown error.',
                'hasOrders' => $hasOrders, 'noPGPKey' => $noPGPKey]);
        }
    }
}