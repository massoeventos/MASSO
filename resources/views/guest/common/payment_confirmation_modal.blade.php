<div class="modal fade align-items-center" id="paymentConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="payment-confirmation-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 id="payment-confirmation-title" class="modal-title mb-0">Confirmar pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Revisa los datos antes de continuar.</p>
                <p class="mb-2"><strong>Curso:</strong> <span id="confirmation-course-name">—</span></p>
                <p class="mb-0"><strong>Monto:</strong> <span id="confirmation-amount">—</span></p>
            </div>
            <div class="modal-footer d-flex flex-column flex-sm-row justify-content-center align-items-stretch">
                <button type="button" class="btn bg-secondary mb-2 mb-sm-0" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn" id="confirmation-modal-confirm">Confirmar y pagar</button>
            </div>
        </div>
    </div>
</div>

<style>
    #paymentConfirmationModal .modal-body p {
        color: initial;
    }
    #paymentConfirmationModal .modal-footer .btn {
        font-size: 12px;
        height: 30px;
        line-height: 30px;
    }
</style>

<script>
    (function () {
        function bindPaymentConfirmationModal(config) {
            var modalId = (config && config.modalId) ? config.modalId : 'paymentConfirmationModal';
            var confirmButtonId = (config && config.confirmButtonId) ? config.confirmButtonId : 'confirmation-modal-confirm';
            var triggerSelector = (config && config.triggerSelector) ? config.triggerSelector : '.payment-trigger';

            var form = config && config.form ? config.form : null;
            var modalElement = document.getElementById(modalId);
            if (!modalElement) return;

            var confirmationModal = window.jQuery ? window.jQuery(modalElement) : null;
            var confirmationCourse = document.getElementById('confirmation-course-name');
            var confirmationAmount = document.getElementById('confirmation-amount');
            var confirmationModalConfirm = document.getElementById(confirmButtonId);
            var confirmationDismissTriggers = modalElement ? modalElement.querySelectorAll('[data-dismiss="modal"]') : [];

            var fallbackBackdrop = null;

            function openConfirmationModal() {
                if (confirmationModal && confirmationModal.modal) {
                    confirmationModal.modal('show');
                } else {
                    modalElement.classList.add('show');
                    modalElement.style.display = 'flex';
                    document.body.classList.add('modal-open');

                    fallbackBackdrop = document.createElement('div');
                    fallbackBackdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(fallbackBackdrop);
                }
            }

            function closeConfirmationModal() {
                if (confirmationModal && confirmationModal.modal) {
                    confirmationModal.modal('hide');
                } else {
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    document.body.classList.remove('modal-open');

                    if (fallbackBackdrop) {
                        fallbackBackdrop.parentNode.removeChild(fallbackBackdrop);
                        fallbackBackdrop = null;
                    }
                }
            }

            function showConfirmation() {
                var courseName = (config && typeof config.getCourseName === 'function') ? config.getCourseName() : '';
                var amountText = (config && typeof config.getAmountText === 'function') ? config.getAmountText() : '';

                confirmationCourse.textContent = courseName && String(courseName).trim() !== '' ? String(courseName).trim() : '—';
                confirmationAmount.textContent = amountText && String(amountText).trim() !== '' ? String(amountText).trim() : '$0';

                openConfirmationModal();
            }

            function validateBeforeOpen() {
                if (config && typeof config.validateBeforeOpen === 'function') {
                    return !!config.validateBeforeOpen();
                }
                return true;
            }

            function validateForm() {
                if (!form) return true;
                if (!form.checkValidity) return true;

                if (!form.checkValidity()) {
                    if (form.reportValidity) {
                        form.reportValidity();
                    }
                    return false;
                }

                return true;
            }

            var triggers = document.querySelectorAll(triggerSelector);
            triggers.forEach(function (button) {
                button.addEventListener('click', function (e) {
                    if (e && e.preventDefault) e.preventDefault();

                    if (config && typeof config.onTriggerClick === 'function') {
                        config.onTriggerClick(button);
                    }

                    if (!validateBeforeOpen()) {
                        return;
                    }

                    if (!validateForm()) {
                        return;
                    }

                    if (config && typeof config.shouldSkipModal === 'function') {
                        if (config.shouldSkipModal(button)) {
                            if (form) {
                                form.submit();
                            }
                            return;
                        }
                    }

                    showConfirmation();
                });
            });

            if (confirmationModalConfirm) {
                confirmationModalConfirm.addEventListener('click', function () {
                    closeConfirmationModal();
                    if (form) {
                        form.submit();
                    }
                });
            }

            confirmationDismissTriggers.forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    if (!(confirmationModal && confirmationModal.modal)) {
                        closeConfirmationModal();
                    }
                });
            });
        }

        window.bindPaymentConfirmationModal = bindPaymentConfirmationModal;
    })();
</script>
