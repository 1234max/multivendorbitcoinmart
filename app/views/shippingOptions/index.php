<?php $title = 'Shipping options | SCAM' ?>

<div class="large-12 columns">
    <h3 class="subheader">Shipping options</h3>

    <?php if($this->fl('success')): ?>
        <div data-alert class="alert-box success">
            <?= $this->fl('success') ?>
        </div>
    <?php endif ?>

    <?php if($this->fl('error')): ?>
        <div data-alert class="alert-box alert">
            <?= $this->fl('error') ?>
        </div>
    <?php endif ?>

    <?php if(empty($shippingOptions)): ?>
        <div data-alert class="alert-box secondary">
            No shipping options found.
        </div>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th width="80"></th>
                <th width="80"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($shippingOptions as $shippingOption): ?>
                <tr>
                    <td><?= $this->e($shippingOption->name) ?></td>
                    <td><?= $this->formatPrice($shippingOption->price) ?></td>
                    <td><a href="?c=shippingOptions&a=edit&h=<?= $this->h($shippingOption->id) ?>" class="button tiny">Edit</a></td>
                    <td>
                        <form action="?c=shippingOptions&a=destroy" method="post">
                            <input type="hidden" name="h" value="<?= $this->h($shippingOption->id) ?>"/>
                            <input type="submit" value="Delete" class="button tiny alert"/>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

    <a href="?c=shippingOptions&a=build" class="button small success">New shipping option</a>
</div>