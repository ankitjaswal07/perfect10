<?php
/** Authorized WXR-derived package importer. Separate from the original sample-site importer. */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

function p10_source_manifest() {
	static $data;
	if ( null === $data ) {
		$file = P10_DIR . 'demo/source-manifest.json';
		$data = is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null;
	}
	return is_array( $data ) && isset( $data['schema'] ) && 1 === $data['schema'] ? $data : array();
}

/**
 * Validate the manifest before anything is written.
 *
 * Checks structure, required keys, duplicate source IDs and the media
 * relationships the importer relies on. Returns human-readable errors rather
 * than silently skipping malformed rows.
 *
 * @return array{ok:bool,errors:string[],counts:array}
 */
function p10_source_validate() {
	$data   = p10_source_manifest();
	$errors = array();
	$counts = array(
		'profiles'         => 0,
		'published'        => 0,
		'drafts'           => 0,
		'media'            => 0,
		'missing_binaries' => 0,
		'staged'           => 0,
	);

	if ( ! $data ) {
		return array(
			'ok'     => false,
			'errors' => array( __( 'demo/source-manifest.json is missing, unreadable or has an unsupported schema.', 'perfect10' ) ),
			'counts' => $counts,
		);
	}

	if ( empty( $data['source'] ) || empty( $data['authorization'] ) ) {
		$errors[] = __( 'Manifest source and authorization metadata are required.', 'perfect10' );
	}

	$profile_ids = array();
	$media_ids   = array();
	foreach ( (array) ( $data['profiles'] ?? array() ) as $index => $profile ) {
		++$counts['profiles'];
		$id = isset( $profile['source_id'] ) ? absint( $profile['source_id'] ) : 0;
		if ( ! $id || isset( $profile_ids[ $id ] ) ) {
			$errors[] = sprintf( __( 'Profile row %d has a missing or duplicate source_id.', 'perfect10' ), $index );
			continue;
		}
		$profile_ids[ $id ] = true;
		if ( 'publish' === ( $profile['status'] ?? '' ) ) {
			++$counts['published'];
		} else {
			++$counts['drafts'];
		}
		foreach ( array( 'slug', 'title', 'content', 'terms', 'media_ids' ) as $key ) {
			if ( ! isset( $profile[ $key ] ) ) {
				$errors[] = sprintf( __( 'Profile %1$d is missing “%2$s”.', 'perfect10' ), $id, $key );
			}
		}
	}

	foreach ( (array) ( $data['media'] ?? array() ) as $index => $media ) {
		++$counts['media'];
		$id = isset( $media['source_id'] ) ? absint( $media['source_id'] ) : 0;
		if ( ! $id || isset( $media_ids[ $id ] ) ) {
			$errors[] = sprintf( __( 'Media row %d has a missing or duplicate source_id.', 'perfect10' ), $index );
			continue;
		}
		$media_ids[ $id ] = true;
		if ( ! empty( $media['missing_binary'] ) || 'missing_binary' === ( $media['outcome'] ?? '' ) ) {
			++$counts['missing_binaries'];
		}
		if ( empty( $media['path'] ) ) {
			$errors[] = sprintf( __( 'Media %d has no relative path.', 'perfect10' ), $id );
		}
	}

	$root = WP_CONTENT_DIR . '/p10-source-media';
	if ( is_dir( $root ) ) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
		);
		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				++$counts['staged'];
			}
		}
	}

	return array(
		'ok'     => ! $errors,
		'errors' => $errors,
		'counts' => $counts,
	);
}

/**
 * Count outcomes for the exported report.
 *
 * @param array $state Import state.
 * @return array
 */
function p10_source_summary( $state ) {
	$summary = array(
		'profiles' => array(),
		'media'    => array(),
		'total'    => array(),
	);
	foreach ( array( 'profiles', 'media' ) as $bucket ) {
		foreach ( (array) ( $state[ $bucket ] ?? array() ) as $row ) {
			$outcome = isset( $row['outcome'] ) ? (string) $row['outcome'] : 'unknown';
			$summary[ $bucket ][ $outcome ] = ( $summary[ $bucket ][ $outcome ] ?? 0 ) + 1;
			$summary['total'][ $outcome ]   = ( $summary['total'][ $outcome ] ?? 0 ) + 1;
		}
	}
	return $summary;
}
function p10_source_key( $id ) {
	return 'lasperfect10s-2026-10-16:' . absint( $id ); }
