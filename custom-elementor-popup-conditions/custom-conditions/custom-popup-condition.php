<?php
if (!defined('ABSPATH')) {
    exit;
}

class CFPM_Custom_Popup_Condition extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

    /**
     * Get condition group type.
     *
     * @return string
     */
    public static function get_type() {
        return 'general';
    }

    /**
     * Get condition name.
     *
     * @return string
     */
    public function get_name() {
        return 'custom_popup_condition';
    }

    /**
     * Get condition label.
     *
     * @return string
     */
    public function get_label() {
        return esc_html__('Custom Popup Condition', 'custom-popup-condition');
    }

    /**
     * Check condition.
     *
     * @param array $args
     * @return bool
     */
    public function check($args) {
        if (is_page()) {
            $post_id = get_the_ID();
            $popup_condition = get_post_meta($post_id, '_cfpm_custom_popup_condition', true);

            // Check if the condition matches any saved assignments
            $popup_assignments = get_option('cfpm_popup_assignments', []);
            if (!empty($popup_condition) && isset($popup_assignments[$popup_condition])) {
                return true;
            }
        }
        return false;
    }
}