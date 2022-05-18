<?php

/* Add admin style */
function twentytwentyonechild_enqueue_admin_script( $hook ) {
    if ( 'toplevel_page_books' != $hook ) {
        return;
    }
    wp_enqueue_style( 'twentytwentyonechild_admin', get_stylesheet_directory_uri(). '/assets/css/admin_style.css', array() );
}
add_action( 'admin_enqueue_scripts', 'twentytwentyonechild_enqueue_admin_script' );
/* EOF Add admin style */

/* Create Books DB table on theme active */
function twentytwentyonechild_create_db_table () {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    global $wpdb;
    $table_name = $wpdb->prefix . "books";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name tinytext NOT NULL,
    small_description text NOT NULL,
    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY  (id)
    ) $charset_collate;";
    maybe_create_table( $table_name, $sql );
}
add_action('after_switch_theme', 'twentytwentyonechild_create_db_table');
/* EOF Create Books DB table on theme active */

/* Register books admin menu */
function twentytwentyonechild_books_admin_menu() {
    add_menu_page(
        __( 'Books', 'twentytwentyonechild' ),
        __( 'Books', 'twentytwentyonechild' ),
        'manage_options',
        'books',
        'twentytwentyonechild_books_admin_page_content',
        'dashicons-book-alt',
        20
    );
}
add_action( 'admin_menu', 'twentytwentyonechild_books_admin_menu' );
/* EOF Register books admin menu */

