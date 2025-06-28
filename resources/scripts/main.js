import Masonry from "masonry-layout";
import PhotoSwipeLightbox from "photoswipe/lightbox";
import InfiniteScroll from "infinite-scroll";
import "photoswipe/style.css";

const configDesktop = {
    itemWidth: 250,
    gap: 32,
};

const configMobile = {
    itemWidth: 150,
    gap: 16,
};

const configBreakpoint = 868;

let config = innerWidth < configBreakpoint ? configMobile : configDesktop;

// If the breakpoint is crossed, reload the page
window.addEventListener("resize", () => {
    if (innerWidth < configBreakpoint && config !== configMobile) {
        window.location.reload();
    } else if (innerWidth >= configBreakpoint && config !== configDesktop) {
        window.location.reload();
    }
});

const initGGallery = (ggallery) => {
    const id = ggallery.getAttribute("id");
    const loadMore = ggallery.getAttribute("data-loadmore") != "false" && ggallery.getAttribute("data-loadmore") != "0";
    
    // Prepare styles for the gallery items
    const styles = document.createElement("style");
    styles.innerHTML = `
        #${id} .ggallery-item {
            width: ${config.itemWidth}px;
            margin-bottom: ${config.gap}px;
        }
    `;
    document.head.appendChild(styles);

    const directory = ggallery.getAttribute("data-directory");

    // Initialize masonry for the gallery
    const msnry = new Masonry(ggallery, {
        itemSelector: ".ggallery-item",
        columnWidth: config.itemWidth,
        gutter: config.gap,
        fitWidth: true,
    });

    // Initialize infinite scroll for the gallery
    let isEmpty = false;
    const infScroll = new InfiniteScroll(ggallery, {
        path: function () {
            let pageNumber = this.loadCount + 1;
            pageNumber += 1;

            if (isEmpty || !loadMore) return null;

            return (
                "/wp-json/ggallery/v1/images?page=" +
                pageNumber +
                "&directory=" +
                directory
            );
        },
        // append: ".ggallery-item",
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
                    ggallery.querySelectorAll(".ggallery-item")
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
        const items = tmpEl.querySelectorAll(".ggallery-item");
        if (items.length === 0) {
            isEmpty = true;
            infScroll.destroy();
            return;
        }
        for (const item of items) {
            ggallery.appendChild(item);
        }
        msnry.appended(items);
    });

    console.log("GGallery main.js loaded");
};

const galleries = document.querySelectorAll(".ggallery");
galleries.forEach((ggallery) => initGGallery(ggallery));
