<?php

namespace FrameZ\Models;

use FrameZ\Utils\Path;

class Settings
{
    public function register()
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function getSettings()
    {
        return [
            'framez_data_directory' => [
                'type' => 'text',
                'label' => __('Data Directory', 'framez'),
                'description' => __('The directory path where FrameZ stores its data files and compiled images on the server.', 'framez'),
                'default' => fn () => FZ_DATA_PATH,
            ]
        ];
    }

    public function registerSettings()
    {
        // Register a new setting for the plugin
        register_setting('framez_settings_group', 'framez_settings');

        // Add a settings section
        add_settings_section(
            'framez_settings_section',
            null,
            null,
            'framez-settings'
        );

        // Add all registered setting fields
        foreach ($this->getSettings() as $key => $setting) {
            add_settings_field(
                $key,
                $setting['label'],
                [$this, 'renderField' . ucfirst($setting['type'])],
                'framez-settings',
                'framez_settings_section',
                $setting
            );
            register_setting('framez_settings_group', 'framez_settings[' . $key . ']');
        }
    }

    public function addSettingsPage()
    {
        add_options_page(
            __('FrameZ Settings', 'framez'),
            __('FrameZ', 'framez'),
            'manage_options',
            'framez-settings',
            [$this, 'renderSettingsPage']
        );
    }

    public function renderSettingsPage()
    {
        // Check if user has permission to manage options
        if (!current_user_can('manage_options')) {
            return;
        }

        // Render the settings page view
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('FrameZ Settings', 'framez') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('framez_settings_group');
        do_settings_sections('framez-settings');
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function renderFieldText($args)
    {
        $options = get_option('framez_settings');
        $value = isset($options[$args['label']]) ? esc_attr($options[$args['label']]) : '';
        if (empty($value) && isset($args['default'])) {
            $value = is_callable($args['default']) ? call_user_func($args['default']) : $args['default'];
        }
        echo '<input type="text" name="framez_settings[' . $args['label'] . ']" value="' . $value . '" />';
        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }
}