function p10_source_find( $type, $key, $meta = '_p10_source_key' ) {
	$ids = get_posts(
		array(
			'post_type'   => $type,
			'post_status' => array_keys( get_post_stati() ),
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => $meta,
			'meta_value'  => $key,
		)
	);
	return $ids ? (int) $ids[0] : 0;
}

add_action(
	'admin_menu',
	function () {
		add_theme_page( 'Authorized Source Import', 'Authorized Source Import', 'manage_options', 'p10-source', 'p10_source_screen' );
	}
);
function p10_source_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return; }
	$state = get_option( 'p10_source_state', array() );
	$check = p10_source_validate();
	$counts = $check['counts'];
	echo '<div class="wrap"><h1>Authorized Source Import</h1>';
	echo '<p>' . esc_html( sprintf(
		/* translators: 1: published profiles, 2: drafts, 3: media references, 4: staged files. */
		__( '%1$s published profiles; %2$s drafts excluded. %3$s attachment references; %4$s files staged in WP_CONTENT_DIR/p10-source-media/.', 'perfect10' ),
		number_format_i18n( $counts['published'] ),
		number_format_i18n( $counts['drafts'] ),
		number_format_i18n( $counts['media'] ),
		number_format_i18n( $counts['staged'] )
	) ) . '</p>';
	echo '<p>' . esc_html__( 'This importer only creates the authorized source records. It does not run the sample-site importer or change the homepage, menus or Elementor templates.', 'perfect10' ) . '</p>';

	if ( ! $check['ok'] ) {
		echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'The manifest is not valid. Nothing will be imported until this is fixed.', 'perfect10' ) . '</strong></p><ul>';
		foreach ( $check['errors'] as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}
		echo '</ul></div>';
	} elseif ( $counts['missing_binaries'] || $counts['staged'] < $counts['media'] ) {
		echo '<div class="notice notice-warning"><p>' . esc_html( sprintf(
			/* translators: 1: missing count, 2: media count. */
			__( '%1$s of %2$s media records have no staged binary. They will be skipped with an explicit outcome; nothing is hotlinked or invented.', 'perfect10' ),
			number_format_i18n( max( $counts['missing_binaries'], $counts['media'] - $counts['staged'] ) ),
			number_format_i18n( $counts['media'] )
		) ) . '</p></div>';
	}

	echo '<p>Back up database and uploads first. Confirm reuse rights. Local media may be staged by an administrator in <code>WP_CONTENT_DIR/p10-source-media/</code> using the WXR attachment relative paths. Block direct web access to that staging directory. No remote image downloads or hotlinks are made.</p>';
	if ( $state ) {
		echo '<p><strong>Progress:</strong> profiles ' . esc_html( $state['profiles_cursor'] ) . '/' . esc_html( $counts['profiles'] ) . '; media ' . esc_html( $state['media_cursor'] ) . '/' . esc_html( $counts['media'] ) . '. Status: ' . esc_html( $state['status'] ) . '</p>';
		$summary = p10_source_summary( $state );
		if ( $summary['total'] ) {
			echo '<p><strong>Outcomes:</strong> ';
			$bits = array();
			foreach ( $summary['total'] as $outcome => $total ) {
				$bits[] = esc_html( $outcome ) . ' ' . esc_html( number_format_i18n( $total ) );
			}
			echo implode( ' · ', $bits ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
			echo '</p>';
		}
	}
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'p10_source_batch' );
	echo '<input type="hidden" name="action" value="p10_source_batch"><p><label><input type="checkbox" required name="authorized" value="1"> I have verified permission to reuse the supplied source content and any staged media.</label></p><button class="button button-primary" name="operation" value="batch">Process / resume next batch</button> <button class="button" name="operation" value="retry">Retry / reconcile records (does not overwrite imported posts)</button></form>';
	if ( $state ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'p10_source_batch' );
		echo '<input type="hidden" name="action" value="p10_source_batch"><button name="operation" value="report" class="button">Download JSON mapping / outcomes</button></form>';
		echo '<h2>Last outcomes</h2><pre style="white-space:pre-wrap">' . esc_html( wp_json_encode( array_slice( $state['profiles'], -5, null, true ), JSON_PRETTY_PRINT ) ) . '</pre>';
	}
	echo '</div>';
}
add_action(
	'admin_post_p10_source_batch',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Permission denied', '', array( 'response' => 403 ) ); }
		check_admin_referer( 'p10_source_batch' );
		$op    = isset( $_POST['operation'] ) ? sanitize_key( $_POST['operation'] ) : 'batch';
		$state = get_option( 'p10_source_state', array() );
		if ( 'report' === $op ) {
			nocache_headers();
			header( 'Content-Type: application/json' );
			header( 'Content-Disposition: attachment; filename=p10-import-report.json' );
			echo wp_json_encode( $state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			exit;
		}
		if ( empty( $_POST['authorized'] ) ) {
			wp_die( 'Reuse authorization is required.' ); }
		try {
			p10_source_batch( 'retry' === $op );
		} catch ( Throwable $error ) {
			wp_die( esc_html( $error->getMessage() ) ); }
		wp_safe_redirect( admin_url( 'themes.php?page=p10-source' ) );
		exit;
	}
);

