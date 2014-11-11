<?php $title = 'Become vendor | SCAM' ?>

<p>Todo: PGP etc.</p>

<h3 class="subheader">Become a vendor</h3>
<div class="large-12 columns">
    <?php if(isset($error)): ?>
        <div data-alert class="alert-box alert">
            <?= $this->e($error) ?>
        </div>
    <?php endif ?>

    <form action="?c=profile&a=doBecomeVendor" method="post">
        <input type="submit" value="Become vendor" class="button small success" />
    </form>
</div>