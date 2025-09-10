<?php

class MeowPro_WPMC_Core {

	private $item = 'Media Cleaner Pro';
	private $core = null;
	private $parsers = null;
	public static $exclude_dir = array( ".", "..", "wpmc-trash", ".htaccess", "index.html", "GeoIP.dat", "GeoIPv6.dat",
		"ptetmp", "profiles", "sites", "bws_captcha_images", "wp-personal-data-exports",
		"wp-security-audit-log", "maxmegamenu", "woocommerce_uploads", "wc-logs", "bb-plugin", "wpallimport", "wpallexport", "yith-gift-cards",
		"pb_backupbuddy", "reports", "utubevideo-cache", "gravity_forms", "ithemes-security" );
	private $cache_meta = null;
	private $cache_mainfile = null;
	private $cache_croppedfiles = null;
	private $check_medialibrary = null;
	private $filepath_basedir = null;
	private $imagefiletypes = '|jpg|jpeg|jpe|gif|png|tiff|bmp';

	public function __construct( $core  ) {
		$this->mainfile = WPMC_ENTRY;
		$this->domain = WPMC_DOMAIN;
		$this->core = $core;

		// Common behaviors, license, update system, etc.
		new MeowCommon_Licenser( WPMC_PREFIX, WPMC_ENTRY, WPMC_DOMAIN, $this->item, WPMC_VERSION );

		//new MeowApps_Admin_Pro( $prefix, $mainfile, $domain, $this->item, $version );

		// Support for WP CLI
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$wpcli = new MeowPro_WPMC_CLI();
			WP_CLI::add_command( 'media-cleaner', $wpcli );
		}

		// Overrides for the Pro
		add_filter( 'wpmc_plugin_title', array( $this, 'plugin_title' ), 10, 1 );
		add_action( 'wpmc_list_uploaded_files', array( $this, 'list_uploaded_files' ), 10, 2 );
		add_filter( 'wpmc_check_file', array( $this, 'check_file' ), 10, 2 );

		// Initializers
		add_action( 'wpmc_initialize_parsers', array( $this, 'initialize_parsers' ), 10, 0 );
		add_action( 'wpmc_check_file_init', array( $this, 'check_file_init' ), 10, 0 );

