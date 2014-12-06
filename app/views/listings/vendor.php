<?php $title = 'Vendor ' . $this->e($vendor->name) . ' | SCAM' ?>

<div class="large-12 columns">
    <h3 class="subheader">Vendor <?= $this->e($vendor->name) ?></h3>
    Number of deals: <strong><?= $numberOfDeals ?></strong><br/>
    Average rating (Ø):
    <?php if($averageRating): ?>
        <strong><?= number_format($averageRating, 2) ?></strong>
    <?php else: ?>
        Not rated yet.
    <?php endif ?>

    <h4 class="subheader">Feedbacks</h4>
    <?php if(empty($feedbacks)): ?>
        <div data-alert class="alert-box secondary">
            This vendor has no feedbacks yet.
        </div>
    <?php else: ?>
        <?php foreach($feedbacks as $feedback): ?>
            <blockquote>Rating: <strong><?= $feedback->rating ?>/5</strong>
                <?php if(mb_strlen($feedback->comment) > 0): ?>
                    <br/>
                    <?= nl2br($this->e($feedback->comment)) ?>
                <?php endif ?>
                <cite>A buyer with <?= $feedback->buyer_deal_count ?> deal(s)</cite>
            </blockquote>
        <?php endforeach ?>
    <?php endif ?>

    <h4 class="subheader">Products</h4>
    <?php if(empty($products)): ?>
        <div data-alert class="alert-box secondary">
            This vendor has no public products.
        </div>
    <?php else: ?>
        <?php
        $productsPerRow = 5;
        $withVendor = false;
        require '../app/views/listings/_product_list.php';
        ?>
    <?php endif ?>

    <h4 class="subheader">PGP public key</h4>
    <pre class="alert-box secondary bitcoin-value"><?= $this->e($this->user->pgp_public_key) ?></pre>

    <a href="?">Back to listings</a>
</div>