/** Five profiles or ten local assets per request; option lock prevents parallel batches. */
function p10_source_batch( $retry = false ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		throw new RuntimeException( 'Permission denied' ); }
	$check = p10_source_validate();
	if ( ! $check['ok'] ) {
		throw new RuntimeException( 'Manifest validation failed: ' . implode( ' ', $check['errors'] ) );
	}
	if ( ! add_option( 'p10_source_lock', time(), '', false ) ) {
		// A failed process can be resumed after five minutes. Never steal a live lock.
		if ( (int) get_option( 'p10_source_lock' ) < time() - 300 ) {
			delete_option( 'p10_source_lock' ); }
		throw new RuntimeException( 'Import locked. Wait for the current batch (or retry after five minutes).' );
	}
	try {
		$data = p10_source_manifest();
		if ( ! $data ) {
			throw new RuntimeException( 'Invalid demo manifest.' ); }
		$state = get_option( 'p10_source_state', array() );
		if ( ! $state ) {
			$state = array(
				'source'          => $data['source'],
				'profiles_cursor' => 0,
				'media_cursor'    => 0,
				'profiles'        => array(),
				'media'           => array(),
				'status'          => 'running',
				'authorized_by'   => get_current_user_id(),
				'authorized_at'   => gmdate( 'c' ),
			); }
		if ( $retry ) {
			$state['profiles_cursor'] = 0;
			$state['media_cursor']    = 0;
			$state['status']          = 'running'; }
		if ( $state['profiles_cursor'] < count( $data['profiles'] ) ) {
			$end = min( count( $data['profiles'] ), $state['profiles_cursor'] + 5 );
			while ( $state['profiles_cursor'] < $end ) {
				$row                                    = $data['profiles'][ $state['profiles_cursor'] ];
				$state['profiles'][ $row['source_id'] ] = p10_source_profile( $row );
				++$state['profiles_cursor'];
				update_option( 'p10_source_state', $state, false );
			}
		} else {
			$end = min( count( $data['media'] ), $state['media_cursor'] + 10 );
			while ( $state['media_cursor'] < $end ) {
				$row                                 = $data['media'][ $state['media_cursor'] ];
				$state['media'][ $row['source_id'] ] = p10_source_media( $row, $data, $state );
				++$state['media_cursor'];
				update_option( 'p10_source_state', $state, false );
			}
			p10_source_link_galleries( $data, $state );
		}
		if ( count( $data['profiles'] ) === $state['profiles_cursor'] && count( $data['media'] ) === $state['media_cursor'] ) {
			$state['status'] = 'processed — inspect missing/conflict/error outcomes';
			p10_add_nested_rules();
			flush_rewrite_rules( false );
		}
		$state['updated_at'] = gmdate( 'c' );
		update_option( 'p10_source_state', $state, false );
		return $state;
	} finally {
		delete_option( 'p10_source_lock' ); }
}

