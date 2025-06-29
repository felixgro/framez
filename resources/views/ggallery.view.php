<?php
use FrameZ\Utils\View;
?>

<div 
    id="<?= esc_attr(uniqid('framez-')); ?>"
    class="framez"
    data-directory="<?= esc_attr($directory); ?>" 
    data-loadmore="<?= esc_attr($loadmore ?? false); ?>"
>

    <?php foreach ($images as $image): ?>

        <?= View::render('framez-item', [
            'image' => $image
        ]) ?>

    <?php endforeach; ?>

</div>