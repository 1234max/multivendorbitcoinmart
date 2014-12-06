<?php $title = 'Become vendor | SCAM' ?>
<div class="large-12 columns">
    <h3 class="subheader">Become a vendor</h3>
    <?php if(isset($error)): ?>
        <div data-alert class="alert-box alert">
            <?= $this->e($error) ?>
        </div>
    <?php endif ?>

    <?php if($hasOrders): ?>
        <div data-alert class="alert-box warning">
            You can't become vendor as long as there are any orders for your account.
        </div>
    <?php elseif($noPGPKey): ?>
        <div data-alert class="alert-box warning">
            You have no PGP key specified. Please set it in your <a href="?c=profile&a=settings#pgp">Profile settings</a>.
        </div>
    <?php else: ?>
        <div data-alert class="alert-box secondary">
            Please note that you can't buy products anymore as vendor and can't downgrade your account to buyer.
        </div>
        <form action="?c=profile&a=doBecomeVendor" method="post">
            <input type="submit" value="Become vendor" class="button small success" />
        </form>
    <?php endif ?>
</div>