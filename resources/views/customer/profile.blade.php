@extends('layouts.customer-panel')

@section('content')

<style type="text/css">
    .page-titles h4.text-themecolor {
        font-size: 1.15rem;
    }
    .card-title {
        font-size: 1.05rem;
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
    /* Botón "amigable" reutilizable -- mismo estilo que el modal de
       identificación del checkout, se va a ir usando en más pantallas. */
    .btn-brand {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-align: center;
        border-radius: 10px;
        padding: 10px 22px;
        font-weight: 600;
        text-transform: none !important;
        font-size: 14px;
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
    .otp-input-group {
        display: flex;
        gap: 8px;
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
</style>

<div class="row page-titles">
    <div class="col-md-5 align-self-center">
        <h4 class="text-themecolor">Mi Perfil</h4>
    </div>
    <div class="col-md-7 align-self-center text-right">
        <div class="d-flex justify-content-end align-items-center">
            <ol class="breadcrumb d-none d-lg-flex">
                <li class="breadcrumb-item"><a href="{{ route('customer.account') }}">Mi Cuenta</a></li>
                <li class="breadcrumb-item active">Mi Perfil</li>
            </ol>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Datos personales</h4>
                <h6 class="card-subtitle mb-3">Estos datos se guardan una sola vez y se autocompletan en tus próximas compras.</h6>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Nombre</label>
                        <input type="text" id="profile-name-input" class="form-control" autocomplete="off" value="{{ $customer->name }}" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Apellido</label>
                        <input type="text" id="profile-lastname-input" class="form-control" autocomplete="off" value="{{ $customer->lastname }}" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Género / Sexo</label>
                        <select id="profile-gender-select" class="form-control" required>
                            <option value="">Seleccione una opción</option>
                            <option value="female" {{ $customer->gender === 'female' ? 'selected' : '' }}>Femenino</option>
                            <option value="male" {{ $customer->gender === 'male' ? 'selected' : '' }}>Masculino</option>
                            <option value="non_binary" {{ $customer->gender === 'non_binary' ? 'selected' : '' }}>No binario</option>
                            <option value="other" {{ $customer->gender === 'other' ? 'selected' : '' }}>Otro / Prefiere no responder</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Nacionalidad</label>
                        <select id="profile-nationality-select" class="form-control" required>
                            <option value="">Seleccione un país</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}" {{ (string) $customer->nationality_country_id === (string) $id ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group" id="profile-rut-group" style="display:none;">
                        <label>RUT</label>
                        <input type="text" id="profile-rut-input" class="form-control" autocomplete="off" value="{{ $customer->rut }}">
                    </div>
                    <div class="col-md-6 form-group" id="profile-passport-group">
                        <label>DNI / Pasaporte</label>
                        <input type="text" id="profile-passport-input" class="form-control" autocomplete="off" value="{{ $customer->passport }}">
                    </div>

                    <div class="col-md-12 form-group">
                        <label>País de residencia</label>
                        <select id="profile-country-select" class="form-control">
                            <option value="">Seleccione un país</option>
                            @foreach($countries as $id => $country)
                                <option value="{{ $id }}" {{ (string) $customer->country_id === (string) $id ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 form-group" id="profile-region-container">
                        <label>Región</label>
                        <select id="profile-region-select" class="form-control" disabled data-initial="{{ $customer->city && $customer->city->region_id ? $customer->city->region_id : '' }}">
                            <option value="">Seleccione una región</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group" id="profile-city-select-container">
                        <label>Ciudad</label>
                        <select id="profile-city-select" class="form-control" disabled data-initial="{{ $customer->city_id }}">
                            <option value="">Seleccione una ciudad</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group d-none" id="profile-city-input-container">
                        <label>Ciudad</label>
                        <input type="text" id="profile-custom-city-input" class="form-control" placeholder="Ingrese su ciudad" maxlength="100" autocomplete="off" value="{{ $customer->custom_city }}">
                    </div>
                </div>

                <button type="button" class="btn btn-brand" id="profile-data-btn">Guardar datos</button>
                <small id="profile-data-feedback" class="d-block mt-2"></small>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Contraseña</h4>
                <h6 class="card-subtitle mb-3">{{ $customer->hasPassword() ? 'Ya tienes una contraseña configurada. Puedes cambiarla acá.' : 'No es obligatorio, pero si configuras una contraseña podrás ingresar más rápido la próxima vez sin esperar un código.' }}</h6>

                <div class="form-group">
                    <label>Nueva contraseña</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="account-password-input" class="form-control" autocomplete="new-password">
                        <i class="fa fa-eye password-toggle-icon" data-target="account-password-input"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirmar contraseña</label>
                    <div class="password-field-wrapper">
                        <input type="password" id="account-password-confirm-input" class="form-control" autocomplete="new-password">
                        <i class="fa fa-eye password-toggle-icon" data-target="account-password-confirm-input"></i>
                    </div>
                </div>
                <button type="button" class="btn btn-brand" id="account-password-btn">Guardar contraseña</button>
                <small id="account-password-feedback" class="d-block mt-2"></small>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Teléfono para recuperación por SMS</h4>

                @if($customer->phone)
                    <p class="mb-2">Teléfono confirmado: <strong>{{ $customer->phone }}</strong></p>
                    <a href="#" id="account-change-phone-link" class="customer-link small">Cambiar número</a>
                @else
                    <h6 class="card-subtitle mb-3">Opcional. Solo se usa como respaldo si no te llega el código de acceso por correo.</h6>
                @endif

                <div id="account-phone-form" class="{{ $customer->phone ? 'd-none' : '' }} mt-2">
                    <div class="form-group">
                        <label>Teléfono (con código de país)</label>
                        <input type="text" id="account-phone-input" class="form-control" placeholder="+56912345678" autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-brand" id="account-phone-send-btn">Enviar código</button>
                    <small id="account-phone-feedback" class="d-block mt-2"></small>

                    <div id="account-phone-code-wrapper" class="d-none mt-3">
                        <div class="form-group">
                            <label>Código recibido por SMS</label>
                            <div class="otp-input-group" id="phone-otp-group">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                                <input type="text" class="otp-box" inputmode="numeric" maxlength="1" autocomplete="off">
                            </div>
                            <input type="hidden" id="account-phone-code-input">
                        </div>
                        <button type="button" class="btn btn-brand" id="account-phone-verify-btn">Confirmar teléfono</button>
                        <small id="account-phone-verify-feedback" class="d-block mt-2"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('footer')
<script>
document.addEventListener('DOMContentLoaded', function () {
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

    document.querySelectorAll('.password-toggle-icon').forEach(function (icon) {
        icon.addEventListener('click', function () {
            const input = document.getElementById(icon.dataset.target);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('fa-eye', !isHidden);
            icon.classList.toggle('fa-eye-slash', isHidden);
        });
    });

    // Datos personales
    const CHILE_ID = '{{ $chile->id }}';

    const nameInput = document.getElementById('profile-name-input');
    const lastnameInput = document.getElementById('profile-lastname-input');
    const genderSelect = document.getElementById('profile-gender-select');
    const nationalitySelect = document.getElementById('profile-nationality-select');
    const rutGroup = document.getElementById('profile-rut-group');
    const rutInput = document.getElementById('profile-rut-input');
    const passportGroup = document.getElementById('profile-passport-group');
    const passportInput = document.getElementById('profile-passport-input');
    const countrySelect = document.getElementById('profile-country-select');
    const regionSelect = document.getElementById('profile-region-select');
    const citySelect = document.getElementById('profile-city-select');
    const regionContainer = document.getElementById('profile-region-container');
    const citySelectContainer = document.getElementById('profile-city-select-container');
    const cityInputContainer = document.getElementById('profile-city-input-container');
    const customCityInput = document.getElementById('profile-custom-city-input');
    const profileDataBtn = document.getElementById('profile-data-btn');
    const profileDataFeedback = document.getElementById('profile-data-feedback');

    function removeInvalidRutCharacters(input) {
        input.value = input.value.replace(/[^0-9Kk-]/g, '');
        if (input.value.length > 11) {
            input.value = input.value.slice(0, 11);
        }
    }

    function validarRUT(rut) {
        rut = rut.replace(/\s|-/g, '');
        if (!/^\d{7,9}[0-9Kk]$/.test(rut)) {
            return false;
        }
        const cuerpo = rut.slice(0, -1);
        const dv = rut.slice(-1).toUpperCase();
        let suma = 0;
        let multiplo = 2;
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo.charAt(i), 10) * multiplo;
            multiplo = multiplo === 7 ? 2 : multiplo + 1;
        }
        let dvEsperado = 11 - (suma % 11);
        dvEsperado = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString();
        return dv === dvEsperado;
    }

    if (rutInput) {
        rutInput.addEventListener('input', function () {
            removeInvalidRutCharacters(rutInput);
            const isValid = rutInput.value ? validarRUT(rutInput.value) : true;
            if (isValid) {
                rutInput.classList.remove('is-invalid');
                rutInput.setCustomValidity('');
            } else {
                rutInput.classList.add('is-invalid');
                rutInput.setCustomValidity('El RUT es inválido');
            }
        });
    }

    function toggleRutPassportFields(nationalityCountryId) {
        const isChile = nationalityCountryId == CHILE_ID;

        if (isChile) {
            rutGroup.style.display = 'block';
            passportGroup.style.display = 'none';
        } else {
            rutGroup.style.display = 'none';
            passportGroup.style.display = 'block';
        }
    }

    if (nationalitySelect) {
        if (nationalitySelect.value) {
            toggleRutPassportFields(nationalitySelect.value);
        }
        nationalitySelect.addEventListener('change', function () {
            toggleRutPassportFields(this.value);
        });
    }

    let initialRegionId = (regionSelect && regionSelect.dataset) ? (regionSelect.dataset.initial || '') : '';
    let initialCityId = (citySelect && citySelect.dataset) ? (citySelect.dataset.initial || '') : '';

    function resetLocationSelect(select, placeholder, isLoading) {
        select.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = isLoading ? 'Cargando...' : placeholder;
        select.appendChild(option);
        select.disabled = true;
    }

    function toggleChileMode(isChile) {
        if (isChile) {
            regionContainer.classList.remove('d-none');
            citySelectContainer.classList.remove('d-none');
            cityInputContainer.classList.add('d-none');
            // Bug corregido: limpiar el input libre de ciudad oculto para
            // que no se envíe mezclado con country_id=Chile.
            if (customCityInput) customCityInput.value = '';
        } else {
            regionContainer.classList.add('d-none');
            citySelectContainer.classList.add('d-none');
            cityInputContainer.classList.remove('d-none');
            regionSelect.value = '';
            citySelect.value = '';
        }
    }

    if (countrySelect) {
        countrySelect.addEventListener('change', function () {
            const countryId = this.value;
            const isChile = countryId == CHILE_ID;
            toggleChileMode(isChile);

            resetLocationSelect(regionSelect, 'Seleccione una región');
            resetLocationSelect(citySelect, 'Seleccione una ciudad');

            if (!countryId) return;

            if (isChile) {
                resetLocationSelect(regionSelect, '', true);
                fetch(`/get-regions/${countryId}?lang=esp`)
                    .then(response => response.json())
                    .then(data => {
                        resetLocationSelect(regionSelect, 'Seleccione una región');
                        for (const id in data) {
                            const option = document.createElement('option');
                            option.value = id;
                            option.textContent = data[id];
                            regionSelect.appendChild(option);
                        }
                        regionSelect.disabled = false;

                        if (initialRegionId) {
                            regionSelect.value = initialRegionId;
                            initialRegionId = '';
                            regionSelect.dispatchEvent(new Event('change'));
                        }
                    })
                    .catch(() => {});
            }
        });

        regionSelect.addEventListener('change', function () {
            const regionId = this.value;
            resetLocationSelect(citySelect, '', true);

            if (!regionId) return;

            fetch(`/get-cities/${regionId}?lang=esp`)
                .then(response => response.json())
                .then(data => {
                    resetLocationSelect(citySelect, 'Seleccione una ciudad');
                    for (const id in data) {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = data[id];
                        citySelect.appendChild(option);
                    }
                    citySelect.disabled = false;

                    if (initialCityId) {
                        citySelect.value = initialCityId;
                        initialCityId = '';
                    }
                })
                .catch(() => {});
        });

        if (countrySelect.value) {
            countrySelect.dispatchEvent(new Event('change'));
        }
    }

    if (profileDataBtn) {
        submitOnEnter(nameInput, profileDataBtn);
        submitOnEnter(lastnameInput, profileDataBtn);
        clearFeedbackOnEdit(nameInput, profileDataFeedback);
        clearFeedbackOnEdit(lastnameInput, profileDataFeedback);

        profileDataBtn.addEventListener('click', function () {
            const isChile = nationalitySelect.value == CHILE_ID;

            profileDataBtn.disabled = true;
            fetch('{{ route('customer.profile.update') }}', {
                method: 'POST',
                headers: csrfHeaders(),
                body: JSON.stringify({
                    name: nameInput.value,
                    lastname: lastnameInput.value,
                    gender: genderSelect.value,
                    nationality_country_id: nationalitySelect.value,
                    rut: isChile ? rutInput.value : '',
                    passport: !isChile ? passportInput.value : '',
                    country_id: countrySelect.value,
                    city_id: citySelect.value,
                    custom_city: customCityInput.value,
                })
            })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ body }) => {
                profileDataFeedback.textContent = body.message || '';
                profileDataFeedback.className = 'd-block mt-2 ' + (body.success ? 'text-success' : 'text-danger');
            })
            .catch(() => {
                profileDataFeedback.textContent = 'Ocurrió un error, intenta nuevamente.';
                profileDataFeedback.className = 'd-block mt-2 text-danger';
            })
            .finally(() => { profileDataBtn.disabled = false; });
        });
    }

    // Contraseña
    const passwordInput = document.getElementById('account-password-input');
    const passwordConfirmInput = document.getElementById('account-password-confirm-input');
    const passwordBtn = document.getElementById('account-password-btn');
    const passwordFeedback = document.getElementById('account-password-feedback');

    submitOnEnter(passwordInput, passwordBtn);
    submitOnEnter(passwordConfirmInput, passwordBtn);
    clearFeedbackOnEdit(passwordInput, passwordFeedback);
    clearFeedbackOnEdit(passwordConfirmInput, passwordFeedback);

    passwordBtn.addEventListener('click', function () {
        const password = passwordInput.value;
        const confirmation = passwordConfirmInput.value;

        if (!password) return;

        passwordBtn.disabled = true;
        fetch('{{ route('customer.password.update') }}', {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ password: password, password_confirmation: confirmation })
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ body }) => {
            passwordFeedback.textContent = body.message || '';
            passwordFeedback.className = 'd-block mt-2 ' + (body.success ? 'text-success' : 'text-danger');
            if (body.success) {
                passwordInput.value = '';
                passwordConfirmInput.value = '';
            }
        })
        .catch(() => {
            passwordFeedback.textContent = 'Ocurrió un error, intenta nuevamente.';
            passwordFeedback.className = 'd-block mt-2 text-danger';
        })
        .finally(() => { passwordBtn.disabled = false; });
    });

    // Teléfono
    const phoneForm = document.getElementById('account-phone-form');
    const phoneInput = document.getElementById('account-phone-input');
    const phoneSendBtn = document.getElementById('account-phone-send-btn');
    const phoneFeedback = document.getElementById('account-phone-feedback');
    const phoneCodeWrapper = document.getElementById('account-phone-code-wrapper');
    const phoneCodeInput = document.getElementById('account-phone-code-input');
    const phoneVerifyBtn = document.getElementById('account-phone-verify-btn');
    const phoneVerifyFeedback = document.getElementById('account-phone-verify-feedback');
    const changePhoneLink = document.getElementById('account-change-phone-link');

    const phoneOtp = initOtpBoxes(document.getElementById('phone-otp-group'), phoneCodeInput);

    submitOnEnter(phoneInput, phoneSendBtn);
    submitOnEnter(phoneCodeInput, phoneVerifyBtn);
    clearFeedbackOnEdit(phoneInput, phoneFeedback);
    clearFeedbackOnEdit(phoneCodeInput, phoneVerifyFeedback);

    if (changePhoneLink) {
        changePhoneLink.addEventListener('click', function (e) {
            e.preventDefault();
            phoneForm.classList.remove('d-none');
        });
    }

    if (phoneSendBtn) {
        phoneSendBtn.addEventListener('click', function () {
            const phone = phoneInput.value.trim();
            if (!phone) return;

            phoneSendBtn.disabled = true;
            fetch('{{ route('customer.phone.requestCode') }}', {
                method: 'POST',
                headers: csrfHeaders(),
                body: JSON.stringify({ phone: phone })
            })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ body }) => {
                phoneFeedback.textContent = body.message || '';
                phoneFeedback.className = 'd-block mt-2 ' + (body.sent ? 'text-success' : 'text-danger');
                if (body.sent) {
                    phoneCodeWrapper.classList.remove('d-none');
                    setTimeout(function () { phoneOtp.focusFirst(); }, 50);
                }
            })
            .catch(() => {
                phoneFeedback.textContent = 'Ocurrió un error, intenta nuevamente.';
                phoneFeedback.className = 'd-block mt-2 text-danger';
            })
            .finally(() => { phoneSendBtn.disabled = false; });
        });
    }

    if (phoneVerifyBtn) {
        phoneVerifyBtn.addEventListener('click', function () {
            const code = phoneCodeInput.value.trim();
            if (!code) return;

            phoneVerifyBtn.disabled = true;
            fetch('{{ route('customer.phone.verify') }}', {
                method: 'POST',
                headers: csrfHeaders(),
                body: JSON.stringify({ code: code })
            })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ body }) => {
                phoneVerifyFeedback.textContent = body.message || '';
                phoneVerifyFeedback.className = 'd-block mt-2 ' + (body.success ? 'text-success' : 'text-danger');
                if (body.success) {
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(() => {
                phoneVerifyFeedback.textContent = 'Ocurrió un error, intenta nuevamente.';
                phoneVerifyFeedback.className = 'd-block mt-2 text-danger';
            })
            .finally(() => { phoneVerifyBtn.disabled = false; });
        });
    }
});
</script>
@endsection
