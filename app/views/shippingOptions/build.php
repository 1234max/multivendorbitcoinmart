<?php $title = 'New shipping option | SCAM' ?>

<div class="large-12 columns">
    <h3 class="subheader">New shipping option</h3>
    <?php if(isset($error)): ?>
        <div data-alert class="alert-box alert">
            <?= $this->e($error) ?>
        </div>
    <?php endif ?>

    <?php require '../app/views/shippingOptions/_form.php'; ?>
</div>