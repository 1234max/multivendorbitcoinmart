<?php if($this->user->is_vendor): ?>
    <div class="callout panel">This order was paid by the customer on the multisig address. <br/>
        Please ship the product now. <br/>
        Then sign the multisig transaction and put in in the field below, then mark the order as 'Shipped'.<br/>
        The buyer will complete the transaction once he has received the goods.</div>

    <form action="?c=orders&a=shipped" method="post">
        <input type="hidden" name="id" value="<?= $order->id ?>"/>
        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Signed transaction</label>
            </div>
            <div class="small-6 columns end">
                <textarea name="signed_transaction"
                          rows="3"
                          placeholder="Raw multisig transaction"
                          required="false"
                          title="Please put the raw multisig transaction - signed by you - here."><?= isset($this->post['signed_transaction']) ? $this->e($this->post['signed_transaction']) : '' ?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="small-6 small-offset-2 columns">
                <input type="submit" value="Order shipped" class="button success small" />
            </div>
        </div>
    </form>
<?php else: ?>
    <div class="callout panel">This order is paid. The vendor will now ship the goods and sign the transaction.</div>
<?php endif ?>