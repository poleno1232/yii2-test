<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent">
        <h1 class="display-4">Congratulations!</h1>

        <p class="lead">You have successfully created your Yii-powered application.</p>

        <p><a class="btn btn-lg btn-success" href="http://www.yiiframework.com">Get started with Yii</a></p>
    </div>

    <div class="body-content">
        <div class="row">
        <?php foreach ($data as $head): ?>
            <div class="col-lg-4">
                <h2><?= $head->heading ?></h2>

                <p><?= $head->desc ?></p>

                <p><a class="btn btn-outline-secondary" href="<?= $head->url ?>"><?= $head->url_name ?></a></p>
            </div>
        <?php endforeach ?>
        </div>
        

    </div>
</div>
