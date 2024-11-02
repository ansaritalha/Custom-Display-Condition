<?php
/*
Plugin Name: Custom Popup Display Condition
Description: assign Elementor popups based on custom display values, and display them dynamically on pages based on custom meta box value.
Version: 1.0
Author: Talha Ansari
*/

if (!defined('ABSPATH')) {
    exit;
}

// ====================== CUSTOM FIELD MANAGEMENT ======================

// Register the Custom Field Management settings page
function cfpm_register_custom_field_page() {
    add_menu_page(
        'Custom POPUP Fields',
        'Elementor Popup Page Brands',
        'manage_options',
        'cfpm-custom-fields',
        'cfpm_render_custom_field_page'
    );
}
add_action('admin_menu', 'cfpm_register_custom_field_page');

// Render the Custom Field Management page
function cfpm_render_custom_field_page() {
    $custom_fields = get_option('cfpm_custom_fields', []);

    // Handle form submission for adding new field
    if (isset($_POST['cfpm_add_field'])) {
        $new_field = sanitize_text_field($_POST['cfpm_field_name']);
        // Check if the new brand already exists (case-insensitive)
        $exists = false;
        foreach ($custom_fields as $field) {
            if (strtolower($field) === strtolower($new_field)) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            echo '<div class="notice notice-error is-dismissible"><p>Brand already exists!</p></div>';
        } elseif (!empty($new_field)) {
            $custom_fields[] = $new_field;
            update_option('cfpm_custom_fields', $custom_fields);
            echo '<div class="notice notice-success is-dismissible"><p>Brand field added successfully!</p></div>';
        }
    }

    // Handle delete option
    if (isset($_POST['cfpm_delete_field'])) {
        $field_to_delete = sanitize_text_field($_POST['cfpm_field_to_delete']);
        if (($key = array_search($field_to_delete, $custom_fields)) !== false) {
            unset($custom_fields[$key]);
            update_option('cfpm_custom_fields', $custom_fields);
            echo '<div class="notice notice-success is-dismissible"><p>Brand field deleted successfully!</p></div>';
        }
    }

    // Handle edit option
    if (isset($_POST['cfpm_edit_field'])) {
        $field_to_edit = sanitize_text_field($_POST['cfpm_field_to_edit']);
        $new_field_name = sanitize_text_field($_POST['cfpm_new_field_name']);
        
        // Check if the new name already exists (case-insensitive)
        $exists = false;
        foreach ($custom_fields as $field) {
            if (strtolower($field) === strtolower($new_field_name)) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            echo '<div class="notice notice-error is-dismissible"><p>Brand already exists!</p></div>';
        } elseif (($key = array_search($field_to_edit, $custom_fields)) !== false && !empty($new_field_name)) {
            $custom_fields[$key] = $new_field_name;
            update_option('cfpm_custom_fields', $custom_fields);
            echo '<div class="notice notice-success is-dismissible"><p>Brand field updated successfully!</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <hr class="wp-header-end">

        <div class="cfpm-section">
            <form method="post" class="cfpm-form">
                <input type="text" name="cfpm_field_name" class="regular-text" placeholder="Brand (e.g. SEO)" required>
                <button type="submit" name="cfpm_add_field" class="button button-primary">Add Brand</button>
            </form>
        </div>

        <div class="cfpm-section">
            <h2 class="cfpm-section-title">Existing Page Brands</h2>
            <ul class="cfpm-custom-fields">
                <?php foreach ($custom_fields as $field) : ?>
                    <li class="cfpm-field-item">
                        <span class="cfpm-field-text"><?php echo esc_html($field); ?></span>

                        <!-- Edit button to toggle input field -->
                        <button type="button" class="button button-secondary cfpm-edit-btn" data-field="<?php echo esc_attr($field); ?>">Edit</button>
                        
                        <!-- Hidden form for editing the field -->
                        <form method="post" class="cfpm-edit-form" style="display:none;">
                            <input type="hidden" name="cfpm_field_to_edit" value="<?php echo esc_attr($field); ?>">
                            <input type="text" name="cfpm_new_field_name" value="<?php echo esc_attr($field); ?>" class="regular-text" required>
                            <button type="submit" name="cfpm_edit_field" class="button button-primary">Update</button>
                        </form>

                        <form method="post" style="display:inline;">
                            <input type="hidden" name="cfpm_field_to_delete" value="<?php echo esc_attr($field); ?>">
                            <button type="submit" name="cfpm_delete_field" class="button button-secondary">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <style>
        .cfpm-section {
            background-color: #f9f9f9;
            border: 1px solid #eaeaea;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .cfpm-section-title {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
        }
        .cfpm-custom-fields {
            list-style-type: none;
            padding: 0;
        }
        .cfpm-field-item {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eaeaea;
        }
        .cfpm-field-item span {
            flex-grow: 1;
        }
        .cfpm-edit-btn {
            margin-right: 10px!important;
        }
    </style>

    <script>
        // JavaScript to handle toggling the edit form
        document.querySelectorAll('.cfpm-edit-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                var parent = button.closest('.cfpm-field-item');
                var editForm = parent.querySelector('.cfpm-edit-form');
                var textSpan = parent.querySelector('.cfpm-field-text');

                // Toggle the form and the text display
                if (editForm.style.display === 'none') {
                    editForm.style.display = 'flex';
                    textSpan.style.display = 'none';
                    button.textContent = 'Cancel';
                } else {
                    editForm.style.display = 'none';
                    textSpan.style.display = 'inline';
                    button.textContent = 'Edit';
                }
            });
        });
    </script>
    <?php
}


