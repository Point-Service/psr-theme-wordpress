<?php
/**
 * Esportazione e sostituzione controllata dei soli dati di Amministrazione Trasparente.
 */

if (!defined('ABSPATH')) {
    exit;
}

function dci_trasparenza_transfer_allowed() {
    return 1 === (int) get_current_user_id() && current_user_can('manage_options');
}

function dci_trasparenza_transfer_post_types() {
    return array(
        'elemento_trasparenza',
        'bando',
        'atto_concessione',
        'titolare_incarico',
        'incarichi_dip',
        'incarico_dirig',
    );
}

function dci_trasparenza_transfer_dir($child = '') {
    $uploads = wp_upload_dir();
    $base = trailingslashit($uploads['basedir']) . 'dci-trasparenza-transfer';
    wp_mkdir_p($base);
    if (!is_file(trailingslashit($base) . 'index.php')) {
        file_put_contents(trailingslashit($base) . 'index.php', "<?php\n// Silence is golden.\n");
    }
    if (!is_file(trailingslashit($base) . '.htaccess')) {
        file_put_contents(trailingslashit($base) . '.htaccess', "Require all denied\nDeny from all\n");
    }

    return $child === '' ? $base : trailingslashit($base) . ltrim($child, '/');
}

function dci_trasparenza_transfer_collect_attachment_ids($value, &$ids) {
    if (is_array($value)) {
        foreach ($value as $item) {
            dci_trasparenza_transfer_collect_attachment_ids($item, $ids);
        }
        return;
    }

    if (!is_numeric($value)) {
        return;
    }

    $id = absint($value);
    if ($id && 'attachment' === get_post_type($id)) {
        $ids[$id] = $id;
    }
}

function dci_trasparenza_transfer_export_data() {
    $post_types = dci_trasparenza_transfer_post_types();
    $posts = get_posts(array(
        'post_type'      => $post_types,
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ));
    $attachment_ids = array();
    $export_posts = array();

    foreach ($posts as $post) {
        $meta = get_post_meta($post->ID);
        foreach ($meta as $values) {
            foreach ($values as $value) {
                dci_trasparenza_transfer_collect_attachment_ids(maybe_unserialize($value), $attachment_ids);
            }
        }

        $terms = wp_get_object_terms($post->ID, 'tipi_cat_amm_trasp', array('fields' => 'ids'));
        $export_posts[] = array(
            'source_id' => (int) $post->ID,
            'data'      => array_intersect_key((array) $post, array_flip(array(
                'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title',
                'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password',
                'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
                'post_content_filtered', 'post_parent', 'menu_order', 'post_type', 'post_mime_type',
            ))),
            'meta'      => array_map(static function ($values) {
                return array_map('maybe_unserialize', $values);
            }, $meta),
            'terms'     => is_wp_error($terms) ? array() : array_map('intval', $terms),
        );
    }

    $terms = get_terms(array(
        'taxonomy'   => 'tipi_cat_amm_trasp',
        'hide_empty' => false,
        'orderby'    => 'term_id',
        'order'      => 'ASC',
    ));
    $export_terms = array();
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $export_terms[] = array(
                'source_id'   => (int) $term->term_id,
                'name'        => $term->name,
                'slug'        => $term->slug,
                'description' => $term->description,
                'parent'      => (int) $term->parent,
                'meta'        => get_term_meta($term->term_id),
            );
        }
    }

    return array(
        'format'         => 'dci-trasparenza-content',
        'version'        => 1,
        'transfer_id'    => substr(hash('sha256', dci_trasparenza_transfer_json(array($export_posts, $export_terms, get_option('trasparenza', array()), array_values($attachment_ids)))), 0, 32),
        'created_at'     => gmdate('c'),
        'source_url'     => home_url('/'),
        'post_types'     => $post_types,
        'posts'          => $export_posts,
        'terms'          => $export_terms,
        'options'        => array('trasparenza' => get_option('trasparenza', array())),
        'attachment_ids' => array_values($attachment_ids),
    );
}

