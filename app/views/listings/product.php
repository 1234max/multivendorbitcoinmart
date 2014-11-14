<?php $title = "Product '" . $this->e($product->name) ."' | SCAM" ?>

<div class="large-12 columns">
    <h2 class="subheader"><?= $this->e($product->name) ?></h2>
    <a href="?c=listings&a=vendor&id=<?= $product->user_id ?>">
        <span class="label dark round"><i class="fi-torso"></i> <?= $this->e($product->user) ?></span>
    </a>
    <?php if(!empty($product->tags)): ?>
        <?php foreach(mb_split(',', $product->tags) as $tag): ?>
            <a href="?q=<?= urlencode($tag) ?>">
                <span class="label orange round"><?= $this->e($tag) ?></span>
            </a>
        <?php endforeach ?>
    <?php endif ?>
    <div class="row">
        <div class="small-4 columns">
            <?php $url = "?c=listings&a=productImage&code=" . $product->code ?>
            <a href="<?= $url ?>" target="_blank">
                <img class="product-image" src="<?= $url ?>"
                     alt="Picture of product <?= $this->e($product->name) ?>"
                     title="Picture of product <?= $this->e($product->name) ?>"
                     width="320"
                    />
            </a>
        </div>
        <div class="small-8 columns">

        </div>
    </div>
    <hr/>
    <form action="#" method="post" class="row">
        <div class="small-7 columns">
            <div class="row collapse prefix-radius">
                <div class="small-4 columns">
                    <span class="prefix">Shipping option</span>
                </div>
                <div class="small-8 columns">
                    <select name="shipping_option">
                        <?php foreach($product->shippingOptions as $shippingOption): ?>
                            <option value="<?= $shippingOption->id ?>">
                                <?= $this->e($shippingOption->name) ?> (<?= $this->formatPrice($shippingOption->price) ?>)
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="small-3 columns">
            <div class="row collapse prefix-radius">
                <div class="small-8 columns">
                    <span class="prefix">Amount</span>
                </div>
                <div class="small-4 columns">
                    <input type="number"
                           name="amount"
                           value="1"
                           required="true"
                           min="1"
                           max="99"
                           title="Amount of items to order">
                </div>
            </div>
        </div>
        <div class="small-2 columns">
            <input type="submit" class="button success small" value="Order"/>
        </div>
    </form>

    <a href="?">Back to listings</a>
</div>