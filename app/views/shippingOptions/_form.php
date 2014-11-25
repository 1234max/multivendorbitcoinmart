<?php if(isset($error)): ?>
    <div data-alert class="alert-box alert">
        <?= $this->e($error) ?>
    </div>
<?php endif ?>

<?php $formAction = isset($shippingOption->id) ? '?c=shippingOptions&a=update' : "?c=shippingOptions&a=create" ?>
<form action="<?= $formAction ?>" method="post">
    <?php if(isset($shippingOption->id)): ?>
        <input type="hidden" name="h" value="<?= $this->h($shippingOption->id) ?>"/>
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
            <hr/>
            <div class="row">
                <div class="large-9 large-offset-3 columns">
                    <input type="submit" value="Save" class="button small success" />
                </div>
            </div>
        </div>
    </div>
</form>

<a href="?c=shippingOptions">Back</a>