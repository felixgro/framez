import Masonry from "masonry-layout";
import PhotoSwipeLightbox from "photoswipe/lightbox";
import InfiniteScroll from "infinite-scroll";
import "photoswipe/style.css";

const isInPreview = !!document.querySelector("#framez_gallery_preview");

const configDesktop = {
    itemWidth: 250,
    gap: 32,
};

const configMobile = {
    itemWidth: 150,
    gap: 16,
};

const configBreakpoint = isInPreview ? 1200 : 868;

let currentConfig = innerWidth < configBreakpoint ? configMobile : configDesktop;

function updateGalleryLayout(framez, config) {
    const id = framez.getAttribute("id");
    let styleTag = document.querySelector(`#style-framez-${id}`);
    if (!styleTag) {
        styleTag = document.createElement("style");
        styleTag.id = `style-framez-${id}`;
        document.head.appendChild(styleTag);
    }
    styleTag.innerHTML = `
        #${id} .framez-item {
            width: ${config.itemWidth}px;
            margin-bottom: ${config.gap}px;
        }
    `;
    // Update Masonry options and relayout
    if (framez.msnry) {
        framez.msnry.options.columnWidth = config.itemWidth;
        framez.msnry.options.gutter = config.gap;
        framez.msnry.layout();
    }
}

function updateConfigAndLayout() {
    const newConfig = innerWidth < configBreakpoint ? configMobile : configDesktop;
    if (currentConfig !== newConfig) {
        currentConfig = newConfig;
        document.querySelectorAll(".framez").forEach((framez) => {
            updateGalleryLayout(framez, currentConfig);
        });
    }
}

window.addEventListener("resize", updateConfigAndLayout);

const initFrameZ = (framez) => {
    const id = framez.getAttribute("id");
    const loadMore =
        framez.getAttribute("data-loadmore") != "false" &&
        framez.getAttribute("data-loadmore") != "0";

    // Prepare styles for the gallery items
    const styles = document.createElement("style");
    styles.innerHTML = `
        #${id} .framez-item {
            width: ${currentConfig.itemWidth}px;
            margin-bottom: ${currentConfig.gap}px;
        }
    `;
    document.head.appendChild(styles);

    const gallery = framez.getAttribute("data-gallery");

    // Initialize masonry for the gallery
    const msnry = new Masonry(framez, {
        itemSelector: ".framez-item",
        columnWidth: currentConfig.itemWidth,
        gutter: currentConfig.gap,
        fitWidth: true,
    });
    framez.msnry = msnry;

    updateGalleryLayout(framez, currentConfig);

    // Initialize infinite scroll for the gallery
    let isEmpty = false;
    const infScroll = new InfiniteScroll(framez, {
        path: function () {
            let pageNumber = this.loadCount + 1;
            pageNumber += 1;

            if (isEmpty || !loadMore) return null;

            return (
                "/wp-json/framez/v1/images?page=" +
                pageNumber +
                "&gallery=" +
                gallery
            );
        },
        // append: ".framez-item",
        responseBody: "json",
        history: false,
        outlayer: msnry,
        checkLastPage: false,
        prefill: false,
    });

    // Initialize lightbox for the gallery
    const lightbox = new PhotoSwipeLightbox({
        gallery: `#${id}`,
        children: "a",
        showHideAnimationType: "zoom",
        loop: false,
        padding: { top: 65, bottom: 60, left: 20, right: 20 },
        pswpModule: () => import("photoswipe"),
    });
    lightbox.on("uiRegister", function () {
        lightbox.pswp.ui.registerElement({
            name: "download-button",
            order: 8,
            isButton: true,
            tagName: "a",

            // SVG with outline
            html: {
                isCustomSVG: true,
                inner: '<path d="M20.5 14.3 17.1 18V10h-2.2v7.9l-3.4-3.6L10 16l6 6.1 6-6.1ZM23 23H9v2h14Z" id="pswp__icn-download"/>',
                outlineID: "pswp__icn-download",
            },

            onInit: (el, pswp) => {
                el.setAttribute("download", "");
                el.setAttribute("target", "_blank");
                el.setAttribute("rel", "noopener");

                pswp.on("change", () => {
                    el.href = pswp.currSlide.data.element.dataset.url;
                });
            },
        });
    });
    let isLoading = false;
    lightbox.on("change", () => {
        if (!loadMore) return;
        const totalSlides = lightbox.pswp.getNumItems();
        const currentSlide = lightbox.pswp.currSlide;
        const shouldLoadMore = currentSlide.index >= totalSlides - 4;
        if (shouldLoadMore && !isEmpty && !isLoading) {
            isLoading = true;
            infScroll.loadNextPage().then(() => {
                lightbox.pswp.options.dataSource = Array.from(
                    framez.querySelectorAll(".framez-item")
                );
                lightbox.pswp.refreshSlideContent(currentSlide.index);
                isLoading = false;
            });
        }
    });
    lightbox.init();

    infScroll.on("load", function (body) {
        const tmpEl = document.createElement("div");
        tmpEl.innerHTML = body;
        const items = tmpEl.querySelectorAll(".framez-item");
        if (items.length === 0) {
            isEmpty = true;
            infScroll.destroy();
            return;
        }
        for (const item of items) {
            framez.appendChild(item);
        }
        msnry.appended(items);
    });
};

const galleries = document.querySelectorAll(".framez");
if (isInPreview) {
    setTimeout(() => {
        galleries.forEach((framez) => {
            initFrameZ(framez);
            setTimeout(() => {
                framez.style.opacity = "1";
            }, 100);
        });
    }, 260);
} else {
    galleries.forEach((framez) => initFrameZ(framez));
}
