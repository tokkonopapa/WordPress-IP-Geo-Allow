<?php
namespace Com\Logotronic\Plugin\v0;
defined ('ABSPATH') || die(-1);

if (!class_exists(__NAMESPACE__.'\Emit')) {
	final class Emit {

		private static function notice ($message, $class) {
			if (is_admin()) {
				add_action ('admin_notices', function () use ($class, $message) {
					printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
				});
			}
		}

		static function noticeInfo ($message) {
			self::notice ($message, 'notice notice-info is-dismissible');
		}

		static function noticeError ($message) {
			self::notice ($message, 'notice notice-error is-dismissible');
		}

		static function noticeSuccess ($message) {
			self::notice ($message, 'notice notice-success is-dismissible');
		}

		static function debug ($message) {
			if (defined (WP_DEBUG) && WP_DEBUG === true) {
				error_log ('debug: '.$message);
				self::noticeInfo ($message);
			}
		}

		static function error ($message) {
			error_log ('error: '.$message);
			self::noticeError ($message);
		}

	}
}
