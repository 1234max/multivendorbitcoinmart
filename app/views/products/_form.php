<?php if(isset($error)): ?>
    <div data-alert class="alert-box alert">
        <?= $this->e($error) ?>
    </div>
<?php endif ?>

<?php $formAction = isset($product->id) ? '?c=products&a=update' : "?c=products&a=create" ?>
<form action="<?= $formAction ?>" method="post" enctype="multipart/form-data">
    <?php if(isset($product->id)): ?>
        <input type="hidden" name="code" value="<?= $product->code ?>"/>
    <?php endif ?>
    <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
    <div class="row">
        <div class="large-8 columns">
            <div class="row">
                <div class="large-3 columns">
                    <label for="name" class="right inline">Name</label>
                </div>
                <div class="large-9 columns">
                    <input type="text"
                           name="name"
                           value="<?= $this->e($product->name) ?>"
                           placeholder="Enter a name"
                           required="true"
                           pattern=".{3,}"
                           title="3 characters minimum">
                </div>
            </div>
            <div class="row">
                <div class="large-3 columns">
                    <label for="description" class="right inline">Description</label>
                </div>
                <div class="large-9 columns">
                    <textarea name="description"
                              rows="6"
                              placeholder="Please put detailled product description here."
                              required="true"
                              title="Will be shown on product page"><?= $this->e($product->description) ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="large-3 columns">
                    <label for="price" class="right inline">Price in BTC</label>
                </div>
                <div class="large-9 columns">
                    <input type="number"
                           step="any"
                           name="price"
                           value="<?= floatval($product->price) ?>"
                           required="true"
                           min="0.0"
                           placeholder="0.1"
                           title="Price in bitcoin">
                </div>
            </div>
            <div class="row">
                <div class="large-3 columns">
                    <label for="image" class="right inline">Image</label>
                </div>
                <div class="large-9 columns">
                    <?php if(isset($product->id)): ?>
                        <?php if($product->hasImage): ?>
                            <?php $imageUrl = "?c=listings&a=productImage&code=" . $product->code ?>
                            <a href="<?= $imageUrl ?>" target="_blank">
                                <img src="<?= $imageUrl ?>"
                                     alt="Picture of product <?= $this->e($product->name) ?>"
                                     title="Picture of product <?= $this->e($product->name) ?>"
                                     width="80"/></a>
                            <a href="?c=products&a=destroyImage&code=<?= $product->code ?>" class="button tiny alert">Delete</a>
                            <br/>
                        <?php else: ?>
                            <small><strong>No image specified.</strong></small>
                        <?php endif ?>
                        <br/>
                    <?php endif ?>

                    <small>Max. 5MB, format: JPEG, PNG or GIF.</small>
                    <input type="file" name="image" id="image"/>
                </div>
            </div>
            <div class="row">
                <div class="large-3 columns">
                    <label for="tags" class="right inline">Tags</label>
                </div>
                <div class="large-9 columns">
                    <textarea name="tags"
                              rows="2"
                              placeholder="Jacket, clothes, winter, coat, ..."
                              title="Comma seperated tags to find the product"><?= $this->e($product->tags) ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="large-3 columns">
                    <label for="hidden" class="right inline">Is hidden</label>
                </div>
                <div class="large-9 columns">
                    <div class="switch">
                        <input type="checkbox"
                               name="is_hidden"
                               id="is_hidden"
                               value="1"
                            <?= $product->is_hidden ? 'checked="checked"' : '' ?> />
                        <label for="is_hidden"></label>
                        <br/>
                        <small>
                            <?php if($product->is_hidden): ?>
                                <?php if(isset($product->id)): ?>
                                    This hidden product can only be found by knowing this link: <a href="?c=listings&a=product&code=<?= $product->code ?>">Product page</a>
                                <?php endif ?>
                            <?php else: ?>
                                By default, the product can be found by everyone on the listings page.<br/>
                            <?php endif ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="large-3 columns">
                    <label for="shipping_options[]" class="right inline">Shipping options</label>
                </div>
                <div class="large-9 columns">
                    <small>Choose at least one shipping option:</small>
                    <br/>
                    <?php foreach($shippingOptions as $shippingOption): ?>
                        <input type="checkbox"
                               name="shipping_options[]"
                               value="<?= $this->h($shippingOption->id) ?>"
                            <?= isset($product->shippingOptions[$shippingOption->id]) ? 'checked="checked"' : '' ?> />
                        <label>
                            <?= $this->e($shippingOption->name) ?> (<?= $this->formatPrice($shippingOption->price) ?>)
                        </label>
                        <br/>
                    <?php endforeach ?>
                </div>
            </div>

            <hr/>
            <div class="row">
                <div class="large-9 large-offset-3 columns">
                    <input type="submit" value="Save" class="button small success" />
                </div>
            </div>
        </div>
    </div>
</form>

<a href="?c=products">Back</a>