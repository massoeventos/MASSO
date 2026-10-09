// RUT chileno: mismo algoritmo (módulo 11) que la regla valid_rut del
// servidor (AppServiceProvider) y que validarRUT() del formulario anterior.

// Deja solo dígitos, K y guion; máximo 11 caracteres (con guion).
export function cleanRutInput(value) {
    return String(value || '').replace(/[^0-9Kk-]/g, '').slice(0, 11);
}

export function isValidRut(value) {
    const rut = String(value || '').replace(/\s|-/g, '');
    if (!/^\d{7,9}[0-9Kk]$/.test(rut)) return false;

    const body = rut.slice(0, -1);
    const dv = rut.slice(-1).toUpperCase();

    let sum = 0;
    let multiplier = 2;
    for (let i = body.length - 1; i >= 0; i--) {
        sum += parseInt(body.charAt(i), 10) * multiplier;
        multiplier = multiplier === 7 ? 2 : multiplier + 1;
    }

    const expected = 11 - (sum % 11);
    const expectedDv = expected === 11 ? '0' : expected === 10 ? 'K' : String(expected);
    return dv === expectedDv;
}