// ====================== POPUP ASSIGNMENT MANAGEMENT ======================

// Register the Popup Assignment settings page
function cfpm_register_popup_assignment_page() {
    add_submenu_page(
        'cfpm-custom-fields', // Parent slug
        'Popup Assignment',
        'Popup Assignment',
        'manage_options',
        'cfpm-popup-assignment',
        'cfpm_render_popup_assignment_page'
    );
}
add_action('admin_menu', 'cfpm_register_popup_assignment_page');

// Render the Popup Assignment page
function cfpm_render_popup_assignment_page() {
    $custom_fields = get_option('cfpm_custom_fields', []);
    $popup_assignments = get_option('cfpm_popup_assignments', []);

    // Handle form submission for assigning popups
    if (isset($_POST['cfpm_assign_popup'])) {
        $field = sanitize_text_field($_POST['cfpm_select_field']);
        $popup_id = sanitize_text_field($_POST['cfpm_select_popup']);
        if (!empty($field) && !empty($popup_id)) {
            $popup_assignments[$field] = $popup_id;
            update_option('cfpm_popup_assignments', $popup_assignments);
            echo '<div class="notice notice-success is-dismissible"><p>Popup assigned successfully!</p></div>';
        }
    }

    // Handle delete option for existing assignments
    if (isset($_POST['cfpm_delete_assignment'])) {
        $field_to_delete = sanitize_text_field($_POST['cfpm_field_to_delete']);
        if (isset($popup_assignments[$field_to_delete])) {
            unset($popup_assignments[$field_to_delete]);
            update_option('cfpm_popup_assignments', $popup_assignments);
            echo '<div class="notice notice-success is-dismissible"><p>Assignment deleted successfully!</p></div>';
        }
    }

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Assign Elementor Popups</h1>
        <hr class="wp-header-end">

        <div class="cfpm-section">
            <h2 class="cfpm-section-title">Select Field and Assign Popup</h2>
            <form method="post" class="cfpm-form">
                <select name="cfpm_select_field" required>
                    <option value="">Select a Custom Field</option>
                    <?php foreach ($custom_fields as $field) : ?>
                        <option value="<?php echo esc_attr($field); ?>"><?php echo esc_html($field); ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="cfpm_select_popup" required>
                    <option value="">Select a Popup</option>
                    <?php
                    $popups = cfpm_get_elementor_popups();
                    foreach ($popups as $popup) {
                        echo '<option value="' . esc_attr($popup->ID) . '">' . esc_html($popup->post_title) . '</option>';
                    }
                    ?>
                </select>

                <button type="submit" name="cfpm_assign_popup" class="button button-primary">Assign Popup</button>
            </form>
        </div>

        <div class="cfpm-section">
            <h2 class="cfpm-section-title">Existing Popup Assignments</h2>
            <ul class="cfpm-popup-assignments">
                <?php foreach ($popup_assignments as $field => $popup_id) : ?>
                    <li class="cfpm-assignment-item">
                        Brand: <?php echo esc_html($field); ?> --> Popup: <?php echo esc_html(get_the_title($popup_id)); ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="cfpm_field_to_delete" value="<?php echo esc_attr($field); ?>">
                            <button type="submit" name="cfpm_delete_assignment" class="button button-secondary">Delete Assignments</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <style>
        .cfpm-section {
            background-color: #f9f9f9;
            border: 1px solid #eaeaea;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .cfpm-section-title {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
        }
        .cfpm-popup-assignments {
            list-style-type: none;
            padding: 0;
        }
        .cfpm-assignment-item {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eaeaea;
        }
        .cfpm-assignment-item:last-child {
            border-bottom: none;
        }
    </style>
    <?php
}
// Get Elementor Popups
function cfpm_get_elementor_popups() {
    $args = array(
        'post_type' => 'elementor_library',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_elementor_template_type',
                'value' => 'popup',
            )
        )
    );
    return get_posts($args);
}