function p10_source_profile( $row ) {
	$result = array(
		'source_id'             => $row['source_id'],
		'source_url'            => $row['source_url'],
		'title'                 => $row['title'],
		'missing'               => $row['missing'],
		'conflicting_meta_keys' => $row['conflicting_meta_keys'],
		'destination_id'        => null,
		'destination_url'       => null,
	);
	if ( 'publish' !== $row['status'] ) {
		return array_merge( $result, array( 'outcome' => 'excluded_draft' ) ); }
	$id = p10_source_find( 'p10_profile', p10_source_key( $row['source_id'] ) );
	if ( $id && get_post_meta( $id, '_p10_source_ready', true ) ) {
		return array_merge(
			$result,
			array(
				'outcome'         => 'existing_source_record_unchanged',
				'destination_id'  => $id,
				'destination_url' => get_permalink( $id ),
			)
		); }
	if ( ! $id && get_page_by_path( $row['slug'], OBJECT, 'p10_profile' ) ) {
		return array_merge( $result, array( 'outcome' => 'conflict_unrelated_slug_manual_review' ) ); }
	$is_new = ! $id;
	if ( $is_new ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'p10_profile',
				'post_status'  => 'publish',
				'post_title'   => sanitize_text_field( $row['title'] ),
				'post_name'    => sanitize_title( $row['slug'] ),
				'post_content' => wp_kses_post( $row['content'] ),
				'post_date'    => $row['date'],
				'meta_input'   => array(
					'_p10_source_key' => p10_source_key( $row['source_id'] ),
					'_p10_source_url' => esc_url_raw( $row['source_url'] ),
				),
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return array_merge(
				$result,
				array(
					'outcome' => 'failed',
					'error'   => $id->get_error_message(),
				)
			); }
	}
	$schema = p10_profile_field_schema();
	foreach ( $row['fields'] as $key => $value ) {
		if ( isset( $schema[ $key ] ) && ! metadata_exists( 'post', $id, 'p10_' . $key ) ) {
			add_post_meta( $id, 'p10_' . $key, p10_sanitize_field( $value, $schema[ $key ] ), true ); }
	}
	// Never inherit the original theme's implicit "available" or "verified" defaults.
	add_post_meta( $id, 'p10_available', 'unknown', true );
	add_post_meta( $id, 'p10_verified', 0, true );
	update_post_meta( $id, '_p10_source_fields', $row['source_fields'] );
	foreach ( array( '_yoast_wpseo_title', '_yoast_wpseo_metadesc', '_yoast_wpseo_focuskw' ) as $key ) {
		if ( ! empty( $row['source_fields'][ $key ] ) ) {
			add_post_meta( $id, $key, sanitize_text_field( $row['source_fields'][ $key ] ), true ); }
	}
	$warnings = array();
	$county   = '';
	foreach ( $row['terms'] as $term ) {
		if ( 'p10_county' === $term['taxonomy'] ) {
			$county = $term['slug']; }
	}
	foreach ( $row['terms'] as $term ) {
		if ( ! in_array( $term['taxonomy'], array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ), true ) ) {
			continue; }
		$existing = get_term_by( 'slug', $term['slug'], $term['taxonomy'] );
		$created  = $existing ? array( 'term_id' => $existing->term_id ) : wp_insert_term( $term['name'], $term['taxonomy'], array( 'slug' => $term['slug'] ) );
		if ( is_wp_error( $created ) ) {
			$warnings[] = $created->get_error_message();
			continue; }
		$term_id = (int) $created['term_id'];
		if ( ! $existing ) {
			update_term_meta( $term_id, '_p10_source_term', 1 ); }
		if ( 'p10_city' === $term['taxonomy'] && $county ) {
			$old = get_term_meta( $term_id, 'p10_county', true );
			if ( $existing && $old !== $county ) {
				$warnings[] = 'Existing city county differs; relationship not overwritten.'; } elseif ( ! $existing ) {
				update_term_meta( $term_id, 'p10_county', $county ); }
		}
		$assigned = wp_set_object_terms( $id, array( $term_id ), $term['taxonomy'], true );
		if ( is_wp_error( $assigned ) ) {
			$warnings[] = $assigned->get_error_message(); }
	}
	if ( ! $warnings ) {
		update_post_meta( $id, '_p10_source_ready', 1 ); }
	return array_merge(
		$result,
		array(
			'outcome'         => $is_new ? 'imported' : 'resumed_partial_source_record',
			'destination_id'  => $id,
			'destination_url' => get_permalink( $id ),
			'warnings'        => $warnings,
		)
	);
}

