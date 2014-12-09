<?php $title = 'Disputes | Admin | SCAM' ?>

<div class="large-12 columns">
    <a href="?c=admin&a=logout" class="button alert logout tiny">
        Logout
        <i class="fa fa-sign-out"></i>
    </a>

    <?php if($this->fl('success')): ?>
        <div data-alert class="alert-box success">
            <?= $this->fl('success') ?>
        </div>
    <?php endif ?>

    <?php if(isset($error)): ?>
        <div data-alert class="alert-box alert">
            <?= $this->e($error) ?>
        </div>
    <?php endif ?>

    <?php
    $order = $dispute;
    $forAdmin = true;
    require '../app/views/orders/_order_infos.php';
    ?>

    <hr/>
    <h4 class="subheader">Add message</h4>
    <form action="?c=admin&a=addDisputeMessage" method="post">
        <input type="hidden" name="id" value="<?= $order->id ?>"/>
        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Message</label>
            </div>
            <div class="small-6 columns end">
                <textarea name="dispute_message"
                          rows="3"
                          placeholder="Hey guys, please sort it out by yourselves..."
                          required="true"
                          title="You can leave another message here"><?= isset($this->post['dispute_message']) ? $this->e($this->post['dispute_message']) : '' ?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="small-6 small-offset-2 columns">
                <input type="submit" value="Add message" class="button small success" />
            </div>
        </div>
    </form>

    <h4 class="subheader">Create new transaction (total: <?= $this->formatPrice($order->price) ?>)</h4>
    <form action="?c=admin&a=createNewTransaction" method="post">
        <input type="hidden" name="id" value="<?= $order->id ?>"/>
        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Vendor refund</label>
            </div>
            <div class="small-6 columns end">
                <input type="number"
                       step="any"
                       name="vendor_refund"
                       value="<?= isset($this->post['vendor_refund']) ? floatval($this->post['vendor_refund']) : '' ?>"
                       min="0.0"
                       placeholder="0.1"
                       title="Vendor refund in bitcoin">
            </div>
        </div>

        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Buyer refund</label>
            </div>
            <div class="small-6 columns end">
                <input type="number"
                       step="any"
                       name="buyer_refund"
                       value="<?= isset($this->post['buyer_refund']) ? floatval($this->post['buyer_refund']) : '' ?>"
                       min="0.0"
                       placeholder="0.1"
                       title="Buyer refund in bitcoin">
            </div>
        </div>

        <div class="row">
            <div class="small-6 small-offset-2 columns">
                <input type="submit" value="Create unsigned transaction" class="button small success" />
            </div>
        </div>
    </form>

    <?php if($dispute->dispute_unsigned_transaction): ?>
        <?php
        $transaction = $order->dispute_unsigned_transaction;
        $keyIndex = $order->admin_key_index;
        require '../app/views/orders/_sign_instructions.php';
        ?>
        <br/><br/>

        <form action="?c=admin&a=enterSignedTransaction" method="post">
            <input type="hidden" name="id" value="<?= $order->id ?>"/>
            <div class="row order">
                <div class="small-2 columns">
                    <label class="right">Unsigned transaction</label>
                </div>
                <div class="small-10 columns end">
                    <code class="bitcoin-value">
                        <?= $this->e($dispute->dispute_unsigned_transaction) ?>
                    </code>
                </div>
            </div>

            <div class="row order">
                <div class="small-2 columns">
                    <label class="right">Signed transaction</label>
                </div>
                <div class="small-10 columns end">
                    <textarea name="partially_signed_transaction"
                              autocomplete="false"
                              rows="6"
                              placeholder="Raw multisig transaction"
                              required="true"
                              title="Please put the raw multisig transaction - signed by you - here."><?= isset($this->post['partially_signed_transaction']) ? $this->e($this->post['partially_signed_transaction']) : '' ?></textarea>
                </div>
            </div>

            <div class="row">
                <div class="small-6 small-offset-2 columns">
                    <input type="submit" value="Save & publish signed transaction" class="button success small" />
                </div>
            </div>
        </form>
    <?php endif ?>

    <a href="?c=admin&a=disputes">Back to disputes</a>
</div>