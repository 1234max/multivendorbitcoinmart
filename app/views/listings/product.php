<?php $title = 'Product ' . $this->e($product->name) .' | SCAM' ?>

<div class="large-12 columns">
    <img src="?c=listings&a=productImage&code=<?= $product->code ?>"
         alt="Picture of product <?= $this->e($product->name) ?>"
         title="Picture of product <?= $this->e($product->name) ?>"/>
</div>