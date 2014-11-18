<?php $title = 'Orders | SCAM' ?>

<div class="large-12 columns">
    <?php if($this->fl('success')): ?>
        <div data-alert class="alert-box success">
            <?= $this->fl('success') ?>
        </div>
    <?php endif ?>

    <?php if(!empty($unconfirmedOrders)): ?>
        <h4 class="subheader">Unconfirmed orders</h4>
        <?php
        $orders = $unconfirmedOrders;
        require '../app/views/orders/_order_list.php';
        ?>
    <?php endif ?>

    <h4 class="subheader">Pending orders</h4>
    <?php if(empty($pendingOrders)): ?>
        <div data-alert class="alert-box secondary">
            No pending orders.
        </div>
    <?php else: ?>
        <?php
        $orders = $pendingOrders;
        require '../app/views/orders/_order_list.php';
        ?>
    <?php endif ?>

    <h4 class="subheader">Finished orders</h4>
    <?php if(empty($finishedOrders)): ?>
        <div data-alert class="alert-box secondary">
            No finished orders.
        </div>
    <?php else: ?>
        <?php
        $orders = $finishedOrders;
        require '../app/views/orders/_order_list.php';
        ?>
    <?php endif ?>
</div>