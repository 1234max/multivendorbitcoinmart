<table class="full-width">
    <thead>
    <tr>
        <th>Title</th>
        <th>Price</th>
        <th>
            <?php if($this->user->is_vendor): ?>
                Buyer
            <?php else: ?>
                Vendor
            <?php endif ?>
        </th>
        <th width="250">Updated at</th>
        <th>State</th>
        <th width="80"></th>
        <th width="80"></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($orders as $order): ?>
        <tr>
            <td><?= $this->e($order->title) ?></td>
            <td><?= $this->formatPrice($order->price) ?></td>
            <td>
                <?php if($this->user->is_vendor): ?>
                    <?= $this->e($order->buyer_name) ?>
                <?php else: ?>
                    <?= $this->e($order->vendor_name) ?>
                <?php endif ?>
            </td>
            <td>
                <?= $this->formatTimestamp($order->updated_at) ?>
            </td>
            <td>
                <span class="label <?= \Scam\OrderModel::needsActionFrom($this->user->is_vendor, $order->state) ? 'alert' : 'secondary' ?>">
                    <?= $this->e(\Scam\OrderModel::stateDescription($order->state)) ?>
                </span>
            </td>
            <td>
                <a href="?c=orders&a=show&h=<?= $this->h($order->id) ?>" class="button tiny">Show</a>
            </td>
            <td>
                <?php if(\Scam\OrderModel::isDeletable($order, $this->user->id)): ?>
                    <form action="?c=orders&a=destroy" method="post">
                        <input type="hidden" name="h" value="<?= $this->h($order->id) ?>"/>
                        <input type="submit" value="Delete" class="button tiny alert"/>
                    </form>
                <?php endif ?>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>