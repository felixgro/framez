const hiddenInput = document.querySelector(
        "#framez_image_choice"
    );


let selectedImages = [];

// read the hidden input field and parse the JSON
if (hiddenInput) {
    /*
    selectedImages = JSON.parse(hiddenInput.value) || [];
    selectImages(selectedImages);
    */
}

const captureGalleryClicks = (gallery) => {
    const items = gallery.querySelectorAll(".framez-item");
    items.forEach((item) => {
        item.addEventListener("click", (event) => {
            const isEditingEnabled =
                document.querySelector("#framez_editing")?.checked;
            if (!isEditingEnabled) {
                return;
            }
            event.stopPropagation();
            event.preventDefault();
            item.dataset.selected =
                item.dataset.selected == "true" ? "false" : "true";
            const imageUrl = item?.dataset?.url;
            if (item.dataset.selected === "true") {
                selectedImages.push(imageUrl);
            } else {
                const index = selectedImages.indexOf(imageUrl);
                if (index > -1) {
                    selectedImages.splice(index, 1);
                }
            }
            updateSelectedCount();
            console.log(selectedImages);
        });
    });
};

// capture the clicks on the gallery
const galleries = document.querySelectorAll(".framez");

galleries.forEach((gallery) => {
    captureGalleryClicks(gallery);
});

const selctInfo = document.querySelector(".framez-preview-header small");
document
    .querySelector("#framez_editing")
    ?.addEventListener("change", (event) => {
        document.querySelector(".framez-preview-header h3").textContent = event
            .target.checked
            ? "Edit Mode"
            : "Preview Mode";
        updateSelectedCount();
        selctInfo.style.display = event.target.checked ? "block" : "none";
        document.getElementById('framez_gallery_preview').dataset.editing = event.target.checked ? "true" : "false";
    });

function updateSelectedCount() {
    const count = selectedImages.length;
    if (count == 1) {
        selctInfo.textContent = `${count} image selected`;
    } else {
        selctInfo.textContent =
            count > 0 ? `${count} images selected` : "No images selected";
    }

    // write the selected images to the hidden input field
    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(selectedImages);
    }
}

function selectImages(images) {
    for (const imageUrl of images) {
        const item = document.querySelector(
            `.framez-item[data-url="${imageUrl}"]`
        );
        if (item) {
            item.dataset.selected = "true";
        }
    }
}