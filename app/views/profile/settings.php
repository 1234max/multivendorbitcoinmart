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
            <div class="large-8 columns">
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
            <div class="large-8 columns">
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
    <p>Todo.</p>
</div>