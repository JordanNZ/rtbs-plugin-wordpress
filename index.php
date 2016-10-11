<?php
/*
Plugin Name: RTBS Booking Plugin
Description: Tour Booking Plugin
Version: 1.0
*/

//############################ Create table automatically #############################//

global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
    global $wpdb;
    global $jal_db_version;

    $table_name = 'rtbs_settings';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `supplier_key` varchar(255) NOT NULL,
  `rtbs_domain` varchar(255) NOT NULL,
  `promo_code` int(11) NOT NULL COMMENT '0=show,1=hide',
  `success_url` varchar(255) NOT NULL,
  `terms_cond` text NOT NULL,
  `tour_keys` varchar(255) NOT NULL COMMENT 'separated by comma',
  `page_title` varchar(255) NOT NULL,
  `section_title` varchar(255) NOT NULL,
  `title_first_page` varchar(255) NOT NULL,
  `content_first_page` text NOT NULL,
  `remaining` int(11) NOT NULL COMMENT '0=true,1=false',
	`css_style` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('jal_db_version', $jal_db_version);
}

function jal_install_data() {
    global $wpdb;

    $table_name = 'rtbs_settings';

    $wpdb->insert(
        $table_name,
        array(
            'id' => 1,
        )
    );
}

function rtbs_table_delete() {
    global $wpdb;
    $wpdb->query('DROP TABLE rtbs_settings');
}

register_activation_hook(__FILE__, 'jal_install');
register_activation_hook(__FILE__, 'jal_install_data');

register_deactivation_hook(__FILE__, 'rtbs_table_delete');
//###########Finished #######################//


add_action('admin_menu', 'RTBS_booking');

function RTBS_booking() {
    add_menu_page('RTBS', 'RTBS', '', __FILE__, 'moveing_company', plugins_url('img/settings.png', __FILE__));
    add_submenu_page(__FILE__, 'Shortcode', 'Shortcode', 'administrator', 'shortcode-rtbs-booking', 'shortcode_RTBS_booking');
    add_submenu_page(__FILE__, 'CSS Style', 'CSS Style', 'administrator', 'css-style-rtbs-booking', 'css_style_rtbs_booking');
    add_submenu_page(__FILE__, 'Settings', 'Settings', 'administrator', 'adminSettings', 'adminSettings');

}

