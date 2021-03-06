<?php
/**
 * Class CPT_GSCR_On_Air Personalities
 *
 * Creates the post type.
 *
 * @since 1.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class CPT_GSCR_Radio_Shows extends RBM_CPT {

	public $post_type = 'radio-show';
	public $label_singular = null;
	public $label_plural = null;
	public $labels = array();
	public $icon = 'controls-volumeon';
	public $post_args = array(
		'hierarchical' => true,
		'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
		'has_archive' => false,
		'rewrite' => array(
			'slug' => 'radio-show',
			'with_front' => false,
			'feeds' => false,
			'pages' => true
		),
		'menu_position' => 11,
		//'capability_type' => 'radio-show',
	);

	/**
	 * CPT_GSCR_Radio_Shows constructor.
	 *
	 * @since 1.1.0
	 */
	function __construct() {

		// This allows us to Localize the Labels
		$this->label_singular = __( 'Radio Show', 'gscr-cpt-radio-shows' );
		$this->label_plural = __( 'Radio Shows', 'gscr-cpt-radio-shows' );

		$this->labels = array(
			'menu_name' => __( 'Radio Shows', 'gscr-cpt-radio-shows' ),
			'all_items' => __( 'All Radio Shows', 'gscr-cpt-radio-shows' ),
		);

		parent::__construct();

		add_action( 'init', array( $this, 'register_taxonomy' ) );
		
		add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'admin_column_add' ) );
		
		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'admin_column_display' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_filter( "post_type_labels_$this->post_type", array( $this, 'change_featured_image_banner_labels' ) );

		add_action( 'init', array( $this, 'register_post_status' ) );

		add_action( 'save_post', array( $this, 'create_child_radio_shows' ) );

		add_action( 'wp_trash_post', array( $this, 'before_trash_post' ) );

		add_action( 'untrash_post', array( $this, 'before_untrash_post' ) );

		add_action( 'before_delete_post', array( $this, 'before_delete_post' ) );

		add_action( 'rbm_cpts_radio_show_day_and_time', array( $this, 'add_day_time_fields' ), 10, 2 );

		add_filter( 'the_permalink', array( $this, 'the_permalink' ) );	
		add_filter( 'post_type_link', array( $this, 'get_permalink' ), 10, 4 );
		
	}

	/**
	 * Registers our Radio Show Categories Taxonomy
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function register_taxonomy() {

		$args = array(
            'hierarchical'          => true,
            'labels'                => $this->get_taxonomy_labels( __( 'Category', 'gscr-cpt-radio-shows' ), __( 'Categories', 'gscr-cpt-radio-shows' ) ),
            'show_in_menu'          => true,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'radio-show-category' ),
        );
    
		register_taxonomy( 'radio-show-category', 'radio-show', $args );
		
		$args = array(
            'hierarchical'          => false,
            'labels'                => $this->get_taxonomy_labels( __( 'Broadcaster', 'gscr-cpt-radio-shows' ), __( 'Broadcasters', 'gscr-cpt-radio-shows' ) ),
            'show_in_menu'          => true,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'radio-show-broacaster' ),
        );
    
        register_taxonomy( 'radio-show-broadcaster', 'radio-show', $args );

	}

	/**
     * DRYs up the code above a little
     *
     * @param   [string]  $singular   Singular Label
     * @param   [string]  $plural     Plural Label
     * @param   [string]  $menu_name  Menu Label. Defaults to Plural Label
     *
     * @since   1.1.0
     * @return  [array]               Taxonomy Labels
     */
    private function get_taxonomy_labels( $singular, $plural, $menu_name = false ) {

        if ( ! $menu_name ) {
            $menu_name = $plural;
        }

        $labels = array(
            'name'                       => $menu_name,
            'singular_name'              => $singular,
            'search_items'               => sprintf( __( 'Search %', 'gscr-cpt-radio-shows' ), $plural ),
            'popular_items'              => sprintf( __( 'Popular %s', 'gscr-cpt-radio-shows' ), $plural ),
            'all_items'                  => sprintf( __( 'All %', 'gscr-cpt-radio-shows' ), $plural ),
            'parent_item'                => sprintf( __( 'Parent %s', 'gscr-cpt-radio-shows' ), $singular ),
            'parent_item_colon'          => sprintf( __( 'Parent %s:', 'gscr-cpt-radio-shows' ), $singular ),
            'edit_item'                  => sprintf( __( 'Edit %s', 'gscr-cpt-radio-shows' ), $singular ),
            'update_item'                => sprintf( __( 'Update %s', 'gscr-cpt-radio-shows' ), $singular ),
            'add_new_item'               => sprintf( __( 'Add New %s', 'gscr-cpt-radio-shows' ), $singular ),
            'new_item_name'              => sprintf( __( 'New %s Name', 'gscr-cpt-radio-shows' ), $singular ),
            'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'gscr-cpt-radio-shows' ), $plural ),
            'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'gscr-cpt-radio-shows' ), $plural ),
            'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'gscr-cpt-radio-shows' ), $plural ),
            'not_found'                  => sprintf( __( 'No %s found.', 'gscr-cpt-radio-shows' ), $plural ),
            'menu_name'                  => $menu_name,
        );

        return $labels;

    }
	
	/**
	 * Adds an Admin Column
	 * 
	 * @param		array $columns Array of Admin Columns
	 *                                       
	 * @access		public
	 * @since		1.1.0
	 * @return		array Modified Admin Column Array
	 */
	public function admin_column_add( $columns ) {
		
		$columns['on-air-personality'] = __( 'On Air Personality/Personalities', 'gscr-cpt-radio-shows' );
		
		return $columns;
		
	}
	
	/**
	 * Displays data within Admin Columns
	 * 
	 * @param		string  $column  Admin Column ID
	 * @param		integer $post_id Post ID
	 *                               
	 * @access		public
	 * @since		1.1.0
	 * @return		void
	 */
	public function admin_column_display( $column, $post_id ) {
		
		switch ( $column ) {
				
			case 'on-air-personality' :
				
				$connected_posts = rbm_cpts_get_p2p_children( 'on-air-personality', $post_id );
				
				if ( ! is_array( $connected_posts ) ) $connected_posts = array( $connected_posts );
				
				echo '<ul style="margin-top: 0; list-style-type: disc; padding-left: 1.25em;">';
				foreach ( $connected_posts as $connected ) : if ( empty( $connected ) ) continue; ?>

					<li>
						<?php edit_post_link( get_the_title( $connected ), '', '', $connected ); ?>
					</li>
					
				<?php endforeach;
				echo '</ul>';
				
				break;
			case 'default' :
				echo rbm_field( $column, $post_id );
				break;
				
		}
		
	}

	/**
	 * Registers our Meta Boxes
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function add_meta_boxes() {

		add_meta_box(
			'radio-show-occurrences',
			__( 'Radio Show Occurrences', 'gscr-cpt-radio-shows' ),
			array( $this, 'radio_show_metabox_content' ),
			$this->post_type,
			'normal'
		);
		
		add_meta_box(
			'radio-show-side-meta',
			__( 'Radio Show Meta', 'gscr-cpt-radio-shows' ),
			array( $this, 'radio_show_side_metabox_content' ),
			$this->post_type,
			'side'
		);

		add_meta_box(
			'radio-show-background-image',
			apply_filters( 'gscr_radio_show_background_image_label', __( 'Background Image', 'gscr-cpt-radio-shows' ) ),
			array( $this, 'radio_show_background_image_metabox_content' ),
			$this->post_type,
			'side',
			'low'
		);

		add_meta_box(
			'radio-show-headshot-image',
			apply_filters( 'gscr_radio_show_headshot_image_label', __( 'Headshot Image', 'gscr-cpt-radio-shows' ) ),
			array( $this, 'radio_show_headshot_image_metabox_content' ),
			$this->post_type,
			'side',
			'low'
		);

	}

	/**
	 * Adds Metabox Content for our Radio Show Occurrences Meta Box
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function radio_show_metabox_content() {

		rbm_cpts_do_field_repeater( array(
			'name' => 'radio_show_times',
			'group' => 'radio_show_meta',
			'sortable' => false,
			'fields' => array(
				'radio_show_day_and_time' => array(
					'type' => 'hook',
					'args' => array(
						'fields' => array(
							'days_of_the_week' => array(
								'type' => 'select',
								'args' => array(
									'label' => '<strong>' . __( 'Day(s) of the Week', 'gscr-cpt-radio-shows' ) . '</strong>',
									'options' => gscr_get_weekdays(),
									'multiple' => true,
								),
							),
							'start_time' => array(
								'type' => 'timepicker',
								'args' => array(
									'label' => '<strong>' . __( 'Start Time', 'gscr-cpt-radio-shows' ) . '</strong>',
									'default' => date_i18n( 'H:i', strtotime( 'noon' ) ),
								),
							),
							'end_time' => array(
								'type' => 'timepicker',
								'args' => array(
									'label' => '<strong>' . __( 'End Time', 'gscr-cpt-radio-shows' ) . '</strong>',
									'default' => date_i18n( 'H:i', strtotime( 'noon' ) ),
								),
							),
						),
						'wrapper_classes' => array(
							'fieldhelpers-col',
							'fieldhelpers-col-2',
						),
					),
				),
				'broadcast_type' => array(
					'type' => 'radio',
					'args' => array(
						'label' => '<strong>' . __( 'Broadcast Type', 'gscr-cpt-radio-shows' ) . '</strong>',
						'options' => array(
							'live' => __( 'Live', 'gscr-cpt-radio-shows' ),
							'encore' => __( 'Encore', 'gscr-cpt-radio-shows' ),
							'best-of' => __( '"The Best Of"', 'gscr-cpt-radio-shows' ),
							'pre_recorded' => __( 'Pre-Recorded', 'gscr-cpt-radio-shows' ),
							'other' => __( 'Other', 'gscr-cpt-radio-shows' ),
						),
						'wrapper_classes' => array(
							'fieldhelpers-col',
							'fieldhelpers-col-2',
						),
					),
				),
				'post_ids' => array(
					'type' => 'hidden',
				),
				'post_ids_to_delete' => array(
					'type' => 'hidden',
				),
			),
		) );

		rbm_cpts_do_field_hidden( array(
			'name' => 'radio_show_occurrences_to_delete',
		) );

		rbm_cpts_init_field_group( 'radio_show_meta' );

	}

	/**
	 * Adds Metabox Content for our Radio Show Metabox on the side
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function radio_show_side_metabox_content() {

		rbm_cpts_do_field_toggle( array(
			'name' => 'radio_show_is_local',
			'group' => 'radio_show_side_meta',
			'label' => __( 'Local Radio Show?', 'gscr-cpt-radio-shows' ),
		) );

		rbm_cpts_do_field_text( array(
			'name' => 'radio_show_call_in',
			'group' => 'radio_show_side_meta',
			'label' => '<strong>' . __( 'Call-In Number', 'gscr-cpt-radio-shows' ) . '</strong>',
		) );

		rbm_cpts_do_field_text( array(
			'name' => 'radio_show_email',
			'group' => 'radio_show_side_meta',
			'label' => '<strong>' . __( 'Email Address', 'gscr-cpt-radio-shows' ) . '</strong>',
		) );

		rbm_cpts_init_field_group( 'radio_show_side_meta' );

	}

	/**
	 * Adds Metabox Content for our Background Image Meta Box
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function radio_show_background_image_metabox_content() {

		rbm_cpts_do_field_media( array(
			'name' => 'radio_show_background_image',
			'type' => 'image',
			'group' => 'radio_show_background_image',
		) );

		rbm_cpts_do_field_colorpicker( array(
			'name' => 'radio_show_background_image_color',
			'label' => '<strong>' . __( 'Background Color (Used if no Background Image is chosen)', 'gscr-cpt-radio-shows' ) . '</strong>',
			'group' => 'radio_show_background_image',
			'default' => '',
		) );

		rbm_cpts_init_field_group( 'radio_show_background_image' );

	}

	/**
	 * Adds Metabox Content for our Headshot Image Meta Box
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function radio_show_headshot_image_metabox_content() {

		rbm_cpts_do_field_media( array(
			'name' => 'radio_show_headshot_image',
			'type' => 'image',
			'group' => 'radio_show_headshot_image',
		) );

		rbm_cpts_init_field_group( 'radio_show_headshot_image' );

	}

	/**
	 * Enqueues the necessary JS/CSS on the Radio Show Edit Screen
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function admin_enqueue_scripts() {

		$current_screen = get_current_screen();
		global $pagenow;
		
		if ( $current_screen->post_type == 'radio-show' && 
			( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) ) {

			wp_enqueue_script( 'gscr-cpt-radio-shows-admin' );
			wp_enqueue_style( 'gscr-cpt-radio-shows-admin' );

			add_filter( 'rbm_fieldhelpers_load_select2', '__return_true' );

		}

	}

	/**
	 * Change Featured Image Labels Radio Shows
	 * 
	 * @param		array $labels Featured Image Labels
	 *            
	 * @access		public                          
	 * @since		1.1.0
	 * @return		array Featured Image Labels
	 */
	public function change_featured_image_banner_labels( $labels ) {

		$labels->featured_image = apply_filters( 'gscr_radio_show_logo_image_label', __( 'Logo Image', 'gscr-cpt-radio-shows' ) );
		$labels->set_featured_image = __( 'Set Logo Image', 'gscr-cpt-radio-shows' );
		$labels->remove_featured_image = __( 'Remove Logo Image', 'gscr-cpt-radio-shows' );
		$labels->use_featured_image = __( 'Use as Logo Image', 'gscr-cpt-radio-shows' );

		return $labels;

	}

	/**
	 * Create/Update/Delete Child Radio Shows which are used for determining our Schedule
	 *
	 * @param   integer  $post_id  WP_Post ID
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function create_child_radio_shows( $post_id ) {

		if ( get_post_type( $post_id ) !== 'radio-show' ) 
			return;
	
		// Autosave, do nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
			return;
		
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;
		
		// Return if it's a post revision
		if ( false !== wp_is_post_revision( $post_id ) )
			return;

		$to_delete = array();

		// Gather any deletions caused by unchecking a day
		if ( isset( $_POST['rbm_cpts_radio_show_times'] ) ) {

			foreach ( $_POST['rbm_cpts_radio_show_times'] as $occurrence ) {

				$delete_ids = json_decode( stripslashes( $occurrence['post_ids_to_delete'] ), true );
				$delete_ids = ( $delete_ids ) ? $delete_ids : array();

				foreach ( $delete_ids as $delete_id ) {
					$to_delete[] = $delete_id;
				}

			}

		}

		// Delete Occurrences that have been "marked for deletion"
		if ( isset( $_POST['rbm_cpts_radio_show_occurrences_to_delete'] ) || ! empty( $to_delete ) ) {

			if ( isset( $_POST['rbm_cpts_radio_show_occurrences_to_delete'] ) ) {
				$delete_ids = explode( ',', $_POST['rbm_cpts_radio_show_occurrences_to_delete'] );
			}
			else {
				$delete_ids = array();
			}

			$delete_ids = array_filter( $delete_ids, function( $item ) {
				return $item !== '';
			} );

			// Ensure we also delete any "pending" Deletions which would have happened from unchecking a day
			$delete_ids = array_merge( $delete_ids, $to_delete );

			foreach ( $delete_ids as $id ) {

				// Make sure a default ID didn't make it through somehow
				if ( (int) $id == 0 ) continue;

				wp_delete_post( (int) $id, true );

			}

		}

		if ( isset( $_POST['rbm_cpts_radio_show_times'] ) ) {

			// Prevent accidentally causing an infinite loop
			remove_action( 'save_post', array( $this, 'create_child_radio_shows' ) );

			$post_status = ( isset( $_POST['post_status'] ) && $_POST['post_status'] == 'publish' ) ? 'radioshow-occurrence' : 'radioshow-draft';

			foreach ( $_POST['rbm_cpts_radio_show_times'] as &$occurrence ) {

				$occurrence_ids = json_decode( stripslashes( $occurrence['post_ids'] ), true );
				$occurrence_ids = ( $occurrence_ids ) ? $occurrence_ids : array();

				$default_days = array();
				foreach ( $occurrence['days_of_the_week'] as $day_index ) {
					$default_days[ $day_index ] = 0;
				}

				// Allow creating a new Occurrence for a Day if needed
				foreach ( $default_days as $day_index => $value ) {

					if ( ! isset( $occurrence_ids[ $day_index ] ) ) {
						$occurrence_ids[ $day_index ] = 0;
					}

				}

				foreach ( $occurrence_ids as $day_index => $occurrence_id ) {

					$created_id = wp_insert_post( array(
						'ID' => $occurrence_id,
						'post_type' => 'radio-show',
						'post_title' => $_POST['post_title'],
						'post_content' => $_POST['post_content'],
						'post_parent' => $post_id,
						'post_status' => $post_status, // Use custom Post Status to prevent it from being accessible on the frontend outside of our schedule-building queries
					), true );
	
					if ( is_wp_error( $created_id ) ) {
						$errors = implode( ';', $created_id->get_error_messages() );
						error_log( $errors );
						continue;
					}

					// Setting here for later
					$occurrence_ids[ $day_index ] = $created_id;

					foreach ( $occurrence as $meta_key => $meta_value ) {

						// No point in storing this
						if ( $meta_key == 'post_ids' || $meta_key == 'rbm_cpts_post_ids_to_delete' ) continue;
	
						// Assign to the Child so we can use this information for sorting/querying outside of a Serialized Repeater
						update_post_meta( $created_id, "rbm_cpts_$meta_key", $meta_value );
	
					}

					// Ensure our day of the week gets saved
					update_post_meta( $created_id, 'rbm_cpts_day_of_the_week', $day_index );

				}

				// Save here in the Parent
				$occurrence['post_ids'] = json_encode( $occurrence_ids, JSON_FORCE_OBJECT );

			}

			add_action( 'save_post', array( $this, 'create_child_radio_shows' ) );

			// Update with our changes to include Post IDs
			update_post_meta( $post_id, 'rbm_cpts_radio_show_times', $_POST['rbm_cpts_radio_show_times'] );

		}

	}

	/**
	 * Creates our custom Post Status which is applied to Radio Show Occurrences
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function register_post_status() {

		register_post_status( 'radioshow-occurrence', array(
			'label' => __( 'Radio Show Occurrence (Hidden)', 'gscr-cpt-radio-shows' ),
			'public' => false, // We're going to show all data on a main, Single template for the parent
			'exclude_from_search' => true,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => false,
			'post_type' => 'radio-show',
			'internal' => true,
		) );

		register_post_status( 'radioshow-draft', array(
			'label' => __( 'Radio Show Occurrence Draft (Hidden)', 'gscr-cpt-radio-shows' ),
			'public' => false, // We're going to show all data on a main, Single template for the parent
			'exclude_from_search' => true,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => false,
			'post_type' => 'radio-show',
			'internal' => true,
		) );

	}

	/**
	 * Runs before Trashing a Radio Show. This is different from Deletion
	 *
	 * @param   integer  $post_id  WP_Post ID
	 *
	 * @access	public
	 * @since	1.1.1
	 * @return  void
	 */
	public function before_trash_post( $post_id ) {

		// Watch only Radio Shows
		if ( get_post_type( $post_id ) !== 'radio-show' ) return;

		remove_action( 'save_post', array( $this, 'create_child_radio_shows' ) );
		
		$query = new WP_Query( array(
			'post_type' => 'radio-show',
			'post_status' => 'radioshow-occurrence',
			'post_parent' => $post_id,
			'posts_per_page' => -1,
			'fields' => 'ids',
		) );

		if ( ! $query->have_posts() ) return;

		foreach ( $query->posts as $delete_id ) {

			$radio_show = get_post( $delete_id );

			$error = wp_insert_post( array(
				'ID' => $delete_id,
				'post_type' => 'radio-show',
				'post_status' => 'radioshow-draft',
				'post_title' => $radio_show->post_title,
				'post_content' => $radio_show->post_content,
				'post_parent' => $post_id,
			), true );

		}

	}

	/**
	 * Runs before Restoring a deleted Radio Show
	 *
	 * @param   integer  $post_id  WP_Post ID
	 *
	 * @access	public
	 * @since	1.1.1
	 * @return  void
	 */
	public function before_untrash_post( $post_id ) {

		// Watch only Radio Shows
		if ( get_post_type( $post_id ) !== 'radio-show' ) return;

		remove_action( 'save_post', array( $this, 'create_child_radio_shows' ) );
		
		$query = new WP_Query( array(
			'post_type' => 'radio-show',
			'post_status' => 'radioshow-draft',
			'post_parent' => $post_id,
			'posts_per_page' => -1,
			'fields' => 'ids',
		) );

		if ( ! $query->have_posts() ) return;

		foreach ( $query->posts as $delete_id ) {

			$radio_show = get_post( $delete_id );

			$error = wp_insert_post( array(
				'ID' => $delete_id,
				'post_type' => 'radio-show',
				'post_status' => 'radioshow-occurrence',
				'post_title' => $radio_show->post_title,
				'post_content' => $radio_show->post_content,
				'post_parent' => $post_id,
			), true );

		}

	}

	/**
	 * Deletes all stored Radio Show Occurrences for a Radio Show on Deletion from the Trash
	 *
	 * @param   integer  $post_id  WP_Post ID
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function before_delete_post( $post_id ) {

		// Watch only Radio Shows
		if ( get_post_type( $post_id ) !== 'radio-show' ) return;
		
		$query = new WP_Query( array(
			'post_type' => 'radio-show',
			'post_status' => 'radioshow-draft',
			'post_parent' => $post_id,
			'posts_per_page' => -1,
			'fields' => 'ids',
		) );

		if ( ! $query->have_posts() ) return;

		foreach ( $query->posts as $delete_id ) {
			wp_delete_post( $delete_id, true );
		}

	}

	/**
	 * Outputs our Day and Time fields. These are done within a Hook in order to wrap them all within a single Field as a way to make a column
	 *
	 * @param   array  $args   Hook Field Args
	 * @param   array  $value  Values for the current Repeater Row
	 *
	 * @access	public
	 * @since	1.1.0
	 * @return  void
	 */
	public function add_day_time_fields( $args, $value ) {

		foreach ( $args['fields'] as $field_name => $field ) {

			// If there's no previously saved values, then the Repeater will not know that a default value should exist for these indices
			if ( ! isset( $value[ $field_name ] ) ) {
				$value[ $field_name ] = '';
			}

			// Tell RBM FH that these are within our Repeater
			$field['args']['repeater'] = 'rbm_cpts_radio_show_times';
			$field['args']['no_init']  = true;

			// Ensure the name and value properties are populated correctly
			$field['args']['id']    = "{$field['args']['repeater']}_{$field_name}";
			$field['args']['value'] = $value[ $field_name ];

			// Create the fields
			call_user_func(
				array(
					$args['fields_instance'],
					"do_field_$field[type]",
				),
				$field_name,
				$field['args']
			);

		}

	}

	/**
	 * Replace the_permalink() calls on the Frontend with the new Permastruct
	 * 
	 * @param		string $url The Post URL
	 *                
	 * @access		public
	 * @since		2.0.0
	 * @return		string Modified URL
	 */
	public function the_permalink( $url ) {
		
		global $post;

		if ( ! is_object( $post ) ) return $url;
		
		if ( $post->post_type !== 'radio-show' ) return $url;

		if ( $post->post_status !== 'radioshow-occurrence' ) return $url;

		$url = get_permalink( $post->post_parent );
		
		return $url;
		
	}
	
	/**
	 * Replace get_peramlink() calls on the Frontend with the new Permastruct
	 * 
	 * @param		string  $url       The Post URL
	 * @param		object  $post      WP Post Object
	 * @param		boolean $leavename Whether to leave the Post Name
	 * @param		boolean $sample    Is it a sample permalink?
	 *     
	 * @access		public
	 * @since		2.0.0
	 * @return		string  Modified URL
	 */
	public function get_permalink( $url, $post, $leavename = false, $sample = false ) {
		
		if ( $post->post_type !== 'radio-show' ) return $url;

		if ( $post->post_status !== 'radioshow-occurrence' ) return $url;

		$url = get_permalink( $post->post_parent );
		
		return $url;
		
	}
	
}