function dci_trasparenza_transfer_json($data) {
    return wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

function dci_trasparenza_transfer_create_content_file($data, $path) {
    $json = dci_trasparenza_transfer_json($data);
    return false !== $json && false !== file_put_contents($path, $json);
}

function dci_trasparenza_transfer_attachment_files($attachment_id) {
    $files = array();
    $main = get_attached_file($attachment_id);
    if ($main && is_file($main)) {
        $files[basename($main)] = $main;
        $metadata = wp_get_attachment_metadata($attachment_id);
        if (is_array($metadata) && !empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size) {
                if (!empty($size['file'])) {
                    $candidate = trailingslashit(dirname($main)) . $size['file'];
                    if (is_file($candidate)) {
                        $files[basename($candidate)] = $candidate;
                    }
                }
            }
        }
    }
    return $files;
}

function dci_trasparenza_transfer_create_media_zip($content_data, $path) {
    if (!class_exists('ZipArchive')) {
        return new WP_Error('zip_missing', __('Estensione PHP ZipArchive non disponibile.', 'design_comuni_italia'));
    }

    $zip = new ZipArchive();
    if (true !== $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        return new WP_Error('zip_open', __('Impossibile creare il pacchetto dei file.', 'design_comuni_italia'));
    }

    $manifest = array(
        'format'      => 'dci-trasparenza-media',
        'version'     => 1,
        'transfer_id' => $content_data['transfer_id'],
        'created_at'  => $content_data['created_at'],
        'attachments' => array(),
    );

    foreach ($content_data['attachment_ids'] as $attachment_id) {
        $post = get_post($attachment_id);
        if (!$post || 'attachment' !== $post->post_type) {
            continue;
        }
        $entry = array(
            'source_id' => (int) $attachment_id,
            'post'      => array_intersect_key((array) $post, array_flip(array(
                'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt',
                'post_status', 'comment_status', 'ping_status', 'post_name', 'post_mime_type',
            ))),
            'metadata'  => wp_get_attachment_metadata($attachment_id),
            'meta'      => array_map(static function ($values) {
                return array_map('maybe_unserialize', $values);
            }, get_post_meta($attachment_id)),
            'files'     => array(),
        );
        foreach (dci_trasparenza_transfer_attachment_files($attachment_id) as $name => $file) {
            $zip_path = 'media/' . (int) $attachment_id . '/' . sanitize_file_name($name);
            $zip->addFile($file, $zip_path);
            $entry['files'][] = array(
                'path'   => $zip_path,
                'name'   => sanitize_file_name($name),
                'sha256' => hash_file('sha256', $file),
                'main'   => get_attached_file($attachment_id) === $file,
            );
        }
        if (!empty($entry['files'])) {
            $manifest['attachments'][] = $entry;
        }
    }

    $zip->addFromString('manifest.json', dci_trasparenza_transfer_json($manifest));
    $zip->close();
    return true;
}

function dci_trasparenza_transfer_download($path, $filename, $content_type) {
    nocache_headers();
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    @unlink($path);
    exit;
}

function dci_trasparenza_transfer_notice($type, $message) {
    set_transient('dci_trasparenza_transfer_notice_' . get_current_user_id(), array($type, $message), MINUTE_IN_SECONDS);
    wp_safe_redirect(admin_url('tools.php?page=dci-trasparenza-transfer'));
    exit;
}

function dci_trasparenza_transfer_export_content() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    check_admin_referer('dci_trasparenza_export_content');
    $data = dci_trasparenza_transfer_export_data();
    $path = wp_tempnam('trasparenza-contenuti.json');
    if (!$path || !dci_trasparenza_transfer_create_content_file($data, $path)) {
        dci_trasparenza_transfer_notice('error', __('Impossibile creare il pacchetto contenuti.', 'design_comuni_italia'));
    }
    dci_trasparenza_transfer_download($path, 'trasparenza-contenuti-' . gmdate('Ymd-His') . '.json', 'application/json');
}
add_action('admin_post_dci_trasparenza_export_content', 'dci_trasparenza_transfer_export_content');

function dci_trasparenza_transfer_export_media() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    check_admin_referer('dci_trasparenza_export_media');
    $data = dci_trasparenza_transfer_export_data();
    $path = wp_tempnam('trasparenza-file.zip');
    $result = $path ? dci_trasparenza_transfer_create_media_zip($data, $path) : new WP_Error('temp', 'Errore file temporaneo');
    if (is_wp_error($result)) {
        dci_trasparenza_transfer_notice('error', $result->get_error_message());
    }
    dci_trasparenza_transfer_download($path, 'trasparenza-file-' . gmdate('Ymd-His') . '.zip', 'application/zip');
}
add_action('admin_post_dci_trasparenza_export_media', 'dci_trasparenza_transfer_export_media');

