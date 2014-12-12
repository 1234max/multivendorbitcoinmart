<?php $title = 'Register | SCAM' ?>

<div class="large-8 large-offset-2 columns">
    <?php if(isset($error)): ?>
        <div data-alert class="alert-box alert">
            <?= $this->e($error) ?>
        </div>
    <?php endif ?>

    <div id="login-logo-wrapper">
        <i class="fi-shield login"></i>
    </div>

    <form action="?c=users&a=doRegister" method="post">
        <div class="row collapse">
            <div class="large-3 large-offset-2 columns">
                <span class="prefix">Username</span>
            </div>
            <div class="large-5 columns end">
                <input type="text" name="name"
                       placeholder="userxy"
                       required="true"
                       autocomplete="off"
                       pattern=".{3,}"
                       title="3 characters minimum"
                       value="<?= isset($this->post['name']) ? $this->e($this->post['name']) : '' ?>">
            </div>
        </div>

        <div class="row collapse">
            <div class="large-3 large-offset-2 columns">
                <span class="prefix">Password</span>
            </div>
            <div class="large-5 columns end">
                <input type="password"
                       name="password"
                       placeholder="Choose a strong one."
                       required="true"
                       autocomplete="off"
                       pattern=".{8,}"
                       title="8 characters minimum">
            </div>
        </div>
        <div class="row collapse">
            <div class="large-3 large-offset-2 columns">
                <span class="prefix">Confirm password</span>
            </div>
            <div class="large-5 columns end">
                <input type="password"
                       name="password_confirmation"
                       placeholder=""
                       required="true"
                       autocomplete="off"
                       pattern=".{8,}"
                       title="8 characters minimum">
            </div>
        </div>

        <div class="row collapse">
            <div class="large-3 large-offset-2 columns">
                <span class="prefix">Profile PIN</span>
            </div>
            <div class="large-5 columns end">
                <input type="password"
                       name="profile_pin"
                       placeholder="Choose a strong one."
                       required="true"
                       autocomplete="off"
                       pattern=".{8,}"
                       title="8 characters minimum">
            </div>
        </div>
        <div class="row collapse">
            <div class="large-3 large-offset-2 columns">
                <span class="prefix">Confirm profile PIN</span>
            </div>
            <div class="large-5 columns end">
                <input type="password"
                       name="profile_pin_confirmation"
                       placeholder=""
                       required="true"
                       autocomplete="off"
                       pattern=".{8,}"
                       title="8 characters minimum">
            </div>
        </div>

        <div class="row collapse">
            <div class="large-3 large-offset-2 columns">
                <img src="?c=users&a=captcha" class="captcha-img"/>
            </div>
            <div class="large-5 columns end">
                <input type="text" name="captcha" placeholder="captcha" required="true" autocomplete="off" class="captcha" maxlength="5">
            </div>
        </div>

        <div class="row collapse">
            <div class="large-8 large-offset-2 columns">
                <input type="submit" value="Register" class="button expand success" />
            </div>
        </div>
</div>
<div class="large-2 columns"></div>