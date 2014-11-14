<?php $title = 'Edit product | SCAM' ?>

<div class="large-12 columns">
    <h3 class="subheader">Edit product</h3>

    <?php if($this->fl('success')): ?>
        <div data-alert class="alert-box success">
            <?= $this->fl('success') ?>
        </div>
    <?php endif ?>

    <?php require '../app/views/products/_form.php'; ?>
</div>