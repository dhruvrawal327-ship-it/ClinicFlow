
(function () {
    "use strict";

    let pendingForm = null;
    let pendingSubmitter = null;

    function createModal() {
        if (document.getElementById("cfConfirmModal")) return;

        const modal = document.createElement("div");
        modal.id = "cfConfirmModal";
        modal.className = "cf-modal";
        modal.setAttribute("aria-hidden", "true");

        modal.innerHTML = `
            <div class="cf-modal-backdrop"></div>

            <div class="cf-modal-dialog"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="cfModalTitle"
                 aria-describedby="cfModalMessage">

                <div class="cf-modal-icon" id="cfModalIcon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div class="cf-modal-content">
                    <h5 id="cfModalTitle">Confirm Action</h5>
                    <p id="cfModalMessage"></p>
                </div>

                <div class="cf-modal-actions">
                    <button type="button"
                            class="cf-modal-cancel"
                            id="cfModalCancel">
                        Cancel
                    </button>

                    <button type="button"
                            class="cf-modal-confirm"
                            id="cfModalConfirm">
                        Confirm
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.querySelector(".cf-modal-backdrop")
            .addEventListener("click", closeModal);

        document.getElementById("cfModalCancel")
            .addEventListener("click", closeModal);

        document.getElementById("cfModalConfirm")
            .addEventListener("click", confirmAction);

        document.addEventListener("keydown", function (event) {
            if (
                event.key === "Escape" &&
                modal.classList.contains("show")
            ) {
                closeModal();
            }
        });
    }

    function openModal(form, submitter) {
        createModal();

        pendingForm = form;
        pendingSubmitter = submitter || null;

        const modal = document.getElementById("cfConfirmModal");

        const title = form.dataset.confirmTitle || "Confirm Action";
        const message = form.dataset.confirm || "Are you sure?";
        const action = form.dataset.confirmAction || "Confirm";
        const tone = form.dataset.confirmTone || "danger";

        document.getElementById("cfModalTitle").textContent = title;
        document.getElementById("cfModalMessage").textContent = message;
        document.getElementById("cfModalConfirm").textContent = action;

        const icon = document.getElementById("cfModalIcon");
        const confirmButton = document.getElementById("cfModalConfirm");

        icon.className = "cf-modal-icon cf-modal-icon--" + tone;
        confirmButton.className = "cf-modal-confirm cf-modal-confirm--" + tone;

        icon.innerHTML = tone === "success"
            ? '<i class="bi bi-check-circle"></i>'
            : '<i class="bi bi-exclamation-triangle"></i>';

        modal.classList.add("show");
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("cf-modal-open");

        document.getElementById("cfModalCancel").focus();
    }

    function closeModal() {
        const modal = document.getElementById("cfConfirmModal");

        if (!modal) return;

        modal.classList.remove("show");
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("cf-modal-open");

        if (pendingForm) {
            pendingForm = null;
            pendingSubmitter = null;
        }
    }

    function confirmAction() {
        if (!pendingForm) return;

        const form = pendingForm;
        const submitter = pendingSubmitter;

        pendingForm = null;
        pendingSubmitter = null;

        closeModal();

        form.dataset.confirmBypass = "true";

        if (submitter && submitter.form === form) {
            form.requestSubmit(submitter);
        } else {
            form.requestSubmit();
        }
    }

    // Intercept confirmation-enabled forms throughout ClinicFlow.
    document.addEventListener("submit", function (event) {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) return;

        if (form.dataset.confirmBypass === "true") {
            delete form.dataset.confirmBypass;
            return;
        }

        if (!form.dataset.confirm) return;

        event.preventDefault();

        openModal(form, event.submitter);
    }, true);

})();