// admin settings---------------------------------------
function adminSettings() {
    global $wpdb;
    $getRowval = $wpdb->get_row("SELECT * FROM rtbs_settings");
    if (isset($_POST['save_set'])) {
        // Update the values---
        if (isset($_POST['promo_code'])) {
            $promo_code = 0;
        } else {
            $promo_code = 1;
        }

        if (isset($_POST['remaining'])) {
            $remaining = 0;
        } else {
            $remaining = 1;
        }

        $checkUpdate = $wpdb->update(
            'rtbs_settings',
            array(
                'api_key' => stripcslashes($_POST['api_key']),
                'password' => stripcslashes($_POST['password']),
                'supplier_key' => stripcslashes($_POST['supplier_key']),
                'rtbs_domain' => stripcslashes($_POST['rtbs_domain']),
                'promo_code' => stripcslashes($promo_code),
                'success_url' => stripcslashes($_POST['success_url']),
                'tour_keys' => stripcslashes($_POST['tour_keys']),
                'page_title' => stripcslashes($_POST['page_title']),
                'section_title' => stripcslashes($_POST['section_title']),
                'title_first_page' => stripcslashes($_POST['title_first_page']),
                'content_first_page' => stripcslashes($_POST['content_first_page']),
                'terms_cond' => stripcslashes($_POST['t_c']),
                'remaining' => stripcslashes($remaining)
            ),
            array('id' => 1),
            array(
                '%s',
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
                '%s',
                '%d'
            ),
            array('%d')
        );
        $getRowval = $wpdb->get_row("SELECT * FROM rtbs_settings");
    }

    ?>

    <div class='wrap'>

        <h2>RTBS Settings</h2>

        <?php if ($checkUpdate === 1) { ?>

            <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                <p><strong>Settings saved.</strong></p>
                <button type="button" class="notice-dismiss"><span
                        class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
        <?php } ?>

        <form class="" action="" method="post">

            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="blogname">API Key</label></th>
                    <td><input name="api_key" type="text" id="blogname" value="<?php echo $getRowval->api_key; ?>"
                               class="regular-text">
                        <p class="description">Your API key.</p>
                    </td>
                </tr>

                <!-- <tr>
<th scope="row"><label for="blogdescription">API Password</label></th>
<td><input name="password" type="text" id="blogdescription" aria-describedby="tagline-description" value="<?php echo $getRowval->password; ?>" class="regular-text">
<p class="description">Your API password (Optional).</p>
</td>
</tr> -->

                <tr>
                    <th scope="row"><label for="blogdescription">Supplier Key</label></th>
                    <td><input name="supplier_key" type="text" id="blogdescription"
                               aria-describedby="tagline-description" value="<?php echo $getRowval->supplier_key; ?>"
                               class="regular-text">
                        <p class="description">Set your Supplier key.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="blogdescription">RTBS Domain</label></th>
                    <td><input name="rtbs_domain" type="text" id="blogdescription"
                               aria-describedby="tagline-description" value="<?php echo $getRowval->rtbs_domain; ?>"
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
                    <th scope="row"><label for="blogdescription">Remaining</label></th>
                    <td><input name="remaining" type="checkbox" id="blogdescription"
                               aria-describedby="tagline-description"
                               class="regular-checkbox" <?php echo ($getRowval->remaining) == 0 ? 'checked' : '' ?>
                               value="0">
                        <p class="description">Show/Hide</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="blogdescription">Success URI</label></th>
                    <td><input name="success_url" type="text" id="blogdescription"
                               aria-describedby="tagline-description" value="<?php echo $getRowval->success_url; ?>"
                               class="regular-text">
                        <p class="description">Return or Success url ( For display ticket you must put
                            [rtbs_show_ticket] shortcode to your return url page ).</p>
                    </td>
                </tr>

                <!-- <tr>
<th scope="row"><label for="blogdescription">Tour Keys</label></th>
<td><input name="tour_keys" type="text" id="blogdescription" aria-describedby="tagline-description" value="<?php echo $getRowval->tour_keys; ?>" class="regular-text">
<p class="description">Seperated by comma (Optional).</p>
</td>
</tr> -->

                <tr>
                    <th scope="row"><label for="blogdescription">Page Title</label></th>
                    <td><input name="page_title" type="text" id="blogdescription" aria-describedby="tagline-description"
                               value="<?php echo $getRowval->page_title; ?>" class="regular-text">
                        <p class="description">Your custom progress bar page title here, Separated by comma ( Default:
                            AVAILABILITY,DETAILS,CONFIRM,PAYMENT )</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="blogdescription">Section Title</label></th>
                    <td><input name="section_title" type="text" id="blogdescription"
                               aria-describedby="tagline-description" value="<?php echo $getRowval->section_title; ?>"
                               class="regular-text">
                        <p class="description">Your custom progress bar section title here, Separated by comma (
                            Default: Number of People,Your Details,Pickup ).</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="blogdescription">Title First Page</label></th>
                    <td><input name="title_first_page" type="text" id="blogdescription"
                               aria-describedby="tagline-description"
                               value="<?php echo $getRowval->title_first_page; ?>" class="regular-text">
                        <p class="description">Your first page title.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="blogdescription">Content First Page</label></th>
                    <td><?php wp_editor($getRowval->content_first_page, 'content_first_page', $settings = array('media_buttons' => false)); ?>
                        <p class="description">Your first page content.</p>
                    </td>
                </tr>


                <tr>
                    <th scope="row"><label for="blogdescription">Terms & Conditions</label></th>
                    <td>
                        <!--<textarea name="t_c" type="text" id="t_c" aria-describedby="tagline-description" value="" class="regular-textarea"></textarea>-->
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

function shortcode_RTBS_booking() {

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

function css_style_rtbs_booking() {
    global $wpdb;
    $getRowval = $wpdb->get_row("SELECT * FROM rtbs_settings");
    if (isset($_POST['save_set'])) {

        $checkUpdate = $wpdb->update(
            'rtbs_settings',
            array(
                'css_style' => stripcslashes($_POST['css_style'])
            ),
            array('id' => 1),
            array(
                '%s'
            ),
            array('%d')
        );
        $getRowval = $wpdb->get_row("SELECT * FROM rtbs_settings");
    } //######## end if ##############
    ?>
    <div class="wrap">
        <h1>RTBS Booking CSS Style</h1>

        <?php if ($checkUpdate === 1) { ?>

            <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                <p><strong>Settings saved.</strong></p>
                <button type="button" class="notice-dismiss"><span
                        class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
        <?php } ?>

        <form class="" action="" method="post">

            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label for="blogname">CSS Style</label></th>
                    <td>
                        <textarea name="css_style" rows="30" cols="50" id="blogname"
                                  class="large-text code"><?php echo($getRowval->css_style <> '' ? $getRowval->css_style : ''); ?></textarea>
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
} //############end admin ######################//
function mainplugin_fn($atts) {
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
    </script>

    <script>
        function selectDate(str) {
            window.location.href = "index.php?tdate=" + str;
        }
    </script>

    <?php
// shortcode with attribute or parameter
    if (isset($atts['tour_key'])) {
        $exp_key = $atts['tour_key'];
        $attribute = explode(",", $exp_key);
    }


    global $wpdb;
    $getRowval = $wpdb->get_row("SELECT * FROM rtbs_settings");
    ?>

    <!-- CSS Styles Custom --->
    <style>
        <?php echo $getRowval->css_style; ?>
    </style>

    <div class="container rtbs-container" style="width:100%;">
        <div style="background-color:#ecf0f1; height:70px;" class="row hidden-xs hidden-sm">

            <center>
                <?php
                $exppageTtle = explode(",", $getRowval->page_title);

                ?>


                <div
                    class="col-md-3 col-sm-3 col-xs-3 <?php echo ($_POST['hd_step'] != '2' && $_POST['hd_step'] != '3' && $_POST['hd_step'] != '4') ? 'selected' : '' ?>">
                    <div style="margin-top: 15px;" class="col-md-2 numberCircle">1</div>
                    <div class="col-md-10" style="margin-top: -32px; margin-left: 24px;">
                        <p class="text-primary"><?php echo($exppageTtle[0] <> '' ? $exppageTtle[0] : 'AVAILABILITY'); ?></p>

                    </div>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-3 <?php echo($_POST['hd_step'] == '2' ? 'selected' : ''); ?>">
                    <div style="margin-top: 15px;" class="col-md-2 numberCircle">2</div>
                    <div class="col-md-10" style="margin-top: -32px; margin-left: 24px;">
                        <p class="text-primary"><?php echo($exppageTtle[1] <> '' ? $exppageTtle[1] : 'DETAILS'); ?></p>

                    </div>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-3 <?php echo($_POST['hd_step'] == '3' ? 'selected' : ''); ?>">
                    <div style="margin-top: 15px;" class="col-md-2 numberCircle">3</div>
                    <div class="col-md-10" style="margin-top: -32px; margin-left: 24px;">
                        <p class="text-primary"><?php echo($exppageTtle[2] <> '' ? $exppageTtle[2] : 'CONFIRM'); ?></p>

                    </div>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-3 <?php echo($_POST['hd_step'] == '4' ? 'selected' : ''); ?>">
                    <div style="margin-top: 15px;" class="col-md-2 numberCircle">4</div>
                    <div class="col-md-10" style="margin-top: -32px; margin-left: 24px;">
                        <p class="text-primary"><?php echo($exppageTtle[3] <> '' ? $exppageTtle[3] : 'PAYMENT'); ?></p>

                    </div>
                </div>
            </center>

        </div>
        <p>&nbsp;</p>
        <?php
        date_default_timezone_set('Pacific/Auckland');
        ?>
        <?php if ($_POST['hd_step'] == 2 || $_POST['hd_step'] == 3 || $_POST['hd_step'] == 4) { ?>
            <h3 class="tour_name"><?php echo htmlentities($_POST['hd_tour_name']); ?></h3>
            <h4>Selected Date & Time: <span
                    style="color:#000;"><?php echo date('l dS F Y h:i a', strtotime($_POST['hd_tour_date_time'])); ?></span>
            </h4>
        <?php } else { ?>
            <?php
            $todayDate = date("Y-m-d");
            $tomorrowDate = date('Y-m-d', strtotime($todayDate . "+1 days"));
            ?>
            <h2 class="title-first-page"><?php echo $getRowval->title_first_page; ?></h2>
            <h4>
                Showing: <?php echo (isset($_REQUEST['tdate'])) ? date('l dS F Y', strtotime($_REQUEST['tdate'])) : date('l dS F Y', strtotime($tomorrowDate)); ?></h4>
            <p>
                <?php echo $getRowval->content_first_page; ?>
            </p>
            <!-- <p>
              <a id="datepicker" href="#"><i class="fa fa-calendar" aria-hidden="true"></i> Change Date</a>

            </p> -->
            <p><i class="fa fa-calendar" aria-hidden="true"></i> <input onchange="selectDate(this.value)" type="text"
                                                                        placeholder="Change Date" id="datepicker"
                                                                        value="<?php echo (isset($_REQUEST['tdate'])) ? $_REQUEST['tdate'] : ''; ?>">
            </p>
        <?php } ?>


        <!--------------------------- Step 4 your details ------------------------------------->

        <?php
        if ($_POST['hd_step'] == '4') {

            date_default_timezone_set('Pacific/Auckland');

            require_once("vendor/autoload.php");

            // To get an API key contact http://whytewaters.com
            $credentials = array(
                "host" => $getRowval->rtbs_domain,
                "key" => $getRowval->api_key,
                "pwd" => $getRowval->password
            );

            global $wpdb;
            $getRowval = $wpdb->get_row("SELECT * FROM rtbs_settings");

            $booking_service = new Rtbs\ApiHelper\BookingServiceImpl($credentials);

            $supplier_key = $getRowval->supplier_key; //a Demonstration Supplier Key. Replace with a Supplier Key as provided to you by Whytewaters

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
            if ($attribute[0] <> '') {
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


//$tour = $tours[mt_rand(0,count($tours)-1)];
//print_r($tour);
//echo PHP_EOL."Sessions for " . $tour->set_name($_POST['hd_tour_name']) . "...";
            $tour_keys = array($_POST['hd_tour_key']);
            $date = $_POST['hd_date'];
            $sessions_and_advanced_dates = $booking_service->get_sessions_and_advance_dates($supplier->get_supplier_key(), $tour_keys, $date);


            /** @var Rtbs\ApiHelper\Models\Session[] $sessions */
            $sessions = $sessions_and_advanced_dates['sessions'];
//echo count($sessions);
            foreach ($sessions as $session) {
                //echo PHP_EOL.'SESSION: ' . $session->get_datetime() . ' ' . ($session->is_open() ? 'OPEN' : 'CLOSED');
            }

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

//echo PHP_EOL."Checking pickups...<br><br>";
//$pickups = $booking_service->get_pickups($tour->get_tour_key());


//echo $_POST['hd_tour_date_time'];
//echo PHP_EOL,"Booking " . $tour->get_name() . " at " . $session->set_datetime($_POST['hd_tour_date_time']) . "...";
            $session->set_datetime($_POST['hd_tour_date_time']);
            $booking = new Rtbs\ApiHelper\Models\Booking();
            $booking->set_tour_key($session->get_tour_key());
            $booking->set_datetime($session->get_datetime());
            $booking->set_first_name($_POST['fname']);
            $booking->set_last_name($_POST['lname']);
            $booking->set_email($_POST['email']);
            $booking->set_phone($_POST['phone']);
            $booking->set_promo_key($_POST['promo']);
            if ($getRowval->success_url <> '') {
                $booking->set_return_url($getRowval->success_url);
            }

            if ($_POST['pickup_key'] != '') {
                $booking->set_pickup_key($_POST['pickup_key']);
            }


            $prices = $session->get_prices();
            $p = 0;
            foreach ($_POST['pr_ice'] as $value) {
                $booking->add_price_selection($prices[$p], $value);
                $p++;
            }


            $url_or_booking = $booking_service->make_booking($booking);
//echo count($url_or_booking);

//echo PHP_EOL, "Booking done. Response is : " . var_export($url_or_booking, true);
//print_r($url_or_booking);
            $urlReturnPayment = var_export($url_or_booking, true);
//echo '<pre>'; print_r($urlReturnPayment);

//echo 'Booking idddddd: '.$urlReturnPayment['return_url'];

            $ret_URL = substr($urlReturnPayment, 1, -1);
//echo $urlReturnPayment;
            if ($urlReturnPayment == '') {
                //echo '<p class="rtbs_error_msg">Error. insufficient capacity or session closed. </p>';
            } else {
                echo '<script>window.location.href="' . $ret_URL . '"</script>';
            }

        }
        ?>


        <div class="row rtbs-tours-step-<?php if ($_POST['hd_step'] == '2') {
            echo '2';
        } elseif ($_POST['hd_step'] == '3') {
            echo '3';
        } elseif ($_POST['hd_step'] == '4') {
            echo '4';
        } else {
            echo '1';
        } ?>">
            <?php
            if ($getRowval->rtbs_domain <> '') {
                date_default_timezone_set('Pacific/Auckland');

                require_once("vendor/autoload.php");

// To get an API key contact http://whytewaters.com
                $credentials = array(
                    "host" => $getRowval->rtbs_domain,
                    "key" => $getRowval->api_key,
                    "pwd" => $getRowval->password
                );


                $booking_service = new Rtbs\ApiHelper\BookingServiceImpl($credentials);

                $supplier_key = $getRowval->supplier_key;//a Demonstration Supplier Key. Replace with a Supplier Key as provided to you by Whytewaters

                $supplier = $booking_service->get_supplier($supplier_key);
                $supplier_name = $supplier->get_name();
//echo PHP_EOL . "Details for $supplier_name...<br>";

                /* @var $supplier Rtbs\ApiHelper\Models\Supplier */
//echo count($supplier->get_tours()) . ' tours.<br>';
                if (count($supplier->get_tours()) < 1) {
                    echo " <p class='rtbs_error_msg'>Error. Require Supplier Key.";
                    return;
                }


                if ($_POST['hd_step'] != '3' && $_POST['hd_step'] != '4' && $_POST['hd_step'] != '2') {

//echo PHP_EOL,"Tours for $supplier_name...";
//echo count($attribute);
                    if ($attribute[0] <> '') {
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

                    foreach ($tours as $tour) { // main loop checking if session true or false....

                        $tour_keys = array($tour->get_tour_key());

                        if (isset($_REQUEST['tdate'])) {
                            $date = $_REQUEST['tdate'];
                        } else {
                            $date = date('Y-m-d');
                        }
                        $sessions_and_advanced_dates = $booking_service->get_sessions_and_advance_dates($supplier->get_supplier_key(), $tour_keys, $date);

                        $sessions = $sessions_and_advanced_dates['sessions'];

                        if (count($sessions) > 0) {

//foreach ($tours as $tour) {
                            ?>

                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><h4><?php echo $tour->get_name(); ?></h4></div>
                                    <div class="panel-body">

                                        <?php

                                        $tour_keys = array($tour->get_tour_key());

                                        if (isset($_REQUEST['tdate'])) {
                                            $date = $_REQUEST['tdate'];
                                        } else {

                                            $todayDate2 = date("Y-m-d");
                                            $tomorrowDate2 = date('Y-m-d', strtotime($todayDate2 . "+1 days"));

                                            $date = $tomorrowDate2;
                                        }
                                        $sessions_and_advanced_dates = $booking_service->get_sessions_and_advance_dates($supplier->get_supplier_key(), $tour_keys, $date);

                                        $sessions = $sessions_and_advanced_dates['sessions'];
                                        //echo count($sessions);
                                        if (count($sessions) == 0) {
                                            echo 'No Sessions available.';
                                        }
                                        //echo count($tour->get_prices());
                                        if (count($tour->get_prices()) > 0) {

                                            foreach ($sessions as $session) {
                                                //if($session->is_open()){

                                                //echo date('h:i a', strtotime($session->get_datetime())).($getRowval->remaining=='0' ? ', '.$session->get_remaining().' remaining' : '');
                                                ?>
                                                <form action="" method="post">
                                                    <p>
                                                        <?php echo date('h:i a', strtotime($session->get_datetime())) . ($getRowval->remaining == '0' ? ', ' . $session->get_remaining() . ' remaining' : ''); ?>
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
                                                <?php //echo PHP_EOL.'SESSION: ' . $session->get_datetime() . ' '. $session->get_remaining().' remaining ' . ($session->is_open() ? 'OPEN' : 'CLOSED').'<br>';
                                                //}

                                            }

                                        } else {
                                            echo 'No Price found.';
                                        }

                                        foreach ($tour->get_prices() as $price) {
                                            //echo PHP_EOL .'TOUR PRICE: '. $price->get_name() . ' = $' . $price->get_rate().'<br>';
                                            $price_for_step2[] = $price;
                                        }
                                        ?>

                                    </div>
                                </div>
                            </div>


                        <?php }
                    } ?>

                <?php } ?>

            <?php } else { ?>
                <p class="rtbs_error_msg">Error. Require RTBS Domain.</p>
            <?php } ?>
            <!---------------------------End Step 1 ------------------------------------->

            <?php if ($_POST['hd_step'] == '2') { ?>
                <!--------------------------- Step 2 your details ------------------------------------->


                <div style="border:1px solid #bdc3c7; padding:10px;" class="col-md-12">
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <?php

                        $credentials = array(
                            "host" => $getRowval->rtbs_domain,
                            "key" => $getRowval->api_key,
                            "pwd" => $getRowval->password
                        );

                        $booking_service = new Rtbs\ApiHelper\BookingServiceImpl($credentials);


                        $expsectionTitle = explode(",", $getRowval->section_title);
                        $pickups = $booking_service->get_pickups($_POST['hd_tour_key']);

                        ?>
                        <center>


                            <form onSubmit="return validd()" class="form-horizontal" action="" method="post">
                                <fieldset>
                                    <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                                       class=""><?php echo($expsectionTitle[0] <> '' ? $expsectionTitle[0] : 'Number of People'); ?> </p>
                                    <?php
                                    $tours = $booking_service->get_tours(array($_POST['hd_tour_key']));

                                    foreach ($tours[0]->get_prices() as $price) {
                                        $price_for_step2[] = $price;
                                    }


                                    ?>

                                    <?php
                                    $z = 1;
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
                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-4 col-sm-4 col-xs-4 col-lg-4">

                                                    <?php echo '$' . $price_cat->get_rate(); ?>
                                                </div>

                                                <input type="hidden" name="hd_price_name[]"
                                                       value="<?php echo $price_cat->get_name(); ?>">
                                                <input type="hidden" name="hd_price_rate[]"
                                                       value="<?php echo $price_cat->get_rate(); ?>">
                                            </div>
                                        </div>
                                        <?php $z++; ?>
                                    <?php } ?>


                                    <p style="font-size:16px;" id="totalPrice"></p>


                                    <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                                       class=""><?php echo($expsectionTitle[1] <> '' ? $expsectionTitle[1] : 'Your Details'); ?> </p>


                                    <div class="form-group">
                                        <label for="inputEmail" class="col-lg-3">First Name</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" type="text" name="fname" value="">

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="inputEmail" class="col-lg-3">Last Name</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" type="text" name="lname" value="">

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="inputEmail" class="col-lg-3">Email</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" type="email" name="email" value="">

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="inputEmail" class="col-lg-3">Phone</label>
                                        <div class="col-lg-9">
                                            <input class="form-control" type="tel" name="phone" value="">

                                        </div>
                                    </div>


                                    <?php
                                    if ($getRowval->promo_code == '0') {
                                        ?>
                                        <div class="form-group">
                                            <label for="inputEmail" class="col-lg-3">Promo Code</label>
                                            <div class="col-lg-9">
                                                <input class="form-control" type="text" name="promo" value="">

                                            </div>
                                        </div>
                                    <?php } ?>

                                    <?php if (count($pickups) > 0) { ?>
                                        <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">
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
                                        <input type="hidden" name="hd_date" value="<?php echo htmlentities($_POST['hd_date']); ?>">
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


            <?php } ?>


            <!-------------------------- step 3 Booking ------------------------------------->
            <?php
            if ($_POST['hd_step'] == '3') {
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
                }
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    $emailErr = "Invalid email format.";
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
                                "host" => $getRowval->rtbs_domain,
                                "key" => $getRowval->api_key,
                                "pwd" => $getRowval->password
                            );

                            $booking_service = new Rtbs\ApiHelper\BookingServiceImpl($credentials);


                            $expsectionTitle = explode(",", $getRowval->section_title);
                            $pickups = $booking_service->get_pickups($_POST['hd_tour_key']);


                            $expsectionTitle = explode(",", $getRowval->section_title);
                            ?>
                            <center>


                                <form onSubmit="return validd()" class="form-horizontal" action="" method="post">
                                    <fieldset>
                                        <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;"
                                           class=""><?php echo($expsectionTitle[0] <> '' ? $expsectionTitle[0] : 'Number of People'); ?> </p>
                                        <?php
                                        $tours = $booking_service->get_tours(array($_POST['hd_tour_key']));

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
                                           class=""><?php echo($expsectionTitle[1] <> '' ? $expsectionTitle[1] : 'Your Details'); ?> </p>


                                        <div class="form-group">
                                            <label for="inputEmail" class="col-lg-3">First Name</label>
                                            <div class="col-lg-9">
                                                <input class="form-control" type="text" name="fname"
                                                       value="<?php echo(isset($_POST['fname']) ? preg_replace('/\\\\/', '', $_POST['fname']) : ''); ?>">

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="inputEmail" class="col-lg-3">Last Name</label>
                                            <div class="col-lg-9">
                                                <input class="form-control" type="text" name="lname"
                                                       value="<?php echo(isset($_POST['lname']) ? preg_replace('/\\\\/', '', $_POST['lname']) : ''); ?>">

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="inputEmail" class="col-lg-3">Email</label>
                                            <div class="col-lg-9">
                                                <input class="form-control" type="email" name="email"
                                                       value="<?php echo(isset($_POST['email']) ? $_POST['email'] : ''); ?>">

                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="inputEmail" class="col-lg-3">Phone</label>
                                            <div class="col-lg-9">
                                                <input class="form-control" type="number" name="phone"
                                                       value="<?php echo(isset($_POST['phone']) ? $_POST['phone'] : ''); ?>">

                                            </div>
                                        </div>


                                        <?php
                                        if ($getRowval->promo_code == '0') {
                                            ?>
                                            <div class="form-group">
                                                <label for="inputEmail" class="col-lg-3">Promo Code</label>
                                                <div class="col-lg-9">
                                                    <input class="form-control" type="text" name="promo"
                                                           value="<?php echo(isset($_POST['promo']) ? $_POST['promo'] : ''); ?>">

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
                                    <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">Confirm
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
                                    <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">Your
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
                                    <input type="hidden" name="email" value="<?php echo htmlentities($_POST['email']); ?>">
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    Phone
                                </td>
                                <td>
                                    <?php echo htmlentities($_POST['phone']); ?>
                                    <input type="hidden" name="phone" value="<?php echo htmlentities($_POST['phone']); ?>">
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <p style="font-size: 18px;background-color: #ecf0f1;padding: 10px;" class="">Terms &
                                        Conditions </p>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">

                                    <p class="t_c"><?php echo $getRowval->terms_cond; ?></p>
                                    <input type="checkbox" name="tandc" id="chk_conf" value="0"> I have read and accept
                                    the Terms and Conditions.<br/><br/>
                                </td>
                            </tr>


                        </table>


                        <div class="hidden_hd">
                            <input type="hidden" name="hd_step" value="4">
                            <input type="hidden" name="hd_tour_key" value="<?php echo htmlentities($_POST['hd_tour_key']); ?>">
                            <input type="hidden" name="hd_date" value="<?php echo htmlentities($_POST['hd_date']); ?>">
                            <input type="hidden" name="hd_tour_name" value="<?php echo htmlentities($_POST['hd_tour_name']); ?>">
                            <input type="hidden" name="hd_tour_date_time" value="<?php echo htmlentities($_POST['hd_tour_date_time']); ?>">
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
} // end ############################ main part #########################


function confirm_page_ticket() {
    global $wpdb;
    $getRowval = $wpdb->get_row("SELECT * FROM rtbs_settings");
    $ticket_url = $getRowval->rtbs_domain . "/api/ticket?token=" . $_REQUEST['token'];
    return '<p><iframe src="' . $ticket_url . '" frameborder="0" style="overflow:hidden;height:1000px;width:100%" height="100%" width="100%"></iframe></p>';
}

add_shortcode('rtbs_plugin', 'mainplugin_fn'); // Shortcode for main plugin.
add_shortcode('rtbs_show_ticket', 'confirm_page_ticket'); // SHortcode for ticket display.