function dci_trasparenza_transfer_validate_content($data) {
    if (!is_array($data) || ($data['format'] ?? '') !== 'dci-trasparenza-content' || 1 !== (int) ($data['version'] ?? 0)) {
        return new WP_Error('invalid_content', __('Pacchetto contenuti non valido o incompatibile.', 'design_comuni_italia'));
    }
    foreach (array('transfer_id', 'posts', 'terms', 'options') as $key) {
        if (!isset($data[$key]) || ('transfer_id' !== $key && !is_array($data[$key]))) {
            return new WP_Error('invalid_content', __('Il pacchetto contenuti è incompleto.', 'design_comuni_italia'));
        }
    }
    return true;
}

function dci_trasparenza_transfer_remap_meta($value, $attachment_map, $force = false) {
    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $child_force = $force || in_array((string) $key, array('id', 'ID', 'attachment_id'), true);
            $value[$key] = dci_trasparenza_transfer_remap_meta($item, $attachment_map, $child_force);
        }
        return $value;
    }
    if ($force && is_numeric($value) && isset($attachment_map[(int) $value])) {
        return is_string($value) ? (string) $attachment_map[(int) $value] : $attachment_map[(int) $value];
    }
    return $value;
}

function dci_trasparenza_transfer_import_content_data($data) {
    global $wpdb;
    $backup_dir = dci_trasparenza_transfer_dir('backups');
    wp_mkdir_p($backup_dir);
    $backup_path = trailingslashit($backup_dir) . 'prima-import-' . gmdate('Ymd-His') . '.json';
    if (!dci_trasparenza_transfer_create_content_file(dci_trasparenza_transfer_export_data(), $backup_path)) {
        return new WP_Error('backup_failed', __('Backup preventivo non riuscito: importazione annullata.', 'design_comuni_italia'));
    }

    $attachment_map = get_option('dci_trasparenza_media_map_' . sanitize_key($data['transfer_id']), array());
    foreach ((array) ($data['attachment_ids'] ?? array()) as $source_attachment_id) {
        if (empty($attachment_map[(int) $source_attachment_id])) {
            return new WP_Error(
                'media_missing',
                __('Pacchetto file mancante o incompleto: importa prima lo ZIP generato insieme a questo JSON.', 'design_comuni_italia')
            );
        }
    }
    $wpdb->query('START TRANSACTION');
    try {
        $existing = get_posts(array(
            'post_type'      => dci_trasparenza_transfer_post_types(),
            'post_status'    => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ));
        foreach ($existing as $post_id) {
            if (!wp_delete_post($post_id, true)) {
                throw new Exception('Impossibile eliminare un contenuto esistente.');
            }
        }
        $existing_terms = get_terms(array('taxonomy' => 'tipi_cat_amm_trasp', 'hide_empty' => false, 'fields' => 'ids'));
        if (!is_wp_error($existing_terms)) {
            foreach (array_reverse($existing_terms) as $term_id) {
                $deleted_term = wp_delete_term($term_id, 'tipi_cat_amm_trasp');
                if (is_wp_error($deleted_term) || false === $deleted_term) {
                    throw new Exception('Impossibile eliminare una categoria Trasparenza esistente.');
                }
            }
        } else {
            throw new Exception($existing_terms->get_error_message());
        }

        $term_map = array();
        $pending = $data['terms'];
        $guard = count($pending) + 1;
        while ($pending && $guard-- > 0) {
            foreach ($pending as $index => $term) {
                $parent = (int) ($term['parent'] ?? 0);
                if ($parent && !isset($term_map[$parent])) {
                    continue;
                }
                $created = wp_insert_term((string) $term['name'], 'tipi_cat_amm_trasp', array(
                    'slug'        => (string) ($term['slug'] ?? ''),
                    'description' => (string) ($term['description'] ?? ''),
                    'parent'      => $parent ? $term_map[$parent] : 0,
                ));
                if (is_wp_error($created)) {
                    throw new Exception($created->get_error_message());
                }
                $new_term_id = (int) $created['term_id'];
                $term_map[(int) $term['source_id']] = $new_term_id;
                foreach ((array) ($term['meta'] ?? array()) as $key => $values) {
                    foreach ((array) $values as $value) {
                        add_term_meta($new_term_id, $key, $value);
                    }
                }
                unset($pending[$index]);
            }
        }
        if ($pending) {
            throw new Exception('Gerarchia delle categorie non valida.');
        }

        $post_map = array();
        foreach ($data['posts'] as $entry) {
            $post_data = (array) ($entry['data'] ?? array());
            if (!in_array($post_data['post_type'] ?? '', dci_trasparenza_transfer_post_types(), true)) {
                throw new Exception('Tipologia contenuto non consentita nel pacchetto.');
            }
            $source_parent = (int) ($post_data['post_parent'] ?? 0);
            $post_data['post_parent'] = $post_map[$source_parent] ?? 0;
            if (empty($post_data['post_author']) || !get_user_by('id', (int) $post_data['post_author'])) {
                $post_data['post_author'] = get_current_user_id();
            }
            $new_id = wp_insert_post(wp_slash($post_data), true);
            if (is_wp_error($new_id)) {
                throw new Exception($new_id->get_error_message());
            }
            $post_map[(int) $entry['source_id']] = (int) $new_id;
            foreach ((array) ($entry['meta'] ?? array()) as $key => $values) {
                $is_attachment_field = (bool) preg_match('/attachment|alleg|file|document|curriculum|immagine|thumbnail|media/i', (string) $key);
                foreach ((array) $values as $value) {
                    $value = dci_trasparenza_transfer_remap_meta($value, $attachment_map, $is_attachment_field);
                    add_post_meta($new_id, $key, $value);
                }
            }
            $new_terms = array();
            foreach ((array) ($entry['terms'] ?? array()) as $source_term_id) {
                if (isset($term_map[(int) $source_term_id])) {
                    $new_terms[] = $term_map[(int) $source_term_id];
                }
            }
            wp_set_object_terms($new_id, $new_terms, 'tipi_cat_amm_trasp', false);
        }

        if (array_key_exists('trasparenza', $data['options'])) {
            update_option('trasparenza', $data['options']['trasparenza']);
        }
        $wpdb->query('COMMIT');
    } catch (Throwable $error) {
        $wpdb->query('ROLLBACK');
        return new WP_Error('import_failed', sprintf(__('Importazione annullata: %s', 'design_comuni_italia'), $error->getMessage()));
    }

    return array('backup' => $backup_path, 'posts' => count($data['posts']), 'terms' => count($data['terms']));
}

