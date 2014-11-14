<?php $title = 'Vendor ' . $this->e($vendor->name) . ' | SCAM' ?>

<div class="large-12 columns">
    <h3 class="subheader">Vendor <?= $this->e($vendor->name) ?></h3>

    <?php
    $productsPerRow = 5;
    $withVendor = false;
    require '../app/views/listings/_product_list.php';
    ?>

    <a href="?">Back to listings</a>
</div>