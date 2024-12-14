jQuery(document).ready(function($) {
    const $ticketNumber = $('#ticketNumber');
    const $checkoutModal = $('#checkoutModal'); // Asegúrate de que el selector sea correcto
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
            selectedTickets = [];
            for (let i = 0; i < quantity; i++) {
                const randomNumber = Math.floor(Math.random() * 1000);
                selectedTickets.push(randomNumber);
            }
            updateSelectedTickets();
            $randomModal.modal('hide');
        } else {
            alert('Por favor, seleccione entre 000 y 999 números');
        }
    });

    // Buscar boleto
    $searchButton.on('click', function() {
        const number = parseInt($ticketNumber.val());

        if (validateTicketNumber(number)) {
            if (!selectedTickets.includes(number)) {
                selectedTickets.push(number);
                updateSelectedTickets();
            } else {
                alert('Este boleto ya ha sido seleccionado');
            }
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

            // Agregar los boletos seleccionados a los datos del formulario
            formData.push({
                name: 'selectedTickets',
                value: $selectedTicketsInput.val()
            });

            // Llamada AJAX para procesar la compra
            $.ajax({
                url: ticketFormData.ajax_url,
                method: 'POST',
                data: {
                    action: 'process_ticket_purchase',
                    formData: formData
                },
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
                error: function() {
                    alert('Hubo un error al procesar la compra');
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