function dci_trasparenza_transfer_import_content() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    check_admin_referer('dci_trasparenza_import_content');
    if (sanitize_text_field(wp_unslash($_POST['confirmation'] ?? '')) !== 'SOSTITUISCI') {
        dci_trasparenza_transfer_notice('error', __('Scrivi SOSTITUISCI per confermare.', 'design_comuni_italia'));
    }
    $tmp = $_FILES['content_package']['tmp_name'] ?? '';
    if (!$tmp || !is_uploaded_file($tmp)) {
        dci_trasparenza_transfer_notice('error', __('Seleziona un pacchetto contenuti JSON.', 'design_comuni_italia'));
    }
    if (filesize($tmp) > 100 * MB_IN_BYTES) {
        dci_trasparenza_transfer_notice('error', __('Il pacchetto contenuti supera il limite di sicurezza di 100 MB.', 'design_comuni_italia'));
    }
    $data = json_decode(file_get_contents($tmp), true);
    $valid = dci_trasparenza_transfer_validate_content($data);
    if (is_wp_error($valid)) {
        dci_trasparenza_transfer_notice('error', $valid->get_error_message());
    }
    $result = dci_trasparenza_transfer_import_content_data($data);
    if (is_wp_error($result)) {
        dci_trasparenza_transfer_notice('error', $result->get_error_message());
    }
    dci_trasparenza_transfer_notice('success', sprintf(__('Importati %1$d contenuti e %2$d categorie. Backup: %3$s', 'design_comuni_italia'), $result['posts'], $result['terms'], $result['backup']));
}
add_action('admin_post_dci_trasparenza_import_content', 'dci_trasparenza_transfer_import_content');

function dci_trasparenza_transfer_safe_zip_path($path) {
    return $path !== '' && false === strpos($path, '..') && 0 !== strpos($path, '/') && false === strpos($path, '\\');
}

