<?php $title = 'BIP32 configuration | SCAM' ?>

<div class="large-12 columns">
    <h3 class="subheader">BIP32 configuration</h3>

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

    <?php if($this->user->bip32_extended_public_key): ?>
        <div data-alert class="alert-box secondary">
            You have specified the following extend public key (M/k'/0):
        </div>
        <pre class="bitcoin-value"><?= $this->e($this->user->bip32_extended_public_key) ?></pre>
        <br/>
    <?php else: ?>
        <div data-alert class="alert-box secondary">
            You need to create a bitcoin BIP32 extended public key and submit it here.<br/>
            Using this master public key, SCAM derives a unique public key for every order. <br/>
            You will then sign the transaction locally with the corresponding private key (of the derived public key).
            <br/><br/>
            <strong>You can use <a href="http://bip32.org/" target="_blank">bip32.org</a> to generate your extended public key:</strong>
            <ol>
                <li>Either derive a new public key from a passphrase or input an existing master public key (select "Derive from: BIP32 Key")</li>
                <li>Choose Derivation Path: <strong>External account (master): m/k'/0</strong> - Account (k) can be chosen freely</li>
                <li>Save the passphrase or the private key somewhere savely, since you will use it for signing again</li>
                <li>Enter <strong>Derived Public Key</strong> below:</li>
            </ol>
        </div>
        <form action="?c=profile&a=setBip32" method="post">
            <div class="row">
                <div class="large-10 columns">
                    <div class="row">
                        <div class="large-3 columns">
                            <label for="bip32_extended_public_key" class="right inline">BIP32 extended public key (M/0'/0):</label>
                        </div>
                        <div class="large-9 columns">
                            <textarea name="bip32_extended_public_key"
                                   rows="2"
                                   autocomplete="false"
                                   placeholder="xpub..."
                                   required="true"
                                   title="BIP32 extended public key (M/0'/0), starting with xpub..."
                                   ><?= isset($this->post['bip32_extended_public_key']) ? $this->e($this->post['bip32_extended_public_key']) : '' ?></textarea>
                        </div>
                    </div>


                    <div class="row">
                        <div class="large-3 columns">
                            <label class="right">Profile pin</label>
                        </div>
                        <div class="large-6 columns end">
                            <input type="password"
                                   name="profile_pin"
                                   placeholder="Enter your profile pin"
                                   required="true"
                                   autocomplete="off">
                        </div>
                    </div>
                    <div class="row">
                        <div class="large-9 large-offset-3 columns">
                            <input type="submit" value="Set key" class="button small success" />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif ?>
</div>