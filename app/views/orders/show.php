<?php $title = 'Order view | SCAM' ?>

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

<h3 class="subheader">Order view</h3>

<?php if($order->product_code): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right"></label>
        </div>
        <div class="small-10 columns">
            <img class="product-image" src="?c=listings&a=productImage&code=<?= $order->product_code ?>"
                 alt="Picture of product <?= $this->e($order->product_name) ?>"
                 title="Picture of product <?= $this->e($order->product_name) ?>"
                 width="100"/>
        </div>
    </div>
<?php endif ?>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">Title</label>
    </div>
    <div class="small-10 columns">
        <?= $this->e($order->title) ?>
    </div>
</div>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">Price</label>
    </div>
    <div class="small-10 columns">
        <?= $this->formatPrice($order->price) ?>
    </div>
</div>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">Amount</label>
    </div>
    <div class="small-10 columns">
        <?= $order->amount ?>
    </div>
</div>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">Product</label>
    </div>
    <div class="small-10 columns">
        <?php if($order->product_id): ?>
            <a href="?c=listings&a=product&code=<?= $order->product_code ?>">
                <?= $this->e($order->product_name) ?>,
            </a> current price: <?= $this->formatPrice($order->product_price) ?>
        <?php else: ?>
            Product does not exist anymore.
        <?php endif ?>
    </div>
</div>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">Vendor</label>
    </div>
    <div class="small-10 columns">
        <a href="?c=listings&a=vendor&u=<?= $this->h($order->vendor_name, false) ?>"><?= $this->e($order->vendor_name) ?></a>
    </div>
</div>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">Buyer</label>
    </div>
    <div class="small-10 columns">
        <?= $this->e($order->buyer_name) ?>
    </div>
</div>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">State</label>
    </div>
    <div class="small-10 columns">
            <span class="label <?= \Scam\OrderModel::needsActionFrom($this->user->is_vendor, $order->state) ? 'alert' : 'secondary' ?>">
                <?= $this->e(\Scam\OrderModel::stateDescription($order->state)) ?>
            </span>
    </div>
</div>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">Created at</label>
    </div>
    <div class="small-10 columns">
        <?= $this->formatTimestamp($order->created_at) ?>
    </div>
</div>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">Last updated at</label>
    </div>
    <div class="small-10 columns">
        <?= $this->formatTimestamp($order->updated_at) ?>
    </div>
</div>

<?php if($order->shipping_info): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Shipping info</label>
        </div>
        <div class="small-10 columns">
            <?= nl2br($this->e($order->shipping_info)) ?>
        </div>
    </div>
<?php endif ?>

<?php if($order->buyer_public_key): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Buyer public key</label>
        </div>
        <div class="small-10 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->buyer_public_key) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->vendor_public_key): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Vendor public key</label>
        </div>
        <div class="small-10 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->vendor_public_key) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->vendor_payout_address): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Vendor payout address</label>
        </div>
        <div class="small-10 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->vendor_payout_address) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->multisig_address): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Multisig address</label>
        </div>
        <div class="small-10 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->multisig_address) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->redeem_script): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Redeem script</label>
        </div>
        <div class="small-10 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->redeem_script) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->unsigned_transaction): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Unsigned transaction</label>
        </div>
        <div class="small-10 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->unsigned_transaction) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->finish_text): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Finish message</label>
        </div>
        <div class="small-10 columns">
            <?= nl2br($this->e($order->finish_text)) ?>
        </div>
    </div>
<?php endif ?>

<?php if($order->feedback_id && $this->user->is_vendor): ?>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Vendor rating</label>
        </div>
        <div class="small-10 columns">
            <?= $order->rating ?>
        </div>
    </div>

    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Rating comment</label>
        </div>
        <div class="small-10 columns">
            <?= nl2br($this->e($order->comment)) ?>
        </div>
    </div>
<?php endif ?>

<hr/>

<?php
require '../app/views/orders/_show_' . \Scam\OrderModel::stateDescription($order->state) . '.php';
?>

<a href="?c=orders">Back to orders</a>
</div>