function dci_trasparenza_transfer_import_media() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    check_admin_referer('dci_trasparenza_import_media');
    if (!class_exists('ZipArchive')) {
        dci_trasparenza_transfer_notice('error', __('Estensione PHP ZipArchive non disponibile.', 'design_comuni_italia'));
    }
    $tmp = $_FILES['media_package']['tmp_name'] ?? '';
    if (!$tmp || !is_uploaded_file($tmp)) {
        dci_trasparenza_transfer_notice('error', __('Seleziona un pacchetto file ZIP.', 'design_comuni_italia'));
    }
    $zip = new ZipArchive();
    if (true !== $zip->open($tmp)) {
        dci_trasparenza_transfer_notice('error', __('Pacchetto ZIP non leggibile.', 'design_comuni_italia'));
    }
    $total_uncompressed = 0;
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $stat = $zip->statIndex($index);
        $entry_name = (string) ($stat['name'] ?? '');
        $entry_size = (int) ($stat['size'] ?? 0);
        if (!dci_trasparenza_transfer_safe_zip_path($entry_name) || $entry_size > 512 * MB_IN_BYTES) {
            $zip->close();
            dci_trasparenza_transfer_notice('error', __('Il pacchetto contiene un file non sicuro o troppo grande.', 'design_comuni_italia'));
        }
        $total_uncompressed += $entry_size;
        if ($total_uncompressed > 2 * GB_IN_BYTES) {
            $zip->close();
            dci_trasparenza_transfer_notice('error', __('Il pacchetto supera il limite di sicurezza di 2 GB non compressi.', 'design_comuni_italia'));
        }
    }
    $manifest_raw = $zip->getFromName('manifest.json');
    $manifest = $manifest_raw ? json_decode($manifest_raw, true) : null;
    if (!is_array($manifest) || ($manifest['format'] ?? '') !== 'dci-trasparenza-media' || 1 !== (int) ($manifest['version'] ?? 0)) {
        $zip->close();
        dci_trasparenza_transfer_notice('error', __('Manifest del pacchetto file non valido.', 'design_comuni_italia'));
    }
    $transfer_id = sanitize_key($manifest['transfer_id']);
    $base_dir = dci_trasparenza_transfer_dir('imported/' . $transfer_id);
    wp_mkdir_p($base_dir);
    $uploads = wp_upload_dir();
    $map = array();

    foreach ((array) $manifest['attachments'] as $attachment) {
        $source_id = (int) ($attachment['source_id'] ?? 0);
        $attachment_dir = trailingslashit($base_dir) . $source_id;
        wp_mkdir_p($attachment_dir);
        $main_path = '';
        foreach ((array) ($attachment['files'] ?? array()) as $file) {
            $zip_path = (string) ($file['path'] ?? '');
            if (!dci_trasparenza_transfer_safe_zip_path($zip_path)) {
                $zip->close();
                dci_trasparenza_transfer_notice('error', __('Il pacchetto contiene un percorso non sicuro.', 'design_comuni_italia'));
            }
            $contents = $zip->getFromName($zip_path);
            if (false === $contents || hash('sha256', $contents) !== ($file['sha256'] ?? '')) {
                $zip->close();
                dci_trasparenza_transfer_notice('error', __('Checksum di un file non valido.', 'design_comuni_italia'));
            }
            $destination = trailingslashit($attachment_dir) . sanitize_file_name($file['name']);
            file_put_contents($destination, $contents);
            if (!empty($file['main'])) {
                $main_path = $destination;
            }
        }
        if (!$main_path) {
            continue;
        }
        $post_data = (array) ($attachment['post'] ?? array());
        $post_data['post_type'] = 'attachment';
        $post_data['post_status'] = 'inherit';
        $post_data['post_parent'] = 0;
        $new_id = wp_insert_attachment(wp_slash($post_data), $main_path, 0, true);
        if (is_wp_error($new_id)) {
            $zip->close();
            dci_trasparenza_transfer_notice('error', $new_id->get_error_message());
        }
        update_attached_file($new_id, $main_path);
        foreach ((array) ($attachment['meta'] ?? array()) as $key => $values) {
            if (in_array($key, array('_wp_attached_file', '_wp_attachment_metadata'), true)) {
                continue;
            }
            delete_post_meta($new_id, $key);
            foreach ((array) $values as $value) {
                add_post_meta($new_id, $key, $value);
            }
        }
        if (is_array($attachment['metadata'] ?? null)) {
            $metadata = $attachment['metadata'];
            $metadata['file'] = _wp_relative_upload_path($main_path);
            wp_update_attachment_metadata($new_id, $metadata);
        }
        update_post_meta($new_id, '_dci_trasparenza_transfer_id', $transfer_id);
        update_post_meta($new_id, '_dci_trasparenza_source_attachment_id', $source_id);
        $map[$source_id] = (int) $new_id;
    }
    $zip->close();
    update_option('dci_trasparenza_media_map_' . $transfer_id, $map, false);
    dci_trasparenza_transfer_notice('success', sprintf(__('Importati %d allegati. Ora importa il pacchetto contenuti corrispondente.', 'design_comuni_italia'), count($map)));
}
add_action('admin_post_dci_trasparenza_import_media', 'dci_trasparenza_transfer_import_media');

