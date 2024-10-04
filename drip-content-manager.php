<?php
/*
Plugin Name: Drip Content Manager
Description: Plugin per gestire il drip content in base a categorie, ruoli utente e criteri selezionati.
Version: 1.1
Author: Riccardo Rosignoli
Author URI: https://www.digitalezen.it/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: drip-content-manager
*/

// Protezione contro l'accesso diretto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Includi le funzioni per il plugin
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// Funzione che visualizza la pagina principale del plugin
function drip_content_page() {
    echo '<div class="wrap">';
    echo '<h1>Gestione Drip Content</h1>';
    echo '<p>Benvenuto nella gestione del Drip Content. Utilizza le opzioni disponibili per configurare le regole di accesso ai contenuti.</p>';
    echo '</div>';
    // Mostra il form per aggiungere nuove regole
    mostra_form_selezione_categoria_e_criterio();
}

// Aggiunge la voce di menu nel backend
function drip_content_menu() {
    add_menu_page(
        'Drip Content Manager',
        'Drip Content',
        'manage_options',
        'drip-content-manager',
        'drip_content_page',
        'dashicons-schedule',
        20
    );

    // Sottopagina per creare nuovi ruoli
    add_submenu_page(
        'drip-content-manager',
        'Crea Nuovo Ruolo',
        'Crea Ruolo',
        'manage_options',
        'crea-ruolo',
        'mostra_form_creazione_ruolo'
    );

    // Sottopagina per la gestione delle regole
    add_submenu_page(
        'drip-content-manager',
        'Regole Drip Content',
        'Regole',
        'manage_options',
        'drip-content-rules',
        'drip_content_rules_page'
    );
}
add_action('admin_menu', 'drip_content_menu');
