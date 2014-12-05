<?php $title = 'Login | Admin | SCAM' ?>

<div class="large-8 large-offset-2 columns">
    <?php if(isset($error)): ?>
        <div data-alert class="alert-box alert">
            <?= $this->e($error) ?>
        </div>
    <?php endif ?>
    <?php if($this->fl('success')): ?>
        <div data-alert class="alert-box success">
            <?= $this->fl('success') ?>
        </div>
    <?php endif ?>

    <code class="bitcoin-value">
        signmessage ADDRESS_OF_ADMIN_PK <?= $_SESSION['random_str'] ?>
    </code>

    <br/>

    <form action="?c=admin&a=doLogin" method="post">
        <div class="row collapse">
            <div class="large-2 large-offset-1 columns">
                <span class="prefix">Signature</span>
            </div>
            <div class="large-8 columns end">
                <input type="text" name="signature" placeholder="Signature" required="true" autocomplete="off">
            </div>
        </div>
        <div class="row collapse">
            <div class="large-10 large-offset-1 columns end">
                <input type="submit" value="Login" class="button expand success" />
            </div>
        </div>
</div>
<div class="large-2 columns"></div>