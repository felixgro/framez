<a
    class="ggallery-item"
    href="<?= esc_url($image['preview']); ?>"
    data-url="<?php echo esc_url($image['url']); ?>"
    data-name="<?php echo esc_attr($image['name']); ?>"
    data-pswp-width="<?php echo esc_attr($image['width']); ?>"
    data-pswp-height="<?php echo esc_attr($image['height']); ?>">

    <img
        src="<?= esc_url($image['thumbnail']); ?>"
        width="<?php echo $image['width']; ?>"
        height="<?php echo $image['height']; ?>"
        alt="<?php echo esc_attr($image['name']); ?>"
        loading="lazy" />

</a>