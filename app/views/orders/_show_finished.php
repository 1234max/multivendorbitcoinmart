<?php if(!$this->user->is_vendor && $order->feedback_id): ?>
    <div class="callout panel">Please rate the vendor:</div>
    <form action="?c=orders&a=feedback" method="post">
        <input type="hidden" name="h" value="<?= $this->h($order->id) ?>"/>
        <div class="row order">
            <div class="small-2 columns">
                <label class="right" for="rating">Rating</label>
            </div>
            <div class="small-6 columns end">
                <input type="number"
                       name="rating"
                       value="<?= $order->rating == null ? '' : $order->rating ?>"
                       required="true"
                       min="1"
                       max="5"
                       title="Rate the vendor with 1 (worst) to 5 (best)">
            </div>
        </div>

        <div class="row order">
            <div class="small-2 columns">
                <label class="right">Comment</label>
            </div>
            <div class="small-6 columns end">
                <textarea name="comment"
                          rows="3"
                          placeholder="Great delivery, ..."><?= $order->comment == null ? '' : $this->e($order->comment) ?></textarea>
            </div>
        </div>

        <div class="row">
            <div class="small-6 small-offset-2 columns">
                <input type="submit" value="Save feedback" class="button small success" />
            </div>
        </div>
    </form>
<?php else: ?>
    <div class="panel">This order is finished.</div>
<?php endif ?>

    <hr/>

<?php if(\Scam\OrderModel::isDeletable($order, $this->user->id)): ?>
    <div class="callout panel">This order with its history can now be deleted. <br/>
        Please leave feedback before deleting since it's not possible afterwards.</div>

    <form action="?c=orders&a=destroy" method="post">
        <input type="hidden" name="h" value="<?= $this->h($order->id) ?>"/>
        <input type="submit" value="Delete" class="button small alert" />
    </form>
<?php else: ?>
    <div class="callout panel">After 14 days upon completion, this order and its history can be deleted (feedbacks are preserved).</div>
<?php endif ?>