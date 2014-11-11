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
                <li class="toggle-topbar menu-icon">
                    <a href="#"><span>menu</span></a>
                </li>
            </ul>
            <?php if($this->isUserLoggedIn()): ?>
                <section class="top-bar-section">
                    <ul class="right">
                        <li><span class="label secondary round account-label"><i class="fi-torso"></i> <?= $this->user->name ?></span></li>
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
                            <a href="#">Profile</a>
                            <ul class="dropdown">
                                <li><label>General</label></li>
                                <li<?= $this->controller == 'profile' && $this->get['a'] == 'settings' ? ' class="active"' : ''?>>
                                    <a href="?c=profile&a=settings">Settings</a>
                                </li>
                                <li<?= $this->controller == 'profile' && $this->get['a'] == 'multisig' ? ' class="active"' : ''?>>
                                    <a href="?c=profile&a=multisig">Multisig configuration</a>
                                </li>
                                <li class="divider"></li>
                                <li><label>Vendor profile</label></li>
                                <?php if($this->user->is_vendor): ?>
                                <li>
                                    <a href="?c=profile&a=settings">Settings</a>
                                </li>
                                <li>
                                    <a href="#">Vendor page</a>
                                </li>
                                <li class="has-dropdown not-click">
                                    <a class="" href="#">Listings</a>
                                    <ul class="dropdown">
                                        <li>
                                            <a href="#">Products</a>
                                        </li>
                                        <li>
                                            <a href="#">Shipping Options</a>
                                        </li>
                                    </ul>
                                </li>
                                <?php else: ?>
                                    <li<?= $this->controller == 'profile' && $this->get['a'] == 'becomeVendor' ? ' class="active"' : ''?>>
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