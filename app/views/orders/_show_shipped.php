<?php if($this->user->is_vendor): ?>
    <div class="callout panel">This order is marked as shipped. <br/>
        The buyer will sign the transaction and thus release the funds once he has received the goods.</div>
<?php else: ?>
    <div class="callout panel">The vendor marked the order as shipped. <br/>
        Please sign & broadcast the multisig transaction with the instructions below, once you've received the goods. <br/>
        This will release the funds and mark the order as finished.</div>

    <form action="?c=orders&a=received" method="post">
        <input type="hidden" name="id" value="<?= $order->id ?>"/>
        <input type="submit" value="Received" class="button success small" />
    </form>
<?php endif ?>