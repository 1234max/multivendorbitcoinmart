<?php foreach(array_chunk($products, $productsPerRow) as $row): ?>
    <ul class="small-block-grid-<?= $productsPerRow ?>">
        <?php foreach($row as $product): ?>
            <li>
                <div class="thumbnail">
                    <?php $url ="?c=listings&a=product&code=" . $product->code ?>
                    <a href="<?= $url ?>">
                        <img src="?c=listings&a=productImage&code=<?= $product->code ?>"
                             alt="Picture of product <?= $this->e($product->name) ?>"
                             title="Picture of product <?= $this->e($product->name) ?>"/>
                    </a>
                    <div class="panel">
                        <a href="<?= $url ?>">
                            <h5><?= $this->e($product->name) ?></h5>
                        </a>
                        <h6 class="price"><?= $this->formatPrice($product->price) ?></h6>
                        <?php if(!empty($product->tags)): ?>
                            <?php foreach(mb_split(',', $product->tags) as $tag): ?>
                                <a href="?q=<?= urlencode(trim($tag)) ?>">
                                    <span class="label orange round"><?= $this->e($tag) ?></span>
                                </a>
                            <?php endforeach ?>
                        <?php endif ?>
                        <?php if($withVendor): ?>
                            <?php if(!empty($product->tags)): ?>
                                <br/>
                            <?php endif ?>

                            <a href="?c=listings&a=vendor&u=<?= $this->h($product->user, false) ?>">
                                <span class="label dark round"><i class="fi-torso"></i> <?= $this->e($product->user) ?></span>
                            </a>
                        <?php endif ?>
                    </div>
                </div>
            </li>
        <?php endforeach ?>
    </ul>
<?php endforeach ?>