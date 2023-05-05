<?php
/*
Plugin Name: Indiecreatorz - Advanced Menu Visibility
Plugin URI: https://github.com/Lonsdale201
Description: Extra beállítások a Wordpress alapértelmezett menürendszeréhez. Kompatibilis: WooCommerce, WooCommerce Membership
Version: 0.01
Author: Soczó Kristóf
Author URI: https://github.com/Lonsdale201
License: GPL2
*/


require_once ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php';

// Check if WooCommerce active
function is_woocommerce_active() {
    return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
}

// Check if WooCommerce membership active
function is_wc_memberships_active() {
    return in_array('woocommerce-memberships/woocommerce-memberships.php', apply_filters('active_plugins', get_option('active_plugins')));
}


// Get WooCommerce products options
function get_wc_products_options() {
    $args = [
        'status' => 'publish',
        'limit' => -1,
        'return' => 'ids',
    ];
    $products_ids = wc_get_products($args);
    $options = [];

    foreach ($products_ids as $product_id) {
        $product = wc_get_product($product_id);
        $sku = $product->get_sku() ? ' (' . $product->get_sku() . ')' : ''; // If not have sku number not need ()
        $options[$product_id] = $product->get_name() . $sku;
    }

    return $options;
}
// Get all WooCommerce membership options
function get_wc_memberships_options() {
    $memberships = wc_memberships_get_membership_plans();
    $options = [];

    foreach ($memberships as $membership) {
        $options[$membership->id] = $membership->name;
    }

    return $options;
}


