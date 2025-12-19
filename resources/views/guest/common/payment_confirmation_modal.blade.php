@php
    $lang = $lang ?? 'esp';
@endphp

<div class="modal fade align-items-center" id="paymentConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="payment-confirmation-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 id="payment-confirmation-title" class="modal-title mb-0">{{ $lang == 'esp' ? 'Confirmar detalles del pago' : 'Confirm payment details' }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ $lang == 'esp' ? 'Cerrar' : 'Close' }}"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" style="font-size: 12px; letter-spacing: .3px; text-transform: uppercase; color: #6c757d;">{{ $lang == 'esp' ? 'Resumen de pago' : 'Payment summary' }}</div>
                
                <div class="p-3 mb-3" style="background: #f8f9fa; border-radius: 6px;">
                    <div class="mt-2">
                        <div style="font-size: 13px; color: #343a40;"><strong>{{ $lang == 'esp' ? 'Evento' : 'Event' }}</strong></div>
                        <div id="confirmation-course-name" style="font-size: 14px; color: #343a40;">—</div>
                    </div>
                    <div class="mt-3">
                        <div style="font-size: 13px; color: #343a40;"><strong>{{ $lang == 'esp' ? 'Total' : 'Total' }}</strong></div>
                        <div style="display: flex; align-items: baseline; gap: 8px;">
                            <span id="confirmation-amount" style="font-size: 22px; font-weight: 700; color: #111;">—</span>
                            <span id="confirmation-currency" style="font-size: 12px; color: #6c757d;">CLP</span>
                        </div>
                        <div id="confirmation-currency-note" style="display:none; font-size: 12px; color: #6c757d;">{{ $lang == 'esp' ? 'Verifica la moneda antes de transferir.' : 'Double-check the currency before transferring.' }}</div>
                    </div>
                </div>

                <div id="confirmation-transfer-section" style="display: none;">
                    <div style="font-size: 12px; letter-spacing: .3px; text-transform: uppercase; color: #6c757d;">{{ $lang == 'esp' ? 'Método de transferencia' : 'Bank transfer method' }}</div>

                    <ul class="nav nav-tabs mt-2" id="confirmation-transfer-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" role="tab" data-transfer-tab="national">{{ $lang == 'esp' ? 'Nacional' : 'Domestic' }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" role="tab" data-transfer-tab="international">{{ $lang == 'esp' ? 'Internacional' : 'International' }}</a>
                        </li>
                    </ul>

                    <div class="border-left border-right border-bottom p-3" style="border-radius: 0 0 6px 6px;">
                        <div data-transfer-panel="national">
                            <div class="mb-2" style="font-size: 12px; color: #6c757d;">{{ $lang == 'esp' ? 'Transferencia nacional (CLP)' : 'Domestic transfer (CLP)' }}</div>

                            <div><strong>{{ $lang == 'esp' ? 'Banco' : 'Bank' }}</strong>: Banco de Chile</div>
                            <div class="mt-1"><strong>{{ $lang == 'esp' ? 'Cuenta corriente N°' : 'Checking account No.' }}</strong>: 00-015-03095-04</div>
                            <div class="mt-1"><strong>{{ $lang == 'esp' ? 'Titular' : 'Account holder' }}</strong>: Paola Massó Masseventos E.I.R.L.</div>
                            <div class="mt-1"><strong>{{ $lang == 'esp' ? 'RUT' : 'Tax ID (RUT)' }}</strong>: 52.001.885-9</div>
                            <div class="mt-1"><strong>{{ $lang == 'esp' ? 'Email' : 'Email' }}</strong>: contacto@massoeventos.cl</div>
                        </div>

                        <div data-transfer-panel="international" style="display:none;">
                            <div class="mb-2" style="font-size: 12px; color: #6c757d;">{{ $lang == 'esp' ? 'Transferencia internacional (CLP)' : 'International transfer (CLP)' }}</div>

                            <div><strong>{{ $lang == 'esp' ? 'Moneda' : 'Currency' }}</strong>: Pesos chilenos (CLP)</div>
                            <div class="mt-1"><strong>Swift</strong>: BCHICLRM</div>
                            <div class="mt-1"><strong>{{ $lang == 'esp' ? 'Banco' : 'Bank' }}</strong>: Banco de Chile</div>
                            <div class="mt-1"><strong>{{ $lang == 'esp' ? 'N° cuenta' : 'Account No.' }}</strong>: 1503095-04</div>
                            <div class="mt-1"><strong>{{ $lang == 'esp' ? 'Beneficiario' : 'Beneficiary' }}</strong>: Paola Massó Masseventos E.I.R.L.</div>
                            <div class="mt-1"><strong>{{ $lang == 'esp' ? 'Dirección banco' : 'Bank address' }}</strong>: Ahumada 251, Santiago, Chile</div>
                            <div class="mt-1"><strong>{{ $lang == 'esp' ? 'Email' : 'Email' }}</strong>: contacto@massoeventos.cl</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex flex-column flex-sm-row justify-content-center align-items-stretch">
                <button type="button" class="btn btn-outline-secondary bg-secondary mb-2 mb-sm-0" data-dismiss="modal">{{ $lang == 'esp' ? 'Cancelar' : 'Cancel' }}</button>
                <button type="button" class="btn" id="confirmation-modal-confirm">{{ $lang == 'esp' ? 'Confirmar y pagar' : 'Confirm and pay' }}</button>
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
            var confirmationCurrency = document.getElementById('confirmation-currency');
            var confirmationCurrencyNote = document.getElementById('confirmation-currency-note');
            var confirmationModalConfirm = document.getElementById(confirmButtonId);
            var confirmationDismissTriggers = modalElement ? modalElement.querySelectorAll('[data-dismiss="modal"]') : [];

            var transferSection = document.getElementById('confirmation-transfer-section');
            var lastPaymentMode = '';

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

            function setActiveTransferTab(tabName) {
                var tabs = modalElement.querySelectorAll('[data-transfer-tab]');
                var panels = modalElement.querySelectorAll('[data-transfer-panel]');

                tabs.forEach(function (t) {
                    var isActive = t.getAttribute('data-transfer-tab') === tabName;
                    if (isActive) {
                        t.classList.add('active');
                    } else {
                        t.classList.remove('active');
                    }
                });

                panels.forEach(function (p) {
                    var isPanel = p.getAttribute('data-transfer-panel') === tabName;
                    p.style.display = isPanel ? 'block' : 'none';
                });
            }

            function showConfirmation() {
                var courseName = (config && typeof config.getCourseName === 'function') ? config.getCourseName() : '';
                var amountText = (config && typeof config.getAmountText === 'function') ? config.getAmountText() : '';
                var currencyText = (config && typeof config.getCurrencyText === 'function') ? config.getCurrencyText() : 'CLP';
                var isTransfer = String(lastPaymentMode || '').toLowerCase().indexOf('transfer') !== -1;

                confirmationCourse.textContent = courseName && String(courseName).trim() !== '' ? String(courseName).trim() : '—';
                confirmationAmount.textContent = amountText && String(amountText).trim() !== '' ? String(amountText).trim() : '$0';

                if (confirmationCurrency) {
                    confirmationCurrency.textContent = currencyText && String(currencyText).trim() !== '' ? String(currencyText).trim() : 'CLP';
                }

                if (transferSection) {
                    transferSection.style.display = isTransfer ? 'block' : 'none';
                    if (isTransfer) {
                        setActiveTransferTab('national');
                    }
                }

                if (confirmationCurrencyNote) {
                    confirmationCurrencyNote.style.display = isTransfer ? 'block' : 'none';
                }

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

                    lastPaymentMode = button && button.value ? String(button.value) : '';

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

            // Tab switching (delegated)
            modalElement.addEventListener('click', function (e) {
                var tabEl = e.target && e.target.closest ? e.target.closest('[data-transfer-tab]') : null;
                if (tabEl) {
                    e.preventDefault();
                    setActiveTransferTab(tabEl.getAttribute('data-transfer-tab'));
                    return;
                }
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
