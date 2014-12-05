<hr/>
<h3 class="subheader">Dispute</h3>
<form action="?c=orders&a=dispute" method="post">
    <input type="hidden" name="h" value="<?= $this->h($order->id) ?>"/>
    <div class="row order">
        <div class="small-2 columns">
            <label class="right">Message</label>
        </div>
        <div class="small-6 columns end">
            <textarea name="dispute_message"
                      rows="3"
                      placeholder="I'm not ok with the handling of the order, because ..."
                      required="true"
                      title="<?= $order->dispute_message ? 'You can leave another message here' : 'Please describe the reason to dispute.' ?>"><?= isset($this->post['dispute_message']) ? $this->e($this->post['dispute_message']) : '' ?></textarea>
        </div>
    </div>

    <div class="row">
        <div class="small-6 small-offset-2 columns">
            <input type="submit" value="<?= $order->dispute_message ? 'Update ' : 'Raise' ?> dispute" class="button small alert" />
        </div>
    </div>
</form>