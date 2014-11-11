<?php $formAction = $this->action == 'build' ? '?c=shippingOptions&a=create' : "?c=shippingOptions&a=update" ?>
<form action="<?= $formAction ?>" method="post">
    <?php if($this->action == 'edit'): ?>
        <input type="hidden" name="id" value="<?= $shippingOption->id ?>"/>
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
                           value="<?= $this->e($shippingOption->name) ?>"
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
                           value="<?= floatval($shippingOption->price) ?>"
                           required="true"
                           min="0.0"
                           placeholder="0.1"
                           title="Price in bitcoin">
                </div>
            </div>
            <div class="row">
                <div class="large-9 large-offset-3 columns">
                    <input type="submit" value="Save" class="button small success" />
                </div>
            </div>
        </div>
    </div>
</form>