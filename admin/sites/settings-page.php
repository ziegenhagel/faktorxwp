<?php
function fxwp_settings_page()
{
    // check if we want fxwp_api_key_renew
    if (isset($_GET['fxwp_api_key_renew']) && $_GET['fxwp_api_key_renew'] == 'true') {
        // DEPRECATED
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Ihr API-Schlüssel wurde erfolgreich erneuert.', 'fxwp') . '</p></div>';
        fxwp_deactivation();
        fxwp_activation();
    }

    // if fxwp_self_update is set
    if (isset($_GET['fxwp_self_update']) && $_GET['fxwp_self_update'] == 'true') {
        // do hourly which does the update
        fxm_do_this_hourly();

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Das Plugin wurde erfolgreich aktualisiert.', 'fxwp') . '</p></div>';
    }

    // Check if the plugin is activated
    $api_key = get_option('fxwp_api_key');
    $google_fonts_remove = get_option('fxwp_google_fonts_remove');

    if (current_user_can("fxm_admin")) {
        // Deactivated features description
        $deactivated_features_description = array(
            'fxwp_deact_ai' => 'KI Funktionen deaktivieren',
            'fxwp_deact_backups' => 'Backups deaktivieren',
            'fxwp_deact_autoupdates' => 'Automatische Updates deaktivieren',
            'fxwp_deact_email_log' => 'E-Mail Log für Kundis ausblenden',
            'fxwp_deact_shortcodes' => 'Shortcodes für Kundis ausblenden',
            'fxwp_deact_dashboards' => 'Alle Dashboards für Kundis ausblenden',
            'fxwp_deact_debug_log_widget' => 'Debug Log Widget ausblenden',
            'fxwp_deact_customer_settings' => 'Plugin Settings für Kundis komplett ausblenden',
            'fxwp_deact_hide_plugin' => 'Plugin vor Kundis komplett verstecken',
        );
        // Restricted features description
        $restricted_features_description = array(
            'fxwp_restr_pages' => 'Seiten',
            'fxwp_restr_posts' => 'Blogposts',
            'fxwp_restr_uploads' => 'Mediendateien',
            'fxwp_restr_themes' => 'Themes',
            'fxwp_restr_updates-submenu' => 'Updates Submenu von Dashboard',
            'fxwp_restr_elememtor-templates' => 'Elementor Templates',
            'fxwp_restr_wpcf7' => 'Contact Form 7',
            'fxwp_restr_new-button' => 'Admin Bar New Button',
            'fxwp_restr_updates-indicator' => 'Admin Bar Updates Indicator',
            'fxwp_restr_my-account' => 'Admin Bar Account',
            'fxwp_restr_admin_plugins' => 'Plugins',
            'fxwp_restr_admin_users' => 'Benutzer',
            'fxwp_restr_admin_tools' => 'Tools',
            'fxwp_restr_admin_settings' => 'WP Einstellungen',
            'fxwp_restr_admin_elementor' => 'Elementor Einstellungen',
            'fxwp_restr_admin_eael' => 'Essential Addons for Elementor Einstellungen',
        );
        //Get debugging options description from mods/debug-mod.php
        global $debugging_options_description;

        // Get deactivated features
        $deactivated_features = fxwp_get_deact();
        // if deactivated features is empty, fill it with false or if it cannot be json parsed
        /*
        if (empty($deactivated_features) || strlen($deactivated_features) < 5) {
            $deactivated_features = array_fill_keys(array_keys($deactivated_features_description), false);
        } else {
            $deactivated_features = get_object_vars(json_decode($deactivated_features));
        }
        */
        // Get debugging options
        $debugging_options = get_option('fxwp_debugging_options');
        // if debugging options are empty, fill it with false
        if (empty($debugging_options)) {
            $debugging_options = array_fill_keys(array_keys($debugging_options_description), false);
        } else {
            $debugging_options = get_object_vars(json_decode($debugging_options));
        }
        // Get deactivated features
        $restricted_features = fxwp_get_restr();
        // if deactivated features is empty or shorter than 5 chars, fill it with false
        if (empty($restricted_features) || strlen($restricted_features) < 5) {
            $restricted_features = array_fill_keys(array_keys($restricted_features_description), false);
        } else {
            $restricted_features = get_object_vars(json_decode($restricted_features));
        }
    }

    /* eine liste mit den wp options verwalteten daten anzeigen, die man einstellen kann, darunter der datentyp des feldes, titel beschreibung (optional), default wert. das ganze in kategorien unterteilt */
    /* aktuell enthält sie nur fxwp_storage_limitfxwp_storage_limit und fxm_customer_update_dashboardfxm_customer_update_dashboard */
    $fxm_options = array(
        // kategorie "Weitere Einstellungen"
        array(
            'title' => 'Weitere Einstellungen',
            'options' => array(
                'fxwp_storage_limit' => array(
                    'type' => 'filesize',
                    'title' => 'Speicherlimit',
                    'description' => 'Das Speicherlimit, das auf der Webseite verwendet werden kann.',
                    'default' => 20 * 1024 * 1024 * 1024, // 20GB
                ),
                'fxm_customer_update_dashboard' => array(
                    'type' => 'checkbox',
                    'title' => 'Kunden Update Dashboard',
                    'description' => 'Wenn true, wird eine Unterseite für Kund:innen angezeigt, die selbst Updates machen wollen.',
                    'default' => false,
                ),
            ),
        ),
    );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Faktor &times; WordPress Einstellungen', 'fxwp'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('fxwp_settings_group');
            do_settings_sections('fxwp_settings_group');
            ?>
            <table class="form-table">

                <!-- View options if current user is fxm_admin -->
                <?php if (current_user_can("fxm_admin")) { ?>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Ansichtsoptionen', 'fxwp'); ?></th>
                        <td>
                            <p><?php echo esc_html__('Wählen Sie die gewünschte Ansichtsoption aus:', 'fxwp'); ?></p>
                            <label>
                                <input type="radio" name="fxwp_view_option"
                                       value="einfach" <?php checked(get_option('fxwp_view_option', 'einfach'), 'einfach'); ?>>
                                <?php echo esc_html__('Einfache Ansicht', 'fxwp'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="radio" name="fxwp_view_option"
                                       value="erweitert" <?php checked(get_option('fxwp_view_option'), 'erweitert'); ?>>
                                <?php echo esc_html__('Erweiterte Ansicht', 'fxwp'); ?>
                            </label>
                        </td>
                    </tr>
                <?php } ?>

                <!-- API Key if current user is fxm_admin-->
                <?php if (current_user_can("fxm_admin")) { ?>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Faktor&times;WP Lizenz', 'fxwp'); ?></th>
                        <td>
                            <p><?php echo esc_html__('Bitte geben Sie Ihren Lizenz Schlüssel ein.', 'fxwp'); ?></p>
                            <div class="flex">
                                <input type="text" name="fxwp_api_key" value="<?php echo esc_attr($api_key); ?>"/>
                                <!-- have a new activation button -->
                                <?php if ($api_key) { ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=fxwp-settings&fxwp_api_key_renew=true')); ?>"
                                       class="button button-secondary"><?php echo esc_html__('Lizenz erneuern', 'fxwp'); ?></a>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>

                <!-- local env? if current user can fxm_admin -->
                <?php if (current_user_can("fxm_admin")) { ?>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Lokale Umgebung', 'fxwp'); ?></th>
                        <td>
                            <!-- use FXWP_LOCAL_ENV constant -->
                            <p><?php
                                if (defined('FXWP_LOCAL_ENV') && FXWP_LOCAL_ENV) {
                                    echo esc_html__('Sie befinden sich in einer lokalen Umgebung.', 'fxwp');
                                } else {
                                    echo esc_html__('Sie befinden sich nicht in einer lokalen Umgebung.', 'fxwp');
                                }
                                ?>
                            </p>
                        </td>
                    </tr>
                <?php } ?>

                <!-- deactivate features if current user can fxm_admin -->
                <?php if (current_user_can("fxm_admin")) { ?>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Funktionen de-/aktivieren', 'fxwp'); ?></th>
                        <td>
                            <ul class="checkbox-list" id="deactivated_features_list">
                                <?php
                                foreach ($deactivated_features_description as $option => $label) {
                                    echo "<li><input type='checkbox' name='{$option}' id='{$option}'";
                                    if ($deactivated_features[$option]) {
                                        echo " checked value='true'";
                                    } else {
                                        echo " value='false'";
                                    }
                                    echo "/><label for='{$option}'>{$label}</label></li>";
                                } ?>
                            </ul>
                            <p style="color: #E88813"><?php echo __('Achtung: Entfernte Haken aktivieren Features nicht direkt wieder! (zb Auto Update muss manuell noch gestaret werden)', 'fxwp'); ?></p>
                        </td>
                    </tr>
                <?php } ?>

                <!-- restrict features if current user can fxm_admin -->
                <?php if (current_user_can("fxm_admin")) { ?>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Menüseiten ausblenden', 'fxwp'); ?></th>
                        <td>
                            <ul class="checkbox-list" id="restricted_features_list">
                                <?php
                                foreach ($restricted_features_description as $option => $label) {
                                    echo "<li><input type='checkbox' name='{$option}' id='{$option}'";
                                    if ($restricted_features[$option]) {
                                        echo " checked value='true'";
                                    } else {
                                        echo " value='false'";
                                    }
                                    echo "/><label for='{$option}'>{$label}</label></li>";
                                } ?>
                            </ul>
                        </td>
                    </tr>
                <?php } ?>

                <!-- change debugging mode -->
                <?php if (current_user_can("fxm_admin")) { ?>
                    <tr id="fxwp-debugging-options">
                        <th scope="row"><?php echo esc_html__('Debugging de-/aktivieren', 'fxwp'); ?></th>
                        <td>
                            <ul class="checkbox-list" id="deactivated_features_list">
                                <?php
                                foreach ($debugging_options_description as $option => $label) {
                                    echo "<li><input type='checkbox' name='{$option}' id='{$option}'";
                                    if ($debugging_options[$option]) {
                                        echo " checked value='true'";
                                    } else {
                                        echo " value='false'";
                                    }
                                    echo "/><label for='{$option}'><code>{$label}</code></label></li>";
                                } ?>
                            </ul>
                        </td>
                    </tr>
                    <script>
                        <!--                        Flash this area if url contains #fxwp-debugging -->
                        if (window.location.hash === '#fxwp-debugging') {
                            //Instead of making background red, show red border
                            document.getElementById('fxwp-debugging').style.border = '5px solid #E88813';
                            //remove flash 1s later
                            setTimeout(function () {
                                document.getElementById('fxwp-debugging').style.border = 'none';
                            }, 500);
                        }
                    </script>
                <?php } ?>

                <!-- print get_option for fxwp_customer and fxwp_project -->
                <!-- only if current user is fxm_admin -->
                <?php if (current_user_can("fxm_admin")) { ?>

                    <tr class="jsondata">
                        <th scope="row"><?php echo esc_html__('Kunde', 'fxwp'); ?></th>
                        <td>
                            <pre class="scroll-box"><?php print_r(get_option('fxwp_customer')); ?></pre>
                        </td>
                    </tr>

                    <tr class="jsondata">
                        <th scope="row"><?php echo esc_html__('Projekt', 'fxwp'); ?></th>
                        <td>
                            <pre class="scroll-box"><?php print_r(get_option('fxwp_project')); ?></pre>
                        </td>
                    </tr>

                    <tr class="jsondata">
                        <th scope="row"><?php echo esc_html__('Pläne', 'fxwp'); ?></th>
                        <td colspan="2">
                            <pre class="scroll-box"><?php print_r(get_option('fxwp_plans')); ?></pre>
                        </td>
                    </tr>

                    <!-- .jsondata per deafult ausblenden und mit einem button einblenden können via js -->
                    <script>
                        document.querySelectorAll('.jsondata').forEach((el) => {
                            el.style.display = 'none';
                        });
                    </script>

                    <tr>
                        <td colspan="2">
                            <a
                                    class="button button-secondary"
                                    onclick="document.querySelectorAll('.jsondata').forEach((el) => {el.style.display = 'block';})">
                                P2 Daten anzeigen
                            </a>
                            <p class="description"> <?php echo esc_html__('Die Daten anzeigen, die aus P2 geladen werden.'); ?></p>
                        </td>
                    </tr>

                    <!-- Hinweis für lokale Instanzen -->
                    <tr>
                        <td colspan="2">
                            <p class="description">
                                <?php echo esc_html__('Hinweis: In lokalen Umgebung kann eine local.php erstellt werden, die direkt ausgeführt wird, z. B. um sich automatisch fxm_admin-Rechte zu geben.', 'fxwp'); ?>
                            </p>
                            <!-- und noch der hinweis dass in der options.php fxwp_storage_limit gesetzt und erhöht werden kann um mehr als 20GB speicher auf der webseite zu haben -->
                            <p class="description">
                                <?php echo esc_html__('Hinweis: In der options.php kann fxwp_storage_limit gesetzt und erhöht werden, um mehr als 20GB Speicher auf der Webseite zu haben.', 'fxwp'); ?>
                            </p>
                            <!-- informiere, dass fxm_customer_update_dashboard eine unterseite für kund:innen anzeigt, die selbst updates machen wollen -->
                            <p class="description">
                                <?php echo esc_html__('Hinweis: In der options.php kann fxm_customer_update_dashboard gesetzt werden, um eine Unterseite für Kund:innen anzuzeigen, die selbst Updates machen wollen.', 'fxwp'); ?>
                            </p>
                        </td>
                    </tr>

                <?php } ?>
            </table>
            <?php submit_button(); ?>
        </form>


        <?php
        // fxm_format_bytes
        function fxm_format_bytes($bytes, $precision = 2)
        {
            $units = array('B', 'KB', 'MB', 'GB', 'TB');

            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);

            $bytes /= (1 << (10 * $pow));

            return round($bytes, $precision) . $units[$pow];
        }

        ?>

        <!-- OPTIONS -->
        <div class="option-categories">
            <?php foreach ($fxm_options as $category) { ?>
                <h3><?php echo esc_html($category['title']); ?></h3>
                <table class="form-table">
                    <?php foreach ($category['options'] as $option => $data) { ?>
                        <tr>
                            <th scope="row"><?php echo esc_html($data['title']); ?></th>
                            <td>
                                <?php
                                switch ($data['type']) {
                                    case 'number':
                                        ?>
                                        <input type="number" name="<?php echo esc_attr($option); ?>"
                                               value="<?php echo esc_attr(get_option($option, $data['default'])); ?>"/>
                                        <?php
                                        break;
                                    case 'filesize':
                                        /* soll unterstützen dass man MB, GB und TB eingeben kann, und das umgerechnet wird. ausserdem soll in dem feld direkt angegeben sein in einem guten format. falls man gar nichts meint sind einfach nur byte gemeint  */
                                        $value = get_option($option, $data['default']);
                                        $value = fxm_format_bytes($value);
                                        ?>
                                        <input type="text" name="<?php echo esc_attr($option); ?>"
                                               value="<?php echo esc_attr($value); ?>"/>
                                        <?php
                                        break;
                                    case 'checkbox':
                                        ?>
                                        <label>
                                            <input type="checkbox" name="<?php echo esc_attr($option); ?>"
                                                   value="true" <?php checked(get_option($option, $data['default']), 'true'); ?>/>
                                            <?php echo esc_html($data['description']); ?>
                                        </label>
                                        <?php
                                        break;
                                }
                                ?>
                            </td>
                            <td>
                                <!-- hier ein script dass die vorherige variable speichert falls sich der wert ändert, hier kurz loading hinschreibt solange bis success true oder false von der serverantwort kommt, und dann gespeichert anzeigt oder fehler message per alert anzeigt. wir schicken es an diese seite, und verarbeiten das in einer funktion ganz oben  -->
                                <a class="save">Speichern</a>
                                <script>
                                    document.querySelector('.save').addEventListener('click', () => {
                                        // get the value
                                        let value = document.querySelector('input[name="<?php echo esc_attr($option); ?>"]').value;
                                        // send the value to the server
                                        fetch('options.php', {
                                            method: 'POST',
                                            body: new FormData({
                                                '<?php echo esc_attr($option); ?>': value
                                            })
                                        }).then((response) => {
                                            console.log(response);
                                            if (response.ok) {
                                                alert('Erfolgreich gespeichert');
                                            } else {
                                                alert('Fehler beim Speichern');
                                            }
                                        });
                                    });
                                </script>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
        </div>
        <!-- END OPTIONS -->


        <div class="flex">
            <form method="post" action="" class="inline">
                <?php echo esc_html__('Version', 'fxwp'); ?>
                <?php echo esc_html(FXWP_VERSION); ?>
                <a href="<?php echo esc_url(admin_url('index.php?fxwp_sync=1')); ?>">
                    <?php echo esc_html__('Prüfen auf Updates', 'fxwp'); ?>
                </a>
            </form>
            <?php if (current_user_can("fxm_admin")) { ?>
                <svg class="inline"
                     height="1.5em"
                     xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     onclick="document.querySelector('svg.inline').classList.toggle('flip');document.querySelector('.tag-update').classList.toggle('inline');"
                >
                    <title>chevron-left</title>
                    <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"/>
                </svg>
                <form method="post" action="index.php?fxwp_sync=1" class="tag-update">
                    <input type="text" name="fxwp_self_update_tag" placeholder="Tag" required style="width:60px"/>
                    <input type="submit" class="button button-primary"
                           value="<?php echo esc_html__('manuell installieren', 'fxwp'); ?>"/>
                </form>
            <?php } ?>
        </div>
    </div>
    <style>
        .scroll-box {
            max-height: 200px;
            overflow: auto;
            border: 1px solid #ccc;
            padding: 10px;
            width: calc(100vw - 400px);
        }

        .flex {
            display: flex;
            height: 2em;
            align-items: center;
        }

        form.inline {
            display: inline;
        }

        svg.inline {
            opacity: 0.5;
            transition: all 0.1s;
        }

        svg.inline:hover {
            cursor: pointer;
            -webkit-transform: rotate(180deg);
            -ms-transform: rotate(45deg);
            opacity: 1;
        }

        .tag-update {
            display: none;
        }

        .flip {
            -webkit-transform: rotate(180deg);
            -ms-transform: rotate(45deg);
            transform: rotate(180deg);
        }
    </style>
    <script>
        document.addEventListener('formdata', (e) => {
            let deactivated_features_list = {}
            document.getElementById('deactivated_features_list').querySelectorAll('input[type="checkbox"]').forEach((el) => {
                deactivated_features_list[el.id] = el.checked
            })
            // If fxwp plugin should be completely hidden, hide menu items as well
            if (deactivated_features_list['fxwp_deact_hide_plugin']) {
                deactivated_features_list['fxwp_deact_customer_settings'] = true
                deactivated_features_list['fxwp_deact_dashboards'] = true
            }
            e.formData.append('fxwp_deactivated_features', JSON.stringify(deactivated_features_list))

            let restricted_features_list = {}
            document.getElementById('restricted_features_list').querySelectorAll('input[type="checkbox"]').forEach((el) => {
                restricted_features_list[el.id] = el.checked
            })
            // // If fxwp plugin should be completely hidden, hide menu items as well
            // if (restricted_features_list['fxwp_deact_hide_plugin']) {
            //     restricted_features_list['fxwp_deact_customer_settings'] = true
            //     restricted_features_list['fxwp_deact_dashboards'] = true
            // }
            e.formData.append('fxwp_restricted_features', JSON.stringify(restricted_features_list))

            let debugging_options_list = {}
            document.getElementById('fxwp-debugging-options').querySelectorAll('input[type="checkbox"]').forEach((el) => {
                debugging_options_list[el.id] = el.checked
            })
            e.formData.append('fxwp_debugging_options', JSON.stringify(debugging_options_list))
            console.log(e.formData)
        });
    </script>
    <?php
}

function fxwp_register_settings()
{
    if (current_user_can("fxm_admin")) {
        register_setting('fxwp_settings_group', 'fxwp_api_key');
        register_setting('fxwp_settings_group', 'fxwp_google_fonts_remove');
        register_setting('fxwp_settings_group', 'fxwp_view_option', array('default' => 'erweitert'));
        register_setting('fxwp_settings_group', 'fxwp_deactivated_features');
        register_setting('fxwp_settings_group', 'fxwp_debugging_options');
        register_setting('fxwp_settings_group', 'fxwp_restricted_features');


    }
    register_setting('fxwp_settings_group', 'fxwp_favicon');
    register_setting('fxwp_settings_group', 'fxwp_logo');
    register_setting('fxwp_settings_group', 'fxwp_404_page');

}

add_action('admin_init', 'fxwp_register_settings');
