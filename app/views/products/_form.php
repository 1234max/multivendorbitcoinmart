<?php if(isset($error)): ?>
    <div data-alert class="alert-box alert">
        <?= $this->e($error) ?>
    </div>
<?php endif ?>

<?php $formAction = isset($product->id) ? '?c=products&a=update' : "?c=products&a=create" ?>
<form action="<?= $formAction ?>" method="post">
    <?php if(isset($product->id)): ?>
        <input type="hidden" name="id" value="<?= $product->id ?>"/>
    <?php endif ?>
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
                    <label for="shipping_options[]" class="right inline">Shipping options</label>
                </div>
                <div class="large-9 columns">
                    <small>Choose at least one shipping option:</small>
                    <br/>
                    <?php foreach($shippingOptions as $shippingOption): ?>
                        <input type="checkbox"
                               name="shipping_options[]"
                               id="shipping_option-<?= $shippingOption->id ?>"
                               value="<?= $shippingOption->id ?>"
                               <?= isset($product->shippingOptions[$shippingOption->id]) ? 'checked="checked"' : '' ?> />
                        <label for="shipping_option-<?= $shippingOption->id ?>"><?= $this->e($shippingOption->name) ?></label>
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