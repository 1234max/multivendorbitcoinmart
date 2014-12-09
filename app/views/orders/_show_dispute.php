<?php if($order->dispute_signed_transaction): ?>
    <div class="callout panel">The admin has crafted a new transaction to resolve this dispute (see dispute messages above).<br/>
    <strong>This transaction needs only consent of one party (buyer/vendor).</strong><br/>
    You can sign and broadcast the transaction using the commands below (this will automatically set the order to finished).<br/>
    Otherwise, you can add another dispute message.
    </div>

    Import command:
    <pre class="bitcoin-value">
    addmultisigaddress 2 '["<?= $this->e($order->vendor_public_key) ?>", "<?= $this->e($order->buyer_public_key) ?>", "<?= $this->e($order->admin_public_key) ?>"]'</pre>
    <br/>
    <?php
    $transaction = $order->dispute_signed_transaction;
    $keyIndex = $this->user->is_vendor ? $order->vendor_key_index : $order->buyer_key_index;
    require '../app/views/orders/_sign_instructions.php';
    ?>
    <br/>
    Broadcast command:
    <pre class="bitcoin-value">
    sendrawtransaction 'PASTE_HERE_THE_HEX_OUTPUT_OF_signrawtransaction_ABOVE'</pre>
    <br/>
<?php else: ?>
    <div class="callout panel">This order is being disputed. Please wait until an admin reviews it. <br/>
        You can leave messages below.</div>
<?php endif ?>

<?php require '../app/views/orders/_dispute_form.php'; ?>