		// Live Content
		if ( get_option( 'wpmc_live_content', false ) ) {
			add_action( 'wpmc_scan_extra', array( $this, 'live_content' ), 10, 1 );
		}
	}

	public function __destruct() {
		remove_filter( 'wpmc_plugin_title', array( $this, 'plugin_title' ), 10 );
		remove_action( 'wpmc_list_uploaded_files', array( $this, 'list_uploaded_files' ), 10 );
		remove_filter( 'wpmc_check_file', array( $this, 'check_file' ), 10, 2 );
		remove_action( 'wpmc_initialize_parsers', array( $this, 'initialize_parsers' ), 10 );
		remove_action( 'wpmc_check_file_init', array( $this, 'check_file_init' ), 10 ); //Without this Apache child crashed
	}

	function initialize_parsers() {
		if ( $this->parsers )
			return;
		$this->parsers = new MeowPro_WPMC_Parsers();
	}

	function plugin_title( $string ) {
		return $string . " (Pro)";
	}

	function live_content( $post ) {
		// The file_get_contents() render operation runs on the server (as a not logged on user)
		// It can only render published public pages (Not future. Not Drafts. etc.)
		$typ = get_post_field( 'post_type', $post );
		if ( get_post_field( 'post_status', $post ) == 'publish' && ( $typ == 'page' || $typ == 'post') ) {
			$html = file_get_contents( $this->core->site_url . '?p=' . get_post_field( 'ID', $post ) );
			if ( $html === false ) {
				$err = "Unable to render Post ID {get_post_field('ID', $post)}";
				error_log( $err );
				$this->core->log( '🚫 ' . $err );
			} else {
				do_action( 'wpmc_scan_post', $html, $post ); // Uses same scan process as post_content. Some duplication of effort...
			}
		}
	}

	function list_uploaded_files( $result, $path ) {
		$real_path = $path ? ( trailingslashit( $this->core->upload_path ) . $path ) : $this->core->upload_path;
		$files = $this->scan_list_uploaded_files( $real_path );
		return $files;
	}

	function scan_list_uploaded_files( $dir ) {
		$result = array();

		// With glob
		$files = glob( $dir . '/*' );
		$files = str_replace( trailingslashit( $dir ), '', $files );

		// With scandir
		//$files = scandir( $dir );

		$files = array_diff( $files, MeowPro_WPMC_Core::$exclude_dir );
		$thumbnails_only = get_option( 'wpmc_thumbnails_only', false );
		$utf8 = get_option( 'wpmc_utf8', false );
		$dirs_filter = get_option( 'wpmc_dirs_filter' );
		$files_filter = get_option( 'wpmc_files_filter' );
		foreach( $files as $file ) {
			if ( !$utf8 && mb_detect_encoding( $file, 'ASCII', true ) === false ) continue; // Can't detect encoding
			$fullpath = trailingslashit( $dir ) . $file;
			$relativepath = $this->core->clean_uploaded_filename( $fullpath );
			$relativepath_dir = dirname( $relativepath );
			$relativepath_dir = preg_replace( '{^\.}', '', $relativepath_dir, 1) ; // Remove the dot at the beginning
			$type=''; // file or dir
			if ( is_dir( $fullpath ) ) {
				$type = 'dir';
			} else {
				if ( $dirs_filter && @preg_match( $dirs_filter, $relativepath_dir ) === 0 ) continue; // Filter doesn't match
				if ( $thumbnails_only && !preg_match("/(\-\b)[0-9]\d*x[0-9]\d*(\b.(jpg|jpeg|png)\b)/", $file ) ) continue;
				if ( $files_filter && @preg_match( $files_filter, $file ) === 0 ) continue; // Filter doesn't match
				$type = 'file';
			}
			array_push( $result, array (
				'path' => $relativepath,
				'type' => $type
			) );
		}
		return $result;
	}

	function getCachedMeta( &$id ) {
		if ( empty($this->cache_meta) || $this->cache_meta[0] != $id ) {
			$meta = wp_get_attachment_metadata( $id );
			$this->cache_meta = array($id, $meta);
			return $meta;
		} else {
			return $this->cache_meta [1];
		}
	}

	function getCachedMainfile( &$path, $mainfile ) {
		global $wpdb;
		if ( !empty( $this->cache_mainfile ) && 
			 ( $this->cache_mainfile[0] == $mainfile || $this->cache_mainfile[2] == $mainfile || $this->cache_mainfile[3] == $mainfile ) ) {
				// Found $mainfile in the cache. 
				$id = $this->cache_mainfile[1];
				//error_log("CACHE HIT: " . $mainfile . " [" . $this->cache_mainfile[1] . "] [" . $this->cache_mainfile[0]  . "] [" . $this->cache_mainfile[2]  . "] [" . $this->cache_mainfile[3] . "] PATH=" . $path);
				// Check for .jpg within meta of .pdf 
				if ( substr ($this->cache_mainfile[0], -3) !== substr ( $this->cache_mainfile[2], -3 ) ) {
					// Must be .jpg resolution image file for a .pdf
					// Return Id of .pdf to let check_meta_value_sizes() check for .jpg in meta_value
					if ( empty( $id ) ) {
						$this->core->log( "🚫 File {$path} not found as Media {$id} (PDF)");
						return null;
					} else {
						return -$id; // Id of mainfile (.pdf)						
					}
				} else {
					if ( empty( $id ) ) {
						$this->core->log( "🚫 File {$path} not found as _wp_attached_file (Library)");
						return null;
					} else {
						if ( $path == $mainfile) {
							$this->core->log( "🚫 File {$path} found as Media {$id}");	
						} 
					}
					return $id; // Id of mainfile or null
				}
		} else {
			// $mainfile does not match the cache
			//error_log("CACHE MISS: " . $mainfile . " " . $this->cache_mainfile[1] . " " . $this->cache_mainfile[0]  . " " . $this->cache_mainfile[2]  . " " . $this->cache_mainfile[3]);
			$id = $this->core->find_media_id_from_file( $mainfile, false ); 
			if ( empty ( $id ) ) {
				// $mainfile not found in Media Library - Try three different methods to find this disk file in the Media Library rows
				//
				$pathinfo = pathinfo( $mainfile );
				$ext = $pathinfo['extension'];
				// These three methods only apply to image files
				if ( strpos( $this->imagefiletypes, $ext ) !== false ) {
					// Did not find the mainfile in the Media Library. 
					// 1. See if there is a cropped main file.
					//$ret = $this->Get_Cropped_File( $mainfile ); // ret [1]=ID, [0]=YYYY/MM/filename-e9999999999999.ext
					$ret = $this->getCroppedFileFromCache ( $mainfile ); // ret [1]=ID, [0]=YYYY/MM/filename-e9999999999999.ext
					if (!empty( $ret ) ) {
						$id = $ret[1];
						// Found cropped filename in Media Library. Make a cache entry to handle cropped mainfile names.
						$this->cache_mainfile = array( $mainfile, $ret[1], $ret[0], null, true );	
						//error_log("CACHE SET 1: " . $this->cache_mainfile[0] . " " . $this->cache_mainfile[1]  . " " . $this->cache_mainfile[2]  . " " . $this->cache_mainfile[3]);
						if ( $path == $mainfile ) {
							$this->core->log("✅ File {$mainfile} found as Media {$id}");	
						}
						return $id;  // Return Id to see if the $path is in the meta_value
					} 
					// 2. It might be an image that is in a pdf's meta
					$skiptablescan = false;
					if ( substr( $mainfile, -4 ) == '.jpg' ) {
						$pdfmainfileDash = '';
						// Create a PDF filename and see if it exists
						if ( substr( $mainfile, -8) == '-pdf.jpg' ) { 
							// -pdf.jpg
							$pdfmainfile = substr( $mainfile, 0, strlen( $mainfile) - 8 ) . ".pdf";
							$pdfmainfileDash = substr( $mainfile, 0, strlen( $mainfile) - 7 ) . ".pdf"; // trailing - (see below)
							$skiptablescan = true; // We know this jpg is associated with a pdf so skip table scan for images below
						} else {
							// .jpg
							$pdfmainfile = substr( $mainfile, 0, strlen( $mainfile) - 4 ) . ".pdf";
						}
						$id = $this->core->find_media_id_from_file( $pdfmainfile, false ); // No need to make log entry	
						if ( !empty( $id ) ) {
							// Found the PDF - Make cache entries so variations will match the cache
							$pdfmainfile1 = substr( $pdfmainfile, 0, strlen($pdfmainfile) - 4 ) . '.jpg';
							$pdfmainfile2 = substr( $pdfmainfile, 0, strlen($pdfmainfile) - 4 ) . '-pdf.jpg';
							$this->cache_mainfile = array( $pdfmainfile, $id,  $pdfmainfile1, $pdfmainfile2, false );	
							//error_log("CACHE SET 2A: " . $this->cache_mainfile[0] . " " . $this->cache_mainfile[1]  . " " . $this->cache_mainfile[2]  . " " . $this->cache_mainfile[3]);
							return -$id; // Return Id to let check_meta_value_sizes() check it
						}
						// Found five filenames like 'Sewer-Rates-Adopted-October-25-2016-.pdf' (trailing dash) in __wp_attached_file. This code handles that.
						$id = $this->core->find_media_id_from_file( $pdfmainfileDash, false ); // No need to make log entry	
						if ( !empty( $id ) ) {
							// Found the PDF - Make cache entries so variations will match the cache
							$pdfmainfile1 = substr( $pdfmainfile, 0, strlen($pdfmainfile) - 4 ) . '.jpg';
							$pdfmainfile2 = substr( $pdfmainfile, 0, strlen($pdfmainfile) - 4 ) . '-pdf.jpg';  
							$this->cache_mainfile = array( $pdfmainfile, $id,  $pdfmainfile1, $pdfmainfile2 , false);	
							//error_log("CACHE SET 2B: " . $this->cache_mainfile[0] . " " . $this->cache_mainfile[1]  . " " . $this->cache_mainfile[2]  . " " . $this->cache_mainfile[3]);
							return -$id; // Return Id to let check_meta_value_sizes() check it
						}
					}
					// 3. See if the image is in the meta_value of another image. Not elegant but it works!
					if ( !$skiptablescan ) {
						$table = $wpdb->prefix . 'postmeta';
						$sql = $wpdb->prepare("SELECT post_id FROM {$table} WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE %s;" ,'%'. $mainfile . '%');
						$id = $wpdb->get_var( $sql );
						if ( !empty( $id ) ) {
							$this->cache_mainfile = array( $mainfile, $id, null, null, false );	
							//error_log("CACHE SET 3A: " . $this->cache_mainfile[0] . " " . $this->cache_mainfile[1]  . " " . $this->cache_mainfile[2]  . " " . $this->cache_mainfile[3]);
							if ( $path == $mainfile ) {
								$this->core->log("✅ File {$mainfile} found in metadata of Media {$id} (_wp_attachment_metadata)");	
							}
							return $id; 
						}
						// Look to see if a resolution image file is within a meta_value and then let check_meta_value_sizes() check it
						if ( $path != $mainfile ) {
							$pathinfo = pathinfo( $path ); 
							$basename = $pathinfo['basename'];
							// Searching for just basename (without directories) may lead to finding the wrong file. 
							// The call to check_meta_value_sizes() will display found or not found comparing the entire $path
							$sql = $wpdb->prepare("SELECT post_id FROM {$table} WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE %s;" ,'%'. $basename . '%');
							$id = $wpdb->get_var( $sql );
							if ( !empty( $id ) ) {
								$this->cache_mainfile = array( $mainfile, $id, null, null, false );	
								//error_log("CACHE SET 3B: " . $this->cache_mainfile[0] . " " . $this->cache_mainfile[1]  . " " . $this->cache_mainfile[2]  . " " . $this->cache_mainfile[3]);
								if ( $path == $mainfile ) {
									$this->core->log("✅ File {$mainfile} found in metadata of Media {$id} (_wp_attachment_metadata)");	
								}
								return $id; 
							}
						}
					}
				
				} 
				// An on disk file not in the Media Library
				$this->core->log( "👻 File {$path} not found as Media");
				$this->cache_mainfile = array( $mainfile, null , null, null, false );	
				//error_log("CACHE SET 4: " . $this->cache_mainfile[0] . " " . $this->cache_mainfile[1]  . " " . $this->cache_mainfile[2]  . " " . $this->cache_mainfile[3]) . " path=" . $path;
				return null; 
			} else {
				// Found in Media Library. If a PDF, make a cache entry to handle .jpg filenames used in PDF's meta
				if ( substr($mainfile, -4 ) == '.pdf' ) {
					// Just in case there will be image files that are in the PDF's meta
					$pdfmainfile1 = substr( $mainfile, 0, strlen($mainfile) - 4 ) . '.jpg';
					$pdfmainfile2 = substr( $mainfile, 0, strlen($mainfile) - 4 ) . '-pdf.jpg';
					$this->cache_mainfile = array( $mainfile, $id,  $pdfmainfile1, $pdfmainfile2, false );	
					//error_log("CACHE SET 5: " . $this->cache_mainfile[0] . " " . $this->cache_mainfile[1]  . " " . $this->cache_mainfile[2]  . " " . $this->cache_mainfile[3] . " path=" . $path);
					$this->core->log( "✅ File {$path} found as Media {$id} (PDF)");
					return $id; // Return Id to let check_meta_value_sizes()
				} else {
					// Found in Media Library
					$this->cache_mainfile = array( $mainfile, $id, null , null, false );
					//error_log("CACHE SET 6: " . $this->cache_mainfile[0] . " " . $this->cache_mainfile[1]  . " " . $this->cache_mainfile[2]  . " " . $this->cache_mainfile[3] . " path=" . $path);
					if ( $path == $mainfile) {
						$this->core->log( "✅ File {$path} found as Media {$id}");	
					} 
				}
				return $id; // Media ID or null
			}
		}
	}

	function buildCroppedFileCache() {
		global $wpdb;
		$this->cache_croppedfiles = array();
		// Get all rows with a -e9999999999999 timestamp in the filename.
		// 'order by meta_value' is required for searchCache() to work correctly.
		$sql = "SELECT meta_value,post_id FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' and meta_value REGEXP '-e[0-9]{13}.[A-Za-z]{3,4}' order by meta_value;";
		$results = $wpdb->get_results( $sql, ARRAY_N );
		foreach ($results as $result) {
			$matches=array();
			// Validate -e9999999999999 timestamp is present and compute offset (matches[1][1]) to the timestamp
			if (preg_match('/[A-Za-z0-9-_,\)\(\\\\\/.\s](-e\d{13}).[a-z]{3,4}/', $result[0], $matches, PREG_OFFSET_CAPTURE ) ) {
				// Remove the -e9999999999999 timestamp to get a mainfile name from before the image was cropped
				$precroppedfilename = substr( $result[0], 0, $matches[1][1] ) . substr( $result[0],  $matches[1][1] + strlen( $matches[1][0] ) );
				$this->cache_croppedfiles[] = array($precroppedfilename, $result[1], $result[0] );
			}
		}
		//error_log("CACHE COUNT=" . count($this->cache_croppedfiles) );
	}

	function getCroppedFileFromCache( &$file ) {
		// When an image is cropped, a filename like '2019/01/image-e1547821274436.jpg' is created, the original file is removed from 
		// Media Library and resolution filenames in the meta_value array may or may not contain the -e1547821274436 timestamp.
		// Here we have a potential main filename assumed to be without a timestamp for which we could find no meta_value in wp_postmeta
		// That may mean that the main filename was cropped so the main filename was changed to include a timestamp.
		// Here we try to find the main filename by looking for a filename with a '-e' followed by a 13-digit timestamp.
		global $wpdb;
		$file = $this->core->clean_uploaded_filename( $file ); // YYYY/MM/filename.ext
		$pathinfo = pathinfo( $file );
		$ext = $pathinfo['extension'];
		if ( strpos( $this->imagefiletypes, $ext ) === false ) {
			// Only images can be cropped
			return null;
		}
		$itm = $this->searchCache( $file ); // itm: [0]=YYYY/MM/filename.ext [1]=media ID [2]=YYYY/MM/filename-e9999999999999.ext (from meta_value)
		if ( empty( $itm ) ) return null;
		//error_log( "ID=" . $itm[1] . " " . $itm[0] . " " . $itm[2] );
		return array($itm[2], $itm[1], $itm[0] );
	}

	function searchCache( &$file ) { 
		if ( empty( $this->cache_croppedfiles ) || count( $this->cache_croppedfiles ) == 0 ) return false; 
		$low = 0; 
		$high = count( $this->cache_croppedfiles ) - 1; 
		while ($low <= $high) { 
			$mid = floor(($low + $high) / 2); 
			if ( $file == $this->cache_croppedfiles[$mid][0] ) {
				return $this->cache_croppedfiles[$mid];
			}	
			if ( $file < $this->cache_croppedfiles[$mid][0] ) { 
				$high = $mid - 1; 
			} 
			else { 
				$low = $mid + 1; 
			} 
		} 
		return null; 
	}

	function check_file_init() {
		$this->check_medialibrary = get_option( 'wpmc_media_library', true );
		$this->filepath_basedir = wp_upload_dir();
		$this->filepath_basedir = trailingslashit( $this->filepath_basedir['basedir'] );
		$this->buildCroppedFileCache();
	}

	// Return true if the files is referenced, false if it is not.
	function check_file( $result, $path ) {
		global $wpdb;
		$filepath = $this->filepath_basedir . stripslashes( $path );

		// Ignored path
		if ( $this->core->check_is_ignore( $path ) )
			return true;

		$clean_path = $this->core->clean_uploaded_filename( $path );
		if ( $this->core->reference_exists( $clean_path, null ) )
			return true;

		// Retina support
		if ( strpos( $path, '@2x.' ) !== false ) {
			$originalfile = str_replace( '@2x.', '.', $filepath );
			if ( file_exists( $originalfile ) )
				return true;
			else {
				$table_name = $wpdb->prefix . "mclean_scan";
				$wpdb->insert( $table_name,
					array(
						'time' => current_time('mysql'),
						'type' => 0,
						'path' => $path,
						'size' => filesize( $filepath ),
						'issue' => 'ORPHAN_RETINA'
					)
				);
				return false;
			}
		}

		// WebP support
		if ( substr( $path, -5) == '.webp' ) {
		//if ( strpos( $path, '.webp' ) !== false ) {  // mm change to substr( $path, -5) == '.webp' )
			$original_file = str_replace( '.webp', '', $filepath );
			$original_file_alt = str_replace( '.webp', '.jpg', $filepath );
			if ( file_exists( $original_file ) ) {
				return true;
			}
			else if ( file_exists( $original_file_alt ) ) {
				return true;
			}
			else {
				$table_name = $wpdb->prefix . "mclean_scan";
				$wpdb->insert( $table_name,
					array(
						'time' => current_time('mysql'),
						'type' => 0,
						'path' => $path,
						'size' => filesize( $filepath ),
						'issue' => 'ORPHAN_WEBP'
					)
				);
				return false;
			}
		}

		$issue = "NO_CONTENT";
		$mainfile = $this->core->clean_url_from_resolution( $path );

		// TODO: Somehow, the getCachedMainfile seems to fail for PDF thumbnails but only
		// when using WP-CLI
		$attachment_Id = $this->getCachedMainfile( $path, $mainfile ); 

		if ( !empty( $attachment_Id ) && ( $attachment_Id < 0 || $path != $mainfile ) ) {
			$attachment_Id = abs( $attachment_Id );
			if ( $this->check_medialibrary )
				// Check for a resolution image file
				if ( !$this->check_meta_value_sizes ( $mainfile, $path, $attachment_Id ) ) {
					$attachment_Id = null; // Wasn't found in meta_value
				}
		}

		if ( $this->check_medialibrary ) {
			if ( empty( $attachment_Id ) ) {
				$issue = "ORPHAN_FILE";
			} else {
				return true;				
			}
		} 

		$table_name = $wpdb->prefix . "mclean_scan";
		$filesize = file_exists( $filepath ) ? filesize ($filepath) : 0;
		$wpdb->insert( $table_name,
			array(
				'time' => current_time('mysql'),
				'type' => 0,
				'path' => $clean_path,
				'size' => $filesize,
				'issue' => $issue
			)
		);
		return false;
	}

	function check_meta_value_sizes ( &$mainfile, &$path, &$attachment_Id ) {
		$meta = $this->getCachedMeta( $attachment_Id );
		if ( $meta ) {
			if ( array_key_exists( 'file', $meta ) ) {
				$pathinfo = pathinfo( $meta['file'] );
				$dirname = $pathinfo['dirname'];
				$dirname = trailingslashit( $dirname );
				if ( $dirname == "./" ) $dirname = '';
				//error_log($meta['file'] . " == " . $path . "   " . $mainfile);
				if ( $this->check_medialibrary && $meta['file'] == $path ) {
					$this->core->log( "✅ File {$path} found in 'file' metadata of Media {$attachment_Id}." );
					//error_log("FOUND main meta: " . $meta['file'] . "==" . $path  . " " . $attachment_Id );
					return true;
				} 
			} else {
				// No 'file' key for PDF thumbnails so use mainfile's pathinfo
				$pathinfo = pathinfo( $mainfile );
				$dirname = $pathinfo['dirname'];
				$dirname = trailingslashit( $dirname );
				if ( $dirname == "./" ) $dirname = '';
			}
			if ( array_key_exists( 'sizes', $meta) ) {	
				foreach ( $meta['sizes'] as $size ) {
					if ( array_key_exists ( 'file', $size) ) {
						//error_log("COMPARE: " . $dirname . $size['file'] . "==" . $path );
						if ( $dirname . $size['file'] == $path ) {
							if ( $this->check_medialibrary ) {
								$this->core->log( "✅ File {$path} found in metadata of Media {$attachment_Id}." );
								//error_log("FOUND: " . $dirname . $size['file'] . "==" . $path . " " . $attachment_Id );
								return true;
							} 
						}
					} else {
						// Soft Fail - unexpected meta_value format
						error_log( $mainfile . ' Unable to parse resolutions metadata. No file key.' );
						error_log( "meta size=" . print_r( $size, true ) );
						$this->core->log( "🚫 File {$path} unrecognized resolution meta for Media {$attachment_Id}." );
						return true; // To be safe!
					}	
				}
				$this->core->log( "🚫 File {$path} not found in metadata of Media {$attachment_Id}." );
				//error_log("NOT FOUND: " . $path . " " . $attachment_Id);
				return false;
			} else {
				// Soft Fail - unexpected meta_value format
				error_log( $mainfile . ' Unable to parse resolutions metadata. No sizes key.' );
				error_log("meta=" . print_r( $meta, true ) );
				$this->core->log( "🚫 File {$path} unrecognized resolution meta for Media {$attachment_Id}." );
				return true; // To be safe!
			}	
		}
		$this->core->log( "👻 File {$path} not found in Media Library." );
		return false;
	}
}