<?php

class rtbslive_settings
{
    public $api_key;
    public $supplier_key;
    public $tour_keys;
    public $url_success;
    public $is_show_promocode;
    public $is_include_bootstrap;
    public $is_show_remaining;
    public $is_test_mode;
    public $text_page_titles;
    public $text_section_titles;
    public $text_first_page_title;
    public $html_first_page_content;
    public $html_terms;
    public $css_style;

    /**
     * @return rtbslive_settings
     */
    public static function load()
    {
        $settings = new self();
        $vars = get_object_vars($settings);

        foreach ($vars as $var_name => $val) {
            $settings->{$var_name} = get_option("rtbslive_{$var_name}", null);
        }

        return $settings;
    }

    /**
     * @param array $data associative array
     */
    public function fill($data) {
        $vars = get_object_vars($this);

        foreach ($vars as $var_name => $val) {
            if (array_key_exists($var_name, $data)) {
                $this->{$var_name} = stripslashes($data[$var_name]);
            }
        }
    }


    public function save()
    {
        $vars = get_object_vars($this);

        if (empty($this->text_page_titles)) {
            $this->text_page_titles = 'Availability,Details,Confirm,Payment';
        }

        if (empty($this->text_section_titles)) {
            $this->text_section_titles = 'Number of People,Your Details,Pickup';
        }

        foreach ($vars as $var_name => $val) {
            update_option("rtbslive_{$var_name}", trim($this->{$var_name}));
        }
    }
}