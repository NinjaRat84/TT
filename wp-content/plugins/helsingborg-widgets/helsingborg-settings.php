<?php

function helsingborg_panel(){
  add_menu_page('Helsingborg',                  // Page title
                'Helsingborg',                  // Menu title
                'manage_options',               // Capability
                'helsingborg',                  // Slug
                'helsingborg_panel_func');      // Function

  add_submenu_page( 'helsingborg',                           // Parent slug
                    'Hantera evenemang',                     // Page title
                    'Hantera evenemang',                     // Menu title
                    'manage_options',                        // Capability
                    'helsingborg-eventhandling',             // Slug
                    'helsingborg_panel_func_eventhandling'); // Function

  add_submenu_page( 'helsingborg',
                    'Inställningar',
                    'Inställningar',
                    'manage_options',
                    'helsingborg-settings',
                    'helsingborg_panel_func_settings');

  // Create the page that loads the details for an event, dynamically built inside
  add_submenu_page( null,  // null makes the page accessible, but hidden in menu
                    'Ändra evenemang',
                    'Ändra evenemang',
                    'manage_options',
                    'helsingborg-event-details',
                    'helsingborg_panel_func_event_details');
}
add_action('admin_menu', 'helsingborg_panel');

function helsingborg_panel_func(){
  echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
  <h2>Helsingborg</h2></div>';
}
function helsingborg_panel_func_eventhandling(){
  include('classes/helsingborg-event-list-table.php');
  include('includes/panel_eventhandling.php');
}
function helsingborg_panel_func_settings(){
  echo '<div class="wrap"><div id="icon-options-general" class="icon32"><br></div>
  <h2>Inställningar</h2></div>';
}
function helsingborg_panel_func_event_details(){
  include('pages/event-page.php');
}
?>
