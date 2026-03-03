import Swal from 'sweetalert2';
export const validarFormulario = (formulario, excepciones = []) => {
    const elements = formulario.querySelectorAll("input, select, textarea");
    let validarFormulario = []
    elements.forEach(element => {
        if (!element.value.trim() && !excepciones.includes(element.id)) {
            element.classList.add('is-invalid');

            validarFormulario.push(false)
        } else {
            element.classList.remove('is-invalid');
        }
    });

    let noenviar = validarFormulario.includes(false);

    return !noenviar;
}

export const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
})

/**
 * confirmar({ titulo, texto, icono, txtConfirmar, txtCancelar })
 * Helper para mostrar un diálogo de confirmación SweetAlert2.
 * Retorna una Promise<boolean>.
 */
export const confirmar = ({
    titulo = '¿Estás seguro?',
    texto = '',
    icono = 'warning',
    txtConfirmar = 'Sí, continuar',
    txtCancelar = 'Cancelar',
} = {}) => {
    return Swal.fire({
        icon: icono,
        title: titulo,
        text: texto,
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: txtConfirmar,
        cancelButtonText: txtCancelar,
        focusCancel: true,
    }).then(r => r.isConfirmed);
}
