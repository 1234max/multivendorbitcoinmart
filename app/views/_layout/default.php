<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html lang="en" >

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php if (isset($title)): ?>
            <?= $title ?>
        <?php else: ?>
            SCAM
        <?php endif ?>
    </title>

    <link rel="stylesheet" href="font/foundation-icons/foundation-icons.css" />
    <link rel="stylesheet" href="font/font-awesome/font-awesome.min.css">

    <link rel="stylesheet" href="css/normalize.css">
    <link rel="stylesheet" href="css/foundation.css">

    <link rel="stylesheet" href="css/app.css">
</head>
<body>
<div class="row">
<div class="large-12 columns">
<div class="row">
    <div class="large-12 columns">
        <nav class="top-bar" data-topbar>
            <ul class="title-area">
                <li class="name">
                    <h1><a href="?">
                            <i class="fi-shield"></i>
                                <span class="title">
                                    <span class="first">S</span>cammers
                                    <span class="first">C</span>an't
                                    <span class="first">A</span>cquire
                                    <span class="first">M</span>oney
                                </span>
                        </a></h1>
                </li>
            </ul>
            <?php if($this->isUserLoggedIn()): ?>
                <section class="top-bar-section">
                    <ul class="right">
                        <li><span class="label secondary round account-label">
                                <?php if($this->user->is_vendor): ?>
                                    <i class="fi-pricetag-multiple" title="This account is a vendor account"></i>
                                <?php endif ?>
                                <i class="fi-torso" title="Logged in as <?= $this->e($this->user->name) ?>"></i>
                                <?= $this->e($this->user->name) ?>
                            </span>
                        </li>
                        <li class="divider"></li>
                        <li<?= $this->controller == 'listings' ? ' class="active"' : ''?>>
                            <a href="?c=listings">Listings</a>
                        </li>
                        <li class="divider"></li>
                        <li<?= $this->controller == 'orders' ? ' class="active"' : ''?>>
                            <a href="?c=orders">Orders</a>
                        </li>
                        <li class="divider"></li>
                        <li class="has-dropdown not-click">
                            <a>Profile</a>
                            <ul class="dropdown">
                                <li><label>General</label></li>
                                <li<?= $this->controller == 'profile' && $this->action == 'settings' ? ' class="active"' : ''?>>
                                    <a href="?c=profile&a=settings">Settings</a>
                                </li>
                                <li<?= $this->action == 'bip32' ? ' class="active"' : ''?>>
                                    <a href="?c=profile&a=bip32">BIP32 configuration</a>
                                </li>
                                <li class="divider"></li>
                                <li><label>Vendor profile</label></li>
                                <?php if($this->user->is_vendor): ?>
                                <li>
                                    <a href="?c=listings&a=vendor&u=<?= $this->h($this->user->name, false) ?>">Vendor page</a>
                                </li>
                                <li class="has-dropdown not-click">
                                    <a>Listings</a>
                                    <ul class="dropdown">
                                        <li<?= $this->controller == 'products' ? ' class="active"' : ''?>>
                                            <a href="?c=products">Products</a>
                                        </li>
                                        <li<?= $this->controller == 'shippingOptions' ? ' class="active"' : ''?>>
                                            <a href="?c=shippingOptions">Shipping options</a>
                                        </li>
                                    </ul>
                                </li>
                                <?php else: ?>
                                    <li<?= $this->controller == 'profile' && $this->action == 'becomeVendor' ? ' class="active"' : ''?>>
                                        <a href="?c=profile&a=becomeVendor">Become vendor</a>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </li>
                        <li class="divider"></li>
                        <li class="has-form">
                            <a href="?c=users&a=logout" class="button alert logout">
                                Logout
                                <i class="fa fa-sign-out"></i>
                            </a>
                        </li>
                    </ul>
                </section>
            <?php endif ?>
        </nav>
    </div>
</div>
<div class="row body">
    <?= $content ?>
</div>
<footer class="row">
    <div class="large-12 columns">
        <hr>
        <div class="row">
            <div class="large-6 columns">
                <p>© Matthias Winzeler @ Berne University of Applied Sciences.</p>
            </div>
            <div class="large-6 columns">
                <ul class="inline-list right">
                    <li>
                        <a href="https://github.com/MatthiasWinzeler/scam" target="_blank">Github repo</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>
</div>
</div>
</body>
</html>