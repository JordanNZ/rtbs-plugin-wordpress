<?php
require_once("vendor/autoload.php");

/*
Plugin Name: RTBS Booking Plugin
Description: Tour Booking Plugin
Version: 1.0
*/
global $wpdb;
new rtbs_plugin($wpdb);

class rtbs_plugin {

    const STEP_AVAILABILITY = 1;
    const STEP_DETAILS = 2;
    const STEP_CONFIRM = 3;
    const STEP_PAYMENT = 4;

    private $jal_db_version = '1.0';
    private $wpdb;

    public function __construct($wpdb) {
        $this->wpdb = $wpdb;

        register_activation_hook(__FILE__, array($this, 'plugin_activate'));
        register_activation_hook(__FILE__, array($this, 'plugin_activate_init'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivate'));

        add_action('admin_menu', array($this, 'build_admin_menu'));

        add_shortcode('rtbs_plugin', array($this, 'rtbs_plugin_main'));
        add_shortcode('rtbs_show_ticket', array($this, 'rtbs_show_ticket'));

    }


    public function plugin_activate() {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE rtbs_settings (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `api_key` varchar(255) NOT NULL,
                  `password` varchar(255) NOT NULL,
                  `supplier_key` varchar(255) NOT NULL,
                  `rtbs_domain` varchar(255) NOT NULL,
                  `is_show_promocode` int(11) NOT NULL,
                  `success_url` varchar(255) NOT NULL,
                  `terms_cond` text NOT NULL,
                  `tour_keys` varchar(255) NOT NULL COMMENT 'separated by comma',
                  `page_title` varchar(255) NOT NULL,
                  `section_title` varchar(255) NOT NULL,
                  `title_first_page` varchar(255) NOT NULL,
                  `content_first_page` text NOT NULL,
                  `is_show_remaining` int(11) NOT NULL,
                  `css_style` longtext NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET={$charset_collate}";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('jal_db_version', $this->jal_db_version);
    }

    public function plugin_activate_init() {
        $this->wpdb->insert('rtbs_settings', array('id' => 1));
    }


    public function plugin_deactivate() {
        $this->wpdb->query('DROP TABLE rtbs_settings');
    }


    public function build_admin_menu() {
        add_menu_page('RTBS', 'RTBS', '', __FILE__, 'moveing_company', plugins_url('img/settings.png', __FILE__));
        add_submenu_page(__FILE__, 'Shortcode', 'Shortcode', 'administrator', 'shortcode-rtbs-booking', array($this, 'rtbs_admin_shortcode'));
        add_submenu_page(__FILE__, 'CSS Style', 'CSS Style', 'administrator', 'css-style-rtbs-booking', array($this, 'rtbs_admin_css_style'));
        add_submenu_page(__FILE__, 'Settings', 'Settings', 'administrator', 'adminSettings', array($this, 'rtbs_admin_settings'));
    }

    private function select_settings() {
        return $this->wpdb->get_row("SELECT * FROM rtbs_settings");
    }


    public function rtbs_admin_settings() {
        $num_rows_updated = 0;
        if (isset($_POST['save_set'])) {
            $num_rows_updated = $this->wpdb->update(
                'rtbs_settings',
                array(
                    'api_key' => $_POST['api_key'],
                    'supplier_key' => $_POST['supplier_key'],
                    'rtbs_domain' => $_POST['rtbs_domain'],
                    'is_show_promocode' => (isset($_POST['is_show_promocode'])) ? 1 : 0,
                    'success_url' => $_POST['success_url'],
                    'page_title' => $_POST['page_title'],
                    'section_title' => $_POST['section_title'],
                    'title_first_page' => $_POST['title_first_page'],
                    'content_first_page' => $_POST['content_first_page'],
                    'terms_cond' => $_POST['terms_cond'],
                    'is_show_remaining' => (isset($_POST['is_show_remaining'])) ? 1 : 0,
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
        $settings = $this->select_settings();
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
                        <td><input name="api_key" type="text" id="api_key" value="<?= $settings->api_key; ?>" class="regular-text">
                            <p class="description">Your API key.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="supplier_key">Supplier Key</label></th>
                        <td><input name="supplier_key" type="text" id="supplier_key" value="<?= $settings->supplier_key; ?>" class="regular-text">
                            <p class="description">Set your Supplier key.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="rtbs_domain">RTBS Domain</label></th>
                        <td><input name="rtbs_domain" type="text" id="rtbs_domain" value="<?= (!empty($settings->rtbs_domain)) ? $settings->rtbs_domain : 'https://rtbslive.com'; ?>" class="regular-text">
                            <p class="description">Set your RTBS domain.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="is_show_promocode">Show Promo Code</label></th>
                        <td><input name="is_show_promocode" type="checkbox" id="is_show_promocode" <?= ($settings->is_show_promocode) ? 'checked' : '' ?> class="regular-checkbox" value="1">
                            <p class="description">Show/Hide</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="is_show_remaining">Show Remaining</label></th>
                        <td><input name="is_show_remaining" type="checkbox" id="is_show_remaining" class="regular-checkbox" <?= ($settings->is_show_remaining) ? 'checked' : '' ?>  value="1">
                            <p class="description">Show/Hide</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="success_url">Success URI</label></th>
                        <td><input name="success_url" type="text" id="success_url" value="<?= $settings->success_url; ?>" class="regular-text">
                            <p class="description">Return or Success url ( For display ticket you must put [rtbs_show_ticket] shortcode to your return url page ).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="page_title">Page Title</label></th>
                        <td><input name="page_title" type="text" id="page_title" value="<?= $settings->page_title; ?>" class="regular-text">
                            <p class="description">Your custom progress bar page title here, Separated by comma (Default: AVAILABILITY,DETAILS,CONFIRM,PAYMENT)</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="section_title">Section Title</label></th>
                        <td><input name="section_title" type="text" id="section_title" value="<?= $settings->section_title; ?>" class="regular-text">
                            <p class="description">Your custom progress bar section title here, Separated by comma (Default: Number of People,Your Details,Pickup).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="title_first_page">Title First Page</label></th>
                        <td><input name="title_first_page" type="text" id="title_first_page" value="<?= $settings->title_first_page; ?>" class="regular-text">
                            <p class="description">Your first page title.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="content_first_page">Content First Page</label></th>
                        <td><?php wp_editor($settings->content_first_page, 'content_first_page', $options = array('media_buttons' => false)); ?>
                            <p class="description">Your first page content.</p>
                        </td>
                    </tr>


                    <tr>
                        <th scope="row"><label for="terms_cond">Terms & Conditions</label></th>
                        <td>
                            <?php wp_editor($settings->terms_cond, 'terms_cond', $options = array('media_buttons' => false)); ?>
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

    public function rtbs_admin_shortcode() {
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


    public function rtbs_admin_css_style() {
        $num_rows_updated = 0;

        if (isset($_POST['save_set'])) {
            $num_rows_updated = $this->wpdb->update(
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

        $settings = $this->select_settings();

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
                        <th scope="row"><label for="css_style">CSS Style</label></th>
                        <td>
                            <textarea name="css_style" rows="30" cols="50" id="css_style" class="large-text code"><?php echo(!empty($settings->css_style) ? $settings->css_style : ''); ?></textarea>
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


    public function rtbs_plugin_main($atts) {

        date_default_timezone_set('Pacific/Auckland');

        // shortcode with attribute or parameter
        if (isset($atts['tour_key'])) {
            $shortcode_tour_keys = explode(',', $atts['tour_key']);
        } else {
            $shortcode_tour_keys = null;
        }

        $settings = $this->select_settings();

        if (empty($settings->rtbs_domain)) {
            die('Error: RTBS Domain Not Set');
        }

        if (empty($settings->supplier_key)) {
            die('Error: Supplier Key Not Set');
        }

        $hdStep = (isset($_POST['hd_step'])) ? $_POST['hd_step'] : self::STEP_AVAILABILITY;
        $date = (isset($_REQUEST['tdate'])) ? $_REQUEST['tdate'] : date('Y-m-d', strtotime("+1 day"));

        $credentials = array(
            'host' => $settings->rtbs_domain,
            'key' => $settings->api_key,
        );

        $booking_service = new Rtbs\ApiHelper\BookingServiceImpl($credentials);

        // payment redirects, so do that first
        if ($hdStep == self::STEP_PAYMENT) {
            $this->step_payment($settings, $booking_service);
        }

        ?>
        <link rel="stylesheet" href="https://bootswatch.com/cerulean/bootstrap.css" media="screen" title="no title">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.css"
              media="screen" title="no title">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <style>
            .numberCircle {
                border-radius: 50%;
                behavior: url(PIE.htc); /* remove if you don't care about IE8 */

                width: 39px;
                height: 39px;
                padding: 6px;

                background: #fff;
                border: 2px solid #666;
                color: #666;
                text-align: center;

                font: 22px Arial, sans-serif;
            }

            .title-rtbs {
                color: #e74c3c;
            }
        </style>
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

        <script>
            $(function () {
                $("#datepicker").datepicker({dateFormat: 'yy-mm-dd'});
            });

            function selectDate(str) {
                window.location.href = "index.php?tdate=" + str;
            }
        </script>

        <!-- CSS Styles Custom --->
        <style>
            <?= $settings->css_style; ?>
        </style>

        <div class="container rtbs-container" style="width:100%;">

            <?php $this->render_navbar($settings, $hdStep); ?>

            <p>&nbsp;</p>

            <?php if (in_array($hdStep, array(self::STEP_DETAILS, self::STEP_CONFIRM, self::STEP_PAYMENT))): ?>
                <h3 class="tour_name"><?= htmlentities($_POST['hd_tour_name']); ?></h3>
                <h4>Selected Date & Time: <span
                        style="color:#000;"><?= date('l dS F Y h:i a', strtotime($_POST['hd_tour_date_time'])); ?></span>
                </h4>
            <?php else: ?>
                <h2 class="title-first-page"><?= htmlentities($settings->title_first_page); ?></h2>
                <h4>
                    Showing: <?= date('l dS F Y', strtotime($date)); ?></h4>
                <p>
                    <?= $settings->content_first_page; ?>
                </p>
                <p><i class="fa fa-calendar"></i>
                    <input onchange="selectDate(this.value)" type="text" placeholder="Change Date" id="datepicker" value="<?= $date; ?>">
                </p>
            <?php endif; ?>


            <div class="row rtbs-tours-step-<?= $hdStep; ?>">
                <?php

                    switch ($hdStep) {
                        case self::STEP_DETAILS:
                            $this->step_details($settings, $booking_service);
                            break;

                        case self::STEP_CONFIRM:
                            $this->step_confirm($settings);
                            break;

                        case self::STEP_AVAILABILITY:
                        default:
                            $this->step_availability($settings, $booking_service, $shortcode_tour_keys, $date);
                            break;
                    }

                ?>
            </div>
        </div>


        <script>
            $(document).ready(function () {

                function isEmail(email) {
                    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                    return regex.test(email);
                }

                $('select.nPeople').on('change', function () {
                    var totalAmount= 0.00,
                        totalPax = 0;

                    $('select.nPeople').each(function () {
                        totalAmount += parseFloat($(this).data('rate')) * parseInt($(this).val(), 10);
                        totalPax += parseInt($(this).data('pax'), 10) * parseInt($(this).val(), 10);
                    });

                    var numRemaining = $('#hd-remaining').val(),
                        $htmlTotalPrice = $('#totalPrice');

                    if (totalPax > numRemaining) {
                        $htmlTotalPrice.css({color: 'red'});
                        $htmlTotalPrice.html("Only " + numRemaining + " places remaining");
                    } else {
                        $htmlTotalPrice.css({color: 'black'});
                        $htmlTotalPrice.html('Total: $' + totalAmount.toFixed(2));
                    }
                });

                $('#details-form').on('submit', function () {
                    var totalPax = 0,
                        errors = [],
                        numRemaining = $('#hd-remaining').val();

                    $('select.nPeople').each(function () {
                        totalPax += parseInt($(this).data('pax'), 10) * parseInt($(this).val(), 10);
                    });

                    if (!$('#rtbsFname').val()) {
                        errors.push('First Name is required');
                    }

                    if (!$('#rtbsLname').val()) {
                        errors.push('Last Name is required');
                    }

                    if (!$('#rtbsEmail').val()) {
                        errors.push('Email is required');
                    } else if (!isEmail($('#rtbsEmail').val())) {
                        errors.push('Email is not valid');
                    }

                    if (!$('#rtbsPhone').val()) {
                        errors.push('Phone is required');
                    }

                    if (totalPax > numRemaining) {
                        errors.push("Only " + numRemaining + " places remaining");
                    }

                    if (errors.length) {
                        $('.alert-danger').show().html(errors.join('<br>'));
                        return false;
                    } else {
                        return true;
                    }
                });

            });

        </script>

        <?php
    }


    public function rtbs_show_ticket() {
        $settings = $this->select_settings();
        $ticket_url = $settings->rtbs_domain . "/api/ticket?token=" . $_REQUEST['token'];
        return '<p><iframe src="' . $ticket_url . '" frameborder="0" style="overflow:hidden;height:1000px;width:100%" height="100%" width="100%"></iframe></p>';
    }


    /**
     * @param stdClass $settings
     */
    private function step_confirm($settings) {

        $price_rates = $_POST['price_rate'];
        $price_names = $_POST['hd_price_name'];

        $total = 0;
        $price_qtys = array();

        foreach ($_POST['price_qty'] as $idx => $qty) {
            if ($qty > 0) {
                $price_qtys[$idx] = $qty;

                $total += ($qty * $price_rates[$idx]);
            }
        }

        ?>

            <form action="" method="post">

                <table class="table">
                    <tr>
                        <td colspan="2">
                            <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;">Confirm Your Booking </p>
                        </td>
                    </tr>
                    <tr>
                        <td>Tour Date Time</td>
                        <td><?= date('l dS F Y h:i A', strtotime($_POST['hd_tour_date_time'])); ?></td>
                    </tr>

                    <?php foreach ($price_qtys as $idx => $qty): ?>
                        <tr>
                            <td><?= htmlentities($price_names[$idx] . ' x ' . $qty); ?></td>
                            <td><?= '$' . number_format($price_rates[$idx] * $qty, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <tr>
                        <td>
                            Total Price:
                        </td>
                        <td>
                            <?= '$' . number_format($total, 2); ?>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;">Your Details</p>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Name
                        </td>
                        <td>
                            <?= htmlentities($_POST['fname']) . ' ' . htmlentities($_POST['lname']); ?>
                            <input type="hidden" name="fname" value="<?= htmlentities($_POST['fname']); ?>">
                            <input type="hidden" name="lname" value="<?= htmlentities($_POST['lname']); ?>">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Email
                        </td>
                        <td>
                            <?= htmlentities($_POST['email']); ?>
                            <input type="hidden" name="email" value="<?= htmlentities($_POST['email']); ?>">
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Phone
                        </td>
                        <td>
                            <?= htmlentities($_POST['phone']); ?>
                            <input type="hidden" name="phone" value="<?= htmlentities($_POST['phone']); ?>">
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;">Terms &amp; Conditions </p>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <p class="terms_cond"><?= $settings->terms_cond; ?></p>
                            <input type="checkbox" name="tandc" id="chk_conf" value="0"> I have read and accept the
                            Terms and Conditions.<br/><br/>
                        </td>
                    </tr>

                </table>

                <div class="hidden_hd">
                    <input type="hidden" name="hd_step" value="4">
                    <input type="hidden" name="hd_tour_key" value="<?= htmlentities($_POST['hd_tour_key']); ?>">
                    <input type="hidden" name="hd_date" value="<?= htmlentities($_POST['hd_date']); ?>">
                    <input type="hidden" name="hd_tour_name" value="<?= htmlentities($_POST['hd_tour_name']); ?>">
                    <input type="hidden" name="hd_tour_date_time" value="<?= htmlentities($_POST['hd_tour_date_time']); ?>">
                    <?php foreach ($price_qtys as $idx => $qty): ?>
                        <input type="hidden" name="price_qty[<?= $idx; ?>]" value="<?= $qty; ?>">
                    <?php endforeach; ?>
                </div>

                <button id="confirm_pay" disabled type="submit" class="btn btn-primary pull-right"
                        name="confirm_payment">Confirm &amp; Make Payment
                </button>

            </form>

            <script>
                $(document).ready(function () {
                    $('#chk_conf').change(function () {
                        if (this.checked) {
                            $('#confirm_pay').prop('disabled', false);
                        } else {
                            $('#confirm_pay').prop('disabled', true);
                        }
                    });
                });

            </script>

        <?php
    }


    /**
     * @param $settings
     * @param \Rtbs\ApiHelper\BookingServiceImpl $booking_service
     */
    private function step_details($settings, $booking_service) {

        $section_titles = explode(",", $settings->section_title);
        $pickups = $booking_service->get_pickups($_POST['hd_tour_key']);
//        $tours = $booking_service->get_tours(array($_POST['hd_tour_key']));
        $sessions = $booking_service->get_sessions_and_advance_dates($settings->supplier_key, array($_POST['hd_tour_key']), $_POST['hd_date']);

        /** @var Rtbs\ApiHelper\Models\Session[] $sessions */
        $sessions = $sessions['sessions'];
        $prices = [];

        foreach ($sessions as $session) {
            if ($session->get_datetime() == $_POST['hd_tour_date_time'] && $session->get_tour_key() == $_POST['hd_tour_key']) {
                $prices = $session->get_prices();
                break;
            }
        }

        $qty_range = range(0, $_POST['hd_remaining']);
    ?>

        <div style="border:1px solid #bdc3c7; padding:10px;" class="col-md-12">
            <div class="col-md-2"></div>
            <div class="col-md-8">

                <center>

                    <form class="form-horizontal" action="" method="post" id="details-form">
                        <fieldset>
                            <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"><?php echo(!empty($section_titles[0]) ? $section_titles[0] : 'Number of People'); ?> </p>
                            <?php foreach ($prices as $price): ?>

                                <div class="form-group">
                                    <label for="select" class="col-md-6 text-left"><?= $price->get_name(); ?></label>
                                    <div class="col-md-6">
                                        <div class="col-md-6">
                                            <input type="hidden" name="price_rate[<?= $price->get_price_key(); ?>]" value="<?= $price->get_rate(); ?>">
                                            <select class="form-control nPeople" name="price_qty[<?= $price->get_price_key(); ?>]" data-rate="<?= $price->get_rate(); ?>" data-pax="<?= $price->get_passenger_count(); ?>">
                                                <?php foreach ($qty_range as $qty): ?>
                                                    <option value="<?= $qty; ?>"><?= $qty; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="text-right"><?= '$' . $price->get_rate(); ?></div>
                                        </div>

                                        <input type="hidden" name="hd_price_name[<?= $price->get_price_key(); ?>]" value="<?= $price->get_name(); ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <p style="font-size:16px;" id="totalPrice">Total: $0.00</p>


                            <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                               class=""><?php echo(!empty($section_titles[1]) ? $section_titles[1] : 'Your Details'); ?> </p>


                            <div class="form-group">
                                <label for="rtbsFname" class="col-lg-3">First Name</label>
                                <div class="col-lg-9">
                                    <input id="rtbsFname" class="form-control" type="text" name="fname" value="">

                                </div>
                            </div>

                            <div class="form-group">
                                <label for="rtbsLname" class="col-lg-3">Last Name</label>
                                <div class="col-lg-9">
                                    <input id="rtbsLname" class="form-control" type="text" name="lname" value="">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="rtbsEmail" class="col-lg-3">Email</label>
                                <div class="col-lg-9">
                                    <input id="rtbsEmail" class="form-control" type="email" name="email" value="">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="rtbsPhone" class="col-lg-3">Phone</label>
                                <div class="col-lg-9">
                                    <input id="rtbsPhone" class="form-control" type="tel" name="phone" value="">
                                </div>
                            </div>


                            <?php if ($settings->is_show_promocode): ?>
                                <div class="form-group">
                                    <label for="rtbsPromo" class="col-lg-3">Promo Code</label>
                                    <div class="col-lg-9">
                                        <input id="rtbsPromo" class="form-control" type="text" name="promo" value="">
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (count($pickups) > 0): ?>
                                <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;">Pickup </p>

                                <div class="form-group">

                                    <div class="col-lg-12">
                                        <select class="form-control" name="pickup_key">
                                            <option value="">Select a Pickup Point</option>
                                            <?php foreach ($pickups as $pkup): ?>
                                                <option value="<?= $pkup->get_pickup_key(); ?>"><?= ($pkup->get_name() == '' ? 'No Pickup available' : $pkup->get_name() . ' - ' . $pkup->get_place() . ' - ' . date('h:i a', strtotime($_POST['hd_tour_date_time'] . ' -' . $pkup->get_minutes() . ' minutes'))); ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="hidden_hd">
                                <input type="hidden" name="hd_step" value="3">
                                <input type="hidden" name="hd_remaining" id="hd-remaining" value="<?= htmlentities($_POST['hd_remaining']); ?>"/>
                                <input type="hidden" name="hd_tour_key" value="<?= htmlentities($_POST['hd_tour_key']); ?>">
                                <input type="hidden" name="hd_date" value="<?= htmlentities($_POST['hd_date']); ?>">
                                <input type="hidden" name="hd_tour_name" value="<?= htmlentities($_POST['hd_tour_name']); ?>">
                                <input type="hidden" name="hd_tour_date_time" value="<?= htmlentities($_POST['hd_tour_date_time']); ?>">
                            </div>

                            <div class="alert alert-danger" style="display: none;"></div>

                            <div class="form-group">
                                <div class="col-lg-10">
                                    <button type="submit" class="btn btn-primary pull-right" name="button">NEXT &gt;</button>
                                </div>
                            </div>


                        </fieldset>
                    </form>

                </center>
            </div>
        </div>

        <div class="col-md-2"></div>

        <?php
    }


    /**
     * @param $settings
     * @param \Rtbs\ApiHelper\BookingServiceImpl $booking_service
     * @param array $shortcode_tour_keys
     * @param string $date
     */
    private function step_availability($settings, $booking_service, $shortcode_tour_keys, $date) {

        $supplier = $booking_service->get_supplier($settings->supplier_key);

        if ($shortcode_tour_keys) {
            $tour_keys = $shortcode_tour_keys;
        } else {
            $tour_keys = array();
            foreach ($supplier->get_tours() as $tour) {
                $tour_keys[] = $tour->get_tour_key();
            }
        }

        $tours = $booking_service->get_tours($tour_keys);

        if (empty($tours)) {
            die('Error: No Tours Found');
        }

        $sessions_and_advanced_dates = $booking_service->get_sessions_and_advance_dates($supplier->get_supplier_key(), $tour_keys, $date);

        /** @var \Rtbs\ApiHelper\Models\Session[] $sessions */
        $sessions = $sessions_and_advanced_dates['sessions'];

        $sessions_by_tour = array();

        foreach ($sessions as $session) {
            $sessions_by_tour[$session->get_tour_key()][] = $session;
        }

        foreach ($tours as $tour) {
            if (!empty($sessions_by_tour[$tour->get_tour_key()])) {
                /** @var \Rtbs\ApiHelper\Models\Session[] $sessions */
                $sessions = $sessions_by_tour[$tour->get_tour_key()];
                ?>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading"><h4><?= htmlentities($tour->get_name()); ?></h4></div>
                        <div class="panel-body">

                            <?php foreach ($sessions as $session): ?>

                                <form action="" method="post">
                                    <p>
                                        <?= date('h:i a', strtotime($session->get_datetime())) . ($settings->is_show_remaining ? ', ' . $session->get_remaining() . ' remaining' : ''); ?>
                                        <input type="hidden" name="hd_step" value="2">
                                        <input type="hidden" name="hd_remaining" value="<?= $session->get_remaining(); ?>"/>
                                        <input type="hidden" name="hd_tour_key" value="<?= $tour->get_tour_key(); ?>">
                                        <input type="hidden" name="hd_date" value="<?= $date; ?>">
                                        <input type="hidden" name="hd_tour_name" value="<?= $tour->get_name(); ?>">
                                        <input type="hidden" name="hd_tour_date_time" value="<?= $session->get_datetime(); ?>">
                                        <button <?= ($session->is_open()) ? '' : 'disabled' ?> class="btn btn-primary" type="submit" name="button"><?= $session->get_state(); ?></button>
                                    </p>
                                </form>
                            <?php endforeach; ?>

                        </div>
                    </div>
                </div>
                <?php
            }
        }
    }

    /**
     * @param stdClass $settings
     * @param \Rtbs\ApiHelper\BookingServiceImpl $booking_service
     */
    private function step_payment($settings, $booking_service) {

        $booking = new Rtbs\ApiHelper\Models\Booking();
        $booking->set_tour_key($_POST['hd_tour_key']);
        $booking->set_datetime($_POST['hd_tour_date_time']);
        $booking->set_first_name($_POST['fname']);
        $booking->set_last_name($_POST['lname']);
        $booking->set_email($_POST['email']);
        $booking->set_phone($_POST['phone']);

        // TODO call promo api
        if (!empty($_POST['promo'])) {
            $booking->set_promo_key($_POST['promo']);
        }

        if (!empty($settings->success_url)) {
            $booking->set_return_url($settings->success_url);
        }

        // TODO pickup keys dont work yet
        if (!empty($_POST['pickup_key'])) {
            $booking->set_pickup_key($_POST['pickup_key']);
        }

        $price_qtys = $_POST['price_qty'];
        foreach ($price_qtys as $key => $qty) {
            $booking->add_price_selection_keys($key, $qty);
        }

        $return_url = $booking_service->make_booking($booking);

        if ($return_url == '') {
            die('<p class="rtbs_error_msg">Error. insufficient capacity or session closed. </p>');
        } else {
            echo '<script>window.location.href="' . $return_url . '"</script>';
            exit;
        }
    }


    private function render_navbar($settings, $hdStep) {
        $page_titles = explode(",", $settings->page_title);

        ?>
        <div style="background-color:#ecf0f1; height:70px;" class="row hidden-xs hidden-sm">
            <center>

                <div
                    class="col-md-3 col-sm-3 col-xs-3 <?= ($hdStep == self::STEP_AVAILABILITY) ? 'selected' : '' ?>">
                    <div style="margin-top: 15px;" class="col-md-2 numberCircle">1</div>
                    <div class="col-md-10" style="margin-top: -32px; margin-left: 24px;">
                        <p class="text-primary"><?php echo(!empty($page_titles[0]) ? $page_titles[0] : 'AVAILABILITY'); ?></p>

                    </div>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-3 <?php echo($hdStep == self::STEP_DETAILS ? 'selected' : ''); ?>">
                    <div style="margin-top: 15px;" class="col-md-2 numberCircle">2</div>
                    <div class="col-md-10" style="margin-top: -32px; margin-left: 24px;">
                        <p class="text-primary"><?php echo(!empty($page_titles[1]) ? $page_titles[1] : 'DETAILS'); ?></p>

                    </div>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-3 <?php echo($hdStep == self::STEP_CONFIRM ? 'selected' : ''); ?>">
                    <div style="margin-top: 15px;" class="col-md-2 numberCircle">3</div>
                    <div class="col-md-10" style="margin-top: -32px; margin-left: 24px;">
                        <p class="text-primary"><?php echo(!empty($page_titles[2]) ? $page_titles[2] : 'CONFIRM'); ?></p>

                    </div>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-3 <?php echo($hdStep == self::STEP_PAYMENT ? 'selected' : ''); ?>">
                    <div style="margin-top: 15px;" class="col-md-2 numberCircle">4</div>
                    <div class="col-md-10" style="margin-top: -32px; margin-left: 24px;">
                        <p class="text-primary"><?php echo(!empty($page_titles[3]) ? $page_titles[3] : 'PAYMENT'); ?></p>

                    </div>
                </div>

            </center>
        </div>
        <?php

    }

}