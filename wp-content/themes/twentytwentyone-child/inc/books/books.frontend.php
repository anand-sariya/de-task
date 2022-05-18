<?php
//[books_list]

function twentytwentyonechild_books_list_shortcode_cb( $atts ) {
    global $wpdb;
    $url = get_permalink();
    $table_name = $wpdb->prefix . "books";
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
            $prev_link = add_query_arg( 'book_paged', ( $book_paged - 1 ).'#books_records', $url );
        }

        if ( ( $book_paged + 1 ) <= $total_pages ) {
            $next_link = add_query_arg( 'book_paged', ( $book_paged + 1 ).'#books_records', $url );
        }
    }
    $books_data = $wpdb->get_results( "SELECT * FROM $table_name LIMIT $offset, $records_per_page" );
    $date_format = get_option( 'date_format' );
    
    ob_start();
    echo '<div id="books_records" class="books_records">';
    if( ! empty( $books_data ) ) {
        ?>
        <h3><?php _e( 'Books', 'twentytwentyonechild' ); ?></h3>
    <?php
        foreach( $books_data as $book ) {
            $name = $book->name;
            $small_description = $book->small_description;
            $created_at = wp_date( $date_format, strtotime($book->created_at) );
    ?>
    <div class="book_card">
        <p><b><?php _e( 'Book Name', 'twentytwentyonechild' ); ?>:</b> <?php echo $name; ?></p>
        <p><b><?php _e( 'Create Date', 'twentytwentyonechild' ); ?>:</b> <?php echo $created_at; ?></p>
        <p><b><?php _e( 'Description', 'twentytwentyonechild' ); ?>:</b> <?php echo $small_description; ?></p>
    </div>
    <br>
    <?php 
        }
        if( $total_pages > 1 ) {
            ?>
            <div class="book_pagination">
                <ul>
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
                            <a href="<?php echo add_query_arg( 'book_paged', $i.'#books_records', $url );; ?>"><?php echo $i; ?></a>
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
            </div>
            <?php
        }
    } else {
        echo '<div class="books_not_found">'.__( 'Books not found', 'twentytwentyonechild' ).'</div>';
    }
    echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'books_list', 'twentytwentyonechild_books_list_shortcode_cb' );