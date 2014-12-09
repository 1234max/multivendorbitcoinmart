<?php if($this->user->is_vendor): ?>
    <div class="callout panel">This order is new and awaiting your acception.<br/>
        Please check if you can fulfill the order and then click 'Accept'. <br/>
        If you can't fullfill it, please provide a detailing message why and click 'Deny'.</div>
<?php else: ?>
    <div class="callout panel">This order must be accepted by the vendor.</div>
<?php endif ?>

<?php if($this->user->is_vendor): ?>
    <form action="?c=orders&a=accept" method="post">
        <input type="hidden" name="h" value="<?= $this->h($order->id) ?>"/>

        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Bitcoin payout address</label>
            </div>
            <div class="small-6 columns end">
                <input name="payout_address"
                       type="text"
                       maxlength="40"
                       autocomplete="false"
                       placeholder="bitcoin payout address (26-35 hex chars)"
                       required="true"
                       title="Please put your payout address (where the funds should be transferred)"
                       value="<?= isset($this->post['payout_address']) ? $this->e($this->post['payout_address']) : '' ?>">
            </div>
        </div>

        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Profile pin</label>
            </div>
            <div class="small-6 columns end">
                <input type="password"
                       name="profile_pin"
                       placeholder="Enter your profile pin"
                       required="true"
                       autocomplete="off">
            </div>
        </div>

        <div class="row">
            <div class="small-6 small-offset-2 columns">
                <input type="submit" value="Accept" class="button small success" />
            </div>
        </div>
    </form>

    <hr/>

    <form action="?c=orders&a=decline" method="post">
        <input type="hidden" name="h" value="<?= $this->h($order->id) ?>"/>
        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Message</label>
            </div>
            <div class="small-6 columns end">
                <textarea name="decline_message"
                          rows="3"
                          placeholder="I'm sorry, but..."
                          required="true"
                          title="Please provide a detailed explanation to show to the buyer."><?= isset($this->post['decline_message']) ? $this->e($this->post['decline_message']) : '' ?></textarea>
            </div>
        </div>

        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Profile pin</label>
            </div>
            <div class="small-6 columns end">
                <input type="password"
                       name="profile_pin"
                       placeholder="Enter your profile pin"
                       required="true"
                       autocomplete="off">
            </div>
        </div>

        <div class="row">
            <div class="small-6 small-offset-2 columns">
                <input type="submit" value="Decline" class="button small alert" />
            </div>
        </div>
    </form>
<?php endif ?>