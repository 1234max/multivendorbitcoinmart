<?php if($this->user->is_vendor): ?>
    <div class="callout panel">This order was paid by the customer on the multisig address. <br/>
        Please ship the product now. <br/>
        Then sign the multisig transaction using the instructions below and put in in the field, then mark the order as 'Shipped'.<br/>
        The buyer will complete the transaction once he has received the goods.</div>

    <pre class="bitcoin-value">
        # todo: take parameters from vin_transaction (create when making unsigned transaction)
        signrawtransaction <?= $this->e($order->unsigned_transaction) ?> ''[{"txid":"13a366b02810cd40b96eb4f2d26f01458142e206055645c35b801c54f29d8d0f", "vout": 1, "scriptPubKey":"a914fd9015513096997509efe977cab9c43d9086b41887", "redeemScript": "522102206f5c1af722393ef88781c9a937a22f441292035417d8c3bc94b2d140c055bc21024c81c8ad7fa418129f3c78990f910cc81fc34276a1d76617464d3732ad6e112a21022710e6fd81b88079fa1f1ca969e4244ab50c64d6c96858a814b26a20ba58c61053ae"}]'' '["$NEW_ADDRESS1_PRIVATE_KEY"]'
    </pre>
    <br/><br/>
    <form action="?c=orders&a=shipped" method="post">
        <input type="hidden" name="h" value="<?= $this->h($order->id) ?>"/>
        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Signed transaction</label>
            </div>
            <div class="small-6 columns end">
                <textarea name="signed_transaction"
                          rows="3"
                          placeholder="Raw multisig transaction"
                          required="true"
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