/* Book Admin page content */
function twentytwentyonechild_books_admin_page_content () {
    global $wpdb;
    $table_name = $wpdb->prefix . "books";
    $url = get_admin_url( null, 'admin.php?page=books' );

    ?>
    <div class="wrap">
    <?php
    if( ( isset( $_GET['action'] ) && "" != $_GET['action'] ) && ( "add_new" == $_GET['action'] || "edit" == $_GET['action'] ) ) {
        
        $book_action = ( "add_new" == $_GET['action'] ) ? 'add_new' : 'edit';
        $name = $small_description = $book_id = "";
        if( 'edit' == $book_action && ( isset( $_GET['book_id'] ) && "" != $_GET['book_id'] ) ) {
            $book_data = $wpdb->get_row( $wpdb->prepare( "SELECT `id`, `name`, `small_description` FROM $table_name WHERE id = '%d'", $_GET['book_id'] )  );
            $name = ( isset( $book_data->name ) ) ? $book_data->name : "";
            $small_description = ( isset( $book_data->small_description ) ) ? $book_data->small_description : "";
            $book_id = $_GET['book_id'];
        }

        /* Add Edit Books Form */
        ?>
        <h1 class="wp-heading-inline">
            <?php 
                if( "add_new" == $book_action ) { 
                    _e( 'Add New Book', 'twentytwentyonechild' ); 
                } else { 
                    _e( 'Edit Book', 'twentytwentyonechild' ); 
                };
            ?>
        </h1>
        <a href="<?php echo $url; ?>" class="page-title-action"><?php _e( 'Back', 'twentytwentyonechild' ); ?></a>

        <?php 
            if( isset( $_GET['book_message'] ) && isset( $_GET['status'] ) && "" != $_GET['book_message'] && "" != $_GET['status'] ) {
                $notice_class = ( "1" == $_GET['status'] ) ? 'success' : 'error';
                ?>
                <div class="notice notice-<?php echo $notice_class; ?> is-dismissible">
                    <p><strong><?php echo $_GET['book_message']; ?></strong></p>
                </div>
                <?php
            }
        ?>

        <form action="<?php echo $url; ?>" method="post">
            <input type="hidden" name="book_action" value="<?php echo $book_action; ?>">
            <?php if( "" != $book_id ) { ?>
            <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
            <?php } ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="book_name">Book Title</label>
                        </th>
                        <td>
                            <input name="name" type="text" id="book_name" value="<?php echo $name; ?>" class="regular-text" required="required">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="small_description">Small Description</label>
                        </th>
                        <td>
                            <textarea name="small_description" rows="8" id="small_description" class="large-text" required="required"><?php echo $small_description; ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'twentytwentyonechild' ) ?>">
            </p>
        </form>
        <?php
        /* EOF Add Edit Books Form */
    } else {
        /* LIST BOOKS */

        // pagination logic
        $total_records = $wpdb->get_var( "SELECT count(*) FROM $table_name" );
        $total_pages = 1;
        $records_per_page = 5;
        $offset = 0;
        $book_paged = ( isset( $_GET['book_paged'] ) && "" != $_GET['book_paged'] ) ? (int) $_GET['book_paged'] : 1;

        if( $total_records > 0 ) {
            $total_pages = $total_records / $records_per_page;
            if( is_float( $total_pages ) ) {
                $total_pages = ((int) $total_pages) +1;
            }
            $offset = ( $records_per_page * $book_paged ) - $records_per_page;
            $prev_link = $next_link = "#";
            if ( ( $book_paged - 1 ) >= 1 ) {
                $prev_link = $url.'&book_paged='.( $book_paged - 1 );
            }

            if ( ( $book_paged + 1 ) <= $total_pages ) {
                $next_link = $url.'&book_paged='.( $book_paged + 1 );
            }
        }
        // EOF pagination logic

        $books_data = $wpdb->get_results( "SELECT * FROM $table_name LIMIT $offset, $records_per_page" );
        $date_format = get_option( 'date_format' );
        ?>        
            <h1 class="wp-heading-inline">
                <?php _e( 'Books', 'twentytwentyonechild' ); ?>
            </h1>
            <a href="<?php echo $url.'&action=add_new'; ?>" class="page-title-action">
                <?php _e( 'Add New', 'twentytwentyonechild' ); ?>
            </a>
                
            <?php 
                if( isset( $_GET['book_message'] ) && isset( $_GET['status'] ) && "" != $_GET['book_message'] && "" != $_GET['status'] ) {
                    $notice_class = ( "1" == $_GET['status'] ) ? 'success' : 'error';
                    ?>
                    <div class="notice notice-<?php echo $notice_class; ?> is-dismissible">
                        <p><strong><?php echo $_GET['book_message']; ?></strong></p>
                    </div>
                    <?php
                }
            ?>

            <table class="wp-list-table widefat fixed striped table-view-list books-table">
                <tr>
                    <th width="25" class="text-center">#</th>
                    <th width="300"><?php _e( 'Book Name', 'twentytwentyonechild' ); ?></th>
                    <th><?php _e( 'Small Description', 'twentytwentyonechild' ); ?></th>
                    <th width="100"><?php _e( 'Created At', 'twentytwentyonechild' ); ?></th>
                    <th width="75" class="text-center"><?php _e( 'Action', 'twentytwentyonechild' ); ?></th>
                </tr>
                <?php 
                    if( !empty( $books_data ) ) {
                        $i=0;
                        foreach( $books_data as $book ) {
                            $name = $book->name;
                            $small_description = ( strlen( $book->small_description ) > 200 ) ? substr( $book->small_description , 0, 197).'...' : $book->small_description;
                            $created_at = wp_date( $date_format, strtotime($book->created_at) );
                            $edit_url = $url.'&action=edit&book_id='.$book->id;
                            $delete_url = $url.'&action=delete&book_id='.$book->id;
                ?>
                <tr>
                    <td class="text-center"><?php echo $i+1; ?></td>
                    <td>
                        <a href="<?php echo $edit_url; ?>"><?php echo $name; ?></a>
                    </td>
                    <td>
                        <p><?php echo $small_description; ?></p>
                    </td>
                    <td>
                        <?php echo $created_at; ?>
                    </td>
                    <td class="text-center">
                        <a href="<?php echo $edit_url; ?>">
                            <span class="dashicons dashicons-edit"></span>
                        </a>
                        <a onclick="return confirm('Are you sure?');" href="<?php echo $delete_url; ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </a>
                    </td>
                </tr>
                <?php
                        $i++; 
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="5" class="text-center">
                            <h4><?php _e( 'Books Not Found', 'twentytwentyonechild' ); ?></h4>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        
            <?php 
                if( $total_pages > 1 ) {
                    ?>
                    <ul class="admin-pagination">
                        <li>
                            <?php if( "#" == $prev_link ) { ?>
                                <span><?php _e( 'Previous', 'twentytwentyonechild' ); ?></span>
                            <?php } else { ?>
                                <a href="<?php echo $prev_link; ?>"><?php _e( 'Previous', 'twentytwentyonechild' ); ?></a>
                            <?php } ?>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                        <li>
                            <?php if( $book_paged == $i ) { ?>
                                <span><?php echo $i; ?></span>
                            <?php } else { ?>
                                <a href="<?php echo $url.'&book_paged='.$i; ?>"><?php echo $i; ?></a>
                            <?php } ?>
                        </li>
                        <?php } ?>
                        <li>
                            <?php if( "#" == $next_link ) { ?>
                                <span><?php _e( 'Next', 'twentytwentyonechild' ); ?></span>
                            <?php } else { ?>
                                <a href="<?php echo $next_link; ?>"><?php _e( 'Next', 'twentytwentyonechild' ); ?></a>
                            <?php } ?>
                        </li>
                    </ul>
                    <?php
                }
            ?>
        <?php
        /* EOF LIST BOOKS */
    }
    ?>
    </div>
    <?php 
}
/* EOF Book Admin page content */

