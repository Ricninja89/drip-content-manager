<?php
// Protezione contro l'accesso diretto
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Funzione per creare nuovi ruoli
function crea_nuovo_ruolo($role_name, $capabilities) {
    // Crea il nuovo ruolo con le capabilities selezionate
    add_role( $role_name, __( $role_name ), $capabilities );
    echo '<div class="updated"><p>Ruolo creato con successo!</p></div>';
}

// Form nel backend per creare nuovi ruoli
function mostra_form_creazione_ruolo() {
    // Gestisci l'invio del form per creare nuovi ruoli
    if ( isset($_POST['ruolo_nome']) && isset($_POST['capabilities']) ) {
        // Verifica il nonce per la sicurezza
        if ( ! isset( $_POST['crea_ruolo_nonce'] ) || ! wp_verify_nonce( $_POST['crea_ruolo_nonce'], 'crea_ruolo_action' ) ) {
            wp_die( 'Errore nella verifica della sicurezza del form.' );
        }

        // Sanifica i dati inviati
        $role_name = sanitize_text_field($_POST['ruolo_nome']);
        $capabilities = array_map( 'sanitize_text_field', $_POST['capabilities'] );  // Sanifica le capabilities

        // Crea il nuovo ruolo
        crea_nuovo_ruolo($role_name, $capabilities);
    }

    ?>
    <div class="wrap">
        <h2>Crea un nuovo ruolo</h2>
        <form method="post" action="">
            <?php wp_nonce_field( 'crea_ruolo_action', 'crea_ruolo_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="ruolo_nome">Nome del ruolo</label></th>
                    <td><input type="text" id="ruolo_nome" name="ruolo_nome" required /></td>
                </tr>
                <tr>
                    <th><label for="capabilities">Capabilities</label></th>
                    <td>
                        <label><input type="checkbox" name="capabilities[read]" value="1" /> Leggere contenuti</label><br>
                        <label><input type="checkbox" name="capabilities[edit_posts]" value="1" /> Modifica post</label><br>
                        <label><input type="checkbox" name="capabilities[delete_posts]" value="1" /> Elimina post</label><br>
                        <label><input type="checkbox" name="capabilities[publish_posts]" value="1" /> Pubblica post</label><br>
                    </td>
                </tr>
            </table>
            <input type="submit" class="button button-primary" value="Crea Ruolo" />
        </form>
    </div>
    <?php
}

// Funzione per visualizzare il form di creazione delle regole
function mostra_form_selezione_categoria_e_criterio() {
    // Ottieni tutte le categorie disponibili, includendo anche quelle vuote
    $args = array(
        'hide_empty' => false,
    );
    $categories = get_categories($args);  // Ottieni tutte le categorie

    // Ottieni tutti i ruoli utente disponibili
    global $wp_roles;
    $ruoli = $wp_roles->get_names();

    // Criteri di confronto
    $criteri = array(
        'iscrizione_precedente_pubblicazione' => 'Data di iscrizione precedente o uguale a data pubblicazione articolo',
    );

    // Gestisci l'invio del form
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['drip_content_action']) ) {
        // Verifica il nonce per la sicurezza
        if ( ! isset( $_POST['drip_content_nonce'] ) || ! wp_verify_nonce( $_POST['drip_content_nonce'], 'drip_content_action' ) ) {
            wp_die( 'Errore nella verifica della sicurezza del form.' );
        }

        // Recupera le regole esistenti
        $drip_content_rules = get_option('drip_content_rules', array());

        // Crea una nuova regola
        $new_rule = array(
            'id' => time(), // ID univoco basato sul timestamp corrente
            'category' => sanitize_text_field($_POST['drip_content_category']),
            'roles' => array_map('sanitize_text_field', $_POST['drip_content_role']),
            'criterio' => sanitize_text_field($_POST['drip_content_criterio']),
        );

        // Aggiungi la nuova regola all'array
        $drip_content_rules[] = $new_rule;

        // Salva le regole aggiornate
        update_option('drip_content_rules', $drip_content_rules);

        echo '<div class="updated"><p>Regola aggiunta con successo!</p></div>';
    }

    // Form per creare una nuova regola
    ?>
    <div class="wrap">
        <h2>Crea una nuova regola</h2>
        <form method="post" action="">
            <?php wp_nonce_field( 'drip_content_action', 'drip_content_nonce' ); ?>
            <input type="hidden" name="drip_content_action" value="add_rule" />
            <!-- Selezione Categoria -->
            <h3>Seleziona Categoria</h3>
            <select name="drip_content_category">
                <?php foreach($categories as $category) : ?>
                    <option value="<?php echo esc_attr($category->term_id); ?>">
                        <?php echo esc_html($category->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Selezione Ruolo -->
            <h3>Seleziona Ruoli</h3>
            <select name="drip_content_role[]" multiple>
                <?php foreach($ruoli as $role_value => $role_name) : ?>
                    <option value="<?php echo esc_attr($role_value); ?>">
                        <?php echo esc_html($role_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Selezione Criterio -->
            <h3>Seleziona Criterio</h3>
            <select name="drip_content_criterio">
                <?php foreach($criteri as $key => $label) : ?>
                    <option value="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" class="button button-primary" value="Aggiungi Regola" />
        </form>
    </div>
    <?php
}

// Funzione per visualizzare la pagina di riepilogo delle regole
function drip_content_rules_page() {
    // Recupera le regole esistenti
    $drip_content_rules = get_option('drip_content_rules', array());

    // Gestisci l'eliminazione di una regola
    if ( isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['rule_id']) ) {
        // Verifica il nonce per la sicurezza
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_rule_' . $_GET['rule_id'] ) ) {
            wp_die( 'Errore nella verifica della sicurezza.' );
        }

        // Rimuovi la regola dall'array
        $rule_id = sanitize_text_field($_GET['rule_id']);
        foreach ( $drip_content_rules as $key => $rule ) {
            if ( $rule['id'] == $rule_id ) {
                unset( $drip_content_rules[$key] );
                break;
            }
        }

        // Salva le regole aggiornate
        update_option('drip_content_rules', $drip_content_rules);

        echo '<div class="updated"><p>Regola eliminata con successo!</p></div>';
    }

    // Mostra la tabella delle regole
    ?>
    <div class="wrap">
        <h2>Regole Drip Content</h2>
        <?php if ( ! empty( $drip_content_rules ) ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Ruoli</th>
                        <th>Criterio</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $drip_content_rules as $rule ) : ?>
                        <tr>
                            <td><?php echo esc_html( get_cat_name( $rule['category'] ) ); ?></td>
                            <td><?php echo esc_html( implode( ', ', $rule['roles'] ) ); ?></td>
                            <td><?php echo esc_html( $rule['criterio'] ); ?></td>
                            <td>
                                <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=drip-content-rules&action=delete&rule_id=' . $rule['id'] ), 'delete_rule_' . $rule['id'] ); ?>" class="button button-secondary" onclick="return confirm('Sei sicuro di voler eliminare questa regola?');">Elimina</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>Non ci sono regole configurate.</p>
        <?php endif; ?>
    </div>
    <?php
}

