<?php if($this->user->is_vendor): ?>
    <div class="callout panel">This order must be paid by the buyer.</div>
<?php else: ?>
    <div class="callout panel">Please pay the amout of <strong><?= $this->formatPrice($order->price) ?></strong>
        on the generated multisig address.<br/>
        It will the automatically be marked as paid and the vendor then must dispatch it.</div>

    <form action="?c=orders&a=paid" method="post">
        <input type="hidden" name="id" value="<?= $this->h($order->id) ?>"/>
        <input type="submit" value="Paid" class="button small success" />
    </form>
<?php endif ?>