// Add custom fields to menu items
function my_custom_menu_item_fields($item_id, $item, $depth, $args) {
    $fields = [
        'visibility' => [
            'label' => __('Láthatóság', 'my-custom-menu-plugin'),
            'options' => [
                'all' => __('Mindenki', 'my-custom-menu-plugin'),
                'logged_in' => __('Csak bejelentkezett felhasználók', 'my-custom-menu-plugin'),
                'logged_out' => __('Csak kijelentkezett felhasználók', 'my-custom-menu-plugin'),
            ],
            'meta_key' => '_my_custom_menu_item_visibility',
        ],
    ];

    // Add WooCommerce product field if WooCommerce is active
    if (is_woocommerce_active()) {
        $fields['wc_product'] = [
            'label' => __('Mutasd, ha megvásárolták*', 'my-custom-menu-plugin'),
            'options' => get_wc_products_options(),
            'meta_key' => '_my_custom_menu_item_wc_product',
        ];
    }
    // Add Membership plans  field if WooCommerce Membership is active
    if (is_wc_memberships_active()) {
    $fields['wc_membership'] = [
        'label' => __('WooCommerce Membership', 'my-custom-menu-plugin'),
        'options' => get_wc_memberships_options(),
        'meta_key' => '_my_custom_menu_item_wc_membership',
    ];
}

    $fields['role'] = [
        'label' => __('Szerepkör', 'my-custom-menu-plugin'),
        'options' => wp_roles()->roles,
        'meta_key' => '_my_custom_menu_item_role',
    ];
    $fields['css_id'] = [
        'label' => __('Egyedi CSS ID', 'my-custom-menu-plugin'),
        'meta_key' => '_my_custom_menu_item_css_id',
    ];
    $fields['svg'] = [
        'label' => __('Inline SVG', 'my-custom-menu-plugin'),
        'meta_key' => '_my_custom_menu_item_svg',
        'type' => 'textarea',
    ];
    $fields['svg_position'] = [
        'label' => __('SVG pozíció', 'my-custom-menu-plugin'),
        'options' => [
            'before' => __('Menüelem előtt', 'my-custom-menu-plugin'),
            'after' => __('Menüelem után', 'my-custom-menu-plugin'),
        ],
        'meta_key' => '_my_custom_menu_item_svg_position',
    ];

    $fields['prefix'] = [
        'label' => __('Prefix', 'my-custom-menu-plugin'),
        'meta_key' => '_my_custom_menu_item_prefix',
    ];

    foreach ($fields as $field_name => $field) {
        if ($field_name === 'role') {
            $field_value = get_post_meta($item_id, $field['meta_key'], true);
            $field_value = is_array($field_value) ? $field_value : [];
            ?>
            <p class="description description-wide">
                <label for="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>">
                    <?php echo $field['label']; ?><br />
                    <select id="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>" class="widefat" name="menu-item-<?php echo $field_name; ?>[<?php echo $item_id; ?>][]" multiple>
                        <?php foreach($field['options'] as $option_key => $option_value): ?>
                            <option value="<?php echo $option_key; ?>" <?php echo in_array($option_key, $field_value) ? 'selected' : ''; ?>><?php echo is_array($option_value) ? $option_value['name'] : $option_value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </p>
            <?php
        } elseif ($field_name === 'wc_product') {
            $field_value = get_post_meta($item_id, $field['meta_key'], true);
            ?>
            <p class="description description-wide">
                <label for="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>">
                    <?php echo $field['label']; ?><br />
                    <select id="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>" class="widefat" name="menu-item-<?php echo $field_name; ?>[<?php echo $item_id; ?>]">
                        <?php
                        echo '<option value=""' . (empty($field_value) ? ' selected' : '') . '>' . __('Kérjük válassz', 'my-custom-menu-plugin') . '</option>';
                        foreach($field['options'] as $option_key => $option_value): ?>
                            <option value="<?php echo $option_key; ?>" <?php selected($field_value, $option_key); ?>><?php echo $option_value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </p>
            <?php
          } elseif ($field_name === 'wc_membership') {
    $field_value = get_post_meta($item_id, $field['meta_key'], true);
    ?>
    <p class="description description-wide">
        <label for="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>">
            <?php echo $field['label']; ?><br />
            <select id="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>" class="widefat" name="menu-item-<?php echo $field_name; ?>[<?php echo $item_id; ?>]">
                <?php
                echo '<option value=""' . (empty($field_value) ? ' selected' : '') . '>' . __('Kérjük válassz', 'my-custom-menu-plugin') . '</option>';
                foreach($field['options'] as $option_key => $option_value): ?>
                    <option value="<?php echo $option_key; ?>" <?php selected($field_value, $option_key); ?>><?php echo $option_value; ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </p>
    <?php
        } else {
            $field_value = get_post_meta($item_id, $field['meta_key'], true);
            if (isset($field['options'])) {
                if ($field_name === 'svg_position') {
                    ?>
                    <p class="description description-wide">
                        <?php echo $field['label']; ?><br />
                        <?php foreach ($field['options'] as $option_key => $option_value): ?>
                            <label>
                                <input type="radio" value="<?php echo $option_key; ?>" name="menu-item-<?php echo $field_name; ?>[<?php echo $item_id; ?>]" <?php checked($field_value, $option_key); ?> />
                                <?php echo $option_value; ?>
                            </label>
                        <?php endforeach; ?>
                    </p>
                    <?php
                } else {
                    ?>
                    <p class="description description-wide">
                        <label for="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>"><?php echo $field['label']; ?><br />
                            <select id="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>" class="widefat" name="menu-item-<?php echo $field_name; ?>[<?php echo $item_id; ?>]">
                                <?php foreach($field['options'] as $option_key => $option_value): ?>
                                    <option value="<?php echo $option_key; ?>" <?php selected($field_value, $option_key); ?>><?php echo $option_value; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </p>
                    <?php
                }
            } elseif (isset($field['type']) && $field['type'] === 'textarea') {
                ?>
                <p class="description description-wide">
                    <label for="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>">
                        <?php echo $field['label']; ?><br />
                        <textarea id="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>" class="widefat code edit-menu-item-<?php echo $field_name; ?>" name="menu-item-<?php echo $field_name; ?>[<?php echo $item_id; ?>]"><?php echo esc_textarea($field_value); ?></textarea>
                    </label>
                </p>
                <?php
            } else {
                ?>
                <p class="description description-wide">
                    <label for="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>">
                        <?php echo $field['label']; ?><br />
                        <input type="text" id="edit-menu-item-<?php echo $field_name; ?>-<?php echo $item_id; ?>" class="widefat code edit-menu-item-<?php echo $field_name; ?>" name="menu-item-<?php echo $field_name; ?>[<?php echo $item_id; ?>]" value="<?php echo esc_attr($field_value); ?>" />
                    </label>
                </p>
                <?php
            }
            
        }
    }
    
}

add_action('wp_nav_menu_item_custom_fields', 'my_custom_menu_item_fields', 10, 4);

function my_custom_menu_filter_menu_item_atts($atts, $item, $args, $depth) {
    $css_id = get_post_meta($item->ID, '_my_custom_menu_item_css_id', true);

    if ($css_id) {
        $atts['id'] = $css_id;
    }

    return $atts;
}
add_filter('nav_menu_link_attributes', 'my_custom_menu_filter_menu_item_atts', 10, 4);

// Save custom menu item fields
function my_custom_menu_item_fields_save($menu_id, $menu_item_db_id) {
    $fields_meta = [
        '_my_custom_menu_item_visibility',
        '_my_custom_menu_item_role',
        '_my_custom_menu_item_css_id',
        '_my_custom_menu_item_svg',
        '_my_custom_menu_item_svg_position',
        '_my_custom_menu_item_prefix',
    ];

    // Add WooCommerce product meta key if WooCommerce is active
    if (is_woocommerce_active()) {
        $fields_meta[] = '_my_custom_menu_item_wc_product';
    }

      // Add WooCommerce membership meta key if WooCommerce Membership is active
    if (is_wc_memberships_active()) {
        $fields_meta[] = '_my_custom_menu_item_wc_membership';
    }

    foreach ($fields_meta as $meta_key) {
        $field_name = str_replace('_my_custom_menu_item_', '', $meta_key);
        $field_value = isset($_POST['menu-item-' . $field_name][$menu_item_db_id]) ? $_POST['menu-item-' . $field_name][$menu_item_db_id] : '';
        update_post_meta($menu_item_db_id, $meta_key, $field_value);
    }
}
add_action('wp_update_nav_menu_item', 'my_custom_menu_item_fields_save', 10, 2);


