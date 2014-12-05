Sign command:
<pre class="bitcoin-value">
    signrawtransaction <?= $this->e($transaction) ?> '''
    [
    <?php $inputs = $this->getModel('Order')->getPaymentInputsForSigning($order->id, $order->redeem_script); foreach($inputs as $input): ?>

        {
        "txid": "<?= $input['txid'] ?>",
        "vout": <?= $input['vout'] ?>,
        "scriptPubKey": "<?= $input['scriptPubKey'] ?>",
        "redeemScript": "<?= $input['redeemScript'] ?>"
        }<?= $input != $inputs[count($inputs)-1] ? ', ' : '' ?>
    <?php endforeach ?>


    ]
    ''' '''
    [
      "PASTE_YOUR_PRIVATE_KEY_HERE"
    ]'''</pre>