document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sorteo-form');
    const submitBtn = document.getElementById('submit-btn');
    const btnText = document.querySelector('.btn-text');
    const loader = document.querySelector('.loader');
    const puntoVentaSelect = document.getElementById('id_punto_venta');
    const valorCompraInput = document.getElementById('valor_compra');
    const valorMinimoInfo = document.getElementById('valor-minimo-info');
    const popup = document.getElementById('success-popup');
    const closePopupBtn = document.querySelector('.popup-close');

    // Validador de solo números
    const numericInputs = document.querySelectorAll('input[inputmode="numeric"]');
    numericInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // Mostrar valor mínimo al seleccionar punto de venta
    puntoVentaSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const minimo = selectedOption.getAttribute('data-minimo');
        if (minimo && minimo > 0) {
            valorMinimoInfo.textContent = `Compra mínima: $${parseFloat(minimo).toLocaleString('es-CO')}`;
            valorMinimoInfo.style.display = 'block';
        } else {
            valorMinimoInfo.style.display = 'none';
        }
    });

    // Envío del formulario con AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validar monto mínimo en el cliente
        const selectedOption = puntoVentaSelect.options[puntoVentaSelect.selectedIndex];
        const minimo = parseFloat(selectedOption.getAttribute('data-minimo'));
        const valorCompra = parseFloat(valorCompraInput.value);

        if (minimo && valorCompra < minimo) {
            alert(`El valor de la compra ($${valorCompra.toLocaleString('es-CO')}) es menor al mínimo requerido de $${minimo.toLocaleString('es-CO')}.`);
            return;
        }

        // Mostrar loader y deshabilitar botón
        btnText.style.display = 'none';
        loader.style.display = 'block';
        submitBtn.disabled = true;

        grecaptcha.ready(function() {
            grecaptcha.execute(recaptchaSiteKey, { action: 'submit' }).then(function(token) {
                document.getElementById('recaptcha_response').value = token;
                
                const formData = new FormData(form);
                
                fetch('submit.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccessPopup(data);
                        form.reset();
                        valorMinimoInfo.style.display = 'none';
                    } else {
                        alert('Error:\n' + data.message.replace(/<br>/g, '\n'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error inesperado. Por favor, intenta de nuevo.');
                })
                .finally(() => {
                    // Ocultar loader y habilitar botón
                    btnText.style.display = 'inline';
                    loader.style.display = 'none';
                    submitBtn.disabled = false;
                });
            });
        });
    });
    
    // Función para mostrar el popup
    function showSuccessPopup(data) {
        document.getElementById('popup-nombre').textContent = data.nombre;
        document.getElementById('popup-cedula').textContent = data.cedula;
        document.getElementById('popup-factura').textContent = data.factura_info;
        document.getElementById('popup-oportunidades').textContent = data.oportunidades;
        popup.classList.add('show');
    }

    // Cerrar popup
    closePopupBtn.addEventListener('click', () => {
        popup.classList.remove('show');
    });
    popup.addEventListener('click', (e) => {
        if (e.target === popup) {
            popup.classList.remove('show');
        }
    });
});