@extends('layouts.public')

@section('content')

<style type="text/css">
    html, body { height: 100%; }
    body header, body footer{ display: none; }
    body {
        background: #f9fafc;
    }
    .customer-login-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        padding-top: 30px;
    }
    .customer-login-page > .container {
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .customer-login-form-row {
        flex: 1;
        display: flex;
        align-items: center;
    }
    .customer-login-card h3 {
        font-size: 1.3rem;
    }
    .customer-link {
        color: #4a6fa5;
        text-decoration: underline;
    }
    .customer-link:hover {
        color: #33507a;
    }
    .password-field-wrapper {
        position: relative;
    }
    .password-field-wrapper .form-control {
        padding-right: 40px;
    }
    .password-toggle-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #9a9a9a;
        font-size: 15px;
    }
    .password-toggle-icon:hover {
        color: #555;
    }
    .otp-input-group {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-bottom: 5px;
    }
    .otp-box {
        width: 44px;
        height: 52px;
        text-align: center;
        font-size: 20px;
        font-weight: 600;
        border: 1px solid #e0e3ec;
        background: #f4f6fb;
        border-radius: 8px;
        color: #2a2a2a;
    }
    .otp-box:focus {
        outline: none;
        border-color: #4a6fa5;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(74,111,165,.15);
    }
    .customer-login-card {
        background: #fff;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 0 15px rgba(0,0,0,.05);
    }
    /* Botón "amigable" reutilizable -- mismo estilo que el modal de
       identificación del checkout, se va a ir usando en más pantallas. */
    .btn-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        text-align: center;
        border-radius: 10px;
        padding: 12px;
        font-weight: 600;
        text-transform: none !important;
        font-size: 15px;
        background-color: #e7015e;
        border: none;
        color: #fff;
        letter-spacing: normal;
        transition: background-color .15s ease, transform .1s ease;
    }
    .btn-brand:hover {
        background-color: #c50150;
        color: #fff;
    }
    .btn-brand:active {
        transform: scale(0.98);
    }
    .btn-brand:disabled {
        opacity: .65;
        cursor: not-allowed;
    }
</style>

