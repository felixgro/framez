<?php
use FrameZ\Utils\View;
?>

<div 
    id="<?= esc_attr(uniqid('framez-')); ?>"
    class="framez"
    data-gallery="<?= esc_attr($gallery); ?>" 
    data-loadmore="<?= esc_attr($loadmore ?? false); ?>"
>

    <?php foreach ($images as $image): ?>

        <?= View::render('gallery-item', [
            'image' => $image
        ]) ?>

    <?php endforeach; ?>

</div>