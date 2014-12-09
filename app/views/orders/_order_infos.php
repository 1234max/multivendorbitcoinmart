<?php if(!$forAdmin && $order->product_code): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right"></label>
        </div>
        <div class="small-9 columns">
            <img class="product-image" src="?c=listings&a=productImage&code=<?= $order->product_code ?>"
                 alt="Picture of product <?= $this->e($order->product_name) ?>"
                 title="Picture of product <?= $this->e($order->product_name) ?>"
                 width="100"/>
        </div>
    </div>
<?php endif ?>

<div class="row order">
    <div class="small-3 columns">
        <label class="right">Title</label>
    </div>
    <div class="small-9 columns">
        <?= $this->e($order->title) ?>
    </div>
</div>

<div class="row order">
    <div class="small-3 columns">
        <label class="right">Price</label>
    </div>
    <div class="small-9 columns">
        <?= $this->formatPrice($order->price) ?>
    </div>
</div>

<div class="row order">
    <div class="small-3 columns">
        <label class="right">Amount</label>
    </div>
    <div class="small-9 columns">
        <?= $order->amount ?>
    </div>
</div>

<div class="row order">
    <div class="small-3 columns">
        <label class="right">Product</label>
    </div>
    <div class="small-9 columns">
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
    <div class="small-3 columns">
        <label class="right">Vendor</label>
    </div>
    <div class="small-9 columns">
        <a href="?c=listings&a=vendor&u=<?= $this->h($order->vendor_name, false) ?>"><?= $this->e($order->vendor_name) ?></a>
    </div>
</div>

<div class="row order">
    <div class="small-3 columns">
        <label class="right">Buyer</label>
    </div>
    <div class="small-9 columns">
        <?= $this->e($order->buyer_name) ?>
    </div>
</div>

<?php if(!$forAdmin): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">State</label>
        </div>
        <div class="small-9 columns">
            <span class="label <?= \Scam\OrderModel::needsActionFrom($this->user->is_vendor, $order->state) ? 'alert' : 'secondary' ?>">
                <?= $this->e(\Scam\OrderModel::stateDescription($order->state)) ?>
            </span>
        </div>
    </div>
<?php endif ?>

<div class="row order">
    <div class="small-3 columns">
        <label class="right">Created at</label>
    </div>
    <div class="small-9 columns">
        <?= $this->formatTimestamp($order->created_at) ?>
    </div>
</div>

<div class="row order">
    <div class="small-3 columns">
        <label class="right">Last updated at</label>
    </div>
    <div class="small-9 columns">
        <?= $this->formatTimestamp($order->updated_at) ?>
    </div>
</div>

<?php if($order->shipping_info): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Shipping info</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= nl2br($this->e($order->shipping_info)) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->finish_text): ?>
    <br/>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Finish message</label>
        </div>
        <div class="small-9 columns">
            <?= nl2br($this->e($order->finish_text)) ?>
        </div>
    </div>
<?php endif ?>

<?php if(!$forAdmin && $order->feedback_id && $this->user->is_vendor): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Vendor rating</label>
        </div>
        <div class="small-9 columns">
            <?= $order->rating ?>
        </div>
    </div>

    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Rating comment</label>
        </div>
        <div class="small-9 columns">
            <?= nl2br($this->e($order->comment)) ?>
        </div>
    </div>
<?php endif ?>

<?php if($order->buyer_public_key): ?>
    <br/>
    <h4 class="subheader">Payment details</h4>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Buyer public key</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->buyer_public_key) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->buyer_refund_address): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Buyer refund address</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->buyer_refund_address) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->vendor_public_key): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Vendor public key</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->vendor_public_key) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->vendor_payout_address): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Vendor payout address</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->vendor_payout_address) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->admin_public_key): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Admin public key</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->admin_public_key) ?>
            </code>
        </div>
    </div>
<?php endif ?>


<?php if($order->multisig_address): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Multisig address</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->multisig_address) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->redeem_script): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Redeem script</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->redeem_script) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->unsigned_transaction): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Unsigned transaction</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->unsigned_transaction) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->partially_signed_transaction): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Partially signed transaction</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->partially_signed_transaction) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->dispute_message): ?>
    <br/>
    <h4 class="subheader">Dispute</h4>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Dispute messages</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= nl2br($this->e($order->dispute_message)) ?>
            </code>
        </div>
    </div>
<?php endif ?>

<?php if($order->dispute_signed_transaction): ?>
    <div class="row order">
        <div class="small-3 columns">
            <label class="right">Dispute transaction (signed by admin)</label>
        </div>
        <div class="small-9 columns">
            <code class="bitcoin-value">
                <?= $this->e($order->dispute_signed_transaction) ?>
            </code>
        </div>
    </div>
<?php endif ?>
