<?php if($this->user->is_vendor): ?>
    <div class="callout panel">This order must be paid by the buyer.</div>
<?php else: ?>
    <div class="callout panel">Please pay the amout of <strong><?= $this->formatPrice($order->price) ?></strong>
        on the generated multisig address <strong><?= $this->e($order->multisig_address) ?></strong>.<br/>
        It will then automatically be marked as paid and the vendor must dispatch it.</div>
<?php endif ?>