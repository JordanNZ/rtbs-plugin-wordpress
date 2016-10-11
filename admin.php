<?php

// admin settings---------------------------------------
function rtbs_admin_settings() {
    global $wpdb;
    $num_rows_updated = 0;

    if (isset($_POST['save_set'])) {

        $num_rows_updated = $wpdb->update(
            'rtbs_settings',
            array(
                'api_key' => $_POST['api_key'],
                'supplier_key' => $_POST['supplier_key'],
                'rtbs_domain' => $_POST['rtbs_domain'],
                'promo_code' => (isset($_POST['promo_code'])) ? 0 : 1,
                'success_url' => $_POST['success_url'],
                'page_title' => $_POST['page_title'],
                'section_title' => $_POST['section_title'],
                'title_first_page' => $_POST['title_first_page'],
                'content_first_page' => $_POST['content_first_page'],
                'terms_cond' => $_POST['t_c'],
                'remaining' => (isset($_POST['remaining'])) ? 0 : 1,
            ),
            array('id' => 1),
            array(
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d'
            ),
            array('%d')
        );
    }

    $getRowval = selectSettings();

?>

    <div class='wrap'>

        <h2>RTBS Settings</h2>

        <?php if ($num_rows_updated === 1): ?>

            <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                <p><strong>Settings saved.</strong></p>
                <button type="button" class="notice-dismiss"><span
                        class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
        <?php endif; ?>

        <form class="" action="" method="post">

            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="api_key">API Key</label></th>
                    <td><input name="api_key" type="text" id="api_key" value="<?php echo $getRowval->api_key; ?>"
                               class="regular-text">
                        <p class="description">Your API key.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="supplier_key">Supplier Key</label></th>
                    <td><input name="supplier_key" type="text" id="supplier_key"
                               aria-describedby="tagline-description" value="<?php echo $getRowval->supplier_key; ?>"
                               class="regular-text">
                        <p class="description">Set your Supplier key.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="rtbs_domain">RTBS Domain</label></th>
                    <td><input name="rtbs_domain" type="text" id="rtbs_domain"
                               aria-describedby="tagline-description"
                               value="<?php echo (isset($getRowval->rtbs_domain)) ? $getRowval->rtbs_domain : 'https://rtbslive.com'; ?>"
                               class="regular-text">
                        <p class="description">Set your RTBS domain.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="blogdescription">Promo Code</label></th>
                    <td><input name="promo_code" type="checkbox" id="blogdescription"
                               aria-describedby="tagline-description" <?php echo ($getRowval->promo_code) == 0 ? 'checked' : '' ?>
                               class="regular-checkbox" value="0">
                        <p class="description">Show/Hide</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="remaining">Remaining</label></th>
                    <td><input name="remaining" type="checkbox" id="remaining"
                               aria-describedby="tagline-description"
                               class="regular-checkbox" <?php echo ($getRowval->remaining) == 0 ? 'checked' : '' ?>
                               value="0">
                        <p class="description">Show/Hide</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="success_url">Success URI</label></th>
                    <td><input name="success_url" type="text" id="success_url"
                               aria-describedby="tagline-description" value="<?php echo $getRowval->success_url; ?>"
                               class="regular-text">
                        <p class="description">Return or Success url ( For display ticket you must put
                            [rtbs_show_ticket] shortcode to your return url page ).</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="page_title">Page Title</label></th>
                    <td><input name="page_title" type="text" id="page_title" aria-describedby="tagline-description"
                               value="<?php echo $getRowval->page_title; ?>" class="regular-text">
                        <p class="description">Your custom progress bar page title here, Separated by comma ( Default:
                            AVAILABILITY,DETAILS,CONFIRM,PAYMENT )</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="section_title">Section Title</label></th>
                    <td><input name="section_title" type="text" id="section_title"
                               aria-describedby="tagline-description" value="<?php echo $getRowval->section_title; ?>"
                               class="regular-text">
                        <p class="description">Your custom progress bar section title here, Separated by comma (
                            Default: Number of People,Your Details,Pickup ).</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="title_first_page">Title First Page</label></th>
                    <td><input name="title_first_page" type="text" id="title_first_page"
                               aria-describedby="tagline-description"
                               value="<?php echo $getRowval->title_first_page; ?>" class="regular-text">
                        <p class="description">Your first page title.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="content_first_page">Content First Page</label></th>
                    <td><?php wp_editor($getRowval->content_first_page, 'content_first_page', $settings = array('media_buttons' => false)); ?>
                        <p class="description">Your first page content.</p>
                    </td>
                </tr>


                <tr>
                    <th scope="row"><label for="t_c">Terms & Conditions</label></th>
                    <td>
                        <?php wp_editor($getRowval->terms_cond, 't_c', $settings = array('media_buttons' => false)); ?>
                    </td>
                </tr>


                </tbody>
            </table>

            <p>
                <button type="submit" name="save_set" class="button-primary">Save Changes</button>
            </p>
        </form>

    </div>

<?php
}


function rtbs_admin_shortcode() {

    echo '<h1>RTBS Booking Shortcode</h1>';
    echo '<div class="card pressthis">';
    echo '<h2>Shortcode for RTBS Booking Plugin</h2>';
    echo '<code>[rtbs_plugin]</code>';
    echo '<h2>Shortcode for RTBS Booking Plugin with tour_key</h2>';
    echo '<code>[rtbs_plugin tour_key="tour_key1,tour_key2"]</code>';
    echo '<p class="description">Seperated by comma.</p>';
    echo '<h2>Shortcode for display ticket to your return url page</h2>';
    echo '<code>[rtbs_show_ticket]</code>';
    echo '</div>';
}



function rtbs_admin_css_style() {
    global $wpdb;
    $num_rows_updated = 0;

    if (isset($_POST['save_set'])) {

        $num_rows_updated = $wpdb->update(
            'rtbs_settings',
            array(
                'css_style' => $_POST['css_style']
            ),
            array('id' => 1),
            array(
                '%s'
            ),
            array('%d')
        );
    }

    $getRowval = selectSettings();

?>
    <div class="wrap">
        <h1>RTBS Booking CSS Style</h1>

        <?php if ($num_rows_updated === 1): ?>

            <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                <p><strong>Settings saved.</strong></p>
                <button type="button" class="notice-dismiss"><span
                        class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
        <?php endif; ?>

        <form class="" action="" method="post">

            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="blogname">CSS Style</label></th>
                    <td>
                        <textarea name="css_style" rows="30" cols="50" id="blogname"
                                  class="large-text code"><?php echo(!empty($getRowval->css_style) ? $getRowval->css_style : ''); ?></textarea>
                        <p class="description">Write your CSS Styles for overwrite the default style. (Optional).</p>
                    </td>
                </tr>
                </tbody>
            </table>

            <p>
                <button type="submit" name="save_set" class="button-primary">Save Changes</button>
            </p>
        </form>
    </div>

<?php

}