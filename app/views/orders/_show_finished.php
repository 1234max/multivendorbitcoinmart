<div class="callout panel">This order is finished.</div>

<div class="row order">
    <div class="small-2 columns">
        <label class="right">Finish message</label>
    </div>
    <div class="small-10 columns">
        <?= nl2br($this->e($order->finish_text)) ?>
    </div>
</div>

<hr/>

<?php if(\Scam\OrderModel::isDeletable($order, $this->user->id)): ?>
    <div class="callout panel">This order with its history can now be deleted.</div>

    <form action="?c=orders&a=destroy" method="post">
        <input type="hidden" name="id" value="<?= $order->id ?>"/>
        <input type="submit" value="Delete" class="button small alert" />
    </form>
<?php else: ?>
    <div class="callout panel">After 14 days upon completion, this order and its history can be deleted.</div>
<?php endif ?>

<!-- TODO: feedback -->