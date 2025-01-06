<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://github.com/Dansware03
 * @since             1.0.0
 * @package           System_grzn_api
 *
 * @wordpress-plugin
 * Plugin Name:       Api Laravel GRZN
 * Plugin URI:        https://github.com/Dansware03/plugin-api-laravel-grzn.git
 * Description:       Conecta el sistema con la api de laravel
 * Version:           1.0.0
 * Author:            Dansware Dev
 * Author URI:        https://https://github.com/Dansware03/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       system_grzn_api
 * Domain Path:       /languages
 */

// Si se intenta acceder directamente al archivo, abortar
if (!defined('WPINC')) {
    die;
}

class System_Grzn_Api
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('ticket_form', array($this, 'render_ticket_form'));
        add_shortcode('lista_ticket', array($this, 'render_lista_ticket'));
    }

    public function enqueue_scripts()
    {
        // Encolar SweetAlert2
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), '11.0.0', true);

        // Encolar estilos de Bootstrap (opcional)
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css');

        // Encolar Bootstrap JS
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.2.3', true);

        // Encolar Font Awesome para iconos
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

        // Scripts personalizados
        wp_enqueue_script('ticket-form-script', plugin_dir_url(__FILE__) . 'js/ticket-form.js', array('jquery', 'sweetalert2'), '1.0.0', true);

        // Pasar datos al script
        wp_localize_script('ticket-form-script', 'ticketFormData', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        wp_enqueue_style('custom-styles', plugin_dir_url(__FILE__) . 'css/custom-styles.css');

        // Agregar estilos inline para el grid de tickets
        wp_add_inline_style('custom-styles', '
            .tickets-container {
                max-height: 400px;
                overflow-y: auto;
                border: 1px solid #dee2e6;
                padding: 15px;
                border-radius: 8px;
            }
            .ticket-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(45px, 1fr));
                gap: 5px;
            }
            .ticket-item {
                font-size: 12px;
                padding: 4px;
                border-radius: 4px;
                text-align: center;
                font-weight: bold;
                background: #f8f9fa;
                border: 1px solid #dee2e6;
            }
            .ticket-item.sold {
                background: #1e1e1e;
                color: black;
                border-color: #1e1e1e;
            }
            /* Personalización del scrollbar */
            .tickets-container::-webkit-scrollbar {
                width: 8px;
            }
            .tickets-container::-webkit-scrollbar-track {
                background: #f1f1f1;
            }
            .tickets-container::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 4px;
            }
            .tickets-container::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
        ');
    }

    public function render_ticket_form()
    {
        ob_start();
?>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Consulta disponibilidad y compra tu boleto</h5>
                            <div class="mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control number-input" id="ticketNumber"
                                        min="0" max="999" placeholder="Ingresa el número (000-999)">
                                    <button class="btn btn-primary" id="searchButton">Buscar</button>
                                </div>
                            </div>
                            <button class="btn btn-primary mb-3" id="randomButton">
                                <i class="fas fa-random me-2"></i>Elegir al azar
                            </button>
                            <button class="btn btn-secondary mb-3" id="clearButton">
                                <i class="fas fa-trash-alt me-2"></i>Limpiar
                            </button>
                            <div id="selectedTickets" class="mb-3 d-none">
                                <h6>Boletos seleccionados:</h6>
                                <div class="selected-numbers"></div>
                                <button class="btn btn-success mt-3" id="checkoutButton">
                                    <i class="fas fa-shopping-cart me-2"></i>Finalizar Compra
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Random Numbers Modal -->
        <div class="modal fade" id="randomModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Selección al azar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="randomQuantity" class="form-label">¿Cuántos números deseas?</label>
                            <input type="number" class="form-control" id="randomQuantity" min="1"
                                max="10" value="1">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="confirmRandom">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Modal -->
        <div class="modal fade" id="checkoutModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Finalizar Compra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="checkoutForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre completo *</label>
                                <input type="text" class="form-control" id="name" name="nombre_apellido"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="cedula" class="form-label">Cédula *</label>
                                <input type="text" class="form-control" id="cedula" name="ci" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Teléfono * (escribir el numero con prefijo internacional sin el +)</label>
                                <input type="tel" class="form-control" id="phone" name="telefono" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Dirección *</label>
                                <textarea class="form-control" id="address" name="direccion" required></textarea>
                            </div>
                            <div class="mb-3">
                                <h6>Boletos seleccionados:</h6>
                                <div id="checkoutTickets"></div>
                            </div>
                            <div class="mb-3">
                                <label for="ticketPrice" class="form-label">Valor de cada boleto:</label>
                                <input type="text" class="form-control" id="ticketPrice" value="$85" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="totalToPay" class="form-label">Total a pagar:</label>
                                <input type="text" class="form-control" id="totalToPay" name="total_a_pagar"
                                    disabled>
                            </div>
                            <input type="hidden" id="selectedTicketsInput" name="selectedTickets">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="confirmPurchase">Confirmar Compra</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">¡Compra Exitosa!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                            <p>Gracias por su compra. En unos minutos se contactará por WhatsApp el departamento de
                                ventas para coordinar los datos de pago.</p>
                            <p>¡Buena suerte!</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    public function render_lista_ticket() {
        ob_start();
        ?>
        <div class="container my-4">
            <h5 class="text-center mb-3">Lista de Boletos</h5>
            <div class="tickets-container">
                <div class="ticket-grid" id="ticketGrid">
                    <div class="text-center w-100">Cargando boletos...</div>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                const apiUrl = 'https://system_grandesrifasdelazonanorte.test/api/tickets/listar';

                $.getJSON(apiUrl, function(response) {
                    const boletos = response.boletos;
                    const ticketGrid = $('#ticketGrid');
                    ticketGrid.empty();

                    boletos.forEach(function(boleto) {
                        const ticketClass = boleto.vendido ? 'ticket-item sold' : 'ticket-item';
                        const ticket = `
                            <div class="${ticketClass}">
                                ${boleto.numero}
                            </div>
                        `;
                        ticketGrid.append(ticket);
                    });
                }).fail(function() {
                    $('#ticketGrid').html('<div class="text-danger">Error al cargar los boletos. Intenta nuevamente.</div>');
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
}

// Inicializar el plugin
function init_system_grzn_api()
{
    new System_Grzn_Api();
}
add_action('plugins_loaded', 'init_system_grzn_api');
