import Masonry from "masonry-layout";
import PhotoSwipeLightbox from "photoswipe/lightbox";
import "photoswipe/style.css";
import InfiniteScroll from "infinite-scroll";

const configDesktop = {
    itemWidth: 250,
    gap: 32,
};

const configMobile = {
    itemWidth: 150,
    gap: 16,  
}

const configBreakpoint = 868;

let config = innerWidth < configBreakpoint ? configMobile : configDesktop;

// Prepare styles for the gallery items
const styles = document.createElement("style");
styles.innerHTML = `
    .ggallery-item {
        width: ${config.itemWidth}px;
        margin-bottom: ${config.gap}px;
    }
`;
document.head.appendChild(styles);

// If the breakpoint is crossed, reload the page
window.addEventListener("resize", () => {
    if (innerWidth < configBreakpoint && config !== configMobile) {
        window.location.reload();
    } else if (innerWidth >= configBreakpoint && config !== configDesktop) {
        window.location.reload();
    }
});

const gallery = document.querySelector("#ggallery");
const directory = gallery.getAttribute("data-directory");

// Initialize masonry for the gallery
const msnry = new Masonry(gallery, {
    itemSelector: ".ggallery-item",
    columnWidth: config.itemWidth,
    gutter: config.gap,
    fitWidth: true,
});

// Initialize infinite scroll for the gallery
let isEmpty = false;
const infScroll = new InfiniteScroll(gallery, {
    path: function () {
      let pageNumber = this.loadCount + 1;
      pageNumber += 1;

      if (isEmpty) return null;
      
      return "/wp-json/ggallery/v1/images?page=" + pageNumber + "&directory=" + directory
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
    gallery: "#ggallery",
    children: "a",
    showHideAnimationType: "zoom",
    loop: false,
    padding: { top: 20, bottom: 20, left: 20, right: 20 },
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
                el.href = pswp.currSlide.data.src;
            });
        },
    });
});
let isLoading = false;
lightbox.on('change', () => {
  const totalSlides = lightbox.pswp.getNumItems();
    const currentSlide = lightbox.pswp.currSlide;
    console.log("Current slide index:", currentSlide.index);
    const shouldLoadMore = currentSlide.index >= totalSlides - 4;
    if (shouldLoadMore && !isEmpty && !isLoading) {
        isLoading = true;
        console.log("Loading next page...");
        infScroll.loadNextPage().then(() => {
          lightbox.pswp.options.dataSource = Array.from(gallery.querySelectorAll(".ggallery-item"));
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
        gallery.appendChild(item);
    }
    msnry.appended(items);
});

console.log("GGallery main.js loaded");
