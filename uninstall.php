<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

    delete_option('oc_app_type');
    delete_option('oc_base_url');
    delete_option('oc_domain_name');
    
    delete_option('oc_restrictWPUserCreation');
    