// ====================== CUSTOM FIELD IN PAGE EDITOR ======================

// Add custom meta box to page editor
function cfpm_add_custom_meta_box() {
    add_meta_box(
        'cfpm_custom_popup_field',
        'Custom Popup Condition',
        'cfpm_render_custom_meta_box',
        'page',
        'side'
    );
}
add_action('add_meta_boxes', 'cfpm_add_custom_meta_box');

// Render custom meta box
function cfpm_render_custom_meta_box($post) {
    $value = get_post_meta($post->ID, '_cfpm_custom_popup_condition', true);
    $custom_fields = get_option('cfpm_custom_fields', []);

    echo '<select name="cfpm_custom_popup_condition">';
    echo '<option value="">Select a condition</option>';
    foreach ($custom_fields as $field) {
        echo '<option value="' . esc_attr($field) . '" ' . selected($value, $field, false) . '>' . esc_html($field) . '</option>';
    }
    echo '</select>';
}

// Save custom field value from page editor
function cfpm_save_custom_meta_box($post_id) {
    if (array_key_exists('cfpm_custom_popup_condition', $_POST)) {
        update_post_meta($post_id, '_cfpm_custom_popup_condition', sanitize_text_field($_POST['cfpm_custom_popup_condition']));
    }
}
add_action('save_post', 'cfpm_save_custom_meta_box');

// Register the custom condition for Elementor
function cfpm_register_custom_popup_condition($conditions_manager) {
    require_once( __DIR__ . '/custom-conditions/custom-popup-condition.php' );
    $conditions_manager->get_condition('general')->register_sub_condition(new \CFPM_Custom_Popup_Condition());
}
add_action('elementor/theme/register_conditions', 'cfpm_register_custom_popup_condition');

function prefix_contact_form_custom_js() {
    ?>
    <script>
        jQuery(document).on('elementor/popup/show', function() {
            jQuery('.elementor-popup-modal .wpcf7-form').each(function(index, form) {
                // Initialize each form individually within the popup modal
                wpcf7.init(form);

                // Check if the Conditional Fields plugin is present and initialize it as well
                if (typeof wpcf7cf !== 'undefined') {
                    wpcf7cf.initForm(jQuery(form));
                }
            });
        });
    </script>
    <?php 
}
add_action('wp_footer', 'prefix_contact_form_custom_js', 100);
