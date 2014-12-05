<?php $title = 'Disputes | Admin | SCAM' ?>

<div class="large-12 columns">
    <a href="?c=admin&a=logout" class="button alert logout tiny">
        Logout
        <i class="fa fa-sign-out"></i>
    </a>

    <h3 class="subheader">Disputes</h3>

    <?php if(empty($disputes)): ?>
        <div data-alert class="alert-box secondary">
            No open disputes.
        </div>
    <?php else: ?>
        <table class="full-width">
            <thead>
            <tr>
                <th>Title</th>
                <th>Price</th>
                <th>Buyer</th>
                <th>Vendor</th>
                <th width="250">Updated at</th>
                <th width="80"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($disputes as $order): ?>
                <tr>
                    <td><?= $this->e($order->title) ?></td>
                    <td><?= $this->formatPrice($order->price) ?></td>
                    <td><?= $this->e($order->buyer_name) ?></td>
                    <td><?= $this->e($order->vendor_name) ?></td>
                    <td><?= $this->formatTimestamp($order->updated_at) ?></td>
                    <td><a href="?c=admin&a=showDispute&id=<?= $order->id ?>" class="button tiny">Show</a></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>
</div>