// Funzione per gestire la visualizzazione dei contenuti in base alle regole
function verifica_accesso_drip_content( $content ) {
    if ( ! is_singular( 'post' ) ) {
        return $content; // Applica solo ai singoli post
    }

    $drip_content_rules = get_option('drip_content_rules', array());
    if ( empty( $drip_content_rules ) ) {
        return $content; // Nessuna regola configurata
    }

    $user_id = get_current_user_id();
    $user_data = get_userdata( $user_id );
    $user_roles = $user_data->roles;

    // Ottieni la data di registrazione dell'utente
    $user_registered_date = $user_data->user_registered;
    $post_publish_date = get_the_date( 'Y-m-d H:i:s' );

    foreach ( $drip_content_rules as $rule ) {
        // Se il post appartiene alla categoria della regola
        if ( has_category( $rule['category'] ) ) {
            // Verifica se l'utente ha uno dei ruoli specificati nella regola
            if ( array_intersect( $rule['roles'], $user_roles ) ) {
                if ( $rule['criterio'] == 'iscrizione_precedente_pubblicazione' ) {
                    if ( strtotime( $user_registered_date ) <= strtotime( $post_publish_date ) ) {
                        return $content;  // Mostra il contenuto
                    } else {
                        return '<p>Questo contenuto non è ancora disponibile per te.</p>';
                    }
                } else {
                    // Aggiungi qui altri criteri se necessario
                    return '<p>Criterio non riconosciuto.</p>';
                }
            } else {
                return '<p>Non hai il ruolo necessario per visualizzare questo contenuto.</p>';
            }
        }
    }

    return $content; // Restituisce il contenuto se nessuna regola corrisponde
}
add_filter( 'the_content', 'verifica_accesso_drip_content' );

// Funzione per aggiornare la data di assegnazione del ruolo all'utente
function aggiorna_data_assegnazione_ruolo( $user_id, $role, $old_roles ) {
    if ( ! empty( $role ) ) {
        // Imposta la data corrente come data di assegnazione del ruolo
        update_user_meta( $user_id, 'role_assigned_date', current_time( 'mysql' ) );
    }
}
add_action( 'set_user_role', 'aggiorna_data_assegnazione_ruolo', 10, 3 );

// Funzione per gestire le pagine speciali
function gestisci_pagine_speciali() {
    if ( is_search() ) {
        // Aggiungi qui la logica per le pagine di ricerca
    }

    if ( is_embed() ) {
        // Aggiungi qui la logica per le pagine embed
    }
}
add_action( 'template_redirect', 'gestisci_pagine_speciali' );
