<?php
use GGallery\Utils\View;
?>

<div 
    id="<?= esc_attr(uniqid('ggallery-')); ?>"
    class="ggallery"
    data-directory="<?= esc_attr($directory); ?>" 
    data-loadmore="<?= esc_attr($loadmore ?? false); ?>"
>

    <?php foreach ($images as $image): ?>

        <?= View::render('ggallery-item', [
            'image' => $image
        ]) ?>

    <?php endforeach; ?>

</div>