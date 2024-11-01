<?php 
/*
    Plugin Name: Advanced Auto-Update disable TLC 
    Description: Disabilita aggiornamenti automatici per wp-core, plugin e tema.
    Version: 1.0.0
    Author: TLC

*/


function NAU_disable_update_TLC()
{
	/* Hook TLC_disable_update */
	do_action('TLC_disable_update');
	
	//Disabilita aggioramento automatico wp core
	add_filter( 'auto_update_core', '__return_false' );

	//Disabilita aggioranmento automatico plugin
	add_filter( 'auto_update_plugin', '__return_false' );

	//Disabilita aggiornamento automatico temi
	add_filter( 'auto_update_theme', '__return_false' );

}

add_action( 'ABSPATH', 'NAU_disable_update_TLC' );


// Nasconde notifica di update per wp core, plugin e temi (da richiamare sulla funzione add_filter)
function NAU_remove_updates()
{
	global $wp_version;
	return(object) array( 'last_checked' => time(), 'version_checked' => $wp_version );
}


function NAU_check_current_user_TLC()
{
	/* Hook TLC_check_current_user */
	do_action('TLC_check_current_user');

	$current_user = wp_get_current_user();
	
	if( $current_user->roles[0] == 'EditorTLC' )
	{

		function NAU_remove_plugins()
		{
			// Rimuove Plugins
			remove_menu_page('plugins.php');
		} 
		add_action( 'admin_menu', 'NAU_remove_plugins' );


		

		// Nasconde notifica di update per wp core
		add_filter( 'pre_site_transient_update_core', 'NAU_remove_updates' ); 

		// Nasconde notifica di update per plugin
		add_filter( 'pre_site_transient_update_plugins', 'NAU_remove_updates' );

		// Nasconde notifica di update per temi
		add_filter( 'pre_site_transient_update_themes', 'NAU_remove_updates' );




		function NAU_disable_plugins( $actions )
		{

			// Rimuove la possibilità di eliminare plugins
				unset( $actions['delete'] );
			// Rimuove la possibilità di disattivare plugins
				unset( $actions['deactivate'] );

			return $actions;
		}

		add_filter( 'plugin_action_links', 'NAU_disable_plugins', 10, 4 );


		function NAU_remove_from_bulk_actions( $actions )
		{
			// Rimuove la possibilità di disattivare plugins in gruppo
			unset( $actions['deactivate-selected'] );

			return $actions;
		}

		add_filter( 'bulk_actions-plugins', 'NAU_remove_from_bulk_actions' );


		// Blocco accesso alle pagine relative ai plugin 
		function NAU_remove_plugin_page($query) {
			global $pagenow,$post_type;
			if ($pagenow=='plugins.php' || $pagenow=='plugin-install.php' || $pagenow=='plugin-editor.php') {
				wp_redirect('/wp-admin');
			}
		}
	
		add_filter( 'setup_theme', 'NAU_remove_plugin_page' );
	}

	if( $current_user->roles[0] == 'Cliente' )
	{
		function NAU_remove_menus()
		{
			global $submenu;
			// Rimuove Temi, Personalizza, Editor Tema
			unset( $submenu['themes.php'][5] );
			unset( $submenu['themes.php'][6] );
			remove_action('admin_menu', '_add_themes_utility_last', 101);
			// Rimuove Strumenti
			remove_menu_page('tools.php');
		}

		add_action( 'admin_menu', 'NAU_remove_menus' );



		// Nasconde notifica di update per wp core
		add_filter( 'pre_site_transient_update_core', 'NAU_remove_updates' ); 

		// Nasconde notifica di update per plugin
		add_filter( 'pre_site_transient_update_plugins', 'NAU_remove_updates' );

		// Nasconde notifica di update per temi
		add_filter( 'pre_site_transient_update_themes', 'NAU_remove_updates' );



		// Abilita admin bar a frontend
		add_filter( 'show_admin_bar', '__return_true' );

	}
}

add_action( 'plugins_loaded', 'NAU_check_current_user_TLC' );



// Creato nuovo ruolo EditorTLC
function NAU_editorTLC_role()
{
	/* Hook TLC_editorTLC_role */
	do_action('TLC_editorTLC_role');

	$admin_role_set = get_role( 'administrator' )->capabilities;

	add_role('EditorTLC', 'EditorTLC', $admin_role_set);

}

add_action('init', 'NAU_editorTLC_role');


// Creato nuovo ruolo Cliente
function NAU_cliente_role()
{
	/* Hook TLC_cliente_role */
	do_action('TLC_cliente_role');

	$admin_role_set = get_role( 'editor' )->capabilities;

	add_role('Cliente', 'Cliente', $admin_role_set);

	$role = get_role( 'Cliente' );
	$role->add_cap( 'edit_theme_options' );

}

add_action('init', 'NAU_cliente_role');
