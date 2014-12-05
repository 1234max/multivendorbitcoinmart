<?php if($this->user->is_vendor): ?>
    <div class="callout panel">This order is marked as shipped. <br/>
        The buyer will sign & broadcast the transaction and thus release the funds once he has received the goods.</div>
<?php else: ?>
    <div class="callout panel">The vendor marked the order as shipped. <br/>
        Please sign & broadcast the multisig transaction with the instructions below, once you've received the goods. <br/>
        This will release the funds and mark the order as finished (as soon as the payment shows up in the blockchain).</div>

    Import command:
    <pre class="bitcoin-value">
    addmultisigaddress 2 '["<?= $this->e($order->vendor_public_key) ?>", "<?= $this->e($order->buyer_public_key) ?>", "<?= BITCOIN_ADMIN_PK ?>"]'</pre>
    <br/>
    <?php
    $transaction = $order->partially_signed_transaction;
    require '../app/views/orders/_sign_instructions.php';
    ?>
    <br/>
    Broadcast command:
    <pre class="bitcoin-value">
    sendrawtransaction 'PASTE_HERE_THE_HEX_OUTPUT_OF_signrawtransaction_ABOVE'</pre>
    <br/>
<?php endif ?>

<?php require '../app/views/orders/_dispute_form.php'; ?>