// Check if the user has the required membership
function my_custom_menu_item_membership_check($menu_item) {
    $current_user = wp_get_current_user();
    $wc_membership = get_post_meta($menu_item->ID, '_my_custom_menu_item_wc_membership', true);

    if(empty($wc_membership)) {
        return true;
    }

    if(is_wc_memberships_active()) {
        $user_membership = wc_memberships_get_user_memberships($current_user->ID);

        if(!empty($user_membership)) {
            foreach($user_membership as $membership) {
                if($membership->get_plan_id() == $wc_membership && $membership->get_status() == 'active') {
                    return true;
                }
            }
        }
    }

    return false;
}

// Add the visibility filtering to the front-end menu rendering
function my_custom_menu_visibility_filter($menu_items, $args) {
    $filtered_menu_items = [];
    $current_user = wp_get_current_user();

    foreach ($menu_items as $menu_item) {
        $visibility = get_post_meta($menu_item->ID, '_my_custom_menu_item_visibility', true);
        $role = get_post_meta($menu_item->ID, '_my_custom_menu_item_role', true);
        $svg = get_post_meta($menu_item->ID, '_my_custom_menu_item_svg', true);
        $svg_position = get_post_meta($menu_item->ID, '_my_custom_menu_item_svg_position', true);

        $show_item = ($visibility === 'all' || ($current_user->ID > 0 && $visibility === 'logged_in') || ($current_user->ID === 0 && $visibility === 'logged_out'));

     // Check if the user has the required role
        if (!empty($role)) {
            $user_roles = (array) $current_user->roles;
            $show_item = count(array_intersect($user_roles, $role)) > 0;
        }
    // Check if the user has the required membership
         if ($show_item) {
            $show_item = my_custom_menu_item_membership_check($menu_item);
        }
    // Check if the user has purchased the required WooCommerce product
    if (is_woocommerce_active()) {
        $wc_product = get_post_meta($menu_item->ID, '_my_custom_menu_item_wc_product', true);

        if ($show_item && !empty($wc_product)) {
            $customer_orders = wc_get_orders([
                'customer_id' => $current_user->ID,
                'status' => 'completed',
                'limit' => -1,
            ]);

            $purchased_product = false;

            foreach ($customer_orders as $order) {
                if (my_custom_find_product_in_order($order, $wc_product)) {
                    $purchased_product = true;
                    break;
                }
            }

            $show_item = $purchased_product;
        }
    }

    if ($show_item) {
        $menu_item = my_custom_menu_item_add_prefix($menu_item); 
        $menu_item->title = ($svg_position === 'before' ? $svg : '') . $menu_item->title . ($svg_position === 'after' ? $svg : '');
        $filtered_menu_items[] = $menu_item;
    }
}

return $filtered_menu_items;
}

function my_custom_find_product_in_order($order, $product_id) {
    $items = $order->get_items();
    foreach ($items as $item) {
        if ($item->get_product_id() == $product_id) {
            return true;
        }
    }
    return false;
}


// prefix megjelenítése
function my_custom_menu_item_add_prefix($menu_item) {
    $prefix = get_post_meta($menu_item->ID, '_my_custom_menu_item_prefix', true);

    if (!empty($prefix)) {
        $menu_item->title = '<span class="indie-prefix">' . $prefix . '</span> ' . $menu_item->title;
    }

    return $menu_item;
}

add_filter('wp_nav_menu_objects', 'my_custom_menu_visibility_filter', 10, 2);

// Enqueue the scripts and styles for the menu plugin
function my_custom_menu_scripts($hook) {
    if ('nav-menus.php' == $hook) {
        // Enqueue the disable script
        wp_enqueue_script('my_custom_menu_disable_script', plugins_url('scripts/disable.js', __FILE__), ['jquery'], '1.0', true);
        wp_enqueue_style('my_custom_menu_styles', plugins_url('custom-menu-styles.css', __FILE__));
        // Enqueue the quick delete script
        wp_enqueue_script('my_custom_menu_delete_script', plugins_url('scripts/quick_delete.js', __FILE__), ['jquery'], '1.0', true);
        wp_localize_script('my_custom_menu_delete_script', 'my_custom_menu_delete_plugin', [
            'nonce' => wp_create_nonce('my_custom_menu_quick_delete_menu_item'),
            'quick_delete_title' => esc_attr(__("Törlés", "my-custom-menu-plugin")),
            'quick_delete_text' => esc_html(__("Törlés", "my-custom-menu-plugin")),
            'quick_delete_error' => __('Hiba történt a törlés során.', 'my-custom-menu-plugin'),
        ]);
    }
}
add_action('admin_enqueue_scripts', 'my_custom_menu_scripts'); 