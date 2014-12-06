<?php $title = 'Settings | SCAM' ?>

<div class="large-12 columns">
    <?php if($this->fl('success')): ?>
        <div data-alert class="alert-box success">
            <?= $this->fl('success') ?>
        </div>
    <?php endif ?>

    <?php if(isset($error)): ?>
        <div data-alert class="alert-box alert">
            <?= $this->e($error) ?>
        </div>
    <?php endif ?>

    <h3 class="subheader">Update password</h3>
    <form action="?c=profile&a=updatePassword" method="post">
        <div class="row">
            <div class="large-10 columns">
                <div class="row">
                    <div class="large-3 columns">
                        <label for="current_password" class="right inline">Current password</label>
                    </div>
                    <div class="large-9 columns">
                        <input type="password"
                               name="current_password"
                               placeholder="Enter your current password"
                               required="true"
                               autocomplete="off">
                    </div>
                </div>
                <div class="row">
                    <div class="large-3 columns">
                        <label for="password" class="right inline">New password</label>
                    </div>
                    <div class="large-9 columns">
                        <input type="password"
                               name="password"
                               placeholder="Choose a strong, new password."
                               required="true"
                               autocomplete="off"
                               pattern=".{8,}"
                               title="8 characters minimum">
                    </div>
                </div>
                <div class="row">
                    <div class="large-3 columns">
                        <label for="password_confirmation" class="right inline">Confirm password</label>
                    </div>
                    <div class="large-9 columns">
                        <input type="password"
                               name="password_confirmation"
                               placeholder="Repeat your new password"
                               required="true"
                               autocomplete="off"
                               pattern=".{8,}"
                               title="8 characters minimum">
                    </div>
                </div>
                <div class="row">
                    <div class="large-9 large-offset-3 columns">
                        <input type="submit" value="Update" class="button small success" />
                    </div>
                </div>
            </div>
        </div>
    </form>

    <h3 class="subheader">Update profile pin</h3>
    <form action="?c=profile&a=updateProfilePin" method="post">
        <div class="row">
            <div class="large-10 columns">
                <div class="row">
                    <div class="large-3 columns">
                        <label for="current_profile_pin" class="right inline">Current PIN</label>
                    </div>
                    <div class="large-9 columns">
                        <input type="password"
                               name="current_profile_pin"
                               placeholder="Enter your current profile pin"
                               required="true"
                               autocomplete="off">
                    </div>
                </div>
                <div class="row">
                    <div class="large-3 columns">
                        <label for="profile_pin" class="right inline">New profile pin</label>
                    </div>
                    <div class="large-9 columns">
                        <input type="password"
                               name="profile_pin"
                               placeholder="Choose a strong, new profile pin."
                               required="true"
                               autocomplete="off"
                               pattern=".{8,}"
                               title="8 characters minimum">
                    </div>
                </div>
                <div class="row">
                    <div class="large-3 columns">
                        <label for="profile_pin_confirmation" class="right inline">Confirm profile pin</label>
                    </div>
                    <div class="large-9 columns">
                        <input type="password"
                               name="profile_pin_confirmation"
                               placeholder="Repeat your new profile pin"
                               required="true"
                               autocomplete="off"
                               pattern=".{8,}"
                               title="8 characters minimum">
                    </div>
                </div>
                <div class="row">
                    <div class="large-9 large-offset-3 columns">
                        <input type="submit" value="Update" class="button small success" />
                    </div>
                </div>
            </div>
        </div>
    </form>

    <h3 class="subheader">Reset profile pin</h3>
    <?php if($this->user->pgp_public_key): ?>
        <form action="?c=profile&a=resetProfilePin" method="post">
            <div class="row">
                <div class="large-11 columns">
                    <div class="row">
                        <div class="large-3 columns">
                            <label for="profile_pin" class="right inline">New profile pin</label>
                        </div>
                        <div class="large-9 columns">
                            <input type="password"
                                   name="profile_pin"
                                   placeholder="Choose a strong, new profile pin."
                                   required="true"
                                   autocomplete="off"
                                   pattern=".{8,}"
                                   title="8 characters minimum">
                        </div>
                    </div>
                    <div class="row">
                        <div class="large-3 columns">
                            <label for="profile_pin_confirmation" class="right inline">Confirm profile pin</label>
                        </div>
                        <div class="large-9 columns">
                            <input type="password"
                                   name="profile_pin_confirmation"
                                   placeholder="Repeat your new profile pin"
                                   required="true"
                                   autocomplete="off"
                                   pattern=".{8,}"
                                   title="8 characters minimum">
                        </div>
                    </div>

                    <div class="row">
                        <div class="large-3 columns">
                            <label for="pgp_sign_challenge" class="right inline">PGP Sign challenge</label>
                        </div>
                        <div class="large-9 columns">
                            <div data-alert class="alert-box secondary">
                                Please provide the possesion of your current PGP key by signing this random message, i.e. using the following command:
                            </div>

                            <code class="bitcoin-value">
                                echo '<?= $_SESSION['random_str'] ?>' | gpg --clearsign -u your@email.com
                            </code>
                            <br/>
                        </div>
                    </div>

                    <div class="row">
                        <div class="large-3 columns">
                            <label for="pgp_sign_response" class="right inline">PGP Sign response</label>
                        </div>
                        <div class="large-9 columns">
                            <textarea name="pgp_sign_response"
                                      rows="12"
                                      placeholder="-----BEGIN PGP SIGNATURE-----..."
                                      required="true"
                                      title="Enter your PGP signature of the above message here"></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="large-9 large-offset-3 columns">
                            <input type="submit" value="Reset profile pin" class="button small success" />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div data-alert class="alert-box warning">
            Profile PIN can only be reset when a PGP public key is defined.
        </div>
    <?php endif ?>


    <a name="pgp"></a>
    <h3 class="subheader">PGP public key</h3>
    <form action="?c=profile&a=setPGP" method="post">
        <div class="row">
            <div class="large-11 columns">
                <div class="row">
                    <div class="large-3 columns">
                        <label for="pgp_public_key" class="right inline">Current PGP public key</label>
                    </div>
                    <div class="large-9 columns">
                        <?php if($this->user->pgp_public_key): ?>
                            <pre class="alert-box secondary bitcoin-value"><?= $this->e($this->user->pgp_public_key) ?></pre>
                        <?php else: ?>
                            <div data-alert class="alert-box secondary">
                                No PGP public key defined.
                            </div>
                        <?php endif ?>
                    </div>
                </div>

                <div class="row">
                    <div class="large-3 columns">
                        <label for="pgp_public_key" class="right inline">New PGP public key</label>
                    </div>
                    <div class="large-9 columns">
                        <textarea name="pgp_public_key"
                                  rows="18"
                                  placeholder="-----BEGIN PGP PUBLIC KEY BLOCK-----..."
                                  required="true"
                                  title="Set your PGP public key here"><?= isset($this->post['pgp_public_key']) ? $this->e($this->post['pgp_public_key']) : '' ?></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="large-3 columns">
                        <label for="pgp_sign_challenge" class="right inline">Sign challenge</label>
                    </div>
                    <div class="large-9 columns">
                        <div data-alert class="alert-box secondary">
                            Please provide the possesion of the <strong>new</strong> key by signing this random message, i.e. using the following command:
                        </div>

                        <code class="bitcoin-value">
                            echo '<?= $_SESSION['random_str'] ?>' | gpg --clearsign -u your@email.com
                        </code>
                        <br/>
                    </div>
                </div>

                <div class="row">
                    <div class="large-3 columns">
                        <label for="pgp_sign_response" class="right inline">Sign response</label>
                    </div>
                    <div class="large-9 columns">
                        <textarea name="pgp_sign_response"
                                  rows="12"
                                  placeholder="-----BEGIN PGP SIGNATURE-----..."
                                  required="true"
                                  title="Enter your PGP signature of the above message here"></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="large-3 columns">
                        <label for="profile_pin" class="right inline">Profile pin</label>
                    </div>
                    <div class="large-9 columns">
                        <input type="password"
                               name="profile_pin"
                               placeholder="Enter your profile pin"
                               required="true"
                               autocomplete="off">
                    </div>
                </div>
                <div class="row">
                    <div class="large-9 large-offset-3 columns">
                        <input type="submit" value="Update" class="button small success" />
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>