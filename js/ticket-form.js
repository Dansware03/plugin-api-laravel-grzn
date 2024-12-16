jQuery(document).ready(function($) {
    const $ticketNumber = $('#ticketNumber');
    const $checkoutModal = $('#checkoutModal');
    const $searchButton = $('#searchButton');
    const $randomButton = $('#randomButton');
    const $clearButton = $('#clearButton');
    const $selectedTicketsContainer = $('#selectedTickets');
    const $selectedNumbersContainer = $('.selected-numbers');
    const $checkoutButton = $('#checkoutButton');
    const $randomModal = $('#randomModal');
    const $randomQuantity = $('#randomQuantity');
    const $confirmRandom = $('#confirmRandom');
    const $checkoutTickets = $('#checkoutTickets');
    const $totalToPay = $('#totalToPay');
    const $selectedTicketsInput = $('#selectedTicketsInput');
    const $confirmPurchase = $('#confirmPurchase');
    const $checkoutForm = $('#checkoutForm');
    const TICKET_PRICE = 85;

    let selectedTickets = [];

    // Validar número de boleto
    function validateTicketNumber(number) {
        return number >= 0 && number <= 999;
    }

    // Manejar confirmación de números aleatorios
    $confirmRandom.on('click', function() {
        const quantity = parseInt($randomQuantity.val());

        if (quantity >= 1 && quantity <= 999) {
            // Llamada a la API para obtener boletos aleatorios
            $.ajax({
                url: 'https://system_grandesrifasdelazonanorte.test/api/tickets/azar', // URL de la API actualizada
                method: 'GET',
                data: { cantidad: quantity },
                success: function(response) {
                    if (response.boletos) {
                        selectedTickets = response.boletos; // La API devuelve un array de boletos
                        updateSelectedTickets();
                        $randomModal.modal('hide');
                    } else {
                        alert('Error: No se pudieron obtener boletos aleatorios.');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        alert('Acceso denegado. Verifica tus permisos o configuración de CORS.');
                    } else if (xhr.status === 401) {
                        alert('No autorizado. Por favor, inicia sesión.');
                    } else {
                        const errorMessage = xhr.responseJSON?.message || 'Hubo un error desconocido';
                        alert('Error: ' + errorMessage);
                    }
                }
            });
        } else {
            alert('Por favor, seleccione entre 1 y 999 números');
        }
    });

    // Buscar boleto
    $searchButton.on('click', function() {
        const number = parseInt($ticketNumber.val());

        if (validateTicketNumber(number)) {
            // Llamada a la API para verificar si el boleto está disponible
            $.ajax({
                url: 'https://system_grandesrifasdelazonanorte.test/api/tickets/buscar', // URL de la API actualizada
                method: 'POST',
                Accept: '*/*',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify({ numero: number }),
                success: function(response) {
                    if (response.disponible) {
                        if (!selectedTickets.includes(number)) {
                            selectedTickets.push(number);
                            updateSelectedTickets();
                        } else {
                            alert('Este boleto ya ha sido seleccionado');
                        }
                    } else {
                        alert('El boleto no está disponible: ' + response.mensaje);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        alert('Acceso denegado. Verifica tus permisos o configuración de CORS.');
                    } else if (xhr.status === 401) {
                        alert('No autorizado. Por favor, inicia sesión.');
                    } else {
                        const errorMessage = xhr.responseJSON?.message || 'Hubo un error desconocido';
                        alert('Error: ' + errorMessage);
                    }
                }
            });
        } else {
            alert('Por favor, ingrese un número entre 000 y 999');
        }
    });

    // Generar boleto aleatorio
    $randomButton.on('click', function() {
        $randomModal.modal('show');
    });

    // Limpiar selección
    $clearButton.on('click', function() {
        selectedTickets = [];
        $ticketNumber.val('');
        updateSelectedTickets();
    });

    // Finalizar compra
    $checkoutButton.on('click', function() {
        if (selectedTickets.length > 0) {
            // Mostrar boletos seleccionados en el modal
            $checkoutTickets.empty();
            selectedTickets.forEach(function(ticket) {
                $checkoutTickets.append(
                    `<span class="badge bg-primary me-2">${ticket.toString().padStart(3, '0')}</span>`
                );
            });

            // Calcular total a pagar
            const totalPrice = selectedTickets.length * TICKET_PRICE;
            $totalToPay.val(`$${totalPrice.toLocaleString()}`);

            // Almacenar boletos en input oculto
            $selectedTicketsInput.val(JSON.stringify(selectedTickets));

            // Mostrar modal
            $checkoutModal.modal('show');
        } else {
            alert('Por favor, seleccione al menos un boleto');
        }
    });

    // Evento de confirmación de compra
    $confirmPurchase.on('click', function() {
        if ($checkoutForm[0].checkValidity()) {
            const formData = $checkoutForm.serializeArray();

            // Preparar datos para la API
            const purchaseData = {
                nombre_apellido: formData.find(field => field.name === 'nombre_apellido').value,
                ci: formData.find(field => field.name === 'ci').value,
                telefono: formData.find(field => field.name === 'telefono').value,
                direccion: formData.find(field => field.name === 'direccion').value,
                total_a_pagar: selectedTickets.length * TICKET_PRICE,
                selectedTickets: JSON.parse($selectedTicketsInput.val())
            };

            // Llamada AJAX a la API Laravel
            $.ajax({
                url: 'https://system_grandesrifasdelazonanorte.test/api/tickets/comprar', // URL de la API actualizada
                method: 'POST',
                Accept: '*/*',
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify(purchaseData),
                success: function(response) {
                    if (response.success) {
                        alert('Compra procesada exitosamente');
                        selectedTickets = [];
                        updateSelectedTickets();
                        $checkoutModal.modal('hide');
                        $checkoutForm[0].reset();
                        // Mostrar modal de éxito
                        $('#successModal').modal('show');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                eerror: function(xhr) {
                    if (xhr.status === 403) {
                        alert('Acceso denegado. Verifica tus permisos o configuración de CORS.');
                    } else if (xhr.status === 401) {
                        alert('No autorizado. Por favor, inicia sesión.');
                    } else {
                        const errorMessage = xhr.responseJSON?.message || 'Hubo un error desconocido';
                        alert('Error: ' + errorMessage);
                    }
                }
            });
        } else {
            $checkoutForm[0].reportValidity();
        }
    });

    // Actualizar vista de boletos seleccionados
    function updateSelectedTickets() {
        $selectedNumbersContainer.empty();

        if (selectedTickets.length > 0) {
            selectedTickets.forEach(function(ticket) {
                $selectedNumbersContainer.append(
                    `<span class="badge bg-primary me-2">${ticket.toString().padStart(3, '0')}</span>`
                );
            });
            $selectedTicketsContainer.removeClass('d-none');
        } else {
            $selectedTicketsContainer.addClass('d-none');
        }
    }
});