function p10_source_media( $row, $data, $state ) {
	$result = array(
		'source_id'      => $row['source_id'],
		'source_url'     => $row['source_url'],
		'destination_id' => null,
	);
	$used   = false;
	foreach ( $data['profiles'] as $profile ) {
		if ( 'publish' === $profile['status'] && in_array( $row['source_id'], $profile['media_ids'], true ) && ! empty( $state['profiles'][ $profile['source_id'] ]['destination_id'] ) ) {
			$used = true;
			break; }
	}
	if ( ! $used ) {
		return array_merge( $result, array( 'outcome' => 'skipped_no_imported_public_profile_relationship' ) ); }
	$id = p10_source_find( 'attachment', p10_source_key( $row['source_id'] ) );
	if ( $id && is_file( get_attached_file( $id ) ) ) {
		return array_merge(
			$result,
			array(
				'outcome'        => 'duplicate_source',
				'destination_id' => $id,
			)
		); }
	$root = realpath( WP_CONTENT_DIR . '/p10-source-media' );
	$file = $root ? realpath( $root . '/' . ltrim( $row['path'], '/' ) ) : false;
	if ( ! $root || ! $file || 0 !== strpos( $file, $root . DIRECTORY_SEPARATOR ) || ! is_file( $file ) ) {
		return array_merge( $result, array( 'outcome' => 'missing_binary_placeholder' ) ); }
	if ( ! is_readable( $file ) ) {
		return array_merge( $result, array( 'outcome' => 'inaccessible' ) ); }
	if ( filesize( $file ) > 20 * MB_IN_BYTES || ! wp_get_image_mime( $file ) ) {
		return array_merge( $result, array( 'outcome' => 'failed_invalid_image_or_over_20MB' ) ); }
	$dimensions = wp_getimagesize( $file );
	if ( ! $dimensions || $dimensions[0] * $dimensions[1] > 40000000 ) {
		return array_merge( $result, array( 'outcome' => 'failed_invalid_dimensions_or_over_40MP' ) ); }
	$hash = hash_file( 'sha256', $file );
	$id   = p10_source_find( 'attachment', $hash, '_p10_source_sha256' );
	if ( $id && is_file( get_attached_file( $id ) ) ) {
		return array_merge(
			$result,
			array(
				'outcome'        => 'duplicate_checksum',
				'destination_id' => $id,
				'sha256'         => $hash,
			)
		); }
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( basename( $file ) );
	if ( ! $tmp || ! copy( $file, $tmp ) ) {
		if ( $tmp ) {
			wp_delete_file( $tmp );
		} return array_merge( $result, array( 'outcome' => 'failed_copy_retryable' ) ); }
	$id = media_handle_sideload(
		array(
			'name'     => sanitize_file_name( basename( $file ) ),
			'tmp_name' => $tmp,
		),
		0,
		sanitize_text_field( $row['title'] )
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return array_merge(
			$result,
			array(
				'outcome' => 'failed',
				'error'   => $id->get_error_message(),
			)
		); }
	update_post_meta( $id, '_p10_source_key', p10_source_key( $row['source_id'] ) );
	update_post_meta( $id, '_p10_source_sha256', $hash );
	update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $row['alt'] ) );
	return array_merge(
		$result,
		array(
			'outcome'         => 'imported',
			'destination_id'  => $id,
			'destination_url' => wp_get_attachment_url( $id ),
			'sha256'          => $hash,
		)
	);
}

function p10_source_link_galleries( $data, &$state ) {
	foreach ( $data['profiles'] as $profile ) {
		$id = isset( $state['profiles'][ $profile['source_id'] ]['destination_id'] ) ? (int) $state['profiles'][ $profile['source_id'] ]['destination_id'] : 0;
		if ( ! $id || 'publish' !== get_post_status( $id ) ) {
			continue; }
		$ids = array();
		foreach ( $profile['media_ids'] as $source_id ) {
			$dest = isset( $state['media'][ $source_id ]['destination_id'] ) ? (int) $state['media'][ $source_id ]['destination_id'] : 0;
			if ( $dest && wp_attachment_is_image( $dest ) && is_file( get_attached_file( $dest ) ) ) {
				$ids[] = $dest; }
		}
		$ids     = array_values( array_unique( $ids ) );
		$missing = max( 0, count( $profile['media_ids'] ) - count( $ids ) );
		$last    = get_post_meta( $id, '_p10_import_gallery', true );
		$current = array_values( array_map( 'intval', p10_profile_gallery( get_post( $id ) ) ) );
		if ( $current && $current !== (array) $last ) {
			$state['profiles'][ $profile['source_id'] ]['gallery'] = 'conflict_editor_gallery_unchanged';
			continue; }
		if ( $ids ) {
			set_post_thumbnail( $id, $ids[0] );
			update_post_meta( $id, 'p10_gallery', implode( ',', array_slice( $ids, 1 ) ) );
			update_post_meta( $id, '_p10_import_gallery', $ids );
		}
		$state['profiles'][ $profile['source_id'] ]['gallery'] = array(
			'linked_ids'     => $ids,
			'expected'       => count( $profile['media_ids'] ),
			'missing'        => $missing,
			'placeholder'    => $missing > 0,
			'outcome'        => $ids ? 'linked_available_media_only' : 'placeholder_no_media_imported',
		);
	}
}
