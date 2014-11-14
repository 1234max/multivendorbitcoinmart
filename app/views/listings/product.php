<?php $title = 'Product ' . $this->e($product->name) .' | SCAM' ?>

<div class="large-12 columns">
    <?php if($product->hasImage): ?>
    <img src="?c=listings&a=productImage&code=<?= $product->code ?>"
         alt="Picture of product <?= $this->e($product->name) ?>"
         title="Picture of product <?= $this->e($product->name) ?>"/>
    <?php else: ?>
    <img src="/img/no_picture.gif"
         alt="No picture for product <?= $this->e($product->name) ?>"
         title="No picture for product <?= $this->e($product->name) ?>"/>
    <?php endif ?>
</div>