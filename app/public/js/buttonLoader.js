/**
 * This class manages the loading icon when a button is clicked
 */
class ButtonLoader {
    #buttonId;
    #loadingSpan
    #hasIcon
    #icon

    constructor(buttonId, hasIcon = false) {
        this.#buttonId = buttonId;
        this.#hasIcon = hasIcon;
        if (hasIcon) {
            this.#icon = $(this.#buttonId).find('i[class*="fa-solid"]');
            this.#loadingSpan = $(this.#buttonId).find('span[class*="loading-circle"]');
        } else {
            this.#loadingSpan = $(this.#buttonId).find('span[class*="loading-circle"]');
        }
    }

    showLoadingAnimation() {
        this.#loadingSpan.removeClass("d-none");
        if (this.#hasIcon) {
            this.#icon.addClass("d-none");
        }
    }

    hideLoadingAnimation() {
        this.#loadingSpan.addClass("d-none");
        if (this.#hasIcon) {
            this.#icon.removeClass("d-none");
        }
    }

    makeRequest(callback) {
        this.showLoadingAnimation();
        // make request
        callback();
    }
}