function dci_trasparenza_transfer_admin_menu() {
    if (dci_trasparenza_transfer_allowed()) {
        add_management_page(
            __('Trasferimento Trasparenza', 'design_comuni_italia'),
            __('Trasferimento Trasparenza', 'design_comuni_italia'),
            'manage_options',
            'dci-trasparenza-transfer',
            'dci_trasparenza_transfer_admin_page'
        );
    }
}
add_action('admin_menu', 'dci_trasparenza_transfer_admin_menu');

function dci_trasparenza_transfer_admin_page() {
    if (!dci_trasparenza_transfer_allowed()) {
        wp_die(esc_html__('Operazione non consentita.', 'design_comuni_italia'), '', array('response' => 403));
    }
    $notice = get_transient('dci_trasparenza_transfer_notice_' . get_current_user_id());
    delete_transient('dci_trasparenza_transfer_notice_' . get_current_user_id());
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Trasferimento Amministrazione Trasparente', 'design_comuni_italia'); ?></h1>
        <p><?php esc_html_e('I due pacchetti sono separati. Sul sito di destinazione importa prima i file e poi i contenuti.', 'design_comuni_italia'); ?></p>
        <?php if ($notice) : ?>
            <div class="notice notice-<?php echo esc_attr($notice[0]); ?> is-dismissible"><p><?php echo esc_html($notice[1]); ?></p></div>
        <?php endif; ?>

        <div class="card" style="max-width:900px">
            <h2><?php esc_html_e('1. Esporta', 'design_comuni_italia'); ?></h2>
            <p><?php esc_html_e('L’esportazione legge i dati senza modificarli.', 'design_comuni_italia'); ?></p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=dci_trasparenza_export_content'), 'dci_trasparenza_export_content')); ?>"><?php esc_html_e('Esporta contenuti JSON', 'design_comuni_italia'); ?></a>
                <a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=dci_trasparenza_export_media'), 'dci_trasparenza_export_media')); ?>"><?php esc_html_e('Esporta file ZIP', 'design_comuni_italia'); ?></a>
            </p>
        </div>

        <div class="card" style="max-width:900px">
            <h2><?php esc_html_e('2. Importa file', 'design_comuni_italia'); ?></h2>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="dci_trasparenza_import_media">
                <?php wp_nonce_field('dci_trasparenza_import_media'); ?>
                <input type="file" name="media_package" accept=".zip,application/zip" required>
                <?php submit_button(__('Importa file ZIP', 'design_comuni_italia'), 'secondary', 'submit', false); ?>
            </form>
        </div>

        <div class="card" style="max-width:900px;border-left:4px solid #d63638">
            <h2><?php esc_html_e('3. Sostituisci contenuti', 'design_comuni_italia'); ?></h2>
            <p><strong><?php esc_html_e('Attenzione:', 'design_comuni_italia'); ?></strong> <?php esc_html_e('questa operazione elimina e sostituisce soltanto le tipologie e le categorie di Amministrazione Trasparente. Prima viene creato un backup JSON automatico.', 'design_comuni_italia'); ?></p>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="dci_trasparenza_import_content">
                <?php wp_nonce_field('dci_trasparenza_import_content'); ?>
                <p><input type="file" name="content_package" accept=".json,application/json" required></p>
                <p><label><?php esc_html_e('Per confermare scrivi SOSTITUISCI:', 'design_comuni_italia'); ?><br><input type="text" name="confirmation" autocomplete="off" required></label></p>
                <?php submit_button(__('Sostituisci i contenuti Trasparenza', 'design_comuni_italia'), 'delete', 'submit', false); ?>
            </form>
        </div>
    </div>
    <?php
}
