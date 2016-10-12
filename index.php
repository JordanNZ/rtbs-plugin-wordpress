<?php
require_once("vendor/autoload.php");

/*
Plugin Name: RTBS Booking Plugin
Description: Tour Booking Plugin
Version: 1.0
*/

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

        register_activation_hook(__FILE__, [$this, 'activation_hook']);
        register_deactivation_hook(__FILE__, [$this, 'deactivation_hook']);

        add_action('admin_menu', [$this, 'build_admin_menu']);

        add_shortcode('rtbs_plugin', [$this, 'rtbs_plugin_main']);
        add_shortcode('rtbs_show_ticket', [$this, 'rtbs_show_ticket']);

    }


    public function activation_hook() {
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
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET={$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('jal_db_version', $this->jal_db_version);

        $this->wpdb->insert('rtbs_settings', ['id' => 1]);
    }

    public function deactivation_hook() {
        $this->wpdb->query('DROP TABLE rtbs_settings');
    }


    public function build_admin_menu() {
        add_menu_page('RTBS', 'RTBS', '', __FILE__, 'moveing_company', plugins_url('img/settings.png', __FILE__));
        add_submenu_page(__FILE__, 'Shortcode', 'Shortcode', 'administrator', 'shortcode-rtbs-booking', [$this, 'rtbs_admin_shortcode']);
        add_submenu_page(__FILE__, 'CSS Style', 'CSS Style', 'administrator', 'css-style-rtbs-booking', [$this, 'rtbs_admin_css_style']);
        add_submenu_page(__FILE__, 'Settings', 'Settings', 'administrator', 'adminSettings', [$this, 'rtbs_admin_settings']);
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
                        <td><input name="api_key" type="text" id="api_key" value="<?php echo $settings->api_key; ?>"
                                   class="regular-text">
                            <p class="description">Your API key.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="supplier_key">Supplier Key</label></th>
                        <td><input name="supplier_key" type="text" id="supplier_key"
                                   aria-describedby="tagline-description" value="<?php echo $settings->supplier_key; ?>"
                                   class="regular-text">
                            <p class="description">Set your Supplier key.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="rtbs_domain">RTBS Domain</label></th>
                        <td><input name="rtbs_domain" type="text" id="rtbs_domain"
                                   aria-describedby="tagline-description"
                                   value="<?php echo (isset($settings->rtbs_domain)) ? $settings->rtbs_domain : 'https://rtbslive.com'; ?>"
                                   class="regular-text">
                            <p class="description">Set your RTBS domain.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="is_show_promocode">Show Promo Code</label></th>
                        <td><input name="is_show_promocode" type="checkbox" id="is_show_promocode"
                                   aria-describedby="tagline-description" <?php echo ($settings->is_show_promocode) ? 'checked' : '' ?>
                                   class="regular-checkbox" value="0">
                            <p class="description">Show/Hide</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="is_show_remaining">Show Remaining</label></th>
                        <td><input name="is_show_remaining" type="checkbox" id="is_show_remaining"
                                   aria-describedby="tagline-description"
                                   class="regular-checkbox" <?php echo ($settings->is_show_remaining) ? 'checked' : '' ?>
                                   value="0">
                            <p class="description">Show/Hide</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="success_url">Success URI</label></th>
                        <td><input name="success_url" type="text" id="success_url"
                                   aria-describedby="tagline-description" value="<?php echo $settings->success_url; ?>"
                                   class="regular-text">
                            <p class="description">Return or Success url ( For display ticket you must put
                                [rtbs_show_ticket] shortcode to your return url page ).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="page_title">Page Title</label></th>
                        <td><input name="page_title" type="text" id="page_title" aria-describedby="tagline-description"
                                   value="<?php echo $settings->page_title; ?>" class="regular-text">
                            <p class="description">Your custom progress bar page title here, Separated by comma (
                                Default:
                                AVAILABILITY,DETAILS,CONFIRM,PAYMENT )</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="section_title">Section Title</label></th>
                        <td><input name="section_title" type="text" id="section_title"
                                   aria-describedby="tagline-description"
                                   value="<?php echo $settings->section_title; ?>"
                                   class="regular-text">
                            <p class="description">Your custom progress bar section title here, Separated by comma (
                                Default: Number of People,Your Details,Pickup ).</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="title_first_page">Title First Page</label></th>
                        <td><input name="title_first_page" type="text" id="title_first_page"
                                   aria-describedby="tagline-description"
                                   value="<?php echo $settings->title_first_page; ?>" class="regular-text">
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
                        <th scope="row"><label for="blogname">CSS Style</label></th>
                        <td>
                        <textarea name="css_style" rows="30" cols="50" id="blogname"
                                  class="large-text code"><?php echo(!empty($settings->css_style) ? $settings->css_style : ''); ?></textarea>
                            <p class="description">Write your CSS Styles for overwrite the default style.
                                (Optional).</p>
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
        $hdStep = (isset($_POST['hd_step'])) ? $_POST['hd_step'] : self::STEP_AVAILABILITY;

        if (empty($settings->rtbs_domain)) {
            die('Error: RTBS Domain Not Set');
        }

        $todayDate = date("Y-m-d");
        $tomorrowDate = date('Y-m-d', strtotime($todayDate . "+1 days"));

        $credentials = array(
            'host' => $settings->rtbs_domain,
            'key' => $settings->api_key,
        );

        $booking_service = new Rtbs\ApiHelper\BookingServiceImpl($credentials);

        if ($hdStep == self::STEP_PAYMENT) {
            $this->step_payment();
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
            <?php echo $settings->css_style; ?>
        </style>

        <div class="container rtbs-container" style="width:100%;">
            <?php $this->render_navbar($settings, $hdStep); ?>
            <p>&nbsp;</p>

            <?php if (in_array($hdStep, array(self::STEP_DETAILS, self::STEP_CONFIRM, self::STEP_PAYMENT))): ?>
                <h3 class="tour_name"><?php echo htmlentities($_POST['hd_tour_name']); ?></h3>
                <h4>Selected Date & Time: <span
                        style="color:#000;"><?php echo date('l dS F Y h:i a', strtotime($_POST['hd_tour_date_time'])); ?></span>
                </h4>
            <?php else: ?>
                <h2 class="title-first-page"><?php echo htmlentities($settings->title_first_page); ?></h2>
                <h4>
                    Showing: <?php echo (isset($_REQUEST['tdate'])) ? date('l dS F Y', strtotime($_REQUEST['tdate'])) : date('l dS F Y', strtotime($tomorrowDate)); ?></h4>
                <p>
                    <?php echo $settings->content_first_page; ?>
                </p>
                <p><i class="fa fa-calendar" aria-hidden="true"></i> <input onchange="selectDate(this.value)"
                                                                            type="text"
                                                                            placeholder="Change Date" id="datepicker"
                                                                            value="<?php echo (isset($_REQUEST['tdate'])) ? $_REQUEST['tdate'] : ''; ?>">
                </p>
            <?php endif; ?>


            <div class="row rtbs-tours-step-<?php echo $hdStep; ?>">
                <?php
                $supplier_key = $settings->supplier_key;//a Demonstration Supplier Key. Replace with a Supplier Key as provided to you by Whytewaters

                $supplier = $booking_service->get_supplier($supplier_key);
                $supplier_name = $supplier->get_name();
                //echo PHP_EOL . "Details for $supplier_name...<br>";

                /* @var $supplier Rtbs\ApiHelper\Models\Supplier */
                //echo count($supplier->get_tours()) . ' tours.<br>';
                if (count($supplier->get_tours()) < 1) {
                    echo " <p class='rtbs_error_msg'>Error. Require Supplier Key.";
                    return;
                }


                switch ($hdStep) {
                    case self::STEP_AVAILABILITY:
                        $this->render_step_availability($settings, $booking_service, $shortcode_tour_keys);
                        break;

                    case self::STEP_DETAILS:
                        $this->render_step_details($settings, $booking_service);

                }

                ?>


                <!-------------------------- step 3 Booking ------------------------------------->
                <?php
                if ($hdStep == self::STEP_CONFIRM) {
                    $userSumRemain = array_sum($_POST['pr_ice']);
                    if (array_sum($_POST['pr_ice']) == 0) {
                        echo '<p class="rtbs_error_msg">Please select at least one unit.</p>';
                    }
                    if ($_POST['fname'] == '') {
                        echo '<p class="rtbs_error_msg">First Name is required.</p>';
                    }
                    if ($_POST['lname'] == '') {
                        echo '<p class="rtbs_error_msg">Last Name is required.</p>';
                    }
                    if ($_POST['email'] == '') {
                        echo '<p class="rtbs_error_msg">Email is required.</p>';
                    } elseif (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
                        echo '<p class="rtbs_error_msg">Email is not valid.</p>';
                    }
                    if ($_POST['phone'] == '') {
                        echo '<p class="rtbs_error_msg">Phone is required.</p>';
                    }
                    if ($userSumRemain > $_POST['hd_remaining']) {
                        echo '<p class="rtbs_error_msg">Error: ' . $_POST['hd_remaining'] . ' Remaining, too many places selected.</p>';
                    }
                    ?>
                    <?php if (array_sum($_POST['pr_ice']) == 0 || $_POST['fname'] == '' || $_POST['lname'] == '' || $_POST['email'] == '' || $_POST['phone'] == '' || $userSumRemain > $_POST['hd_remaining']) { ?>
                        <div style="border:1px solid #bdc3c7; padding:10px;" class="col-md-12">
                            <div class="col-md-2"></div>
                            <div class="col-md-8">
                                <?php
                                $credentials = array(
                                    "host" => $settings->rtbs_domain,
                                    "key" => $settings->api_key,
                                    "pwd" => $settings->password
                                );

                                $booking_service = new Rtbs\ApiHelper\BookingServiceImpl($credentials);


                                $expsectionTitle = explode(",", $settings->section_title);
                                $pickups = $booking_service->get_pickups($_POST['hd_tour_key']);


                                $expsectionTitle = explode(",", $settings->section_title);
                                ?>
                                <center>


                                    <form onSubmit="return validd()" class="form-horizontal" action="" method="post">
                                        <fieldset>
                                            <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                                               class=""><?php echo(!empty($expsectionTitle[0]) ? $expsectionTitle[0] : 'Number of People'); ?> </p>
                                            <?php
                                            $tours = $booking_service->get_tours(array($_POST['hd_tour_key']));
                                            $price_for_step2 = [];

                                            foreach ($tours[0]->get_prices() as $price) {
                                                $price_for_step2[] = $price;
                                            }


                                            ?>

                                            <?php
                                            $z = 1;
                                            $x = 0;
                                            foreach ($price_for_step2 as $price_cat) { ?>

                                                <div class="form-group">
                                                    <label for="select"
                                                           class="col-lg-2 col-md-2 col-sm-2"><?php echo $price_cat->get_name(); ?></label>
                                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                                        <div class="col-md-8 col-sm-8 col-xs-8 col-lg-8">
                                                            <input type="hidden" name="<?php echo 'rate' . $z; ?>"
                                                                   id="<?php echo 'rate' . $z; ?>" class=""
                                                                   value="<?php echo $price_cat->get_rate(); ?>">
                                                            <select class="form-control nPeople" name="pr_ice[]"
                                                                    id="<?php echo $z; ?>">
                                                                <?php for ($i = 0; $i <= 20; $i++) { ?>
                                                                    <option <?php echo($_POST['pr_ice'][$x] == $i ? 'selected' : ''); ?>
                                                                        value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4 col-sm-4 col-xs-4 col-lg-4">
                                                            <?php echo $price_cat->get_rate(); ?>
                                                        </div>
                                                        <input type="hidden" name="hd_price_name[]"
                                                               value="<?php echo $price_cat->get_name(); ?>">
                                                        <input type="hidden" name="hd_price_rate[]"
                                                               value="<?php echo $price_cat->get_rate(); ?>">
                                                    </div>
                                                </div>
                                                <?php $x++; ?>
                                                <?php $z++; ?>
                                            <?php } ?>


                                            <p style="font-size:16px;" id="totalPrice"></p>


                                            <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                                               class=""><?php echo(!empty($expsectionTitle[1]) ? $expsectionTitle[1] : 'Your Details'); ?> </p>


                                            <div class="form-group">
                                                <label for="inputEmail" class="col-lg-3">First Name</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" type="text" name="fname"
                                                           value="<?php echo htmlentities(isset($_POST['fname']) ? $_POST['fname'] : ''); ?>">

                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="inputEmail" class="col-lg-3">Last Name</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" type="text" name="lname"
                                                           value="<?php echo htmlentities(isset($_POST['lname']) ? $_POST['lname'] : ''); ?>">

                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="inputEmail" class="col-lg-3">Email</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" type="email" name="email"
                                                           value="<?php echo htmlentities(isset($_POST['email']) ? $_POST['email'] : ''); ?>">

                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="inputEmail" class="col-lg-3">Phone</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" type="tel" name="phone"
                                                           value="<?php echo htmlentities(isset($_POST['phone']) ? $_POST['phone'] : ''); ?>">

                                                </div>
                                            </div>


                                            <?php
                                            if ($settings->is_show_promocode) {
                                                ?>
                                                <div class="form-group">
                                                    <label for="inputEmail" class="col-lg-3">Promo Code</label>
                                                    <div class="col-lg-9">
                                                        <input class="form-control" type="text" name="promo"
                                                               value="<?php echo htmlentities(isset($_POST['promo']) ? $_POST['promo'] : ''); ?>">

                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <?php if (count($pickups) > 0) { ?>
                                                <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                                                   class="">Pickup </p>

                                                <div class="form-group">

                                                    <div class="col-lg-12">
                                                        <select class="form-control" name="pickup_key">
                                                            <option value="">Select a Pickup Point</option>
                                                            <?php foreach ($pickups as $pkup) { ?>
                                                                <option
                                                                    value="<?php echo $pkup->get_pickup_key(); ?>"><?php echo($pkup->get_name() == '' ? 'No Pickup available' : $pkup->get_name() . ' - ' . $pkup->get_place() . ' - ' . date('h:i a', strtotime($_POST['hd_tour_date_time'] . ' -' . $pkup->get_minutes() . ' minutes'))); ?></option>
                                                            <?php } ?>
                                                        </select>

                                                    </div>
                                                </div>
                                            <?php } ?>


                                            <div class="hidden_hd">
                                                <input type="hidden" name="hd_step" value="3">
                                                <input type="hidden" name="hd_remaining"
                                                       value="<?php echo htmlentities($_POST['hd_remaining']); ?>"/>
                                                <input type="hidden" name="hd_tour_key"
                                                       value="<?php echo htmlentities($_POST['hd_tour_key']); ?>">
                                                <input type="hidden" name="hd_date"
                                                       value="<?php echo htmlentities($_POST['hd_date']); ?>">
                                                <input type="hidden" name="hd_tour_name"
                                                       value="<?php echo htmlentities($_POST['hd_tour_name']); ?>">
                                                <input type="hidden" name="hd_tour_date_time"
                                                       value="<?php echo htmlentities($_POST['hd_tour_date_time']); ?>">
                                            </div>


                                            <div class="form-group">
                                                <div class="col-lg-10">
                                                    <button type="submit" onclick="confirmm()"
                                                            class="btn btn-primary pull-right" name="button">NEXT
                                                    </button>
                                                </div>
                                            </div>


                                        </fieldset>
                                    </form>


                                </center>
                            </div>
                        </div>


                    <?php } else {
                    ?>

                    <?php
                    $index = 0;
                    $k = array();
                    foreach ($_POST['pr_ice'] as $key => $value) {
                        if ($value != '0') {
                            $m = array_push($k, $index);
                        }
                        $index++;
                    }

                    foreach ($k as $m) {
                        $pricnamee[] = $_POST['hd_price_name'][$m];
                    }

                    foreach ($k as $r) {
                        $rr[] = $_POST['hd_price_rate'][$r];
                    }

                    foreach ($k as $q) {
                        $qq[] = $_POST['pr_ice'][$q];
                    }


                    ?>

                        <form action="" method="post">

                            <table class="table">
                                <tr>
                                    <td colspan="2">
                                        <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">
                                            Confirm
                                            Your Booking </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Tour Date Time
                                    </td>
                                    <td>
                                        <?php echo date('l dS F Y h:i A', strtotime($_POST['hd_tour_date_time'])); ?>
                                    </td>
                                </tr>
                                <?php
                                $i = 0;
                                $t = 0;
                                foreach ($pricnamee as $ss) {
                                    ?>
                                    <tr>
                                        <td>

                                            <?php echo '<p>' . $ss . ' x ' . $qq[$i] . '</p>'; ?>

                                        </td>
                                        <td>
                                            <?php echo '$' . $rr[$i] * $qq[$i]; ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $t += $rr[$i] * $qq[$i];
                                    $i++;
                                }
                                ?>

                                <?php
                                foreach ($_POST['pr_ice'] as $value_price) {
                                    echo '<input type="hidden" name="pr_ice[]" value="' . $value_price . '">';
                                }
                                ?>


                                <tr>
                                    <td>
                                        Total Price:
                                    </td>
                                    <td>
                                        <?php echo '$' . $t; ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2">
                                        <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">
                                            Your
                                            Details </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Name
                                    </td>
                                    <td>
                                        <?php echo preg_replace('/\\\\/', '', $_POST['fname']) . ' ' . preg_replace('/\\\\/', '', $_POST['lname']); ?>
                                        <input type="hidden" name="fname"
                                               value="<?php echo preg_replace('/\\\\/', '', $_POST['fname']); ?>">
                                        <input type="hidden" name="lname"
                                               value="<?php echo preg_replace('/\\\\/', '', $_POST['lname']); ?>">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Email
                                    </td>
                                    <td>
                                        <?php echo htmlentities($_POST['email']); ?>
                                        <input type="hidden" name="email"
                                               value="<?php echo htmlentities($_POST['email']); ?>">
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Phone
                                    </td>
                                    <td>
                                        <?php echo htmlentities($_POST['phone']); ?>
                                        <input type="hidden" name="phone"
                                               value="<?php echo htmlentities($_POST['phone']); ?>">
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2">
                                        <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">
                                            Terms &
                                            Conditions </p>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2">

                                        <p class="terms_cond"><?php echo $settings->terms_cond; ?></p>
                                        <input type="checkbox" name="tandc" id="chk_conf" value="0"> I have read and
                                        accept
                                        the Terms and Conditions.<br/><br/>
                                    </td>
                                </tr>


                            </table>


                            <div class="hidden_hd">
                                <input type="hidden" name="hd_step" value="4">
                                <input type="hidden" name="hd_tour_key"
                                       value="<?php echo htmlentities($_POST['hd_tour_key']); ?>">
                                <input type="hidden" name="hd_date"
                                       value="<?php echo htmlentities($_POST['hd_date']); ?>">
                                <input type="hidden" name="hd_tour_name"
                                       value="<?php echo htmlentities($_POST['hd_tour_name']); ?>">
                                <input type="hidden" name="hd_tour_date_time"
                                       value="<?php echo htmlentities($_POST['hd_tour_date_time']); ?>">
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
                }
                ?>
                <!-------------------------- step 4 Booking ------------------------------------->


            </div>
        </div>


        <script>
            $(document).ready(function () {
                $('select.nPeople').on('change', function () {
                    var fieldRate = '';
                    var total = 0.00;
                    $('select.nPeople').each(function () {
                        fieldRate = 'rate' + $(this).attr('id') + '';
                        total += parseFloat($('input[name=' + fieldRate + ']').val()) * parseFloat($(this).val());
                    });
                    $('#totalPrice').html('Total: ' + total.toFixed(2));
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
     * @param $settings
     * @param \Rtbs\ApiHelper\BookingServiceImpl $booking_service
     */
    private function render_step_details($settings, $booking_service) {

        $section_titles = explode(",", $settings->section_title);
        $pickups = $booking_service->get_pickups($_POST['hd_tour_key']);
        $tours = $booking_service->get_tours(array($_POST['hd_tour_key']));

        $prices = [];
        foreach ($tours[0]->get_prices() as $price) {
            $prices[] = $price;
        }

        ?>

        <div style="border:1px solid #bdc3c7; padding:10px;" class="col-md-12">
            <div class="col-md-2"></div>
            <div class="col-md-8">

                <center>

                    <form onSubmit="return validd()" class="form-horizontal" action="" method="post">
                        <fieldset>
                            <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                               class=""><?php echo(!empty($section_titles[0]) ? $section_titles[0] : 'Number of People'); ?> </p>
                            <?php
                            $z = 1;
                            foreach ($prices as $price) { ?>

                                <div class="form-group">
                                    <label for="select"
                                           class="col-lg-2 col-md-2 col-sm-2"><?php echo $price->get_name(); ?></label>
                                    <div class="col-lg-10 col-md-10 col-sm-10">
                                        <div class="col-md-8 col-sm-8 col-xs-8 col-lg-8">

                                            <input type="hidden" name="<?php echo 'rate' . $z; ?>"
                                                   id="<?php echo 'rate' . $z; ?>" class=""
                                                   value="<?php echo $price->get_rate(); ?>">
                                            <select class="form-control nPeople" name="pr_ice[]"
                                                    id="<?php echo $z; ?>">

                                                <?php for ($i = 0; $i <= 20; $i++) { ?>
                                                    <option
                                                        value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-4 col-sm-4 col-xs-4 col-lg-4">

                                            <?php echo '$' . $price->get_rate(); ?>
                                        </div>

                                        <input type="hidden" name="hd_price_name[]"
                                               value="<?php echo $price->get_name(); ?>">
                                        <input type="hidden" name="hd_price_rate[]"
                                               value="<?php echo $price->get_rate(); ?>">
                                    </div>
                                </div>
                                <?php $z++; ?>
                            <?php } ?>


                            <p style="font-size:16px;" id="totalPrice"></p>


                            <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                               class=""><?php echo(!empty($section_titles[1]) ? $section_titles[1] : 'Your Details'); ?> </p>


                            <div class="form-group">
                                <label for="rtbsFname" class="col-lg-3">First Name</label>
                                <div class="col-lg-9">
                                    <input id="rtbsFname" class="form-control" type="text" name="fname"
                                           value="">

                                </div>
                            </div>

                            <div class="form-group">
                                <label for="rtbsLname" class="col-lg-3">Last Name</label>
                                <div class="col-lg-9">
                                    <input id="rtbsLname" class="form-control" type="text" name="lname"
                                           value="">

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


                            <?php
                            if ($settings->is_show_promocode) {
                                ?>
                                <div class="form-group">
                                    <label for="rtbsPromo" class="col-lg-3">Promo Code</label>
                                    <div class="col-lg-9">
                                        <input id="rtbsPromo" class="form-control" type="text" name="promo" value="">
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if (count($pickups) > 0) { ?>
                                <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                                   class="">
                                    Pickup </p>

                                <div class="form-group">

                                    <div class="col-lg-12">
                                        <select class="form-control" name="pickup_key">
                                            <option value="">Select a Pickup Point</option>
                                            <?php foreach ($pickups as $pkup) { ?>
                                                <option
                                                    value="<?php echo $pkup->get_pickup_key(); ?>"><?php echo($pkup->get_name() == '' ? 'No Pickup available' : $pkup->get_name() . ' - ' . $pkup->get_place() . ' - ' . date('h:i a', strtotime($_POST['hd_tour_date_time'] . ' -' . $pkup->get_minutes() . ' minutes'))); ?></option>
                                            <?php } ?>
                                        </select>

                                    </div>
                                </div>
                            <?php } ?>


                            <div class="hidden_hd">
                                <input type="hidden" name="hd_step" value="3">
                                <input type="hidden" name="hd_remaining"
                                       value="<?php echo htmlentities($_POST['hd_remaining']); ?>"/>
                                <input type="hidden" name="hd_tour_key"
                                       value="<?php echo htmlentities($_POST['hd_tour_key']); ?>">
                                <input type="hidden" name="hd_date"
                                       value="<?php echo htmlentities($_POST['hd_date']); ?>">
                                <input type="hidden" name="hd_tour_name"
                                       value="<?php echo htmlentities($_POST['hd_tour_name']); ?>">
                                <input type="hidden" name="hd_tour_date_time"
                                       value="<?php echo htmlentities($_POST['hd_tour_date_time']); ?>">
                            </div>


                            <div class="form-group">
                                <div class="col-lg-10">
                                    <button type="submit" onclick="confirmm()"
                                            class="btn btn-primary pull-right" name="button">NEXT
                                    </button>
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
     */
    private function render_step_availability($settings, $booking_service, $shortcode_tour_keys) {

        $date = (isset($_REQUEST['tdate'])) ? $_REQUEST['tdate'] : date('Y-m-d');

        $supplier = $booking_service->get_supplier($settings->supplier_key);

        if ($shortcode_tour_keys) {
            $tour_keys = $shortcode_tour_keys;
        } else {
            $tour_keys = [];
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

        $sessions_by_tour = [];

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
                        <div class="panel-heading"><h4><?php echo htmlentities($tour->get_name()); ?></h4></div>
                        <div class="panel-body">

                            <?php foreach ($sessions as $session): ?>

                                <form action="" method="post">
                                    <p>
                                        <?php echo date('h:i a', strtotime($session->get_datetime())) . ($settings->is_show_remaining ? ', ' . $session->get_remaining() . ' remaining' : ''); ?>
                                        <input type="hidden" name="hd_step" value="2">
                                        <input type="hidden" name="hd_remaining"
                                               value="<?php echo $session->get_remaining(); ?>"/>
                                        <input type="hidden" name="hd_tour_key"
                                               value="<?php echo $tour->get_tour_key(); ?>">
                                        <input type="hidden" name="hd_date"
                                               value="<?php echo (isset($_REQUEST['tdate'])) ? $_REQUEST['tdate'] : date('Y-m-d'); ?>">
                                        <input type="hidden" name="hd_tour_name"
                                               value="<?php echo $tour->get_name(); ?>">
                                        <input type="hidden" name="hd_tour_date_time"
                                               value="<?php echo $session->get_datetime(); ?>">
                                        <button <?php echo ($session->is_open()) ? '' : 'disabled' ?>
                                            class="btn btn-primary" type="submit"
                                            name="button"><?php echo $session->get_state(); ?></button>
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


    private function step_payment() {

        $settings = $this->select_settings();

        // To get an API key contact http://whytewaters.com
        $credentials = array(
            "host" => $settings->rtbs_domain,
            "key" => $settings->api_key,
        );

        $booking_service = new Rtbs\ApiHelper\BookingServiceImpl($credentials);

        $supplier_key = $settings->supplier_key; //a Demonstration Supplier Key. Replace with a Supplier Key as provided to you by Whytewaters

        $supplier = $booking_service->get_supplier($supplier_key);
        $supplier_name = $supplier->get_name();
        //echo PHP_EOL . "Details for $supplier_name...<br>";

        /* @var $supplier Rtbs\ApiHelper\Models\Supplier */
        //echo count($supplier->get_tours()) . ' tours.<br>';
        if (count($supplier->get_tours()) < 1) {
            echo " Stopping.";
            return;
        }

        //echo PHP_EOL,"Tours for $supplier_name...";
        //echo count($attribute);
        if (!empty($attribute[0])) {
            $tour_keys = array();
            foreach ($attribute as $tour) {
                $tour_keys[] = $tour;
            }
        } else {
            $tour_keys = array();
            foreach ($supplier->get_tours() as $tour) {
                $tour_keys[] = $tour->get_tour_key();
            }
        }

        //print_r($tour_keys);

        $tours = $booking_service->get_tours($tour_keys);
        //echo count($tours).'<br>';

        if (empty($tours)) {
            echo " Stopping." . PHP_EOL;
            return;
        }

        $tour_keys = array($_POST['hd_tour_key']);
        $date = $_POST['hd_date'];
        $sessions_and_advanced_dates = $booking_service->get_sessions_and_advance_dates($supplier->get_supplier_key(), $tour_keys, $date);


        /** @var Rtbs\ApiHelper\Models\Session[] $sessions */
        $sessions = $sessions_and_advanced_dates['sessions'];

        if (!empty($sessions_and_advanced_dates['advance_dates'])) {
            echo PHP_EOL . "Found " . count($sessions_and_advanced_dates['advance_dates']) . " advance dates.";
        }

        $found = false;
        foreach ($sessions as $session) {
            if ($session->is_open() && sizeof($session->get_prices()) > 0) {
                $found = $session;
                break;
            }
        }
        if (!$found) {
            echo PHP_EOL . "No open sessions with prices found, stopping.";
            echo PHP_EOL;
            return;
        }
        $session = $found;

        $session->set_datetime($_POST['hd_tour_date_time']);
        $booking = new Rtbs\ApiHelper\Models\Booking();
        $booking->set_tour_key($session->get_tour_key());
        $booking->set_datetime($session->get_datetime());
        $booking->set_first_name($_POST['fname']);
        $booking->set_last_name($_POST['lname']);
        $booking->set_email($_POST['email']);
        $booking->set_phone($_POST['phone']);

        if (!empty($_POST['promo'])) {
            $booking->set_promo_key($_POST['promo']);
        }

        if (!empty($settings->success_url)) {
            $booking->set_return_url($settings->success_url);
        }

        if (!empty($_POST['pickup_key'])) {
            $booking->set_pickup_key($_POST['pickup_key']);
        }


        $prices = $session->get_prices();
        $p = 0;
        foreach ($_POST['pr_ice'] as $value) {
            $booking->add_price_selection($prices[$p], $value);
            $p++;
        }


        $url_or_booking = $booking_service->make_booking($booking);
        $urlReturnPayment = var_export($url_or_booking, true);
        $ret_URL = substr($urlReturnPayment, 1, -1);
        if ($urlReturnPayment == '') {
            //echo '<p class="rtbs_error_msg">Error. insufficient capacity or session closed. </p>';
        } else {
            echo '<script>window.location.href="' . $ret_URL . '"</script>';
        }
    }


    private function render_navbar($settings, $hdStep) {
        $page_titles = explode(",", $settings->page_title);

        ?>
        <div style="background-color:#ecf0f1; height:70px;" class="row hidden-xs hidden-sm">
            <center>

                <div
                    class="col-md-3 col-sm-3 col-xs-3 <?php echo ($hdStep == self::STEP_AVAILABILITY) ? 'selected' : '' ?>">
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