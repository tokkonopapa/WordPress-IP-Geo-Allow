<?php // Copyright 2017 Dragan Đurić <dragan.djuritj@gmail.com> All rights reserved
namespace Com\Logotronic\Plugin\v0;
defined ('ABSPATH') || die(-1);

if (!class_exists(__NAMESPACE__.'\AbstractPlugin')) {
	abstract class AbstractPlugin {

		protected static $file;
		protected static $path;
		protected static $basename;
		protected static $slug;
		protected static $prefix;
		protected static $optionId;
		protected static $languageDir;
		private static $initialized = false;

		protected function __construct ($file) {
			#if (!self::$initialized && is_string ($file) && $file !== __FILE__ && file_exists ($file)) {
				self::$file = $file;
				self::$path = plugin_dir_path (self::$file);
				self::$basename = plugin_basename (self::$file);
				self::$slug = dirname (self::$basename);
				self::$prefix = preg_replace ('/^[^_a-zA-Z\x80-\xff]/', '_', self::$slug);
				self::$prefix = preg_replace ('/[^_a-zA-Z\x80-\xff\d]/', '_', self::$prefix);
				self::$optionId = self::$prefix . '_option';
				self::$languageDir = self::$slug . '/languages';
				self::$initialized = true;
			#} else {
			#	throw new \Exception (__CLASS__.':__construct(): invalid argument');
			#}
		}

		public static function getFile () { return self::$file; }
		public static function getPath () { return self::$path; }
		public static function getBasename () { return self::$basename; }

		/* Slug is identical to (unique!) plugin directory name (path excluded) */
		/* Use 'slug' for string prefix or Text Domain Identifier (i10n) */
		public static function getSlug () { return self::$slug; }

		/* Prefix is identical to (unique!) plugin directory name (path excluded) */
		/* Unacceptable directory characters replaced with '_' (underscore) */
		/* Use 'prefix' for PHP Names */
		public static function getPrefix () { return self::$prefix; }
		public static function getOptionId () { return self::$optionId; }
		public static function getLanguageDir () { return self::$languageDir; }
	}
}