<section class="ts-speakers-standard ts-speakers speaker-classic section-bg customer-login-page">
    <div class="container">
        <div class="row">
            <div class="col-lg-9 mx-auto">
                <a class="navbar-brand" href="/">
                    <img src="/images/logo.jpg" alt="">
                </a><br>
            </div>
        </div>
        <div class="row customer-login-form-row">
            <div class="col-lg-5 mx-auto">
                <div class="customer-login-card">
                    <h3 class="mb-4 text-center">Ingresar a mi cuenta</h3>

                    <div id="login-step-email">
                        <div class="form-group">
                            <label>Correo electrónico</label>
                            <input type="email" id="login-email-input" class="form-control" autocomplete="off" required>
                        </div>
                        <button type="button" class="btn btn-brand" id="login-continue-btn">Continuar</button>
                        <small id="login-email-feedback" class="d-block mt-2"></small>
                    </div>

                    <div id="login-step-password" style="display:none;">
                        <p>Ingresa tu contraseña:</p>
                        <div class="form-group">
                            <div class="password-field-wrapper">
                                <input type="password" id="login-password-input" class="form-control" autocomplete="off">
                                <i class="fa fa-eye password-toggle-icon" data-target="login-password-input"></i>
                            </div>
                        </div>
                        <button type="button" class="btn btn-brand" id="login-password-btn">Ingresar</button>
                        <small id="login-password-feedback" class="d-block mt-2"></small>
                        <a href="#" id="login-prefer-code" class="customer-link small d-block mt-2">Prefiero recibir un código por correo</a>
                        <a href="#" class="login-change-email customer-link small d-block mt-1">Usar otro correo</a>
                    </div>

                    <div id="login-step-code" style="display:none;">
                        <p>Te enviamos un código de 6 dígitos a tu correo.</p>
                        <div class="form-group">
                            <div class="otp-input-group" id="login-otp-group">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                            </div>
                            <input type="hidden" id="login-code-input">
                        </div>
                        <button type="button" class="btn btn-brand" id="login-code-btn">Verificar</button>
                        <small id="login-code-feedback" class="d-block mt-2"></small>
                        <a href="#" id="login-resend-email" class="customer-link small d-block mt-2">Reenviar código por correo</a>
                        <a href="#" id="login-resend-sms" class="customer-link small d-block d-none">¿No recibiste el correo? Enviar por SMS</a>
                        <a href="#" class="login-change-email customer-link small d-block mt-1">Usar otro correo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('footer')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stepEmail = document.getElementById('login-step-email');
    const stepPassword = document.getElementById('login-step-password');
    const stepCode = document.getElementById('login-step-code');

    const emailInput = document.getElementById('login-email-input');
    const emailFeedback = document.getElementById('login-email-feedback');
    const continueBtn = document.getElementById('login-continue-btn');

    const passwordInput = document.getElementById('login-password-input');
    const passwordFeedback = document.getElementById('login-password-feedback');
    const passwordBtn = document.getElementById('login-password-btn');
    const preferCodeLink = document.getElementById('login-prefer-code');

    const codeInput = document.getElementById('login-code-input');
    const codeFeedback = document.getElementById('login-code-feedback');
    const codeBtn = document.getElementById('login-code-btn');
    const resendEmailLink = document.getElementById('login-resend-email');
    const resendSmsLink = document.getElementById('login-resend-sms');

    function initOtpBoxes(container, hiddenInput) {
        const boxes = Array.from(container.querySelectorAll('.otp-box'));

        function sync() {
            hiddenInput.value = boxes.map(function (b) { return b.value; }).join('');
            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        }

        boxes.forEach(function (box, index) {
            box.addEventListener('input', function () {
                box.value = box.value.replace(/[^0-9]/g, '').slice(-1);
                if (box.value && index < boxes.length - 1) {
                    boxes[index + 1].focus();
                }
                sync();
            });

            box.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !box.value && index > 0) {
                    boxes[index - 1].focus();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    hiddenInput.dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', bubbles: true }));
                }
            });

            box.addEventListener('paste', function (e) {
                const text = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                if (!text) return;
                e.preventDefault();
                text.slice(0, boxes.length).split('').forEach(function (digit, i) {
                    if (boxes[i]) boxes[i].value = digit;
                });
                const lastIndex = Math.min(text.length, boxes.length) - 1;
                if (boxes[lastIndex]) boxes[lastIndex].focus();
                sync();
            });
        });

        return {
            clear: function () {
                boxes.forEach(function (b) { b.value = ''; });
                hiddenInput.value = '';
            },
            focusFirst: function () {
                boxes[0].focus();
            }
        };
    }

    const loginOtp = initOtpBoxes(document.getElementById('login-otp-group'), codeInput);

    function showStep(step) {
        stepEmail.style.display = step === 'email' ? 'block' : 'none';
        stepPassword.style.display = step === 'password' ? 'block' : 'none';
        stepCode.style.display = step === 'code' ? 'block' : 'none';

        if (step === 'code') {
            setTimeout(function () { loginOtp.focusFirst(); }, 50);
        }
    }

    function csrfHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        };
    }

    function submitOnEnter(input, button) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                button.click();
            }
        });
    }

    function clearFeedbackOnEdit(input, feedbackEl) {
        input.addEventListener('input', function () {
            feedbackEl.textContent = '';
            feedbackEl.className = feedbackEl.className.replace(/\btext-(success|danger)\b/g, '').trim();
        });
    }

    document.querySelectorAll('.password-toggle-icon').forEach(function (icon) {
        icon.addEventListener('click', function () {
            const input = document.getElementById(icon.dataset.target);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isHidden);
            icon.classList.toggle('fa-eye-slash', isHidden);
        });
    });

    submitOnEnter(emailInput, continueBtn);
    submitOnEnter(passwordInput, passwordBtn);
    submitOnEnter(codeInput, codeBtn);

    clearFeedbackOnEdit(emailInput, emailFeedback);
    clearFeedbackOnEdit(passwordInput, passwordFeedback);
    clearFeedbackOnEdit(codeInput, codeFeedback);

    continueBtn.addEventListener('click', function () {
        const email = emailInput.value.trim();
        if (!email) return;

        emailFeedback.textContent = '';
        continueBtn.disabled = true;

        fetch('{{ route('customer.login.identify') }}', {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ email: email })
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ body }) => {
            if (body.status === 'has_password') {
                showStep('password');
            } else if (body.status === 'code_sent') {
                if (body.can_sms) resendSmsLink.classList.remove('d-none');
                showStep('code');
            } else {
                emailFeedback.textContent = body.message || 'No encontramos una cuenta con ese correo.';
                emailFeedback.className = 'd-block mt-2 text-danger';
            }
        })
        .catch(() => {
            emailFeedback.textContent = 'Ocurrió un error, intenta nuevamente.';
            emailFeedback.className = 'd-block mt-2 text-danger';
        })
        .finally(() => { continueBtn.disabled = false; });
    });

    passwordBtn.addEventListener('click', function () {
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        if (!password) return;

        passwordBtn.disabled = true;
        fetch('{{ route('customer.login.password') }}', {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ email: email, password: password })
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ body }) => {
            if (body.success) {
                window.location.href = body.redirect;
            } else {
                passwordFeedback.textContent = body.message || 'Contraseña incorrecta.';
                passwordFeedback.className = 'd-block mt-2 text-danger';
            }
        })
        .catch(() => {
            passwordFeedback.textContent = 'Ocurrió un error, intenta nuevamente.';
            passwordFeedback.className = 'd-block mt-2 text-danger';
        })
        .finally(() => { passwordBtn.disabled = false; });
    });

    function sendCode(channel, linkEl) {
        const email = emailInput.value.trim();
        const originalText = linkEl ? linkEl.textContent : '';
        if (linkEl) linkEl.textContent = 'Enviando...';

        fetch('{{ route('customer.login.sendCode') }}', {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ email: email, channel: channel })
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ body }) => {
            codeFeedback.textContent = body.message || '';
            codeFeedback.className = 'd-block mt-2 ' + (body.sent ? 'text-success' : 'text-danger');
            if (body.sent) showStep('code');
        })
        .catch(() => {
            codeFeedback.textContent = 'No pudimos enviar el código, intenta nuevamente.';
            codeFeedback.className = 'd-block mt-2 text-danger';
        })
        .finally(() => { if (linkEl) linkEl.textContent = originalText; });
    }

    preferCodeLink.addEventListener('click', function (e) {
        e.preventDefault();
        sendCode('email', null);
    });

    resendEmailLink.addEventListener('click', function (e) {
        e.preventDefault();
        sendCode('email', resendEmailLink);
    });

    resendSmsLink.addEventListener('click', function (e) {
        e.preventDefault();
        sendCode('sms', resendSmsLink);
    });

    codeBtn.addEventListener('click', function () {
        const email = emailInput.value.trim();
        const code = codeInput.value.trim();
        if (!code) return;

        codeBtn.disabled = true;
        fetch('{{ route('customer.login.verifyCode') }}', {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ email: email, code: code })
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ body }) => {
            if (body.success) {
                window.location.href = body.redirect;
            } else {
                codeFeedback.textContent = body.message || 'Código inválido.';
                codeFeedback.className = 'd-block mt-2 text-danger';
            }
        })
        .catch(() => {
            codeFeedback.textContent = 'Ocurrió un error, intenta nuevamente.';
            codeFeedback.className = 'd-block mt-2 text-danger';
        })
        .finally(() => { codeBtn.disabled = false; });
    });

    document.querySelectorAll('.login-change-email').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            showStep('email');
        });
    });
});
</script>
@endsection
