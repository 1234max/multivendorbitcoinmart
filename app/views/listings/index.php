<?php $title = 'Listings | SCAM' ?>

<div class="large-4 small-12 columns">
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

    <div class="hide-for-small panel">
        <h3 class="subheader">Be safe using <abbr title="Scammer Can't Acquire Money">SCAM.</h3>
        <h5 class="subheader">
            Don't get scammed - use our highly secure market that never stores any of your valuable bitcoins.
        </h5>
    </div>

    <?php if(!empty($unconfirmedOrders) || !empty($orderNeedingActions)): ?>
    <a href="?c=orders">
        <div class="panel callout">
            <?php if(!empty($unconfirmedOrders)): ?>
                <h6>You have <strong><?= count($unconfirmedOrders) ?></strong> unconfirmed order(s).</h6>
            <?php endif ?>
            <?php if(!empty($orderNeedingActions)): ?>
                <h6>You have <strong><?= count($orderNeedingActions) ?> order(s)</strong> that need your interaction.</h6>
            <?php endif ?>
        </div>
    </a>
    <?php endif ?>
</div>

<div class="large-8 columns">
    <div class="row content">
        <form action="?" method="get" class="row">
            <div class="small-4 columns">
                <div class="row collapse prefix-radius">
                    <div class="small-4 columns">
                        <span class="prefix">Sort by</span>
                    </div>
                    <div class="small-8 columns">
                        <select name="sort">
                            <option value="date-asc" <?= $sorting == 'date-asc' ? 'selected="selected"' : '' ?>>Date ⇡</option>
                            <option value="date-desc" <?= $sorting == 'date-desc' ? 'selected="selected"' : '' ?>>Date ⇣</option>
                            <option value="price-asc" <?= $sorting == 'price-asc' ? 'selected="selected"' : '' ?>>Price ⇡</option>
                            <option value="price-desc" <?= $sorting == 'price-desc' ? 'selected="selected"' : '' ?>>Price ⇣</option>
                            <option value="name-asc" <?= $sorting == 'name-asc' ? 'selected="selected"' : '' ?>>Name ⇡</option>
                            <option value="name-desc" <?= $sorting == 'name-desc' ? 'selected="selected"' : '' ?>>Name ⇣</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="small-8 columns">
                <div class="row collapse postfix-round">
                    <div class="small-9 columns">
                        <input type="text" name="q" placeholder="Search" value="<?= $this->e($query) ?>">
                    </div>
                    <div class="small-3 columns">
                        <input type="submit" class="button postfix" value="Go"/>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <hr/>
    <div class="row content">
        <?php
        $productsPerRow = 3;
        $withVendor = true;
        require '../app/views/listings/_product_list.php';
        ?>
    </div>