/* Books create, update & delete process */
function twentytwentyonechild_books_cud_process() {
    if( isset( $_POST['book_action'] ) && ( "add_new" == $_POST['book_action'] || "edit" == $_POST['book_action'] ) ) {
        // echo '<pre>'.print_r($_POST, true).'</pre>';
        // die();
        global $wpdb;
        $table_name = $wpdb->prefix . "books";
        $url = get_admin_url( null, 'admin.php?page=books' );
        $form_url = get_admin_url( null, 'admin.php?page=books&action=' );

        $form_url.= ( "add_new" == $_POST['book_action'] ) ? 'add_new' : 'edit';

        /* validation */
        if( !isset ( $_POST['name'] ) || "" == $_POST['name'] ) {
            $form_url.= '&book_message=Name is reqire&status=0';
            header("Location: ".$form_url);
            die();
        }
        if( !isset ( $_POST['small_description'] ) || "" == $_POST['small_description'] ) {
            $form_url.= '&book_message=Small description is reqire&status=0';
            header("Location: ".$form_url);
            die();
        }
        /* EOF validation */

        $input_name = sanitize_text_field($_POST['name']);
        $input_small_description = sanitize_text_field($_POST['small_description']);

        if( "add_new" == $_POST['book_action'] ) {
            $created_at = date('Y-m-d H:i:s');
            $insert_book = $wpdb->insert( 
                $table_name, 
                array( 
                    'name' => $input_name, 
                    'small_description' => $input_small_description,
                    'created_at' => $created_at
                ), 
                array( 
                    '%s', 
                    '%s', 
                    '%s' 
                )
            );

            if( is_wp_error( $insert_book ) ) {
                $url.= '&book_message=Something is Wrong! Please try again.&status=0';
                header("Location: ".$url);
                die();
            }

            $inserted_book_id = $wpdb->insert_id;
            if( is_wp_error( $inserted_book_id ) ) {
                $url.= '&book_message=Something is Wrong! Please try again.&status=0';
                header("Location: ".$url);
                die();
            }

            $url.= '&book_message=Book Inserted Successfuly&status=1';
            header("Location: ".$url);
            die();

        } elseif( "edit" == $_POST['book_action'] && ( isset( $_POST['book_id'] ) && "" != $_POST['book_id'] ) ) {
            $update_book = $wpdb->update( 
                $table_name, 
                array( 
                    'name' => $input_name, 
                    'small_description' => $input_small_description
                ),
                array( 'id' => $_POST['book_id'] ), 
                array( 
                    '%s', 
                    '%s'
                ),
                array( 
                    '%d'
                ) 
            );

            if( is_wp_error( $update_book ) ) {
                $url.= '&book_message=Something is Wrong! Please try again.&status=0';
                header("Location: ".$url);
                die();
            }

            $url.= '&book_message=Book Updated Successfuly&status=1';
            header("Location: ".$url);
            die();
        }
    } elseif( isset( $_GET['action'] ) && "delete" == $_GET['action'] && isset( $_GET['book_id'] ) && "" != $_GET['book_id'] ) {
        global $wpdb;
        $table_name = $wpdb->prefix . "books";
        $url = get_admin_url( null, 'admin.php?page=books' );
        $delete_book = $wpdb->delete( 
            $table_name, 
            array( 'id' => $_GET['book_id'] ), 
            array( '%d' )
        );

        if( is_wp_error( $delete_book ) ) {
            $url.= '&book_message=Something is Wrong! Please try again.&status=0';
            header("Location: ".$url);
            die();
        }

        $url.= '&book_message=Book Deleted Successfuly&status=1';
        header("Location: ".$url);
        die();
    }
}
add_action( 'admin_init', 'twentytwentyonechild_books_cud_process' );
/* EOF Books create, update & delete process */